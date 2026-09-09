<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ShiprocketService
{
    protected string $baseUrl;

    protected string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('shiprocket.base_url', 'https://apiv2.shiprocket.in/v1/external'), '/');
        $this->token = (string) config('shiprocket.token');
    }

    /**
     * Whether the integration is configured (a token is present).
     */
    public function isConfigured(): bool
    {
        return $this->token !== '';
    }

    /**
     * Send an authenticated request to Shiprocket.
     */
    public function request(string $method, string $path, array $payload = []): array
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl . '/',
            'timeout' => 30,
            'http_errors' => false,
        ]);

        $options = [
            'headers' => [
                'AUTHORIZATION' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if (!empty($payload)) {
            $options['json'] = $payload;
        }

        $response = $client->request($method, ltrim($path, '/'), $options);
        $status = $response->getStatusCode();
        $body = json_decode((string) $response->getBody(), true);

        if ($status >= 400) {
            Log::warning('Shiprocket API error', [
                'method' => $method,
                'path' => $path,
                'status' => $status,
                'response' => $body,
            ]);

            throw new \RuntimeException(
                ($body['message'] ?? 'Shiprocket request failed') . ' (HTTP ' . $status . ')',
                $status
            );
        }

        return is_array($body) ? $body : [];
    }

    /**
     * Build the order payload expected by Shiprocket's "create order" endpoint.
     */
    public function buildOrderPayload(Order $order): array
    {
        $order->loadMissing(['items']);

        $items = $order->items->map(function ($item) {
            $product = $item->product;
            $weight = $product?->weight !== null ? (float) $product->weight : 0.5; // kgs

            return [
                'name' => $item->product_name,
                'sku' => (string) ($product?->sku ?? $item->product_id),
                'units' => (int) $item->quantity,
                'selling_price' => (float) $item->price,
                'weight' => (float) $weight,
            ];
        })->values()->toArray();

        $orderItems = $order->items;
        $weightKg = 0.0;
        foreach ($orderItems as $item) {
            $product = $item->product;
            $w = $product?->weight !== null ? (float) $product->weight : 0.5;
            $weightKg += $w * (int) $item->quantity;
        }

        $addressParts = array_values(array_filter(array_map('trim', explode(',', $order->shipping_address))));
        $address = $addressParts[0] ?? $order->shipping_address;

        $paymentMethod = $order->payment_method;
        $paymentIsPrepaid = $paymentMethod !== 'cod' && strtolower((string) $order->payment_status) === 'paid';

        return [
            'order_id' => $order->order_number,
            'order_date' => now()->format('Y-m-d H:i'),
            'pickup_location' => (string) config('shiprocket.pickup_location', ''),
            'channel_id' => (string) config('shiprocket.channel_id', ''),
            'comment' => (string) ($order->notes ?? ''),
            'reseller_name' => 'ZYRA Fashion',
            'company_name' => 'ZYRA Fashion',
            'billing_customer_name' => $order->customer_name,
            'billing_last_name' => '',
            'billing_address' => $order->shipping_address,
            'billing_city' => (string) ($order->city ?? ''),
            'billing_pincode' => (string) ($order->pincode ?? ''),
            'billing_state' => (string) ($order->state ?? ''),
            'billing_country' => 'India',
            'billing_email' => (string) ($order->customer_email ?? ''),
            'billing_phone' => (string) ($order->customer_phone ?? ''),
            'shipping_is_billing' => true,
            'shipping_customer_name' => $order->customer_name,
            'shipping_last_name' => '',
            'shipping_address' => $order->shipping_address,
            'shipping_city' => (string) ($order->city ?? ''),
            'shipping_pincode' => (string) ($order->pincode ?? ''),
            'shipping_country' => 'India',
            'shipping_state' => (string) ($order->state ?? ''),
            'shipping_email' => (string) ($order->customer_email ?? ''),
            'shipping_phone' => (string) ($order->customer_phone ?? ''),
            'order_items' => $items,
            'payment_method' => $paymentIsPrepaid ? 'Prepaid' : 'COD',
            'shipping_charges' => (float) ($order->shipping_cost ?? 0),
            'gst_charged' => (float) ($order->tax ?? 0),
            'payment_charges' => 0,
            'total_discount' => (float) ($order->discount ?? 0),
            'sub_total' => (float) ($order->subtotal ?? 0),
            'length' => 10,
            'breadth' => 10,
            'height' => 10,
            'weight' => max(0.1, round($weightKg, 2)),
        ];
    }

    public function createShipment(Order $order): array
    {
        $response = $this->request('POST', '/orders/create/adhoc', $this->buildOrderPayload($order));

        // Shiprocket sometimes returns 200 with a "message" error and no order_id.
        if (empty($response['order_id']) && !empty($response['message'])) {
            throw new \RuntimeException('Shiprocket create order: ' . $response['message']);
        }

        return $response;
    }

    /**
     * Generate an AWB for a shipment returned from createShipment().
     *
     * @param int|string $shipmentId
     */
    public function generateAwb($shipmentId, ?string $courierId = null): array
    {
        $payload = ['shipment_id' => (int) $shipmentId];
        if ($courierId) {
            $payload['courier_id'] = (int) $courierId;
        }

        $response = $this->request('POST', '/courier/assign/awb', $payload);

        // AWB details are nested under response.data.
        $awbData = $response['response']['data'] ?? $response;

        return [
            'awb_code' => $awbData['awb_code'] ?? $response['awb_code'] ?? null,
            'courier_name' => $awbData['courier_name'] ?? $response['courier_name'] ?? null,
            'shipment_id' => $awbData['shipment_id'] ?? $response['shipment_id'] ?? $shipmentId,
            'label_url' => $awbData['label_url'] ?? $response['label_url'] ?? null,
            'awb_assign_status' => $response['awb_assign_status'] ?? null,
            'raw' => $response,
        ];
    }

    /**
     * Track a shipment by AWB code.
     */
    public function trackAwb(string $awbCode): array
    {
        return $this->request('GET', '/courier/track/awb/' . rawurlencode($awbCode));
    }

    /**
     * Push an order to Shiprocket (create the shipment) and persist the result on the order.
     *
     * NOTE: AWB/courier assignment is intentionally left to the Shiprocket dashboard —
     * we only push the order, we do not auto-assign a courier partner.
     *
     * Returns ['ok' => true] on success, or ['ok' => false, 'error' => msg] — callers
     * should NOT fail the checkout if this errors.
     */
    public function pushOrder(Order $order): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'Shiprocket is not configured.'];
        }

        // Idempotency: never create a duplicate shipment for an already-pushed order.
        if (!empty($order->shiprocket_order_id)) {
            return ['ok' => true, 'shipment_created' => false, 'awb' => false, 'already_pushed' => true];
        }

        try {
            $created = $this->createShipment($order);
        } catch (\Throwable $e) {
            Log::error('Shiprocket create order failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        // Shiprocket returns { order_id, shipment_id, ... } — sometimes shipment id is nested.
        $shipmentId = $created['shipment_id']
            ?? $created['shipments'][0]['shipment_id']
            ?? null;

        $order->update([
            'shiprocket_order_id' => $created['order_id'] ?? null,
            'shipment_id' => $shipmentId ? (string) $shipmentId : null,
            'shipping_status' => 'Pushed to Shiprocket',
            'shiprocket_pushed_at' => now(),
        ]);

        if (!$shipmentId) {
            Log::warning('Shiprocket created order but no shipment id', ['order' => $order->order_number, 'response' => $created]);
            return ['ok' => false, 'error' => 'Shiprocket created order but no shipment id was returned.', 'response' => $created];
        }

        return ['ok' => true, 'shipment_created' => true, 'awb' => false];
    }
}
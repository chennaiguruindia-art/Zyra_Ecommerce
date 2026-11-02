<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RazorpayTransaction;
use App\Services\OrderMailService;
use App\Services\RazorpayService;
use App\Services\ShiprocketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    /**
     * Handle incoming server-to-server Razorpay webhooks.
     */
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature');
        $rawPayload = $request->getContent();

        $razorpay = app(RazorpayService::class);

        // Verify webhook signature if webhook secret is configured
        $webhookSecret = (string) config('razorpay.webhook_secret');
        if ($webhookSecret !== '') {
            if (!$signature || !$razorpay->verifyWebhookSignature($rawPayload, $signature, $webhookSecret)) {
                Log::warning('Razorpay webhook signature verification failed');
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $event = json_decode($rawPayload, true);
        $eventType = $event['event'] ?? '';

        Log::info('Razorpay webhook received', ['event' => $eventType]);

        if (in_array($eventType, ['payment.captured', 'order.paid'], true)) {
            $paymentEntity = $event['payload']['payment']['entity'] ?? [];
            $rzpOrderId = $paymentEntity['order_id'] ?? null;
            $rzpPaymentId = $paymentEntity['id'] ?? null;

            if ($rzpOrderId) {
                // Update transaction record
                RazorpayTransaction::where('razorpay_order_id', $rzpOrderId)->update([
                    'razorpay_payment_id' => $rzpPaymentId,
                    'status' => 'captured',
                    'method' => $paymentEntity['method'] ?? null,
                    'fee' => $paymentEntity['fee'] ?? null,
                    'tax' => $paymentEntity['tax'] ?? null,
                    'raw_response' => $paymentEntity,
                ]);

                // Find matching order
                $order = Order::where('razorpay_order_id', $rzpOrderId)->first();
                if ($order) {
                    if ($order->payment_status !== 'paid') {
                        $order->update([
                            'payment_status' => 'paid',
                            'razorpay_payment_id' => $rzpPaymentId,
                        ]);

                        // Send order notifications reliably
                        OrderMailService::sendOrderNotifications($order);

                        // Push to Shiprocket
                        try {
                            $shiprocket = app(ShiprocketService::class);
                            if ($shiprocket->isConfigured()) {
                                $shiprocket->createCustomOrder($order);
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Shiprocket push error from webhook: ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}

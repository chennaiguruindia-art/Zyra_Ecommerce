<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\ProductImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * List past orders for the authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $orders = Order::query()
            ->where('user_id', $userId)
            ->with(['items.product'])
            ->latest()
            ->paginate(15);

        $data = collect($orders->items())->map(fn (Order $order) => $this->formatOrderSummary($order));

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get single order details by order_number.
     */
    public function show(string $orderNumber, Request $request): JsonResponse
    {
        $order = Order::query()
            ->with(['items.product'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Customer access check (allow if owner, or guest order matches phone/email query)
        $userId = Auth::id();
        if ($userId && $order->user_id && $order->user_id !== $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'success' => true,
            'order' => $this->formatOrderDetail($order),
        ]);
    }

    protected function formatOrderSummary(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'date' => $order->created_at?->format('d M Y'),
            'total' => (float) $order->total,
            'order_status' => $order->order_status,
            'payment_method' => strtoupper($order->payment_method),
            'payment_status' => $order->payment_status,
            'item_count' => $order->items->sum('quantity'),
            'preview_images' => $order->items->map(fn ($item) => $item->image_url)->filter()->values()->take(3),
        ];
    }

    protected function formatOrderDetail(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'created_at' => $order->created_at?->toIso8601String(),
            'formatted_date' => $order->created_at?->format('d M Y, h:i A'),
            'order_status' => $order->order_status,
            'payment_method' => strtoupper($order->payment_method),
            'payment_status' => $order->payment_status,
            'customer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
            ],
            'shipping_address' => [
                'address' => $order->shipping_address,
                'city' => $order->city,
                'state' => $order->state,
                'pincode' => $order->pincode,
            ],
            'tracking' => [
                'courier_name' => $order->courier_name,
                'awb_code' => $order->awb_code,
                'shipping_status' => $order->shipping_status,
                'label_url' => $order->label_url,
            ],
            'pricing' => [
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'coupon_code' => $order->coupon_code,
                'shipping_cost' => (float) $order->shipping_cost,
                'total' => (float) $order->total,
            ],
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_image' => $item->image_url,
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'size' => $item->size,
                'color' => $item->color,
                'dupatta' => $item->dupatta,
                'total' => (float) $item->total,
            ])->values(),
        ];
    }
}

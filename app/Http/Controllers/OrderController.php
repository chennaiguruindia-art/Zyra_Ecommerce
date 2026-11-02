<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function success(string $orderNumber)
    {
        $order = Order::query()
            ->with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Protect customer PII: allow only order owner, seller/admin, or session that just completed checkout
        $isOwner = auth()->check() && ($order->user_id === auth()->id() || auth()->user()->isSeller());
        $isSessionOrder = session('recent_placed_order') === $orderNumber;

        if (!$isOwner && !$isSessionOrder) {
            if (!auth()->check()) {
                return redirect()->route('login')->with('message', 'Please log in to view your order details.');
            }
            abort(403, 'You are not authorized to view this order.');
        }

        return view('order-success', compact('order'));
    }

    public function myOrders()
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->with('items')
            ->latest()
            ->get();

        return view('my-orders', compact('orders'));
    }
}

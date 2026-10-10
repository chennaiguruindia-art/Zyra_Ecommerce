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

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;

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

        $userReviews = Review::query()
            ->where('user_id', auth()->id())
            ->get()
            ->keyBy('product_id');

        return view('my-orders', compact('orders', 'userReviews'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('message', 'Please login to proceed to checkout.');
        }

        $user = auth()->user();
        $latestOrder = Order::where('user_id', $user->id)->latest()->first();

        $nameParts = explode(' ', trim($user->name));
        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        $customer = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'phone' => $user->phone_number ?? $latestOrder?->customer_phone ?? '',
            'address' => $user->address ?? $latestOrder?->shipping_address ?? '',
            'apartment' => $user->nearby_area ?? '',
            'city' => $user->district ?? $latestOrder?->city ?? '',
            'state' => $user->state ?? $latestOrder?->state ?? '',
            'pincode' => $user->pincode ?? $latestOrder?->pincode ?? '',
        ];

        $cartItems = (new CartController())->resolvedCart();
        $cartSubtotal = 0;
        foreach ($cartItems as $item) {
            $cartSubtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }
        $cartShipping = ($cartSubtotal === 0 || $cartSubtotal >= 999) ? 0 : 99;
        $cartTotal = $cartSubtotal + $cartShipping;

        return view('checkout', [
            'user' => $user,
            'customer' => $customer,
            'cartItems' => $cartItems,
            'cartSubtotal' => $cartSubtotal,
            'cartShipping' => $cartShipping,
            'cartTotal' => $cartTotal,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'apartment' => 'nullable|string|max:200',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'payment_method' => 'required|in:cod,upi,card,netbanking',
            'items' => 'nullable|array',
            'items.*.id' => 'required_with:items|integer',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $cartItems = array_values(session()->get('cart', []));
            if (empty($cartItems) && !empty($data['items'])) {
                (new CartController())->replaceCartFromItems($data['items']);
                $cartItems = array_values(session()->get('cart', []));
            }

            $subtotal = 0;
            $lineItems = [];

            foreach ($cartItems as $item) {
                $product = Product::query()->find($item['id'] ?? 0);
                if (!$product) {
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) $product->price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'price' => $price,
                    'total' => $lineTotal,
                ];
            }

            if (empty($lineItems)) {
                abort(422, 'Your cart is empty or products are no longer available.');
            }

            $discount = 0;
            $couponCode = strtoupper(trim((string) ($data['coupon_code'] ?? '')));
            if ($couponCode !== '') {
                $coupon = Coupon::query()->where('code', $couponCode)->first();
                if ($coupon && $coupon->isValidFor($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                    $coupon->increment('used_count');
                }
            }

            $shipping = $subtotal >= 999 ? 0 : 99;
            $total = max(0, $subtotal - $discount + $shipping);

            $paymentMethod = $data['payment_method'];
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => auth()->id(),
                'customer_name' => trim($data['first_name'] . ' ' . $data['last_name']),
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'],
                'shipping_address' => trim($data['address'] . (!empty($data['apartment']) ? ', ' . $data['apartment'] : '')),
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentMethod === 'cod' ? 'pending' : 'paid',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shipping,
                'tax' => 0,
                'total' => $total,
                'coupon_code' => $couponCode !== '' ? $couponCode : null,
                'order_status' => 'Pending',
                'notes' => $request->input('notes'),
            ]);

            foreach ($lineItems as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'product_image' => $line['product']->image,
                    'price' => $line['price'],
                    'quantity' => $line['quantity'],
                    'size' => $line['size'],
                    'color' => $line['color'],
                    'total' => $line['total'],
                ]);

                $newStock = max(0, $line['product']->stock_units - $line['quantity']);
                $line['product']->update([
                    'stock_units' => $newStock,
                    'in_stock' => $newStock > 0,
                ]);
            }

            session()->forget(['cart', 'coupon']);

            return $order;
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'redirect' => route('order.success', $order->order_number),
            ]);
        }

        return redirect()->route('order.success', $order->order_number);
    }
}

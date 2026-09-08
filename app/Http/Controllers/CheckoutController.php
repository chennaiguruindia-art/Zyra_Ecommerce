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
        return view('checkout');
    }

    public function placeOrder(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'apartment' => 'nullable|string|max:200',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'payment_method' => 'required|in:cod,upi,card,netbanking',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $subtotal = 0;
            $lineItems = [];

            foreach ($data['items'] as $item) {
                $product = Product::query()->find($item['id']);
                if (!$product) {
                    continue;
                }

                $qty = (int) $item['quantity'];
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

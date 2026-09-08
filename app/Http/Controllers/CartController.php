<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'size' => 'nullable|string',
            'color' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::query()->with(['category', 'sizes', 'colors', 'images'])->findOrFail($data['product_id']);

        if (!$product->in_stock || $product->stock_units < ($data['quantity'] ?? 1)) {
            return response()->json(['success' => false, 'message' => 'This item is currently out of stock.'], 422);
        }

        $catalog = $product->toCatalogArray();
        $cart = session()->get('cart', []);
        $key = $product->id . '|' . ($data['size'] ?? 'M') . '|' . ($data['color'] ?? ($catalog['colors'][0] ?? 'Standard'));

        $qty = (int) ($data['quantity'] ?? 1);
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'old_price' => $product->old_price ? (float) $product->old_price : null,
                'image' => $catalog['image'],
                'category' => $catalog['category'],
                'size' => $data['size'] ?? ($catalog['sizes'][0] ?? 'M'),
                'color' => $data['color'] ?? ($catalog['colors'][0] ?? 'Standard'),
                'quantity' => $qty,
            ];
        }

        session(['cart' => $cart]);

        return response()->json([
            'success' => true,
            'message' => '"' . $product->name . '" added to cart!',
            'cart' => array_values($cart),
            'count' => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    public function items()
    {
        $cart = array_values(session()->get('cart', []));

        foreach ($cart as &$item) {
            $product = Product::query()->with(['images', 'category'])->find($item['id']);
            if ($product) {
                $item['price'] = (float) $product->price;
                $item['old_price'] = $product->old_price ? (float) $product->old_price : null;
                $item['image'] = $product->image_url;
                $item['name'] = $product->name;
            }
        }

        return response()->json(['items' => $cart]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'index' => 'required',
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = session()->get('cart', []);
        $keys = array_keys($cart);
        $index = (int) $data['index'];

        if (!isset($keys[$index])) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $key = $keys[$index];
        if ($data['quantity'] <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = $data['quantity'];
        }

        session(['cart' => $cart]);

        return response()->json(['success' => true, 'cart' => array_values($cart)]);
    }

    public function remove(Request $request)
    {
        $index = (int) $request->input('index');
        $cart = session()->get('cart', []);
        $keys = array_keys($cart);

        if (isset($keys[$index])) {
            unset($cart[$keys[$index]]);
            session(['cart' => $cart]);
        }

        return response()->json(['success' => true, 'cart' => array_values($cart)]);
    }

    public function applyCoupon(Request $request)
    {
        $code = strtoupper(trim((string) $request->input('code')));
        $subtotal = (float) $request->input('subtotal', 0);

        $coupon = Coupon::query()->where('code', $code)->first();

        if (!$coupon || !$coupon->isValidFor($subtotal)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code. Try WELCOME10, SAVE20 or FASHION15',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);
        session(['coupon' => [
            'code' => $coupon->code,
            'discount' => (float) $coupon->discount_value,
            'type' => $coupon->discount_type,
            'label' => $coupon->label,
            'amount' => $discount,
        ]]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon "' . $coupon->code . '" applied successfully!',
            'coupon' => [
                'code' => $coupon->code,
                'discount' => (float) $coupon->discount_value,
                'type' => $coupon->discount_type,
                'label' => $coupon->label,
                'amount' => $discount,
            ],
        ]);
    }

    public function removeCoupon()
    {
        session()->forget('coupon');

        return response()->json(['success' => true, 'message' => 'Coupon removed']);
    }
}

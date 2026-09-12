<?php

namespace App\Http\Controllers;

use App\Models\Cart;
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
        $qty = (int) ($data['quantity'] ?? 1);
        $size = $data['size'] ?? null;

        if (!$product->in_stock) {
            return response()->json(['success' => false, 'message' => 'This item is currently out of stock.'], 422);
        }

        // Per-size availability check (falls back to the product total for legacy sizes).
        $availableForSize = $product->stockForSize($size);
        if ($availableForSize <= 0) {
            $sizeLabel = $size ? " Size {$size} is" : 'This item is';
            return response()->json(['success' => false, 'message' => "{$sizeLabel} currently out of stock."], 422);
        }

        $catalog = $product->toCatalogArray();
        $cart = session()->get('cart', []);
        $key = $product->id . '|' . ($size ?? 'M') . '|' . ($data['color'] ?? ($catalog['colors'][0] ?? 'Standard'));

        $existingQty = isset($cart[$key]) ? (int) $cart[$key]['quantity'] : 0;
        if ($existingQty + $qty > $availableForSize) {
            $left = max(0, $availableForSize - $existingQty);
            return response()->json([
                'success' => false,
                'message' => $left <= 0
                    ? "No more stock available for size {$size}."
                    : "Only {$left} unit(s) left in size {$size}.",
            ], 422);
        }

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
                'size' => $size ?? ($catalog['sizes'][0] ?? 'M'),
                'color' => $data['color'] ?? ($catalog['colors'][0] ?? 'Standard'),
                'quantity' => $qty,
            ];
        }

        session(['cart' => $cart]);

        return response()->json([
            'success' => true,
            'message' => '"' . $product->name . '" added to cart!',
            'cart' => $this->resolvedCart(),
            'count' => array_sum(array_column($this->resolvedCart(), 'quantity')),
        ]);
    }

    public function sync(Request $request)
    {
        $data = $request->validate([
            'items' => 'nullable|array',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
        ]);

        $items = $data['items'] ?? [];
        if (!empty($items)) {
            $this->replaceCartFromItems($items);
        }

        return response()->json([
            'success' => true,
            'cart' => $this->resolvedCart(),
            'count' => array_sum(array_column($this->resolvedCart(), 'quantity')),
        ]);
    }

    public function replaceCartFromItems(array $items): void
    {
        $cart = [];

        foreach ($items as $item) {
            $product = Product::query()
                ->with(['category', 'sizes', 'colors', 'images'])
                ->find($item['id'] ?? 0);

            if (!$product) {
                continue;
            }

            $catalog = $product->toCatalogArray();
            $size = $item['size'] ?? ($catalog['sizes'][0] ?? 'M');
            $color = $item['color'] ?? ($catalog['colors'][0] ?? 'Standard');
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $key = $product->id . '|' . $size . '|' . $color;

            if (isset($cart[$key])) {
                $cart[$key]['quantity'] += $qty;
                continue;
            }

            $cart[$key] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'old_price' => $product->old_price ? (float) $product->old_price : null,
                'image' => $catalog['image'],
                'category' => $catalog['category'],
                'size' => $size,
                'color' => $color,
                'quantity' => $qty,
            ];
        }

        session(['cart' => $cart]);
    }

    public function items()
    {
        return response()->json(['items' => $this->resolvedCart()]);
    }

    /**
     * Save the current cart (with the visitor's email) so an abandoned-cart
     * reminder can be sent later. Idempotent per session; updates last activity.
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:190',
            'items' => 'nullable|array',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
        ]);

        $items = $data['items'] ?? [];
        if (!empty($items)) {
            $this->replaceCartFromItems($items);
        }

        $cartItems = $this->resolvedCart();
        if (empty($cartItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty. Add items before saving.',
            ], 422);
        }

        $record = $this->updateOrCreateCartRecord($data['email'], $cartItems);

        return response()->json([
            'success' => true,
            'message' => 'Your cart is saved! We\'ll remind you if you forget it.',
            'cart_id' => $record->id,
        ]);
    }

    /**
     * Upsert the persistent cart record used by the abandoned-cart reminder.
     */
    public function updateOrCreateCartRecord(string $email, array $cartItems): Cart
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        $record = Cart::query()
            ->where('user_id', $userId)
            ->orWhere(function ($q) use ($sessionId) {
                $q->whereNotNull('session_id')->where('session_id', $sessionId);
            })
            ->active()
            ->latest()
            ->first();

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        if ($record) {
            $record->update([
                'email' => $email,
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'last_activity_at' => now(),
            ]);

            return $record;
        }

        return Cart::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'email' => $email,
            'items' => $cartItems,
            'subtotal' => $subtotal,
            'status' => Cart::STATUS_ACTIVE,
            'last_activity_at' => now(),
            'reminder_count' => 0,
        ]);
    }

    public function resolvedCart(): array
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

        return $cart;
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

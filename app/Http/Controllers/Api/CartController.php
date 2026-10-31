<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get current cart items and totals.
     */
    public function index(Request $request): JsonResponse
    {
        $cartRecord = $this->resolveCartRecord($request);
        $items = $this->resolveItemsWithLiveProducts($cartRecord->items ?? []);

        $couponCode = $request->input('coupon_code') ?: ($cartRecord->coupon_code ?? null);
        $pricing = $this->calculateTotals($items, $couponCode, Auth::id());

        return response()->json([
            'success' => true,
            'items' => array_values($items),
            'count' => array_sum(array_column($items, 'quantity')),
            'subtotal' => $pricing['subtotal'],
            'discount' => $pricing['discount'],
            'coupon_code' => $pricing['coupon_code'],
            'shipping' => $pricing['shipping'],
            'total' => $pricing['total'],
        ]);
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'size' => 'nullable|string',
            'color' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'dupatta' => 'nullable|boolean',
        ]);

        $product = Product::query()
            ->withoutGlobalScope(\App\Models\Scopes\ProductVisibleScope::class)
            ->with(['category', 'sizes', 'colors', 'images'])
            ->find($data['product_id']);

        if (!$product || !$product->is_visible) {
            return response()->json(['success' => false, 'message' => 'This product is no longer available.'], 422);
        }

        if (!$product->in_stock) {
            return response()->json(['success' => false, 'message' => 'This product is out of stock.'], 422);
        }

        $size = $data['size'] ?? ($product->sizes->first()?->name ?? 'M');
        $color = $data['color'] ?? ($product->colors->first()?->name ?? 'Standard');
        $qty = (int) ($data['quantity'] ?? 1);
        $withDupatta = filter_var($data['dupatta'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $availableStock = $product->stockForSize($size);
        if ($availableStock <= 0) {
            return response()->json(['success' => false, 'message' => "Size {$size} is currently out of stock."], 422);
        }

        $cartRecord = $this->resolveCartRecord($request);
        $items = $cartRecord->items ?? [];

        $key = $product->id . '|' . $size . '|' . $color . '|' . ($withDupatta ? '1' : '0');
        $existingQty = isset($items[$key]) ? (int) $items[$key]['quantity'] : 0;

        if ($existingQty + $qty > $availableStock) {
            $left = max(0, $availableStock - $existingQty);
            return response()->json([
                'success' => false,
                'message' => $left <= 0
                    ? "Maximum available quantity for size {$size} reached."
                    : "Only {$left} unit(s) left in size {$size}.",
            ], 422);
        }

        $unitPrice = $withDupatta ? (float) $product->price : $product->withoutDupattaPrice();

        if (isset($items[$key])) {
            $items[$key]['quantity'] += $qty;
        } else {
            $items[$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $unitPrice,
                'quantity' => $qty,
                'size' => $size,
                'color' => $color,
                'dupatta' => $withDupatta,
                'image' => $product->image_url,
            ];
        }

        $cartRecord->items = $items;
        $cartRecord->last_activity_at = now();
        $cartRecord->save();

        $resolved = $this->resolveItemsWithLiveProducts($items);
        $pricing = $this->calculateTotals($resolved, null, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart.',
            'items' => array_values($resolved),
            'total' => $pricing['total'],
        ]);
    }

    /**
     * Update item quantity.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $key = $request->input('key');
        $qty = (int) $request->input('quantity');

        $cartRecord = $this->resolveCartRecord($request);
        $items = $cartRecord->items ?? [];

        if (!isset($items[$key])) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
        }

        if ($qty <= 0) {
            unset($items[$key]);
        } else {
            $item = $items[$key];
            $product = Product::query()->find($item['product_id'] ?? $item['id']);
            if ($product) {
                $available = $product->stockForSize($item['size'] ?? null);
                if ($qty > $available) {
                    return response()->json([
                        'success' => false,
                        'message' => "Only {$available} unit(s) available for this size.",
                    ], 422);
                }
            }
            $items[$key]['quantity'] = $qty;
        }

        $cartRecord->items = $items;
        $cartRecord->last_activity_at = now();
        $cartRecord->save();

        $resolved = $this->resolveItemsWithLiveProducts($items);
        $pricing = $this->calculateTotals($resolved, null, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Cart updated.',
            'items' => array_values($resolved),
            'total' => $pricing['total'],
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate(['key' => 'required|string']);

        $key = $request->input('key');
        $cartRecord = $this->resolveCartRecord($request);
        $items = $cartRecord->items ?? [];

        if (isset($items[$key])) {
            unset($items[$key]);
            $cartRecord->items = $items;
            $cartRecord->save();
        }

        $resolved = $this->resolveItemsWithLiveProducts($items);
        $pricing = $this->calculateTotals($resolved, null, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'items' => array_values($resolved),
            'total' => $pricing['total'],
        ]);
    }

    /**
     * Sync local cart from mobile app.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'items.*.dupatta' => 'nullable|boolean',
        ]);

        $cartRecord = $this->resolveCartRecord($request);
        $syncedItems = [];

        foreach ($request->input('items') as $raw) {
            $product = Product::query()->with(['sizes', 'colors', 'images'])->find($raw['product_id']);
            if (!$product || !$product->is_visible || !$product->in_stock) {
                continue;
            }

            $size = $raw['size'] ?? ($product->sizes->first()?->name ?? 'M');
            $color = $raw['color'] ?? ($product->colors->first()?->name ?? 'Standard');
            $withDupatta = filter_var($raw['dupatta'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $available = $product->stockForSize($size);

            if ($available <= 0) {
                continue;
            }

            $qty = min((int) $raw['quantity'], $available);
            $key = $product->id . '|' . $size . '|' . $color . '|' . ($withDupatta ? '1' : '0');
            $unitPrice = $withDupatta ? (float) $product->price : $product->withoutDupattaPrice();

            $syncedItems[$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $unitPrice,
                'quantity' => $qty,
                'size' => $size,
                'color' => $color,
                'dupatta' => $withDupatta,
                'image' => $product->image_url,
            ];
        }

        $cartRecord->items = $syncedItems;
        $cartRecord->last_activity_at = now();
        $cartRecord->save();

        $pricing = $this->calculateTotals($syncedItems, null, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Cart synchronized.',
            'items' => array_values($syncedItems),
            'total' => $pricing['total'],
        ]);
    }

    /**
     * Apply coupon code for mobile cart.
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) $request->input('code')));
        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'Please enter coupon code.'], 422);
        }

        $cartRecord = $this->resolveCartRecord($request);
        $items = $this->resolveItemsWithLiveProducts($cartRecord->items ?? []);

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += ((float) $item['price']) * ((int) $item['quantity']);
        }

        $coupon = Coupon::query()->where('code', $code)->first();
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 422);
        }

        $userId = Auth::id();
        if (!$coupon->isValidFor($subtotal, $userId)) {
            return response()->json(['success' => false, 'message' => 'This coupon is not valid for your cart.'], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);
        $finalTotal = max(0, $subtotal - $discount);

        return response()->json([
            'success' => true,
            'message' => "Coupon '{$code}' applied successfully!",
            'coupon' => [
                'code' => $coupon->code,
                'discount' => round($discount, 2),
                'discount_type' => $coupon->discount_type,
            ],
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round($finalTotal, 2),
        ]);
    }

    /**
     * Resolve cart DB record for user or device session.
     */
    protected function resolveCartRecord(Request $request): Cart
    {
        $user = Auth::user();
        $deviceId = $request->header('X-Device-ID') ?: $request->input('device_id') ?: session()->getId();

        if ($user) {
            $cart = Cart::query()->where('user_id', $user->id)->where('status', Cart::STATUS_ACTIVE)->first();
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                    'session_id' => $deviceId,
                    'status' => Cart::STATUS_ACTIVE,
                    'items' => [],
                    'last_activity_at' => now(),
                ]);
            }
            return $cart;
        }

        $cart = Cart::query()->where('session_id', $deviceId)->where('status', Cart::STATUS_ACTIVE)->first();
        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $deviceId,
                'status' => Cart::STATUS_ACTIVE,
                'items' => [],
                'last_activity_at' => now(),
            ]);
        }
        return $cart;
    }

    /**
     * Refresh prices & details against database to prevent stale client tampering.
     */
    protected function resolveItemsWithLiveProducts(array $items): array
    {
        $resolved = [];
        foreach ($items as $k => $item) {
            $productId = $item['product_id'] ?? $item['id'] ?? 0;
            $product = Product::query()->with(['sizes', 'images'])->find($productId);
            if (!$product || !$product->is_visible) {
                continue;
            }

            $size = $item['size'] ?? null;
            $color = $item['color'] ?? 'Standard';
            $withDupatta = filter_var($item['dupatta'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $price = $withDupatta ? (float) $product->price : $product->withoutDupattaPrice();

            $resolved[$k] = [
                'key' => $k,
                'product_id' => $product->id,
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $price,
                'quantity' => (int) ($item['quantity'] ?? 1),
                'size' => $size,
                'color' => $color,
                'dupatta' => $withDupatta,
                'image' => $product->image_url,
                'stock' => $product->stockForSize($size),
            ];
        }
        return $resolved;
    }

    protected function calculateTotals(array $items, ?string $couponCode, ?int $userId): array
    {
        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += ((float) $item['price']) * ((int) $item['quantity']);
        }

        $discount = 0.0;
        $appliedCoupon = null;

        if ($couponCode) {
            $coupon = Coupon::query()->where('code', strtoupper($couponCode))->first();
            if ($coupon && $coupon->isValidFor($subtotal, $userId)) {
                $discount = $coupon->calculateDiscount($subtotal);
                $appliedCoupon = $coupon->code;
            }
        }

        $shipping = 0.0; // Free delivery
        $total = max(0, $subtotal - $discount + $shipping);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'coupon_code' => $appliedCoupon,
            'shipping' => $shipping,
            'total' => round($total, 2),
        ];
    }
}

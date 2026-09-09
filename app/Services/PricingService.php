<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class PricingService
{
    /** GST rate as a percentage applied after coupon discount. */
    public const GST_RATE = 5.0;

    /**
     * Build a pricing breakdown for the current session cart + coupon.
     *
     * @return array{subtotal:float,discount:float,gst:float,total:float,coupon_code:?string,receipt:string,line_items:array}
     */
    public function breakdown(?string $couponCode = null, ?int $userId = null): array
    {
        $cartItems = array_values(session()->get('cart', []));

        $lineItems = [];
        $subtotal = 0.0;

        foreach ($cartItems as $item) {
            $product = Product::query()->find($item['id'] ?? 0);
            if (!$product) {
                continue;
            }
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) $product->price;
            $subtotal += $price * $qty;

            $lineItems[] = [
                'product' => $product,
                'quantity' => $qty,
                'size' => $item['size'] ?? null,
                'color' => $item['color'] ?? null,
                'price' => $price,
                'total' => $price * $qty,
            ];
        }

        if (empty($lineItems)) {
            abort(422, 'Your cart is empty or products are no longer available.');
        }

        $discount = 0.0;
        $normalized = strtoupper(trim((string) ($couponCode ?? '')));
        $coupon = null;

        if ($normalized !== '') {
            $coupon = Coupon::query()->where('code', $normalized)->first();
            if ($coupon && $coupon->isValidFor($subtotal, $userId)) {
                $discount = (float) $coupon->calculateDiscount($subtotal);
            }
        }

        // GST only applies once an order total is computed (after discount).
        $gst = round(($subtotal - $discount) * (self::GST_RATE / 100), 2);
        $total = max(0, $subtotal - $discount + $gst);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'gst' => $gst,
            'total' => round($total, 2),
            'coupon_code' => $normalized !== '' && $coupon ? $normalized : null,
            'coupon' => $coupon,
            'receipt' => Order::generateOrderNumber(),
            'line_items' => $lineItems,
        ];
    }
}

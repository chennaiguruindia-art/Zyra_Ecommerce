<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PricingService
{
    /** GST rate as a percentage applied after coupon discount. */
    public const GST_RATE = 0.0;

    /** Special Friday promo price for single Short Kurti */
    public const FRIDAY_SHORT_KURTI_PRICE = 500.0;

    /**
     * Check if Special Friday Deal is currently active.
     */
    public function isFridayDealActive(): bool
    {
        return Carbon::now()->isFriday() || config('app.force_friday_deal', false);
    }

    /**
     * Determine if a product is a Short Kurti by subcategory slug/name or category.
     */
    public function isShortKurtiProduct(?Product $product): bool
    {
        if (!$product) {
            return false;
        }

        $subName = strtolower($product->subcategory->name ?? '');
        $subSlug = strtolower($product->subcategory->slug ?? '');

        return str_contains($subName, 'short') || str_contains($subSlug, 'short');
    }

    /**
     * Build a pricing breakdown for the current session cart + coupon.
     *
     * @return array{subtotal:float,discount:float,gst:float,total:float,coupon_code:?string,receipt:string,line_items:array,friday_deal:array}
     */
    public function breakdown(?string $couponCode = null, ?int $userId = null, ?string $email = null, ?string $phone = null): array
    {
        $cartItems = array_values(session()->get('cart', []));

        $rawItems = [];
        $totalNonShortKurtiCount = 0;
        $totalShortKurtiCount = 0;

        foreach ($cartItems as $item) {
            $product = Product::query()->with('subcategory')->find($item['id'] ?? 0);
            if (!$product) {
                continue;
            }
            $qty = (int) ($item['quantity'] ?? 1);
            $withDupatta = filter_var($item['dupatta'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $price = $withDupatta ? (float) $product->price : $product->withoutDupattaPrice();

            $isShort = $this->isShortKurtiProduct($product);
            if ($isShort) {
                $totalShortKurtiCount += $qty;
            } else {
                $totalNonShortKurtiCount += $qty;
            }

            $rawItems[] = [
                'product' => $product,
                'quantity' => $qty,
                'size' => $item['size'] ?? null,
                'color' => $item['color'] ?? null,
                'dupatta' => $withDupatta,
                'price' => $price,
                'is_short_kurti' => $isShort,
            ];
        }

        if (empty($rawItems)) {
            abort(422, 'Your cart is empty or products are no longer available.');
        }

        // Friday Deal Check:
        // Friday + at least 2 non-short-kurti products in cart
        $fridayActive = $this->isFridayDealActive();
        $fridayDealEligible = $fridayActive && ($totalNonShortKurtiCount >= 2);
        $fridayDiscountTotal = 0.0;
        $fridayPieceDiscountApplied = false;

        $lineItems = [];
        $subtotal = 0.0;

        foreach ($rawItems as $raw) {
            $product = $raw['product'];
            $qty = $raw['quantity'];
            $origPrice = $raw['price'];
            $isShort = $raw['is_short_kurti'];

            // Apply ₹500 to exactly 1 piece of Short Kurti if eligible
            if ($fridayDealEligible && $isShort && !$fridayPieceDiscountApplied && $origPrice > self::FRIDAY_SHORT_KURTI_PRICE) {
                // 1 piece at 500, remaining at origPrice
                $discountPerPiece = $origPrice - self::FRIDAY_SHORT_KURTI_PRICE;
                $fridayDiscountTotal += $discountPerPiece;
                $fridayPieceDiscountApplied = true;

                $itemTotal = self::FRIDAY_SHORT_KURTI_PRICE + ($origPrice * ($qty - 1));
                $subtotal += $itemTotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'size' => $raw['size'],
                    'color' => $raw['color'],
                    'dupatta' => $raw['dupatta'],
                    'price' => $origPrice,
                    'friday_deal_price' => self::FRIDAY_SHORT_KURTI_PRICE,
                    'total' => $itemTotal,
                ];
            } else {
                $itemTotal = $origPrice * $qty;
                $subtotal += $itemTotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'size' => $raw['size'],
                    'color' => $raw['color'],
                    'dupatta' => $raw['dupatta'],
                    'price' => $origPrice,
                    'total' => $itemTotal,
                ];
            }
        }

        $discount = 0.0;
        $normalized = strtoupper(trim((string) ($couponCode ?? '')));
        $coupon = null;
        $applied = false;

        if ($normalized !== '') {
            $coupon = Coupon::query()->where('code', $normalized)->first();
            if ($coupon && $coupon->isValidFor($subtotal, $userId, $email, $phone)) {
                $applied = true;
                $discount = (float) $coupon->calculateDiscount($subtotal);
            }
        }

        // GST only applies once an order total is computed (after discount).
        $gst = round(($subtotal - $discount) * (self::GST_RATE / 100), 2);
        $total = max(0, $subtotal - $discount + $gst);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'friday_deal' => [
                'is_active_day' => $fridayActive,
                'is_eligible' => $fridayDealEligible,
                'non_short_kurti_count' => $totalNonShortKurtiCount,
                'short_kurti_count' => $totalShortKurtiCount,
                'discount_amount' => round($fridayDiscountTotal, 2),
                'unlocked' => $fridayPieceDiscountApplied,
            ],
            'gst' => $gst,
            'total' => round($total, 2),
            'coupon_code' => $applied ? $normalized : null,
            'coupon' => $coupon,
            'receipt' => Order::generateOrderNumber(),
            'line_items' => $lineItems,
        ];
    }
}


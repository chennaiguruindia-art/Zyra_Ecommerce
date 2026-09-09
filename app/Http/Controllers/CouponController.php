<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * Apply a coupon for the current user against the live session cart.
     */
    public function apply(Request $request)
    {
        $code = strtoupper(trim((string) $request->input('code')));

        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'Please enter a coupon code.'], 422);
        }

        $coupon = Coupon::query()->where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 422);
        }

        $subtotal = 0.0;
        foreach (array_values(session()->get('cart', [])) as $item) {
            $subtotal += ((float) ($item['price'] ?? 0)) * (int) ($item['quantity'] ?? 1);
        }

        if (!\App\Models\Product::query()->whereIn('id', collect(session()->get('cart', []))->pluck('id'))->count()) {
            $subtotal = 0.0;
        }

        if (!$coupon->isValidFor($subtotal, Auth::id())) {
            return $this->couponInvalidMessage($coupon);
        }

        $pricing = app(PricingService::class)->breakdown($code, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Coupon "' . $coupon->code . '" applied successfully!',
            'coupon' => [
                'code' => $coupon->code,
                'discount' => (float) $pricing['discount'],
                'type' => $coupon->discount_type,
                'label' => $coupon->label,
            ],
            'pricing' => [
                'subtotal' => $pricing['subtotal'],
                'discount' => $pricing['discount'],
                'gst' => $pricing['gst'],
                'total' => $pricing['total'],
            ],
        ]);
    }

    /**
     * Remove the applied coupon for the current user (recompute totals without it).
     */
    public function remove()
    {
        $pricing = app(PricingService::class)->breakdown(null, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed.',
            'pricing' => [
                'subtotal' => $pricing['subtotal'],
                'discount' => $pricing['discount'],
                'gst' => $pricing['gst'],
                'total' => $pricing['total'],
            ],
        ]);
    }

    protected function couponInvalidMessage(Coupon $coupon)
    {
        if (strtoupper($coupon->code) === 'FIRSTORDER') {
            $hasOrder = \App\Models\Order::where('user_id', Auth::id())->exists();
            if ($hasOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'FIRSTORDER is valid only for your very first order.',
                ], 422);
            }
        }

        if ($coupon->start_date && \Carbon\Carbon::now()->lt($coupon->start_date)) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is not active yet. Check back on ' . $coupon->start_date->format('d M Y') . '.',
            ], 422);
        }

        if ($coupon->expiry_date && \Carbon\Carbon::now()->gt($coupon->expiry_date->endOfDay())) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, this coupon expired on ' . $coupon->expiry_date->format('d M Y') . '.',
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => 'This coupon cannot be applied to your current order (check the order minimum).',
        ], 422);
    }
}

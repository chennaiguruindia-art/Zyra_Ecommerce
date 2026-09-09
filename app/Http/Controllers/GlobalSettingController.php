<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GlobalSettingController extends Controller
{
    public function index()
    {
        $products = Product::query()->with('category')->orderBy('id')->get();
        $welcomeCoupon = Coupon::query()->where('code', 'WELCOME10')->first();
        $firstOrderCoupon = Coupon::query()->where('code', 'FIRSTORDER')->first();
        $coupons = Coupon::query()->orderBy('code')->get();

        return view('global-setting', [
            'products' => $products,
            'welcomeCoupon' => $welcomeCoupon,
            'firstOrderCoupon' => $firstOrderCoupon,
            'coupons' => $coupons,
        ]);
    }

    public function toggle(Request $request)
    {
        $data = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'field' => 'required|in:is_best_seller,is_trending',
            'value' => 'required|boolean',
        ])->validate();

        $product = Product::query()->findOrFail((int) $data['product_id']);
        $product->update([
            $data['field'] => filter_var($data['value'], FILTER_VALIDATE_BOOLEAN),
        ]);

        return response()->json([
            'success' => true,
            'product' => $product->fresh()->load('category')->toCatalogArray(),
        ]);
    }

    public function updateCouponDates(Request $request)
    {
        $data = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:start_date',
        ])->validate();

        $coupon = Coupon::query()->where('code', 'WELCOME10')->first();

        if (!$coupon) {
            $coupon = Coupon::create([
                'code' => 'WELCOME10',
                'discount_type' => 'percent',
                'discount_value' => 10.00,
                'min_order_amount' => 0,
                'status' => true,
            ]);
        }

        $coupon->update([
            'start_date' => $data['start_date'],
            'expiry_date' => $data['expiry_date'],
            'status' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'WELCOME10 coupon window updated.',
            'coupon' => $coupon->fresh(),
        ]);
    }

    public function storeCoupon(Request $request)
    {
        $data = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0.01|max:10000',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|boolean',
        ])->validate();

        $code = strtoupper(trim($data['code']));

        $coupon = Coupon::query()->where('code', $code)->first();
        if (!$coupon) {
            $coupon = new Coupon(['code' => $code]);
        }

        $coupon->discount_type = $data['discount_type'];
        $coupon->discount_value = $data['discount_value'];
        $coupon->min_order_amount = $data['min_order_amount'] ?? 0;
        $coupon->max_discount = $data['max_discount'] ?? null;
        $coupon->start_date = $data['start_date'] ?? null;
        $coupon->expiry_date = $data['expiry_date'] ?? null;
        $coupon->status = ($data['status'] ?? true) ? true : false;
        $coupon->save();

        return response()->json([
            'success' => true,
            'message' => ($coupon->wasRecentlyCreated ? 'Coupon "' : 'Coupon "') . $code . '" saved.',
            'coupon' => $coupon->fresh(),
        ]);
    }

    public function destroyCoupon(Request $request, $id)
    {
        $coupon = Coupon::query()->findOrFail((int) $id);
        $code = $coupon->code;
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon "' . $code . '" deleted.',
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalSettingController extends Controller
{
    public function index()
    {
        $products = Product::query()->withoutGlobalScope(\App\Models\Scopes\ProductVisibleScope::class)->with('category')->orderBy('id')->get();
        $coupons = Coupon::query()->orderBy('code')->get();

        return view('global-setting', [
            'products' => $products,
            'coupons'  => $coupons,
        ]);
    }

    /**
     * Lightweight funnel snapshot for the Global Settings analytics panel.
     * Returns counts for Today, This Week, This Month, All Time.
     */
    public function analyticsSnapshot()
    {
        $today      = now()->toDateString();
        $weekStart  = now()->startOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        // Site visits from dedicated table
        $siteVisits = DB::table('site_visits')->select(
            DB::raw("SUM(CASE WHEN visit_date = '$today' THEN visits ELSE 0 END)              AS today"),
            DB::raw("SUM(CASE WHEN visit_date >= '$weekStart' THEN visits ELSE 0 END)         AS week"),
            DB::raw("SUM(CASE WHEN visit_date >= '$monthStart' THEN visits ELSE 0 END)        AS month"),
            DB::raw('SUM(visits)                                                               AS all_time')
        )->first();

        // Activity events from customer_activities
        $acts = DB::table('customer_activities')->select(
            // Today
            DB::raw("COUNT(CASE WHEN action='product_viewed'      AND DATE(created_at)='$today'      THEN 1 END) AS views_today"),
            DB::raw("COUNT(CASE WHEN action='wishlist_added'       AND DATE(created_at)='$today'      THEN 1 END) AS wishlist_today"),
            DB::raw("COUNT(CASE WHEN action='cart_added'           AND DATE(created_at)='$today'      THEN 1 END) AS cart_today"),
            DB::raw("COUNT(CASE WHEN action='checkout_started'     AND DATE(created_at)='$today'      THEN 1 END) AS checkout_today"),
            DB::raw("COUNT(CASE WHEN action='purchase_completed'   AND DATE(created_at)='$today'      THEN 1 END) AS purchase_today"),
            // This week
            DB::raw("COUNT(CASE WHEN action='product_viewed'      AND DATE(created_at)>='$weekStart'  THEN 1 END) AS views_week"),
            DB::raw("COUNT(CASE WHEN action='wishlist_added'       AND DATE(created_at)>='$weekStart'  THEN 1 END) AS wishlist_week"),
            DB::raw("COUNT(CASE WHEN action='cart_added'           AND DATE(created_at)>='$weekStart'  THEN 1 END) AS cart_week"),
            DB::raw("COUNT(CASE WHEN action='checkout_started'     AND DATE(created_at)>='$weekStart'  THEN 1 END) AS checkout_week"),
            DB::raw("COUNT(CASE WHEN action='purchase_completed'   AND DATE(created_at)>='$weekStart'  THEN 1 END) AS purchase_week"),
            // This month
            DB::raw("COUNT(CASE WHEN action='product_viewed'      AND DATE(created_at)>='$monthStart' THEN 1 END) AS views_month"),
            DB::raw("COUNT(CASE WHEN action='wishlist_added'       AND DATE(created_at)>='$monthStart' THEN 1 END) AS wishlist_month"),
            DB::raw("COUNT(CASE WHEN action='cart_added'           AND DATE(created_at)>='$monthStart' THEN 1 END) AS cart_month"),
            DB::raw("COUNT(CASE WHEN action='checkout_started'     AND DATE(created_at)>='$monthStart' THEN 1 END) AS checkout_month"),
            DB::raw("COUNT(CASE WHEN action='purchase_completed'   AND DATE(created_at)>='$monthStart' THEN 1 END) AS purchase_month"),
            // All time
            DB::raw("COUNT(CASE WHEN action='product_viewed'      THEN 1 END) AS views_all"),
            DB::raw("COUNT(CASE WHEN action='wishlist_added'       THEN 1 END) AS wishlist_all"),
            DB::raw("COUNT(CASE WHEN action='cart_added'           THEN 1 END) AS cart_all"),
            DB::raw("COUNT(CASE WHEN action='checkout_started'     THEN 1 END) AS checkout_all"),
            DB::raw("COUNT(CASE WHEN action='purchase_completed'   THEN 1 END) AS purchase_all"),
            // Wishlist DB count
            DB::raw("(SELECT COUNT(*) FROM wishlists) AS wishlist_saved_total")
        )->first();

        $v = fn($val) => (int) ($val ?? 0);

        return response()->json([
            'site_visits' => [
                'today'    => $v($siteVisits->today),
                'week'     => $v($siteVisits->week),
                'month'    => $v($siteVisits->month),
                'all_time' => $v($siteVisits->all_time),
            ],
            'funnel' => [
                [
                    'label' => 'Site Visits',
                    'icon'  => 'bi-globe2',
                    'color' => '#6366f1',
                    'today' => $v($siteVisits->today),
                    'week'  => $v($siteVisits->week),
                    'month' => $v($siteVisits->month),
                    'all'   => $v($siteVisits->all_time),
                ],
                [
                    'label' => 'Product Views',
                    'icon'  => 'bi-eye',
                    'color' => '#3b82f6',
                    'today' => $v($acts->views_today),
                    'week'  => $v($acts->views_week),
                    'month' => $v($acts->views_month),
                    'all'   => $v($acts->views_all),
                ],
                [
                    'label' => 'Wishlist Adds',
                    'icon'  => 'bi-heart',
                    'color' => '#ec4899',
                    'today' => $v($acts->wishlist_today),
                    'week'  => $v($acts->wishlist_week),
                    'month' => $v($acts->wishlist_month),
                    'all'   => $v($acts->wishlist_all),
                ],
                [
                    'label' => 'Cart Adds',
                    'icon'  => 'bi-cart-plus',
                    'color' => '#f59e0b',
                    'today' => $v($acts->cart_today),
                    'week'  => $v($acts->cart_week),
                    'month' => $v($acts->cart_month),
                    'all'   => $v($acts->cart_all),
                ],
                [
                    'label' => 'Checkouts',
                    'icon'  => 'bi-credit-card',
                    'color' => '#10b981',
                    'today' => $v($acts->checkout_today),
                    'week'  => $v($acts->checkout_week),
                    'month' => $v($acts->checkout_month),
                    'all'   => $v($acts->checkout_all),
                ],
                [
                    'label' => 'Purchases',
                    'icon'  => 'bi-bag-check',
                    'color' => '#22c55e',
                    'today' => $v($acts->purchase_today),
                    'week'  => $v($acts->purchase_week),
                    'month' => $v($acts->purchase_month),
                    'all'   => $v($acts->purchase_all),
                ],
            ],
            'wishlist_saved' => $v($acts->wishlist_saved_total),
        ]);
    }

    public function toggle(Request $request)
    {
        $data = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'field'      => 'required|in:is_best_seller,is_trending,dupatta_enabled',
            'value'      => 'required|boolean',
        ])->validate();

        $product = Product::query()->withoutGlobalScope(\App\Models\Scopes\ProductVisibleScope::class)->findOrFail((int) $data['product_id']);
        $product->update([
            $data['field'] => filter_var($data['value'], FILTER_VALIDATE_BOOLEAN),
        ]);

        return response()->json([
            'success' => true,
            'product' => $product->fresh()->load('category')->toCatalogArray(),
        ]);
    }

    public function storeCoupon(Request $request)
    {
        $data = Validator::make($request->all(), [
            'code'              => 'required|string|max:50',
            'discount_type'     => 'required|in:percent,fixed',
            'discount_value'    => 'required|numeric|min:0.01|max:10000',
            'min_order_amount'  => 'nullable|numeric|min:0',
            'before_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount'      => 'nullable|numeric|min:0',
            'start_date'        => 'nullable|date',
            'expiry_date'       => 'nullable|date|after_or_equal:start_date',
            'status'            => 'nullable|boolean',
        ])->validate();

        $code = strtoupper(trim($data['code']));

        $coupon = Coupon::query()->where('code', $code)->first();
        if (!$coupon) {
            $coupon = new Coupon(['code' => $code]);
        }

        $coupon->discount_type    = $data['discount_type'];
        $coupon->discount_value   = $data['discount_value'];
        $coupon->min_order_amount = $data['min_order_amount'] ?? 0;
        $coupon->before_purchase_amount = $data['before_purchase_amount'] ?? 0;
        $coupon->max_discount     = $data['max_discount'] ?? null;
        $coupon->start_date       = $data['start_date'] ?? null;
        $coupon->expiry_date      = $data['expiry_date'] ?? null;
        $coupon->status           = ($data['status'] ?? true) ? true : false;
        $coupon->save();

        return response()->json([
            'success' => true,
            'message' => 'Coupon "' . $code . '" saved.',
            'coupon'  => $coupon->fresh(),
        ]);
    }

    public function destroyCoupon(Request $request, $id)
    {
        $coupon = Coupon::query()->findOrFail((int) $id);
        $code   = $coupon->code;
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon "' . $code . '" deleted.',
        ]);
    }
}

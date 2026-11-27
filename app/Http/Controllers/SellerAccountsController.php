<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SellerAccountsController extends Controller
{
    /**
     * Display the Sales & Revenue Tally Dashboard.
     */
    public function index(Request $request)
    {
        $range = $request->input('range', 'all');
        $query = Order::query()->with('items')->latest('created_at');

        // Apply filters
        $this->applyFilters($query, $request);

        $allOrders = (clone $query)->get();

        // Calculate clear revenue & tally metrics
        $analytics = $this->calculateFinancialAnalytics($allOrders);

        // Paginate for the clean sales ledger
        $orders = (clone $query)->paginate(15)->withQueryString();

        return view('seller.accounts', [
            'orders' => $orders,
            'analytics' => $analytics,
            'currentRange' => $range,
            'couponsList' => Coupon::all(),
        ]);
    }

    /**
     * Export the Sales & Revenue Ledger to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Order::query()->with('items')->latest('created_at');
        $this->applyFilters($query, $request);
        $orders = $query->get();

        $fileName = 'zyra-sales-revenue-tally-' . date('Y-m-d') . '.csv';

        return response()->stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number',
                'Date',
                'Day',
                'Customer Name',
                'Phone / Email',
                'Order Status',
                'Payment Status',
                'Original Total (INR)',
                'Coupon Code',
                'Discount Given (INR)',
                'Customer Paid Net (INR)',
                'Friday Deal Applied',
            ]);

            foreach ($orders as $o) {
                $created = Carbon::parse($o->created_at);
                $isFriday = $created->isFriday();
                $gross = (float) ($o->subtotal > 0 ? $o->subtotal : $o->total + $o->discount);
                $discount = (float) $o->discount;
                $net = (float) $o->total;

                fputcsv($handle, [
                    $o->order_number,
                    $created->format('Y-m-d H:i'),
                    $created->format('l'),
                    $o->customer_name,
                    $o->customer_phone ?: $o->customer_email,
                    $o->order_status,
                    strtoupper($o->payment_status),
                    number_format($gross, 2, '.', ''),
                    $o->coupon_code ?: 'None',
                    number_format($discount, 2, '.', ''),
                    number_format($net, 2, '.', ''),
                    $isFriday ? 'Yes' : 'No',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Filter orders by date range, coupon, payment status, day.
     */
    protected function applyFilters($query, Request $request): void
    {
        $range = $request->input('range', 'all');

        if ($range === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($range === 'week') {
            $query->whereBetween('created_at', [
                now()->startOfWeek()->toDateTimeString(),
                now()->endOfWeek()->toDateTimeString(),
            ]);
        } elseif ($range === 'month') {
            $query->whereBetween('created_at', [
                now()->startOfMonth()->toDateTimeString(),
                now()->endOfMonth()->toDateTimeString(),
            ]);
        } elseif ($range === 'friday_only') {
            $query->whereRaw('DAYOFWEEK(created_at) = 6'); // 6 = Friday in MySQL
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [
                $request->input('from') . ' 00:00:00',
                $request->input('to') . ' 23:59:59',
            ]);
        }

        if ($request->filled('coupon') && $request->coupon !== 'all') {
            if ($request->coupon === 'with_coupon') {
                $query->whereNotNull('coupon_code')->where('coupon_code', '!=', '');
            } elseif ($request->coupon === 'no_coupon') {
                $query->where(fn($q) => $q->whereNull('coupon_code')->orWhere('coupon_code', ''));
            } else {
                $query->where('coupon_code', $request->coupon);
            }
        }

        if ($request->filled('order_status') && $request->order_status !== 'all') {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_email', 'like', "%{$s}%")
                  ->orWhere('coupon_code', 'like', "%{$s}%");
            });
        }
    }

    /**
     * Compute clean, simple accounting tally without confusing cost assumptions.
     */
    protected function calculateFinancialAnalytics($orders): array
    {
        $totalOrdersCount = $orders->count();
        $totalGrossSales = 0.0;
        $totalDiscountsGiven = 0.0;
        $totalNetRevenue = 0.0;

        $deliveredOrdersCount = 0;
        $cancelledOrdersCount = 0;

        $fridayOrdersCount = 0;
        $fridayNetRevenue = 0.0;
        $fridayDiscounts = 0.0;

        // Initialize coupons list
        $couponStats = [];
        foreach (Coupon::all() as $cp) {
            $couponStats[$cp->code] = [
                'code' => $cp->code,
                'used_count' => 0,
                'gross_sales' => 0.0,
                'discount_cost' => 0.0,
                'net_revenue' => 0.0,
            ];
        }

        foreach ($orders as $order) {
            $isFriday = Carbon::parse($order->created_at)->isFriday();
            $gross = (float) ($order->subtotal > 0 ? $order->subtotal : $order->total + $order->discount);
            $discount = (float) $order->discount;
            $net = (float) $order->total;

            $code = strtoupper(trim((string) $order->coupon_code));
            if ($code !== '') {
                if (!isset($couponStats[$code])) {
                    $couponStats[$code] = [
                        'code' => $code,
                        'used_count' => 0,
                        'gross_sales' => 0.0,
                        'discount_cost' => 0.0,
                        'net_revenue' => 0.0,
                    ];
                }
                $couponStats[$code]['used_count']++;
                // Track coupon financial numbers only if order is not cancelled
                if ($order->order_status !== 'Cancelled') {
                    $couponStats[$code]['gross_sales'] += $gross;
                    $couponStats[$code]['discount_cost'] += $discount;
                    $couponStats[$code]['net_revenue'] += $net;
                }
            }

            if ($order->order_status === 'Cancelled') {
                $cancelledOrdersCount++;
            } else {
                $totalGrossSales += $gross;
                $totalDiscountsGiven += $discount;
                $totalNetRevenue += $net;

                if ($order->order_status === 'Delivered') {
                    $deliveredOrdersCount++;
                }

                if ($isFriday) {
                    $fridayOrdersCount++;
                    $fridayNetRevenue += $net;
                    $fridayDiscounts += $discount;
                }
            }
        }

        return [
            'total_orders' => $totalOrdersCount,
            'delivered_orders' => $deliveredOrdersCount,
            'cancelled_orders' => $cancelledOrdersCount,
            'total_gross_sales' => round($totalGrossSales, 2),
            'total_discounts_given' => round($totalDiscountsGiven, 2),
            'total_net_revenue' => round($totalNetRevenue, 2),

            'friday' => [
                'orders_count' => $fridayOrdersCount,
                'net_revenue' => round($fridayNetRevenue, 2),
                'discounts' => round($fridayDiscounts, 2),
            ],

            'coupons' => array_values($couponStats),
        ];
    }
}

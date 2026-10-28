<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderHistoryController extends Controller
{
    /**
     * Compute financial tally KPIs across the filtered orders.
     */
    protected function computeTallyMetrics($query): array
    {
        // Clone query so we don't mutate pagination
        $allOrders = (clone $query)->get();

        $totalOrders = $allOrders->count();
        $totalGross = 0.0;
        $totalDiscount = 0.0;
        $totalNet = 0.0;
        $couponOrdersCount = 0;

        foreach ($allOrders as $order) {
            // Determine original gross price
            $gross = (float) $order->subtotal;
            if ($gross <= 0 && $order->items->isNotEmpty()) {
                $gross = (float) $order->items->sum(fn ($i) => (float)$i->price * (int)$i->quantity);
            }
            if ($gross <= 0) {
                $gross = (float) $order->total + (float) $order->discount;
            }

            $discount = (float) $order->discount;
            $net = (float) $order->total;

            $totalGross += $gross;
            $totalDiscount += $discount;
            $totalNet += $net;

            if (!empty($order->coupon_code) && $discount > 0) {
                $couponOrdersCount++;
            }
        }

        $couponRate = $totalOrders > 0 ? round(($couponOrdersCount / $totalOrders) * 100, 1) : 0;
        $avgDiscount = $couponOrdersCount > 0 ? round($totalDiscount / $couponOrdersCount, 2) : 0;

        return [
            'total_orders' => $totalOrders,
            'total_gross' => $totalGross,
            'total_discount' => $totalDiscount,
            'total_net' => $totalNet,
            'coupon_orders_count' => $couponOrdersCount,
            'coupon_rate' => $couponRate,
            'avg_discount' => $avgDiscount,
        ];
    }

    /**
     * Build the filtered orders query.
     */
    protected function filteredOrdersQuery(Request $request)
    {
        $query = Order::query()->with('items')->latest('created_at');

        // Range filter
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
        }

        // Custom date range
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [
                $request->input('from') . ' 00:00:00',
                $request->input('to') . ' 23:59:59',
            ]);
        }

        // Coupon filter
        $couponFilter = $request->input('coupon', 'all');
        if ($couponFilter === 'with_coupon') {
            $query->whereNotNull('coupon_code')
                  ->where('coupon_code', '!=', '')
                  ->where('discount', '>', 0);
        } elseif ($couponFilter === 'no_coupon') {
            $query->where(function ($q) {
                $q->whereNull('coupon_code')
                  ->orWhere('coupon_code', '')
                  ->orWhere('discount', '<=', 0);
            });
        }

        // Payment status filter
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Search
        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_email', 'like', "%{$s}%")
                  ->orWhere('customer_phone', 'like', "%{$s}%")
                  ->orWhere('coupon_code', 'like', "%{$s}%");
            });
        }

        return $query;
    }

    /**
     * Order History & Purchase Tally Dashboard.
     */
    public function index(Request $request)
    {
        $baseQuery = $this->filteredOrdersQuery($request);
        $metrics = $this->computeTallyMetrics($baseQuery);
        $orders = (clone $baseQuery)->paginate(20)->withQueryString();

        return view('seller.order-history', compact('orders', 'metrics'));
    }

    /**
     * Export order history & coupon tally as an Excel / CSV spreadsheet.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $range = $request->input('range', 'all');
        $query = $this->filteredOrdersQuery($request);
        $metrics = $this->computeTallyMetrics($query);
        $orders = $query->get();

        $fileName = 'zyra-order-history-tally-' . $range . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($orders, $metrics, $range) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM for Excel rupee & international character support
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['ZYRA LUXURY BOUTIQUE - ORDER HISTORY & FINANCIAL PURCHASE TALLY REPORT']);
            fputcsv($out, ['Report Scope:', ucfirst($range), 'Generated On:', now()->format('d M Y, h:i A')]);
            fputcsv($out, []);

            // Summary Tally Block
            fputcsv($out, ['ACCOUNTING TALLY SUMMARY']);
            fputcsv($out, ['Metric', 'Value']);
            fputcsv($out, ['Total Orders Placed', $metrics['total_orders']]);
            fputcsv($out, ['Gross Original Sales (Before Discounts)', 'INR ' . number_format($metrics['total_gross'], 2)]);
            fputcsv($out, ['Total Coupon Discounts Deducted', 'INR ' . number_format($metrics['total_discount'], 2)]);
            fputcsv($out, ['Net Final Revenue Collected', 'INR ' . number_format($metrics['total_net'], 2)]);
            fputcsv($out, ['Orders with Coupon Applied', $metrics['coupon_orders_count'] . ' (' . $metrics['coupon_rate'] . '%)']);
            fputcsv($out, ['Average Discount per Coupon Order', 'INR ' . number_format($metrics['avg_discount'], 2)]);
            fputcsv($out, []);

            // Itemized Order Tally
            fputcsv($out, ['ITEMIZED ORDER PURCHASE & TALLY AUDIT']);
            fputcsv($out, [
                'Order Number',
                'Date & Time',
                'Customer Name',
                'Email',
                'Phone',
                'City',
                'Items Summary',
                'Original Gross Price (INR)',
                'Coupon Applied?',
                'Coupon Code',
                'Discount Amount (INR)',
                'Discount %',
                'Shipping (INR)',
                'Tax (INR)',
                'Final Net Paid (INR)',
                'Payment Method',
                'Payment Status',
                'Order Status',
            ]);

            foreach ($orders as $o) {
                $gross = (float) $o->subtotal;
                if ($gross <= 0 && $o->items->isNotEmpty()) {
                    $gross = (float) $o->items->sum(fn ($i) => (float)$i->price * (int)$i->quantity);
                }
                if ($gross <= 0) {
                    $gross = (float) $o->total + (float) $o->discount;
                }

                $discount = (float) $o->discount;
                $hasCoupon = !empty($o->coupon_code) && $discount > 0;
                $discountPct = $gross > 0 && $discount > 0 ? round(($discount / $gross) * 100, 1) . '%' : '0%';

                $itemsSummary = $o->items->map(function ($it) {
                    return $it->product_name . ' (' . ($it->size ?: 'Free') . ') x ' . $it->quantity;
                })->implode('; ');

                fputcsv($out, [
                    $o->order_number,
                    $o->created_at?->format('d M Y, h:i A') ?? '-',
                    $o->customer_name,
                    $o->customer_email ?: '-',
                    $o->customer_phone ?: '-',
                    $o->city ?: '-',
                    $itemsSummary,
                    number_format($gross, 2),
                    $hasCoupon ? 'YES' : 'NO',
                    $hasCoupon ? $o->coupon_code : '-',
                    number_format($discount, 2),
                    $discountPct,
                    number_format((float)$o->shipping_cost, 2),
                    number_format((float)$o->tax, 2),
                    number_format((float)$o->total, 2),
                    strtoupper($o->payment_method),
                    ucfirst($o->payment_status),
                    $o->order_status,
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, [
                'TOTALS',
                '',
                '',
                '',
                '',
                '',
                '',
                number_format($metrics['total_gross'], 2),
                '',
                '',
                number_format($metrics['total_discount'], 2),
                '',
                '',
                '',
                number_format($metrics['total_net'], 2),
                '',
                '',
                '',
            ]);

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Printable PDF Statement view with ZYRA letterhead and formatted tables.
     */
    public function exportPdf(Request $request)
    {
        $range = $request->input('range', 'all');
        $query = $this->filteredOrdersQuery($request);
        $metrics = $this->computeTallyMetrics($query);
        $orders = $query->get();

        return view('seller.order-history-pdf', compact('orders', 'metrics', 'range'));
    }
}

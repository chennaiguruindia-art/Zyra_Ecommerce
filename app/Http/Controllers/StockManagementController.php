<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockManagementController extends Controller
{
    /**
     * Compute aggregate metrics for stock received and valuation.
     */
    protected function getMetrics(): array
    {
        $todayReceipts = StockReceipt::today();
        $weekReceipts = StockReceipt::thisWeek();
        $monthReceipts = StockReceipt::thisMonth();

        $totalUnits = Product::withoutGlobalScopes()->sum('stock_units');
        $totalValuation = Product::withoutGlobalScopes()
            ->selectRaw('SUM(stock_units * price) as val')
            ->value('val') ?? 0;

        return [
            'today' => [
                'units' => (int) $todayReceipts->sum('quantity'),
                'amount' => (float) $todayReceipts->sum('total_cost'),
            ],
            'week' => [
                'units' => (int) $weekReceipts->sum('quantity'),
                'amount' => (float) $weekReceipts->sum('total_cost'),
            ],
            'month' => [
                'units' => (int) $monthReceipts->sum('quantity'),
                'amount' => (float) $monthReceipts->sum('total_cost'),
            ],
            'inventory' => [
                'total_units' => (int) $totalUnits,
                'total_valuation' => (float) $totalValuation,
            ],
        ];
    }

    /**
     * Build the filtered receipts query.
     */
    protected function filteredReceiptsQuery(Request $request)
    {
        $query = StockReceipt::with(['product' => function ($q) {
            $q->withoutGlobalScopes()->with(['category', 'sizes', 'images']);
        }, 'user'])->latest();

        $range = $request->input('range', 'all');
        if ($range === 'today') {
            $query->today();
        } elseif ($range === 'week') {
            $query->thisWeek();
        } elseif ($range === 'month') {
            $query->thisMonth();
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->whereHas('product', function ($q) use ($search) {
                $q->withoutGlobalScopes()
                  ->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Display the Stock Management Dashboard.
     */
    public function index(Request $request)
    {
        $metrics = $this->getMetrics();
        $receipts = $this->filteredReceiptsQuery($request)->paginate(20)->withQueryString();

        $products = Product::withoutGlobalScopes()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->orderBy('name')
            ->get();

        return view('seller.stock-management', compact('metrics', 'receipts', 'products'));
    }

    /**
     * JSON API for asynchronous filtering and stats.
     */
    public function data(Request $request): JsonResponse
    {
        $metrics = $this->getMetrics();
        $receipts = $this->filteredReceiptsQuery($request)->paginate(20);

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
            'receipts' => $receipts,
        ]);
    }

    /**
     * Inward/receive new stock batch with per-size calculation.
     */
    public function storeInward(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'nullable|integer|min:0|max:10000',
            'stock_units' => 'nullable|integer|min:0|max:10000',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $product = Product::withoutGlobalScopes()->with('sizes')->findOrFail($request->product_id);
        $totalAdded = 0;
        $updatedSizes = [];
        $sizesBreakdown = [];

        $hasSizes = $product->sizes->isNotEmpty();

        if ($hasSizes && $request->has('quantities') && is_array($request->quantities)) {
            foreach ($request->quantities as $sizeName => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }

                $size = $product->sizes->first(fn ($s) => $s->name === $sizeName);
                if (!$size) {
                    continue;
                }

                $current = $size->pivot->stock !== null ? (int) $size->pivot->stock : 0;
                $newStock = $current + $qty;

                $affected = $product->sizes()->updateExistingPivot($size->id, ['stock' => $newStock]);
                if ($affected === 0) {
                    $product->sizes()->attach($size->id, ['stock' => $newStock]);
                }

                $updatedSizes[$sizeName] = $newStock;
                $sizesBreakdown[$sizeName] = $qty;
                $totalAdded += $qty;
            }
        } elseif (!$hasSizes && $request->filled('stock_units')) {
            $totalAdded = (int) $request->input('stock_units');
        }

        if ($totalAdded <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a received quantity greater than 0.',
            ], 422);
        }

        $unitCost = $request->filled('unit_cost') && (float) $request->unit_cost > 0
            ? (float) $request->unit_cost
            : (float) $product->price;

        $totalCost = round($totalAdded * $unitCost, 2);

        $receipt = DB::transaction(function () use ($product, $totalAdded, $unitCost, $totalCost, $sizesBreakdown, $request) {
            $product->stock_units = (int) $product->stock_units + $totalAdded;
            $product->in_stock = true;
            $product->save();

            return StockReceipt::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'quantity' => $totalAdded,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'sizes_breakdown' => !empty($sizesBreakdown) ? $sizesBreakdown : null,
                'source' => 'inward_batch',
                'reference' => $request->input('reference') ?: 'Direct Inward',
                'note' => $request->input('note'),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Successfully received {$totalAdded} units (Total: ₹" . number_format($totalCost, 2) . ") for {$product->name}!",
            'receipt' => $receipt->load(['product', 'user']),
            'metrics' => $this->getMetrics(),
            'product' => $product->fresh()->load(['category', 'subcategory', 'sizes', 'colors', 'images'])->toCatalogArray(),
        ]);
    }

    /**
     * Download stock receipts as Excel / CSV spreadsheet.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $range = $request->input('range', 'all');
        $receipts = $this->filteredReceiptsQuery($request)->get();
        $metrics = $this->getMetrics();

        $fileName = 'zyra-stock-report-' . $range . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($receipts, $range, $metrics) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM for Excel rupee symbol & character encoding support
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['ZYRA LUXURY BOUTIQUE - STOCK MANAGEMENT & INWARD REPORT']);
            fputcsv($out, ['Report Range:', ucfirst($range), 'Generated On:', now()->format('d M Y, h:i A')]);
            fputcsv($out, []);

            // Summary metrics section
            fputcsv($out, ['SUMMARY METRICS']);
            fputcsv($out, ['Period', 'Units Received', 'Total Inward Amount (INR)']);
            fputcsv($out, ['Today', $metrics['today']['units'], number_format($metrics['today']['amount'], 2)]);
            fputcsv($out, ['This Week', $metrics['week']['units'], number_format($metrics['week']['amount'], 2)]);
            fputcsv($out, ['This Month', $metrics['month']['units'], number_format($metrics['month']['amount'], 2)]);
            fputcsv($out, ['Current Inventory Holding', $metrics['inventory']['total_units'], number_format($metrics['inventory']['total_valuation'], 2)]);
            fputcsv($out, []);

            // Inward transactions section
            fputcsv($out, ['STOCK INWARD TRANSACTIONS']);
            fputcsv($out, [
                'Receipt ID',
                'Date & Time',
                'Product Name',
                'SKU',
                'Category',
                'Sizes Breakdown',
                'Units Received',
                'Unit Cost / Price (INR)',
                'Total Amount (INR)',
                'Source / Type',
                'Reference / Batch',
                'Notes',
            ]);

            $totalUnits = 0;
            $totalAmount = 0.0;

            foreach ($receipts as $r) {
                $totalUnits += $r->quantity;
                $totalAmount += (float) $r->total_cost;

                $sizeStr = '-';
                if (!empty($r->sizes_breakdown) && is_array($r->sizes_breakdown)) {
                    $parts = [];
                    foreach ($r->sizes_breakdown as $s => $q) {
                        $parts[] = "{$s}: +{$q}";
                    }
                    $sizeStr = implode(', ', $parts);
                }

                fputcsv($out, [
                    '#SR-' . str_pad($r->id, 5, '0', STR_PAD_LEFT),
                    $r->created_at->format('d M Y, h:i A'),
                    $r->product?->name ?? 'Deleted Product',
                    $r->product?->sku ?? '-',
                    $r->product?->category?->name ?? '-',
                    $sizeStr,
                    $r->quantity,
                    number_format((float) $r->unit_cost, 2),
                    number_format((float) $r->total_cost, 2),
                    ucfirst(str_replace('_', ' ', $r->source)),
                    $r->reference ?: '-',
                    $r->note ?: '-',
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['TOTALS', '', '', '', '', '', $totalUnits, '', number_format($totalAmount, 2), '', '', '']);

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Printable PDF format view with ZYRA letterhead and formatted tables.
     */
    public function exportPdf(Request $request)
    {
        $range = $request->input('range', 'all');
        $receipts = $this->filteredReceiptsQuery($request)->get();
        $metrics = $this->getMetrics();

        return view('seller.stock-pdf', compact('receipts', 'metrics', 'range'));
    }
}

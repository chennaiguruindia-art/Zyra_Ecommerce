<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\CustomerActivity;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityAnalyticsController extends Controller
{
    public function index()
    {
        return view('seller.customer-activity', [
            'filterProducts' => Product::query()->select(['id', 'name'])->orderBy('name')->get(),
            'filterCategories' => Category::query()->select(['id', 'name'])->orderBy('name')->get(),
            'filterSizes' => Size::query()->select(['name'])->orderBy('sort_order')->get(),
            'filterColors' => Color::query()->select(['name'])->orderBy('name')->get(),
        ]);
    }

    public function data(Request $request)
    {
        $f = $this->filters($request);
        $base = $this->filteredQuery($f);

        $summary = (clone $base)->selectRaw($this->aggregateSelect())->first();

        $s = [
            'views' => (int) ($summary->views ?? 0),
            'wishlist' => (int) ($summary->wishlist ?? 0),
            'carts' => (int) ($summary->carts ?? 0),
            'checkouts' => (int) ($summary->checkouts ?? 0),
            'purchases' => (int) ($summary->purchases ?? 0),
            'uniq_viewers' => (int) ($summary->uniq_viewers ?? 0),
            'uniq_wishlist' => (int) ($summary->uniq_wishlist ?? 0),
            'uniq_carts' => (int) ($summary->uniq_carts ?? 0),
            'uniq_checkouts' => (int) ($summary->uniq_checkouts ?? 0),
            'uniq_purchases' => (int) ($summary->uniq_purchases ?? 0),
        ];
        $s['cart_conversion'] = $this->rate($s['uniq_carts'], $s['uniq_viewers']);
        $s['checkout_conversion'] = $this->rate($s['uniq_checkouts'], $s['uniq_carts']);
        $s['purchase_conversion'] = $this->rate($s['uniq_purchases'], $s['uniq_checkouts']);

        return response()->json([
            'summary' => $s,
            'funnel' => $this->funnel($s),
            'products' => $this->productRows($f),
            'variants' => $this->variantRows($f),
            'trend' => $this->trend($f),
            'range' => ['from' => $f['from'], 'to' => $f['to']],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $f = $this->filters($request);
        $products = $this->productRows($f);
        $variants = $this->variantRows($f);

        return response()->streamDownload(function () use ($products, $variants, $f) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ZYRA Shopping Analytics', 'From: ' . $f['from'], 'To: ' . $f['to']]);
            fputcsv($out, []);
            fputcsv($out, ['PRODUCT ANALYTICS']);
            fputcsv($out, ['Product', 'Category', 'Views', 'Unique Viewers', 'Wishlist', 'Cart', 'Checkout', 'Purchased', 'Unique Buyers']);
            foreach ($products as $r) {
                fputcsv($out, [$r['name'], $r['category'], $r['views'], $r['uniq_viewers'], $r['wishlist'], $r['carts'], $r['checkouts'], $r['purchases'], $r['uniq_buyers']]);
            }
            fputcsv($out, []);
            fputcsv($out, ['VARIANT ANALYTICS (SIZE x COLOR)']);
            fputcsv($out, ['Product', 'Size', 'Color', 'Views', 'Wishlist', 'Cart', 'Checkout', 'Purchased']);
            foreach ($variants as $r) {
                fputcsv($out, [$r['name'], $r['size'], $r['color'], $r['views'], $r['wishlist'], $r['carts'], $r['checkouts'], $r['purchases']]);
            }
            fclose($out);
        }, 'shopping-analytics-' . $f['from'] . '-to-' . $f['to'] . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ------------------------------------------------------------------

    protected function filters(Request $request): array
    {
        $data = $request->validate([
            'range' => 'nullable|string|in:today,yesterday,last7,last30,this_month,custom',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'product_id' => 'nullable|integer|exists:products,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'size' => 'nullable|string|max:40',
            'color' => 'nullable|string|max:60',
        ]);

        $range = $data['range'] ?? 'last30';
        $today = now()->toDateString();

        [$from, $to] = match ($range) {
            'today' => [$today, $today],
            'yesterday' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'last7' => [now()->subDays(6)->toDateString(), $today],
            'this_month' => [now()->startOfMonth()->toDateString(), $today],
            'custom' => [$data['from'] ?? now()->subDays(29)->toDateString(), $data['to'] ?? $today],
            default => [now()->subDays(29)->toDateString(), $today],
        };

        return [
            'from' => $from,
            'to' => $to,
            'product_id' => isset($data['product_id']) ? (int) $data['product_id'] : null,
            'category_id' => isset($data['category_id']) ? (int) $data['category_id'] : null,
            'size' => isset($data['size']) && $data['size'] !== '' ? $data['size'] : null,
            'color' => isset($data['color']) && $data['color'] !== '' ? $data['color'] : null,
        ];
    }

    protected function filteredQuery(array $f)
    {
        $q = CustomerActivity::query()->betweenDates($f['from'], $f['to']);

        if ($f['product_id']) {
            $q->where('customer_activities.product_id', $f['product_id']);
        }
        if ($f['category_id']) {
            $q->whereHas('product', fn ($p) => $p->where('category_id', $f['category_id']));
        }
        if ($f['size']) {
            $q->where('customer_activities.size', $f['size']);
        }
        if ($f['color']) {
            $q->where('customer_activities.color', $f['color']);
        }

        return $q;
    }

    protected function aggregateSelect(): string
    {
        return "
            SUM(action = 'product_viewed') AS views,
            SUM(action = 'wishlist_added') AS wishlist,
            SUM(action = 'cart_added') AS carts,
            SUM(action = 'checkout_started') AS checkouts,
            SUM(action = 'purchase_completed') AS purchases,
            COUNT(DISTINCT CASE WHEN action = 'product_viewed' THEN anonymous_id END) AS uniq_viewers,
            COUNT(DISTINCT CASE WHEN action = 'wishlist_added' THEN anonymous_id END) AS uniq_wishlist,
            COUNT(DISTINCT CASE WHEN action = 'cart_added' THEN anonymous_id END) AS uniq_carts,
            COUNT(DISTINCT CASE WHEN action = 'checkout_started' THEN anonymous_id END) AS uniq_checkouts,
            COUNT(DISTINCT CASE WHEN action = 'purchase_completed' THEN anonymous_id END) AS uniq_purchases
        ";
    }

    protected function funnel(array $s): array
    {
        return [
            ['stage' => 'Product View', 'total' => $s['views'], 'users' => $s['uniq_viewers']],
            ['stage' => 'Wishlist', 'total' => $s['wishlist'], 'users' => $s['uniq_wishlist']],
            ['stage' => 'Cart', 'total' => $s['carts'], 'users' => $s['uniq_carts']],
            ['stage' => 'Checkout', 'total' => $s['checkouts'], 'users' => $s['uniq_checkouts']],
            ['stage' => 'Purchase', 'total' => $s['purchases'], 'users' => $s['uniq_purchases']],
        ];
    }

    protected function productRows(array $f): array
    {
        return $this->filteredQuery($f)
            ->join('products', 'products.id', '=', 'customer_activities.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw("
                customer_activities.product_id AS id,
                MAX(customer_activities.product_name) AS name,
                MAX(categories.name) AS category,
                " . $this->aggregateSelect() . ",
                COUNT(DISTINCT CASE WHEN action = 'purchase_completed' THEN anonymous_id END) AS uniq_buyers
            ")
            ->groupBy('customer_activities.product_id')
            ->orderByDesc('views')
            ->limit(200)
            ->get()
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'name' => (string) ($r->name ?? ('#' . $r->id)),
                'category' => (string) ($r->category ?? '—'),
                'views' => (int) $r->views,
                'uniq_viewers' => (int) $r->uniq_viewers,
                'wishlist' => (int) $r->wishlist,
                'carts' => (int) $r->carts,
                'checkouts' => (int) $r->checkouts,
                'purchases' => (int) $r->purchases,
                'uniq_buyers' => (int) $r->uniq_buyers,
                'conversion' => $this->rate((int) $r->purchases, (int) $r->views),
            ])
            ->all();
    }

    protected function variantRows(array $f): array
    {
        return $this->filteredQuery($f)
            ->selectRaw("
                customer_activities.product_id AS id,
                MAX(customer_activities.product_name) AS name,
                customer_activities.size AS size,
                customer_activities.color AS color,
                " . $this->aggregateSelect() . "
            ")
            ->groupBy('customer_activities.product_id', 'customer_activities.size', 'customer_activities.color')
            ->orderByDesc('views')
            ->limit(300)
            ->get()
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'name' => (string) ($r->name ?? ('#' . $r->id)),
                'size' => $r->size !== null ? (string) $r->size : '—',
                'color' => $r->color !== null ? (string) $r->color : '—',
                'views' => (int) $r->views,
                'wishlist' => (int) $r->wishlist,
                'carts' => (int) $r->carts,
                'checkouts' => (int) $r->checkouts,
                'purchases' => (int) $r->purchases,
            ])
            ->all();
    }

    protected function trend(array $f): array
    {
        $rows = $this->filteredQuery($f)
            ->selectRaw("DATE(customer_activities.created_at) AS day, action, COUNT(*) AS total")
            ->groupBy(DB::raw('DATE(customer_activities.created_at)'), 'action')
            ->orderBy('day')
            ->limit(600)
            ->get();

        $labels = [];
        $series = [];
        foreach (CustomerActivity::FUNNEL as $a) {
            $series[$a] = [];
        }
        foreach ($rows as $r) {
            if (!in_array($r->day, $labels, true)) {
                $labels[] = $r->day;
            }
            if (isset($series[$r->action])) {
                $series[$r->action][$r->day] = (int) $r->total;
            }
        }
        foreach ($series as $a => $byDay) {
            $series[$a] = array_map(fn ($d) => $byDay[$d] ?? 0, $labels);
        }

        return ['labels' => array_values($labels), 'series' => $series];
    }

    protected function rate(int $part, int $whole): float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : 0.0;
    }
}
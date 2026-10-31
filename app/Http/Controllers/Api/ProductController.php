<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomerActivity;
use App\Models\Product;
use App\Services\ActivityTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected array $withRelations = ['category', 'subcategory', 'sizes', 'colors', 'images'];

    /**
     * List products with multi-attribute filtering, sorting, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->buildFilteredQuery($request);

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map->toCatalogArray()->values();

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * Single product details with full gallery, stock per size, and related items.
     */
    public function show(string $idOrSlug): JsonResponse
    {
        $productModel = Product::query()
            ->withoutGlobalScope(\App\Models\Scopes\ProductVisibleScope::class)
            ->with($this->withRelations)
            ->when(is_numeric($idOrSlug), fn ($q) => $q->where('id', $idOrSlug), fn ($q) => $q->where('slug', $idOrSlug))
            ->first();

        if (!$productModel || !$productModel->is_visible) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        ActivityTracker::track(CustomerActivity::VIEWED, $productModel->id);

        $relatedProducts = Product::query()
            ->with($this->withRelations)
            ->where('category_id', $productModel->category_id)
            ->where('id', '!=', $productModel->id)
            ->take(6)
            ->get()
            ->map->toCatalogArray()
            ->values();

        return response()->json([
            'success' => true,
            'product' => $productModel->toCatalogArray(),
            'related_products' => $relatedProducts,
        ]);
    }

    /**
     * Quick view summary for lightweight bottom sheets.
     */
    public function quickView(string $id): JsonResponse
    {
        $product = Product::query()->with($this->withRelations)->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        ActivityTracker::track(CustomerActivity::VIEWED, $product->id);

        return response()->json([
            'success' => true,
            'product' => $product->toCatalogArray(),
        ]);
    }

    /**
     * Category list with subcategories and counts.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->active()
            ->with('subcategories')
            ->withCount('products')
            ->get()
            ->map(fn (Category $cat) => $cat->toNavArray())
            ->values();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Filter query builder.
     */
    protected function buildFilteredQuery(Request $request)
    {
        $sizes = $request->input('sizes', []);
        if (is_string($sizes)) {
            $sizes = array_filter(explode(',', $sizes));
        }

        $colors = $request->input('colors', []);
        if (is_string($colors)) {
            $colors = array_filter(explode(',', $colors));
        }

        $filters = [
            'category' => $request->input('category'),
            'subcategory' => $request->input('subcategory'),
            'sizes' => $sizes,
            'colors' => $colors,
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
            'rating' => $request->input('rating'),
            'in_stock' => $request->boolean('in_stock'),
        ];

        if ($request->filled('price_range') && $request->input('price_range') !== 'all') {
            [$min, $max] = array_pad(explode('-', $request->input('price_range')), 2, null);
            $filters['price_min'] = $min;
            $filters['price_max'] = $max;
        }

        $query = Product::query()->with($this->withRelations)->filter($filters);

        if ($request->boolean('dupatta')) {
            $query->where('dupatta_enabled', true);
        }

        if ($request->input('filter') === 'new') {
            $query->where('badge', 'New');
        } elseif ($request->input('filter') === 'sale') {
            $query->where(function ($q) {
                $q->where('badge', 'Sale')->orWhere('discount', '>', 0);
            });
        } elseif ($request->input('filter') === 'trending') {
            $query->trending();
        }

        $sort = $request->input('sort', 'featured');
        return match ($sort) {
            'price-low' => $query->orderBy('price'),
            'price-high' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating'),
            'newest' => $query->latest(),
            'popular' => $query->orderByDesc('reviews_count'),
            default => $query->orderByDesc('is_featured')->latest(),
        };
    }
}

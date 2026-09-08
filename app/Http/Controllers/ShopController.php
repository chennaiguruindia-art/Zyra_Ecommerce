<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = $this->filteredQuery($request)->get()->map->toCatalogArray()->all();
        $categories = $this->categoryPayload();

        return view('shop', compact('products', 'categories'));
    }

    public function category(Request $request, string $category)
    {
        $current = Category::query()
            ->with('subcategories')
            ->withCount('products')
            ->where('slug', $category)
            ->first();

        $currentCategory = $current
            ? $current->toNavArray()
            : [
                'name' => ucfirst($category),
                'slug' => $category,
                'description' => "Explore the finest women's fashion collection.",
                'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=800&q=80',
                'subcategories' => [],
                'count' => 0,
            ];

        $request->merge(['category' => $category]);
        $products = $this->filteredQuery($request)->get()->map->toCatalogArray()->all();
        $categories = $this->categoryPayload();

        return view('category', compact('products', 'currentCategory', 'categories'));
    }

    public function shortcut(Request $request, string $slug)
    {
        $viewMap = [
            'tops' => 'pages.tops',
            'leggings' => 'pages.leggings',
            'kurtis' => 'pages.kurtis',
            'maxi' => 'pages.maxi',
            'nightwear' => 'pages.nightwear',
        ];

        $request->merge(['category' => $slug]);
        $products = $this->filteredQuery($request)->get()->map->toCatalogArray()->all();
        $categories = $this->categoryPayload();
        $currentCategory = Category::query()->where('slug', $slug)->with('subcategories')->withCount('products')->first()?->toNavArray();

        $view = $viewMap[$slug] ?? 'category';

        return view($view, compact('products', 'categories', 'currentCategory'));
    }

    public function filter(Request $request)
    {
        $query = $this->filteredQuery($request);
        $products = $query->get()->map->toCatalogArray()->all();

        $html = view('partials.product-grid', compact('products'))->render();

        return response()->json([
            'html' => $html,
            'count' => count($products),
            'products' => $products,
        ]);
    }

    protected function filteredQuery(Request $request)
    {
        $filters = [
            'category' => $request->input('category'),
            'subcategory' => $request->input('subcategory'),
            'sizes' => $request->input('sizes', []),
            'colors' => $request->input('colors', []),
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

        $query = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->filter($filters);

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

    protected function categoryPayload(): array
    {
        return Category::query()
            ->active()
            ->with('subcategories')
            ->withCount('products')
            ->get()
            ->map(fn (Category $category) => $category->toNavArray())
            ->all();
    }
}

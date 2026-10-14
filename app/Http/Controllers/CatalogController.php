<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Render the professional catalog / lookbook view with real database products only.
     */
    public function index(Request $request)
    {
        $selectedCategory = $request->query('category');

        $categories = Category::query()
            ->active()
            ->with('subcategories')
            ->withCount('products')
            ->get()
            ->map(fn (Category $category) => $category->toNavArray())
            ->all();

        $query = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images']);

        if ($selectedCategory) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $selectedCategory));
        }

        // Only real products from database - no dummy products
        $products = $query->latest()->get()->map->toCatalogArray()->all();

        return view('catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'totalProducts' => count($products),
        ]);
    }

    public function json()
    {
        $products = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->get()
            ->map->toCatalogArray()
            ->all();

        $categories = Category::query()
            ->active()
            ->with('subcategories')
            ->withCount('products')
            ->get()
            ->map(fn (Category $category) => $category->toNavArray())
            ->all();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class CatalogController extends Controller
{
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

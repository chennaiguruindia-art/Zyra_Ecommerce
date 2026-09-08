<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index()
    {
        $allProducts = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->get()
            ->map->toCatalogArray()
            ->all();
        $categories = Category::query()->active()->withCount('products')->get()->map->toNavArray()->all();

        return view('search', compact('allProducts', 'categories'));
    }

    public function live(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('subcategory', fn ($c) => $c->where('name', 'like', "%{$q}%"));
            })
            ->take(12)
            ->get()
            ->map->toCatalogArray()
            ->all();

        return response()->json(['products' => $products]);
    }
}

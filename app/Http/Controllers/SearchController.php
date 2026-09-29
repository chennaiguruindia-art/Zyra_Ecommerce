<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\SearchMatcher;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $with = ['category', 'subcategory', 'sizes', 'colors', 'images'];

        // 1. Strict pass: every query token must match (Flipkart/Amazon style).
        $allProducts = SearchMatcher::apply(Product::query()->with($with), $search)
            ->get()
            ->map->toCatalogArray()
            ->all();

        // 2. Partial pass: multi-word query with no strict hits → any token (OR).
        $partial = false;
        if ($allProducts === [] && SearchMatcher::tokenize($search) !== []) {
            $allProducts = SearchMatcher::apply(Product::query()->with($with), $search, 'or')
                ->get()
                ->map->toCatalogArray()
                ->all();
            $partial = $allProducts !== [];
        }

        // 3. Relevance ranking (name > colour > category > material > …).
        $allProducts = SearchMatcher::rank($allProducts, $search, ! $partial);

        $categories = Category::query()->active()->withCount('products')->get()->map->toNavArray()->all();

        return view('search', compact('allProducts', 'categories', 'search', 'partial'));
    }

    public function live(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return response()->json(['products' => []]);
        }

        $with = ['category', 'subcategory', 'sizes', 'colors', 'images'];

        $products = SearchMatcher::apply(Product::query()->with($with), $q)
            ->limit(60)
            ->get()
            ->map->toCatalogArray()
            ->all();

        $partial = false;
        if ($products === []) {
            $products = SearchMatcher::apply(Product::query()->with($with), $q, 'or')
                ->limit(60)
                ->get()
                ->map->toCatalogArray()
                ->all();
            $partial = $products !== [];
        }

        $products = SearchMatcher::rank($products, $q, ! $partial);
        $products = array_slice($products, 0, 12);

        return response()->json(['products' => $products, 'partial' => $partial]);
    }
}

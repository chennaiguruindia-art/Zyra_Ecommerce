<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\SearchMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected array $withRelations = ['category', 'subcategory', 'sizes', 'colors', 'images'];

    /**
     * Search products with Flipkart/Amazon-style strict + fuzzy fallback matching.
     */
    public function search(Request $request): JsonResponse
    {
        $search = trim((string) ($request->input('q') ?: $request->input('search', '')));
        if ($search === '') {
            return response()->json([
                'success' => true,
                'query' => '',
                'count' => 0,
                'data' => [],
            ]);
        }

        $allProducts = SearchMatcher::apply(Product::query()->with($this->withRelations), $search)
            ->get()
            ->map->toCatalogArray()
            ->all();

        $partial = false;
        if ($allProducts === [] && SearchMatcher::tokenize($search) !== []) {
            $allProducts = SearchMatcher::apply(Product::query()->with($this->withRelations), $search, 'or')
                ->get()
                ->map->toCatalogArray()
                ->all();
            $partial = $allProducts !== [];
        }

        $ranked = SearchMatcher::rank($allProducts, $search, ! $partial);

        return response()->json([
            'success' => true,
            'query' => $search,
            'partial_match' => $partial,
            'count' => count($ranked),
            'data' => array_values($ranked),
        ]);
    }

    /**
     * Live search for mobile instant-search / typeahead.
     */
    public function live(Request $request): JsonResponse
    {
        $q = trim((string) ($request->input('q') ?: $request->input('search', '')));
        if ($q === '') {
            return response()->json([
                'success' => true,
                'query' => '',
                'products' => [],
            ]);
        }

        $products = SearchMatcher::apply(Product::query()->with($this->withRelations), $q)
            ->limit(15)
            ->get()
            ->map->toCatalogArray()
            ->values();

        if ($products->isEmpty()) {
            $products = SearchMatcher::apply(Product::query()->with($this->withRelations), $q, 'or')
                ->limit(15)
                ->get()
                ->map->toCatalogArray()
                ->values();
        }

        return response()->json([
            'success' => true,
            'query' => $q,
            'products' => $products,
        ]);
    }
}

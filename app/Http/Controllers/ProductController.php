<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CustomerActivity;
use App\Services\ActivityTracker;
use App\Services\ZyraCache;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function show(string $idOrSlug)
    {
        $cacheKey = "product_details_{$idOrSlug}";

        $cachedData = Cache::remember($cacheKey, ZyraCache::TTL_PRODUCT_DETAIL, function () use ($idOrSlug) {
            $productModel = Product::query()
                ->withoutGlobalScope(\App\Models\Scopes\ProductVisibleScope::class)
                ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
                ->when(is_numeric($idOrSlug), fn ($q) => $q->where('id', $idOrSlug), fn ($q) => $q->where('slug', $idOrSlug))
                ->first();

            if (!$productModel || !$productModel->is_visible) {
                return null;
            }

            $product = $productModel->toCatalogArray();

            $relatedProducts = Product::query()
                ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
                ->where('category_id', $productModel->category_id)
                ->where('id', '!=', $productModel->id)
                ->take(4)
                ->get()
                ->map->toCatalogArray()
                ->all();

            $recentlyViewed = Product::query()
                ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
                ->where('id', '!=', $productModel->id)
                ->latest()
                ->take(4)
                ->get()
                ->map->toCatalogArray()
                ->all();

            return [
                'model_id' => $productModel->id,
                'product' => $product,
                'relatedProducts' => $relatedProducts,
                'recentlyViewed' => $recentlyViewed,
            ];
        });

        if (!$cachedData) {
            abort(404);
        }

        ActivityTracker::track(CustomerActivity::VIEWED, $cachedData['model_id']);

        return view('product-details', [
            'product' => $cachedData['product'],
            'relatedProducts' => $cachedData['relatedProducts'],
            'recentlyViewed' => $cachedData['recentlyViewed'],
        ]);
    }

    public function quickView(string $id)
    {
        $product = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        ActivityTracker::track(CustomerActivity::VIEWED, $product->id);

        return response()->json($product->toCatalogArray());
    }
}

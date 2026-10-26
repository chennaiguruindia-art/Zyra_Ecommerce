<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CustomerActivity;
use App\Services\ActivityTracker;

class ProductController extends Controller
{
    public function show(string $idOrSlug)
    {
        // Look up bypassing the visibility scope so a hidden product gets an
        // explicit 404 instead of falling through to the legacy fallback.
        $productModel = Product::query()
            ->withoutGlobalScope(\App\Models\Scopes\ProductVisibleScope::class)
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->when(is_numeric($idOrSlug), fn ($q) => $q->where('id', $idOrSlug), fn ($q) => $q->where('slug', $idOrSlug))
            ->first();

        if (!$productModel || !$productModel->is_visible) {
            abort(404);
        }

        $product = $productModel->toCatalogArray();

        ActivityTracker::track(CustomerActivity::VIEWED, $productModel->id);

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

        return view('product-details', compact('product', 'relatedProducts', 'recentlyViewed'));
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

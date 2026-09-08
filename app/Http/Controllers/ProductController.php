<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;

class ProductController extends Controller
{
    public function show(string $idOrSlug)
    {
        $productModel = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images', 'reviews'])
            ->when(is_numeric($idOrSlug), fn ($q) => $q->where('id', $idOrSlug), fn ($q) => $q->where('slug', $idOrSlug))
            ->first();

        if (!$productModel) {
            $productModel = Product::query()->with(['category', 'subcategory', 'sizes', 'colors', 'images', 'reviews'])->firstOrFail();
        }

        $product = $productModel->toCatalogArray();
        $reviews = $productModel->reviews->all();

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

        return view('product-details', compact('product', 'relatedProducts', 'recentlyViewed', 'reviews'));
    }

    public function quickView(string $id)
    {
        $product = Product::query()
            ->with(['category', 'subcategory', 'sizes', 'colors', 'images'])
            ->find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product->toCatalogArray());
    }
}

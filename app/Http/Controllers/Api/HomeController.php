<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\InstagramItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Home screen payload for mobile app: banners, categories, featured carousels.
     */
    public function index(): JsonResponse
    {
        $with = ['category', 'subcategory', 'sizes', 'colors', 'images'];

        $categories = Category::query()
            ->active()
            ->with('subcategories')
            ->withCount('products')
            ->get()
            ->map(fn (Category $category) => $category->toNavArray())
            ->values();

        $newArrivals = Product::query()->with($with)->latest()->take(8)->get()->map->toCatalogArray()->values();
        $bestSellers = Product::query()->with($with)->bestSeller()->take(8)->get()->map->toCatalogArray()->values();
        $trending = Product::query()->with($with)->trending()->take(8)->get()->map->toCatalogArray()->values();

        $heroBanner = Banner::query()->active()->where('position', 'hero')->first();
        $promoBanner = Banner::query()->active()->where('position', 'promo')->first();
        $allBanners = Banner::query()->active()->get();

        $instagramItems = InstagramItem::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (InstagramItem $item) => [
                'id' => $item->id,
                'url' => $item->url,
                'image' => $item->image ? \App\Support\ProductImageStorage::url($item->image) : null,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'hero_banner' => $heroBanner ? [
                'id' => $heroBanner->id,
                'title' => $heroBanner->title,
                'subtitle' => $heroBanner->subtitle,
                'image' => $heroBanner->image ? \App\Support\ProductImageStorage::url($heroBanner->image) : null,
                'link' => $heroBanner->link,
                'button_text' => $heroBanner->button_text,
            ] : null,
            'promo_banner' => $promoBanner ? [
                'id' => $promoBanner->id,
                'title' => $promoBanner->title,
                'subtitle' => $promoBanner->subtitle,
                'image' => $promoBanner->image ? \App\Support\ProductImageStorage::url($promoBanner->image) : null,
                'link' => $promoBanner->link,
                'button_text' => $promoBanner->button_text,
            ] : null,
            'banners' => $allBanners->map(fn ($b) => [
                'id' => $b->id,
                'position' => $b->position,
                'title' => $b->title,
                'subtitle' => $b->subtitle,
                'image' => $b->image ? \App\Support\ProductImageStorage::url($b->image) : null,
                'link' => $b->link,
                'button_text' => $b->button_text,
            ]),
            'categories' => $categories,
            'sections' => [
                'new_arrivals' => $newArrivals,
                'best_sellers' => $bestSellers,
                'trending' => $trending,
            ],
            'instagram' => $instagramItems,
        ]);
    }
}

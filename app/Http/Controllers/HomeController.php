<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\InstagramItem;
use App\Models\Product;
use App\Services\ZyraCache;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $payload = Cache::remember('web_home_payload', ZyraCache::TTL_HOME_PAYLOAD, function () {
            $with = ['category', 'subcategory', 'sizes', 'colors', 'images'];

            $categories = Category::query()
                ->active()
                ->with('subcategories')
                ->withCount('products')
                ->get()
                ->map(fn (Category $category) => $category->toNavArray())
                ->all();

            $newArrivals = Product::query()->with($with)->latest()->take(4)->get()->map->toCatalogArray()->all();
            $bestSellers = Product::query()->with($with)->bestSeller()->take(8)->get()->map->toCatalogArray()->all();
            $trending = Product::query()->with($with)->trending()->take(8)->get()->map->toCatalogArray()->all();
            $totalProductsCount = Product::query()->count();
            $allProducts = Product::query()->with($with)->latest()->take(8)->get()->map->toCatalogArray()->all();

            $heroBanner = Banner::query()->active()->where('position', 'hero')->first();
            $promoBanner = Banner::query()->active()->where('position', 'promo')->first();

            $instagramItems = InstagramItem::query()
                ->where('status', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (InstagramItem $item) => ['id' => $item->id, 'url' => $item->url])
                ->all();

            return compact(
                'categories',
                'newArrivals',
                'bestSellers',
                'trending',
                'allProducts',
                'totalProductsCount',
                'heroBanner',
                'promoBanner',
                'instagramItems'
            );
        });

        return view('home', $payload);
    }
}

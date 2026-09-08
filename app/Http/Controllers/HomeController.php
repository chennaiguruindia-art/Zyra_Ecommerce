<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\InstagramItem;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
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
        $allProducts = Product::query()->with($with)->latest()->get()->map->toCatalogArray()->all();

        $heroBanner = Banner::query()->active()->where('position', 'hero')->first();
        $promoBanner = Banner::query()->active()->where('position', 'promo')->first();

        $instagramItems = InstagramItem::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (InstagramItem $item) => ['id' => $item->id, 'url' => $item->url])
            ->all();

        return view('home', compact(
            'categories',
            'newArrivals',
            'bestSellers',
            'trending',
            'allProducts',
            'heroBanner',
            'promoBanner',
            'instagramItems'
        ));
    }
}

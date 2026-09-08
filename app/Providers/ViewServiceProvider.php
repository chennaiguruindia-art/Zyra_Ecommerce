<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['components.header', 'components.footer', 'components.filter-sidebar'], function ($view) {
            try {
                $navCategories = Category::query()
                    ->active()
                    ->with('subcategories')
                    ->withCount('products')
                    ->get()
                    ->map(fn (Category $category) => $category->toNavArray())
                    ->values()
                    ->all();

                $filterSizes = Size::query()->orderBy('sort_order')->pluck('name')->all();
                $filterColors = Color::query()->orderBy('name')->get(['name', 'hex_code']);
            } catch (Throwable $e) {
                $navCategories = [];
                $filterSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                $filterColors = collect();
            }

            $view->with('navCategories', $navCategories);
            $view->with('filterSizes', $filterSizes);
            $view->with('filterColors', $filterColors);
        });
    }
}

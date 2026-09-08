<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            static $navCategories = null;
            if ($navCategories === null) {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
                        $navCategories = \App\Models\Category::query()
                            ->active()
                            ->with('subcategories')
                            ->withCount('products')
                            ->get()
                            ->map(fn ($c) => $c->toNavArray())
                            ->all();
                    }
                } catch (\Throwable $e) {
                    $navCategories = [];
                }
            }
            $view->with('navCategories', $navCategories ?? []);
        });
    }
}

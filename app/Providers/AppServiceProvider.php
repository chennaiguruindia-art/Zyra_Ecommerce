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
        $this->app->extend('translation.loader', function ($loader, $app) {
            if ($loader instanceof \App\Translation\SafeFileLoader) {
                return $loader;
            }

            $safe = new \App\Translation\SafeFileLoader($app['files'], $loader->paths());

            foreach ($loader->jsonPaths() as $path) {
                $safe->addJsonPath($path);
            }

            foreach ($loader->namespaces() as $namespace => $hint) {
                $safe->addNamespace($namespace, $hint);
            }

            return $safe;
        });
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

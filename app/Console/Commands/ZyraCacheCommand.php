<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\ZyraCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ZyraCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zyra:cache {--clear : Clear all ZYRA caches instead of warming}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up or clear high-performance application caches for ZYRA';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('clear')) {
            $this->info('Clearing ZYRA application caches...');
            ZyraCache::clearAll();
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            $this->info('✓ All application, route, view, and config caches cleared.');
            return self::SUCCESS;
        }

        $this->info('🚀 Warming up ZYRA High-Performance Caches...');

        // 1. Navigation Categories
        $this->line('Caching Navigation Category Tree...');
        try {
            Cache::remember('zyra_nav_categories', ZyraCache::TTL_NAV_CATEGORIES, function () {
                return Category::query()
                    ->active()
                    ->with('subcategories')
                    ->withCount('products')
                    ->get()
                    ->map(fn ($c) => $c->toNavArray())
                    ->all();
            });
            $this->info('✓ Navigation categories cached.');
        } catch (\Throwable $e) {
            $this->warn('! Skipped nav categories (DB offline or table missing).');
        }

        // 2. Blade Views
        $this->line('Compiling Blade templates...');
        Artisan::call('view:cache');
        $this->info('✓ Blade templates compiled and cached.');

        // 3. Routes
        $this->line('Caching application routes...');
        Artisan::call('route:cache');
        $this->info('✓ Application routes cached.');

        $this->newLine();
        $this->info('🎉 ZYRA is fully optimized for 100+ concurrent shoppers!');
        return self::SUCCESS;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ZyraCache
{
    /**
     * Cache TTL constants (in seconds).
     */
    public const TTL_NAV_CATEGORIES = 86400; // 24 hours
    public const TTL_HOME_PAYLOAD   = 1800;  // 30 minutes
    public const TTL_PRODUCT_DETAIL = 900;   // 15 minutes

    /**
     * Invalidate home page caches (both web & mobile API).
     */
    public static function clearHome(): void
    {
        Cache::forget('web_home_payload');
        Cache::forget('api_home_payload');
    }

    /**
     * Invalidate navigation and category tree caches.
     */
    public static function clearCategories(): void
    {
        Cache::forget('zyra_nav_categories');
        Cache::forget('web_home_payload');
        Cache::forget('api_home_payload');
    }

    /**
     * Invalidate product detail caches.
     */
    public static function clearProduct(int|string $id, ?string $slug = null): void
    {
        Cache::forget("product_details_{$id}");
        if ($slug) {
            Cache::forget("product_details_{$slug}");
        }
        self::clearHome();
    }

    /**
     * Flush all application caches.
     */
    public static function clearAll(): void
    {
        self::clearCategories();
        self::clearHome();
    }
}

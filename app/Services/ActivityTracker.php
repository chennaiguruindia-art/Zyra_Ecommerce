<?php

namespace App\Services;

use App\Models\Color;
use App\Models\CustomerActivity;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Anonymous shopping-behavior tracking.
 *
 * Guests are identified by a random UUID cookie; logged-in shoppers by an
 * HMAC-SHA256 hash of their user id (stable across sessions, irreversible,
 * keyed by the app key). No name, email, phone, address or any other
 * personal data is ever written to customer_activities.
 *
 * Every method fails silently (debug log only) so tracking can never
 * break cart / wishlist / checkout / payment flows.
 */
class ActivityTracker
{
    public const COOKIE = 'zyra_aid';

    public static function anonymousId(): string
    {
        if (auth()->check()) {
            return 'u:' . hash_hmac('sha256', 'user:' . auth()->id(), (string) config('app.key'));
        }

        $id = (string) request()->cookie(self::COOKIE, '');
        if (!preg_match('/^g:[a-f0-9-]{10,60}$/i', $id)) {
            $id = 'g:' . (string) Str::uuid();
            try {
                cookie()->queue(self::COOKIE, $id, 60 * 24 * 365, null, null, null, true, false, 'Lax');
            } catch (\Throwable $e) {
                Log::debug('Analytics cookie queue failed', ['error' => $e->getMessage()]);
            }
        }

        return $id;
    }

    /**
     * @param array{size?: ?string, color?: ?string, quantity?: ?int} $opts
     */
    public static function track(string $action, int $productId, array $opts = []): void
    {
        try {
            if (!in_array($action, CustomerActivity::ACTIONS, true) || $productId <= 0) {
                return;
            }

            $product = Product::query()->select(['id', 'name'])->find($productId);
            if (!$product) {
                return;
            }

            $size = self::clean($opts['size'] ?? null, 40);
            $color = self::clean($opts['color'] ?? null, 60);
            $qty = isset($opts['quantity']) ? max(1, (int) $opts['quantity']) : null;

            CustomerActivity::create([
                'anonymous_id' => self::anonymousId(),
                'product_id' => $product->id,
                'product_name' => mb_substr($product->name, 0, 190),
                'variant_id' => self::resolveVariantId($size, $color),
                'size' => $size,
                'color' => $color,
                'action' => $action,
                'quantity' => $qty,
            ]);
        } catch (\Throwable $e) {
            Log::debug('Activity tracking failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Track the same action for many items (checkout started / purchased).
     * Each item: ['product_id' => int, 'size' => ?, 'color' => ?, 'quantity' => ?].
     */
    public static function trackMany(string $action, array $items): void
    {
        foreach ($items as $item) {
            if (!is_array($item) || empty($item['product_id'])) {
                continue;
            }
            self::track($action, (int) $item['product_id'], [
                'size' => $item['size'] ?? null,
                'color' => $item['color'] ?? null,
                'quantity' => $item['quantity'] ?? null,
            ]);
        }
    }

    /**
     * Reuse the existing Size/Color lookup rows instead of duplicating
     * variant data. Prefers the Size id, falls back to the Color id.
     */
    protected static function resolveVariantId(?string $size, ?string $color): ?int
    {
        try {
            if ($size !== null && $size !== '') {
                $id = Size::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($size)])->value('id');
                if ($id) {
                    return (int) $id;
                }
            }
            if ($color !== null && $color !== '') {
                $id = Color::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($color)])->value('id');
                if ($id) {
                    return (int) $id;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Variant id resolution failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected static function clean(mixed $value, int $max): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }
}
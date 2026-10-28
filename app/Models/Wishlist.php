<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $fillable = ['user_id', 'session_id', 'product_id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return the active query scope for the current visitor.
     * Logged-in: filter by user_id. Guest: filter by session_id.
     */
    public static function forCurrentUser(): \Illuminate\Database\Eloquent\Builder
    {
        if (auth()->check()) {
            return static::query()->where('user_id', auth()->id());
        }

        return static::query()->where('session_id', session()->getId());
    }

    /**
     * Return all product IDs in the current visitor's wishlist.
     */
    public static function currentIds(): array
    {
        return static::forCurrentUser()->pluck('product_id')->map(fn($id) => (int) $id)->all();
    }

    /**
     * After login: move any guest wishlist rows to the authenticated user.
     * Call this in your LoginController / authenticated event.
     */
    public static function mergeGuestToUser(int $userId, string $sessionId): void
    {
        $guestIds = static::query()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->pluck('product_id')
            ->all();

        foreach ($guestIds as $productId) {
            // upsert so we never violate the unique constraint
            static::query()->updateOrInsert(
                ['user_id' => $userId, 'product_id' => $productId],
                ['session_id' => null, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // Remove leftover guest rows after merge
        static::query()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->delete();
    }
}

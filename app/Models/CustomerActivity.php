<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerActivity extends Model
{
    public const VIEWED = 'product_viewed';
    public const WISHLIST_ADDED = 'wishlist_added';
    public const WISHLIST_REMOVED = 'wishlist_removed';
    public const CART_ADDED = 'cart_added';
    public const CART_REMOVED = 'cart_removed';
    public const CHECKOUT_STARTED = 'checkout_started';
    public const PURCHASED = 'purchase_completed';

    public const ACTIONS = [
        self::VIEWED,
        self::WISHLIST_ADDED,
        self::WISHLIST_REMOVED,
        self::CART_ADDED,
        self::CART_REMOVED,
        self::CHECKOUT_STARTED,
        self::PURCHASED,
    ];

    /** Funnel order for the customer-journey display. */
    public const FUNNEL = [
        self::VIEWED,
        self::WISHLIST_ADDED,
        self::CART_ADDED,
        self::CHECKOUT_STARTED,
        self::PURCHASED,
    ];

    protected $fillable = [
        'anonymous_id',
        'product_id',
        'product_name',
        'variant_id',
        'size',
        'color',
        'action',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeBetweenDates($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('customer_activities.created_at', '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $query->where('customer_activities.created_at', '<=', $to . ' 23:59:59');
        }

        return $query;
    }
}
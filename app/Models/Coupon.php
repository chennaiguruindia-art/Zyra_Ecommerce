<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'discount_type', 'discount_value', 'min_order_amount',
        'max_discount', 'start_date', 'expiry_date', 'status', 'used_count',
    ];

    protected $casts = [
        'status' => 'boolean',
        'start_date' => 'date',
        'expiry_date' => 'date',
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    public function isValidFor(float $orderAmount, ?int $userId = null): bool
    {
        if (!$this->status) return false;
        if ($orderAmount < $this->min_order_amount) return false;
        if ($this->start_date && Carbon::now()->lt($this->start_date)) return false;
        if ($this->expiry_date && Carbon::now()->gt($this->expiry_date->endOfDay())) return false;

        // FIRSTORDER: common first-purchase coupon, only valid if the customer has no orders yet.
        if (strtoupper($this->code) === 'FIRSTORDER') {
            if (!$userId) return false;
            $hasOrder = \App\Models\Order::where('user_id', $userId)->exists();
            if ($hasOrder) return false;
        }

        // Single-use per customer: a coupon code may be redeemed only once
        // per user. Past orders count and a cancelled order does not free it.
        if ($this->hasBeenUsedBy($userId)) {
            return false;
        }

        return true;
    }

    /**
     * Has this user already redeemed this coupon code on any past order?
     * Orders store both coupon_code and user_id, so history is the source
     * of truth (covers orders placed before this rule existed).
     */
    public function hasBeenUsedBy(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return \App\Models\Order::query()
            ->where('user_id', $userId)
            ->where('coupon_code', $this->code)
            ->exists();
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percent') {
            $discount = round(($subtotal * $this->discount_value) / 100, 2);
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
            return $discount;
        }
        return min($this->discount_value, $subtotal);
    }

    public function getLabelAttribute(): string
    {
        if ($this->discount_type === 'percent') {
            return $this->discount_value . '% Discount (' . $this->code . ')';
        }
        return '₹' . $this->discount_value . ' Off (' . $this->code . ')';
    }
}

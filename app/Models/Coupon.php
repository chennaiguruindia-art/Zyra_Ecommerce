<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'discount_type', 'discount_value', 'min_order_amount',
        'before_purchase_amount', 'max_discount', 'start_date', 'expiry_date', 'status', 'used_count',
    ];

    protected $casts = [
        'status' => 'boolean',
        'start_date' => 'date',
        'expiry_date' => 'date',
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'before_purchase_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    public function isValidFor(float $orderAmount, ?int $userId = null, ?string $email = null, ?string $phone = null): bool
    {
        if (!$this->status) return false;
        if ($orderAmount < $this->min_order_amount) return false;
        if ($this->start_date && Carbon::now()->lt($this->start_date)) return false;
        if ($this->expiry_date && Carbon::now()->gt($this->expiry_date->endOfDay())) return false;

        // FIRSTORDER: common first-purchase coupon, only valid if the customer has no orders yet.
        // Validated across user_id, email, and phone to prevent guest/multi-account abuse.
        if (strtoupper($this->code) === 'FIRSTORDER') {
            $hasOrder = \App\Models\Order::query()
                ->where(function ($q) use ($userId, $email, $phone) {
                    if ($userId) $q->orWhere('user_id', $userId);
                    if ($email && trim($email) !== '') $q->orWhereRaw('LOWER(customer_email) = ?', [strtolower(trim($email))]);
                    if ($phone) {
                        $digits = preg_replace('/\D/', '', $phone);
                        if ($digits !== '') $q->orWhere('customer_phone', 'LIKE', '%' . substr($digits, -10));
                    }
                })
                ->exists();

            if ($hasOrder) return false;
        }

        // Loyalty gate: the customer's LAST Delivered order must be worth at
        // least this coupon's before_purchase_amount. A guest (no userId) or a
        // customer with no Delivered orders has not purchased before, so they
        // are not eligible. When the field is empty/0 there is no gate.
        if (!$this->meetsBeforePurchaseRequirement($userId)) {
            return false;
        }

        // Single-use per customer: a coupon code may be redeemed only once
        // per customer across user_id, email, and phone number.
        if ($this->hasBeenUsedBy($userId, $email, $phone)) {
            return false;
        }

        return true;
    }

    /**
     * Does this customer satisfy the coupon's "before purchase amount" gate?
     * Compares the total of their most recent DELIVERED order (cancelled,
     * pending and in-transit orders do not count).
     */
    public function meetsBeforePurchaseRequirement(?int $userId): bool
    {
        $required = (float) ($this->before_purchase_amount ?? 0);
        if ($required <= 0) {
            return true; // No gate configured on this coupon.
        }

        if (!$userId) {
            return false; // Gate needs a known customer.
        }

        $lastDelivered = \App\Models\Order::query()
            ->where('user_id', $userId)
            ->where('order_status', 'Delivered')
            ->orderByDesc('id')
            ->value('total');

        if ($lastDelivered === null) {
            return false; // Nothing delivered yet — no purchase history.
        }

        return (float) $lastDelivered >= $required;
    }

    /**
     * Has this customer already redeemed this coupon code on any past order?
     * Checked across user_id, email, and phone number to stop multi-account abuse.
     */
    public function hasBeenUsedBy(?int $userId, ?string $email = null, ?string $phone = null): bool
    {
        if (!$userId && empty($email) && empty($phone)) {
            return false;
        }

        return \App\Models\Order::query()
            ->where('coupon_code', $this->code)
            ->where(function ($q) use ($userId, $email, $phone) {
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
                if ($email && trim($email) !== '') {
                    $q->orWhereRaw('LOWER(customer_email) = ?', [strtolower(trim($email))]);
                }
                if ($phone) {
                    $digits = preg_replace('/\D/', '', $phone);
                    if ($digits !== '') {
                        $q->orWhere('customer_phone', 'LIKE', '%' . substr($digits, -10));
                    }
                }
            })
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

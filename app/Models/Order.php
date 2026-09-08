<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'city', 'state', 'pincode',
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature', 'razorpay_amount', 'razorpay_status',
        'payment_method', 'payment_status',
        'subtotal', 'discount', 'shipping_cost', 'tax', 'total',
        'coupon_code', 'order_status', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function razorpayTransactions(): HasMany
    {
        return $this->hasMany(RazorpayTransaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ZYRA-' . strtoupper(substr(uniqid(), -6));
    }
}

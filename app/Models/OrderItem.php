<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'product_image',
        'price', 'quantity', 'size', 'color', 'total',
    ];

    protected $casts = ['price' => 'decimal:2', 'total' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->product_image && str_starts_with($this->product_image, 'http')) {
            return $this->product_image;
        }
        return $this->product_image ? asset('storage/' . $this->product_image) : asset('images/placeholder.jpg');
    }
}

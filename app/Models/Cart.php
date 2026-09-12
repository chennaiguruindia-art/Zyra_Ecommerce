<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'email',
        'phone',
        'items',
        'subtotal',
        'status',
        'last_activity_at',
        'reminded_at',
        'reminder_count',
        'converted_at',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'float',
        'last_activity_at' => 'datetime',
        'reminded_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CONVERTED = 'converted';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeWithEmail($query)
    {
        return $query->whereNotNull('email')->where('email', '!=', '');
    }

    public function scopeIdleSince($query, $threshold)
    {
        return $query->where(function ($q) use ($threshold) {
            $q->whereNull('last_activity_at')->orWhere('last_activity_at', '<', $threshold);
        });
    }

    public function scopeNotYetReminded($query)
    {
        return $query->whereNull('reminded_at');
    }

    public function markConverted(): void
    {
        $this->update([
            'status' => self::STATUS_CONVERTED,
            'converted_at' => now(),
        ]);
    }
}
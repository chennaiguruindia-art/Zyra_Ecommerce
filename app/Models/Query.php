<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    protected $fillable = [
        'order_id',
        'name',
        'phone_number',
        'email',
        'query_status',
        'query_subject',
        'message',
        'status',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_RESOLVED = 'resolved';

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }
}
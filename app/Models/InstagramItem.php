<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstagramItem extends Model
{
    protected $table = 'instagram_gallery';

    protected $fillable = [
        'url',
        'sort_order',
        'status',
    ];
}
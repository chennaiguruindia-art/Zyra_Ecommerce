<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        return \App\Support\ProductImageStorage::url($this->image, 'images/placeholder-category.jpg');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function toNavArray(): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image_url,
            'count' => $this->products_count ?? $this->products()->count(),
            'subcategories' => $this->relationLoaded('subcategories')
                ? $this->subcategories->pluck('name')->toArray()
                : $this->subcategories()->pluck('name')->toArray(),
        ];
    }
}

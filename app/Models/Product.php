<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'subcategory_id', 'name', 'slug', 'sku', 'price', 'old_price',
        'discount', 'stock_units', 'in_stock', 'image', 'material', 'fit', 'care',
        'description', 'badge', 'is_featured', 'is_best_seller', 'is_trending',
        'rating', 'reviews_count', 'weight',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_trending' => 'boolean',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function coverImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_cover', true)->limit(1);
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_size');
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_color');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageUrlAttribute(): string
    {
        return \App\Support\ProductImageStorage::url($this->image);
    }

    public function getSizeNamesAttribute(): array
    {
        return $this->sizes->pluck('name')->toArray();
    }

    public function getColorNamesAttribute(): array
    {
        return $this->colors->pluck('name')->toArray();
    }

    public function getColorCodesAttribute(): array
    {
        return $this->colors->pluck('hex_code')->toArray();
    }

    public function getGalleryUrlsAttribute(): array
    {
        return $this->images->map(function ($img) {
            return \App\Support\ProductImageStorage::url($img->image_path);
        })->toArray();
    }

    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestSeller($query)
    {
        return $query->where('is_best_seller', true);
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function toCatalogArray(): array
    {
        $this->loadMissing(['category', 'subcategory', 'sizes', 'colors', 'images']);

        $images = $this->gallery_urls;
        if (empty($images)) {
            $images = [$this->image_url];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category?->name ?? '',
            'category_slug' => $this->category?->slug ?? '',
            'subcategory' => $this->subcategory?->name ?? '',
            'price' => (float) $this->price,
            'old_price' => $this->old_price !== null ? (float) $this->old_price : null,
            'discount' => (int) ($this->discount ?? 0),
            'rating' => (float) $this->rating,
            'reviews' => (int) $this->reviews_count,
            'image' => $this->image_url,
            'images' => $images,
            'sizes' => $this->size_names,
            'colors' => $this->color_names,
            'color_codes' => $this->color_codes,
            'badge' => $this->badge ?? '',
            'stock' => (bool) $this->in_stock,
            'stock_units' => (int) $this->stock_units,
            'sku' => $this->sku,
            'material' => $this->material,
            'fit' => $this->fit,
            'care' => $this->care,
            'description' => $this->description,
            'featured' => (bool) $this->is_featured,
            'best_seller' => (bool) $this->is_best_seller,
            'trending' => (bool) $this->is_trending,
        ];
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['category'])) {
            $query->whereHas('category', fn($q) => $q->where('slug', $filters['category']));
        }
        if (!empty($filters['subcategory'])) {
            $query->whereHas('subcategory', fn($q) => $q->where('slug', $filters['subcategory']));
        }
        if (!empty($filters['sizes'])) {
            $query->whereHas('sizes', fn($q) => $q->whereIn('name', (array)$filters['sizes']));
        }
        if (!empty($filters['colors'])) {
            $query->whereHas('colors', fn($q) => $q->whereIn('name', (array)$filters['colors']));
        }
        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }
        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }
        if (!empty($filters['rating'])) {
            $query->where('rating', '>=', $filters['rating']);
        }
        if (!empty($filters['in_stock'])) {
            $query->where('in_stock', true);
        }
        return $query;
    }
}

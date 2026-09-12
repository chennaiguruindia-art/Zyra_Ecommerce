@props(['product'])

@php
    $id = $product['id'] ?? 1;
    $name = $product['name'] ?? 'Fashion Item';
    $category = $product['category'] ?? 'Apparel';
    $price = $product['price'] ?? 0;
    $oldPrice = $product['old_price'] ?? null;
    $discount = $product['discount'] ?? 0;
    $rating = $product['rating'] ?? 4.5;
    $reviews = $product['reviews'] ?? 50;
    $rawImage = $product['image'] ?? 'https://images.unsplash.com/photo-1534126511673-b6899657816a?auto=format&fit=crop&w=800&q=80';
    $image = (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) ? $rawImage : asset('storage/' . $rawImage);
    $badge = $product['badge'] ?? '';
    $starsFull = (int) round($rating);
@endphp

<div class="zyra-product-card">
    <div class="zyra-product-thumb">
        <a href="{{ route('product.details', $id) }}" class="d-block w-100 h-100">
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy">
        </a>

        <!-- Badges -->
        <div class="zyra-badge-stack">
            @if(!empty($badge))
                <span class="badge-zyra-{{ strtolower(str_replace(' ', '', $badge)) }}">{{ $badge }}</span>
            @endif
            @if($discount > 0)
                <span class="badge-zyra-discount">{{ $discount }}% OFF</span>
            @endif
        </div>

        <!-- Wishlist Button -->
        <button type="button" class="zyra-wishlist-btn" data-product-id="{{ $id }}" onclick="ZyraWishlist.toggleWishlist({{ $id }}, this)" title="Love it" aria-label="Love it">
            <i class="bi bi-heart"></i>
        </button>

        <!-- Hover Actions -->
        <div class="zyra-card-actions">
            <button type="button" class="btn-card-quickview" onclick="ZyraApp.openQuickView(@js($product))" title="Quick View" aria-label="Quick View">
                <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn-card-addcart" onclick="ZyraCart.addToCart({{ $id }}, null, null, 1, @js($product))" title="Add to Cart">
                <i class="bi bi-bag-plus"></i>
            </button>
        </div>
    </div>

    <!-- Product Details -->
    <div class="zyra-product-body">
        <span class="zyra-product-category">{{ $category }}</span>
        <h6 class="zyra-product-title">
            <a href="{{ route('product.details', $id) }}" title="{{ $name }}">{{ $name }}</a>
        </h6>

        <!-- Rating -->
        <div class="zyra-product-rating">
            <span>{!! str_repeat('★', $starsFull) . str_repeat('☆', 5 - $starsFull) !!}</span>
            <span class="review-count">({{ $reviews }})</span>
        </div>

        <!-- Pricing -->
        <div class="zyra-product-price-box">
            <span class="zyra-current-price">₹{{ $price }}</span>
            @if($oldPrice)
                <span class="zyra-old-price">₹{{ $oldPrice }}</span>
            @endif
            @if($discount > 0)
                <span class="zyra-discount-tag">{{ $discount }}% OFF</span>
            @endif
        </div>
    </div>
</div>

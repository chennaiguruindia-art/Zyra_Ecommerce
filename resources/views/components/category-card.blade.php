@props(['category'])

@php
    $slug = $category['slug'] ?? 'shop';
    $name = $category['name'] ?? 'Category';
    $rawImage = $category['image'] ?? 'https://images.unsplash.com/photo-1534126511673-b6899657816a?auto=format&fit=crop&w=800&q=80';
    $image = (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) ? $rawImage : asset('storage/' . $rawImage);
    $categoryImages = [
        'tops' => 'images/categories/tops.jpg',
        'leggings' => 'images/categories/leggings.jpg',
        'kurtis' => 'images/categories/kurthi.jpg',
        'maxi' => 'images/categories/maxi.jpg',
        'nightwear' => 'images/categories/nightwear.jpg',
    ];
    $image = isset($categoryImages[$slug]) ? asset($categoryImages[$slug]) : $image;
    $count = $category['count'] ?? 8;
@endphp

<a href="{{ route('category', $slug) }}" class="zyra-category-card text-decoration-none">
    <div class="zyra-category-img-wrap">
        <img src="{{ $image }}" alt="{{ $name }}" loading="lazy">
        <div class="zyra-category-overlay">
            <h5 class="zyra-category-title">{{ $name }}</h5>
            <span class="zyra-category-subtitle">{{ $count }}+ Styles Available</span>
            <div class="zyra-category-btn-shop">
                Shop Now <i class="bi bi-arrow-right"></i>
            </div>
        </div>
    </div>
</a>

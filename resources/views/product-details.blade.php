@extends('layouts.app')

@section('title', $product['name'] . ' | ZYRA Lifestyle')
@section('meta_description', $product['description'])
@section('og_title', $product['name'] . ' - ₹' . $product['price'])
@section('og_image', $product['image'])
@section('og_type', 'product')

@push('schema')
    {!! \App\Support\Seo::breadcrumbSchema([
        ['name' => 'Home', 'url' => \App\Support\Seo::url('/')],
        ['name' => 'Shop', 'url' => \App\Support\Seo::url('/shop')],
        ['name' => $product['category'] ?? 'Category', 'url' => \App\Support\Seo::url('/category/' . strtolower($product['category'] ?? ''))],
        ['name' => $product['name']],
    ]) !!}
    {!! \App\Support\Seo::productSchema($product) !!}
@endpush

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Shop', 'url' => route('shop')],
    ['label' => $product['category'], 'url' => route('category', strtolower($product['category']))],
    ['label' => $product['name'], 'url' => '']
]" />

<div class="container py-5">

    {{-- ═══════════════════ HERO: Gallery + Buy Panel ═══════════════════ --}}
    @php
        $mainImg = (str_starts_with($product['image'], 'http://') || str_starts_with($product['image'], 'https://')) ? $product['image'] : asset('storage/' . $product['image']);
        $defaultSize = null;
        foreach ($product['sizes'] as $size) {
            $sizeQty = isset($product['size_stock'][$size]) ? (int) $product['size_stock'][$size] : null;
            if ($sizeQty === null || $sizeQty > 0) { $defaultSize = $size; break; }
        }
        $defaultSize = $defaultSize ?? ($product['sizes'][0] ?? 'M');
        $shortDesc = mb_strimwidth(strip_tags($product['description']), 0, 140, '…');
    @endphp

    <div class="row g-5 zyra-pdp-hero">

        {{-- LEFT: Gallery --}}
        <div class="col-lg-6">
            <div class="zyra-pdp-gallery">
                <div class="zyra-pdp-gallery-main">
                    <img id="productMainImage" src="{{ $mainImg }}" alt="{{ $product['name'] }}" loading="eager">
                    @if($product['discount'] > 0)
                        <span class="zyra-pdp-badge">{{ $product['discount'] }}% OFF</span>
                    @endif
                </div>
                <div class="zyra-pdp-thumbs">
                    @foreach($product['images'] ?? [$product['image']] as $idx => $img)
                        @php
                            $resolvedImg = (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) ? $img : asset('storage/' . $img);
                        @endphp
                        <div class="zyra-thumb-item {{ $idx === 0 ? 'active' : '' }}" data-img-src="{{ $resolvedImg }}">
                            <img src="{{ $resolvedImg }}" alt="{{ $product['name'] }} thumbnail {{ $idx + 1 }}">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT: Buy Panel --}}
        <div class="col-lg-6">
            <div class="zyra-pdp-buy">

                <div class="zyra-pdp-eyebrow">{{ $product['category'] }} / {{ $product['subcategory'] }}</div>
                <h1 class="zyra-pdp-title">{{ $product['name'] }}</h1>

                {{-- Rating Row --}}
                <div class="zyra-pdp-rating-row">
                    <div class="d-flex align-items-center gap-1 text-warning">
                        <span class="d-inline-flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= (int)round($product['rating']))
                                    <i class="bi bi-star-fill"></i>
                                @elseif($i - 0.5 <= $product['rating'])
                                    <i class="bi bi-star-half"></i>
                                @else
                                    <i class="bi bi-star"></i>
                                @endif
                            @endfor
                        </span>
                        <span class="text-dark fw-bold ms-1">{{ $product['rating'] }}</span>
                    </div>
                    <span class="text-muted">·</span>
                    <span class="text-muted">{{ $product['reviews'] }} reviews</span>
                    <span class="text-muted">·</span>
                    <span class="text-muted">SKU {{ $product['sku'] }}</span>
                </div>

                {{-- Price --}}
                <div class="zyra-pdp-price-box">
                    <span id="pdPriceDisplay" class="zyra-pdp-price">₹{{ $product['price'] }}</span>
                    @if($product['old_price'])
                        <span class="zyra-pdp-old-price">₹{{ $product['old_price'] }}</span>
                    @endif
                    @if($product['discount'] > 0)
                        <span class="zyra-pdp-discount-badge">SAVE {{ $product['discount'] }}%</span>
                    @endif
                    <div class="zyra-pdp-tax-note">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>Free shipping
                    </div>
                </div>

                {{-- Short description --}}
                <p class="zyra-pdp-short">{{ $shortDesc }}</p>

                <div class="zyra-pdp-divider"></div>

                {{-- Color Selection --}}
                @if(count($product['colors']) > 1)
                <div class="zyra-pdp-option-group">
                    <label class="zyra-pdp-label">
                        Color: <span id="selectedColorName">{{ $product['colors'][0] ?? 'Default' }}</span>
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product['colors'] as $idx => $color)
                            @php $code = $product['color_codes'][$idx] ?? '#1a1a1a'; @endphp
                            <button type="button"
                                class="zyra-pdp-swatch pd-color-swatch {{ $idx === 0 ? 'active' : '' }}"
                                style="background-color: {{ $code }};"
                                data-color="{{ $color }}"
                                title="{{ $color }}"
                                onclick="document.querySelectorAll('.pd-color-swatch').forEach(b=>b.classList.remove('active')); this.classList.add('active'); document.getElementById('selectedColorName').textContent='{{ $color }}';">
                                <span class="visually-hidden">{{ $color }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Size Selection --}}
                <div class="zyra-pdp-option-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="zyra-pdp-label m-0">
                            Size: <span id="selectedSizeName">{{ $defaultSize }}</span>
                        </label>
                        <button type="button" class="zyra-pdp-sizechart-link" data-bs-toggle="modal" data-bs-target="#sizeChartModal">
                            <i class="bi bi-rulers me-1"></i>Size Chart
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product['sizes'] as $size)
                            @php
                                $sizeQty = isset($product['size_stock'][$size]) ? (int) $product['size_stock'][$size] : null;
                                $sizeSoldOut = $sizeQty !== null && $sizeQty <= 0;
                            @endphp
                            <div class="position-relative d-inline-block">
                                <button type="button"
                                    class="zyra-pdp-size pd-size-btn {{ $size === $defaultSize ? 'active' : '' }}"
                                    data-size="{{ $size }}"
                                    @if($sizeSoldOut) disabled @endif
                                    onclick="document.querySelectorAll('.pd-size-btn').forEach(b=>b.classList.remove('active')); this.classList.add('active'); document.getElementById('selectedSizeName').textContent='{{ $size }}';">
                                    {{ $size }}
                                </button>
                                @if($sizeSoldOut)
                                    <span class="zyra-pdp-size-soldout"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Dupatta Option --}}
                @if(!empty($product['dupatta_enabled']) && !empty($product['without_dupatta_price']) && (float) $product['without_dupatta_price'] < (float) $product['price'])
                <div class="zyra-pdp-option-group">
                    <label class="zyra-pdp-label">Dupatta Option</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="zyra-pdp-chip pd-dupatta-btn active" data-dupatta="1">
                            <i class="bi bi-check-circle-fill me-1"></i>With Dupatta
                            <span class="zyra-pdp-chip-price">₹{{ $product['price'] }}</span>
                        </button>
                        <button type="button" class="zyra-pdp-chip pd-dupatta-btn" data-dupatta="0">
                            Without Dupatta
                            <span class="zyra-pdp-chip-price">₹{{ $product['without_dupatta_price'] }}</span>
                        </button>
                    </div>
                    <small class="zyra-pdp-hint"><i class="bi bi-tag me-1"></i>Choose "Without Dupatta" and save ₹{{ (float) $product['price'] - (float) $product['without_dupatta_price'] }}</small>
                </div>
                @endif

                <div class="zyra-pdp-divider"></div>

                {{-- Qty + Actions --}}
                <div class="zyra-pdp-option-group">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="d-flex align-items-center">
                            <span class="zyra-pdp-label m-0 me-2">Qty</span>
                            <x-quantity-selector :value="1" id="pdQuantityInput" />
                        </div>

                        <button type="button" class="zyra-pdp-add-to-cart" id="pdAddToCartBtn" onclick="
                            const size = document.querySelector('.pd-size-btn.active')?.getAttribute('data-size') || 'M';
                            const color = document.querySelector('.pd-color-swatch.active')?.getAttribute('data-color') || 'Standard';
                            const qty = parseInt(document.getElementById('pdQuantityInput')?.value) || 1;
                            ZyraCart.addToCart({{ (int) $product['id'] }}, size, color, qty, null, selectedDupatta);
                        ">
                            <i class="bi bi-bag-plus me-1"></i> Add to Cart
                        </button>

                        <button type="button" class="zyra-pdp-buy-now" onclick="
                            const size = document.querySelector('.pd-size-btn.active')?.getAttribute('data-size') || 'M';
                            const color = document.querySelector('.pd-color-swatch.active')?.getAttribute('data-color') || 'Standard';
                            const qty = parseInt(document.getElementById('pdQuantityInput')?.value) || 1;
                            if (ZyraCart.addToCart({{ (int) $product['id'] }}, size, color, qty, null, selectedDupatta)) {
                                setTimeout(() => window.location.href = '{{ route('checkout') }}', 300);
                            }
                        ">
                            Buy Now
                        </button>

                        <button type="button" class="zyra-pdp-wishlist-btn zyra-wishlist-btn" data-product-id="{{ $product['id'] }}" onclick="ZyraWishlist.toggleWishlist({{ $product['id'] }}, this)" title="Add to Wishlist" aria-label="Add to Wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                </div>

                {{-- Assurances --}}
                <div class="zyra-pdp-assurances">
                    <div class="zyra-pdp-assurance"><i class="bi bi-shield-check"></i><span>100% Original</span></div>
                    <div class="zyra-pdp-assurance"><i class="bi bi-truck"></i><span>Free Delivery</span></div>
                </div>

            </div>
        </div>

    </div>

    {{-- ═══════════════════ Editorial: About This Design ═══════════════════ --}}
    <div class="zyra-pdp-editorial mt-5 pt-5 border-top">
        <div class="zyra-pdp-editorial-header text-center mb-4">
            <span class="zyra-pdp-eyebrow">ZYRA EDITORIAL</span>
            <h2 class="zyra-pdp-section-title">About This Design</h2>
            <div class="zyra-pdp-header-rule"></div>
        </div>
        <div class="row g-5 align-items-start">
            <div class="col-lg-7">
                <p class="zyra-pdp-editorial-text">{{ $product['description'] }}</p>
                <p class="zyra-pdp-editorial-text">
                    Designed specifically for the contemporary woman who appreciates traditional craftsmanship combined with clean, modern silhouettes. Made with breathable lightweight fibers that offer maximum airflow and luxurious all-day comfort.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="zyra-pdp-highlights-card">
                    <h6 class="zyra-pdp-highlights-title"><i class="bi bi-stars me-1"></i>Style Highlights</h6>
                    <ul class="zyra-pdp-highlights-list">
                        <li><i class="bi bi-check2-circle"></i> Versatile for morning casual or evening gatherings</li>
                        <li><i class="bi bi-check2-circle"></i> Pre-shrunk and bio-washed fabric</li>
                        <li><i class="bi bi-check2-circle"></i> Double-stitched seams for longevity</li>
                        <li><i class="bi bi-check2-circle"></i> Fade-resistant eco-friendly dye</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ Info Tabs ═══════════════════ --}}
    <div class="zyra-pdp-tabs mt-5 pt-5 border-top">
        <ul class="nav zyra-pdp-tab-nav justify-content-center mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc-tab-pane" type="button" role="tab">Description</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs-tab-pane" type="button" role="tab">Specifications & Care</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shipping-tab-pane" type="button" role="tab">Shipping & Returns</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews-tab-pane" type="button" role="tab">Reviews ({{ $product['reviews'] }})</button>
            </li>
        </ul>

        <div class="zyra-pdp-tab-content tab-content">

            {{-- Description --}}
            <div class="tab-pane fade show active" id="desc-tab-pane" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <h5 class="zyra-pdp-tab-heading">About This Design</h5>
                        <p class="text-muted">{{ $product['description'] }}</p>
                        <p class="text-muted">
                            Designed specifically for the contemporary woman who appreciates traditional craftsmanship combined with clean, modern silhouettes. Made with breathable lightweight fibers that offer maximum airflow and luxurious all-day comfort.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="zyra-pdp-highlights-card">
                            <h6 class="zyra-pdp-highlights-title">Style Highlights</h6>
                            <ul class="zyra-pdp-highlights-list">
                                <li><i class="bi bi-check2-circle"></i> Versatile for morning casual or evening gatherings</li>
                                <li><i class="bi bi-check2-circle"></i> Pre-shrunk and bio-washed fabric</li>
                                <li><i class="bi bi-check2-circle"></i> Double-stitched seams for longevity</li>
                                <li><i class="bi bi-check2-circle"></i> Fade-resistant eco-friendly dye</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Specifications --}}
            <div class="tab-pane fade" id="specs-tab-pane" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h5 class="zyra-pdp-tab-heading">Product Details</h5>
                        <div class="zyra-pdp-spec-grid">
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Material</span><span class="zyra-pdp-spec-val">{{ $product['material'] ?? 'Pure Cotton' }}</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Fit</span><span class="zyra-pdp-spec-val">{{ $product['fit'] ?? 'Regular Fit' }}</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Pattern</span><span class="zyra-pdp-spec-val">{{ $product['subcategory'] }}</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Origin</span><span class="zyra-pdp-spec-val">Crafted with pride in India</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="zyra-pdp-tab-heading">Care Instructions</h5>
                        <div class="zyra-pdp-spec-grid">
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Wash</span><span class="zyra-pdp-spec-val">{{ $product['care'] ?? 'Gentle Machine Wash' }}</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Ironing</span><span class="zyra-pdp-spec-val">Warm iron on reverse side</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Bleach</span><span class="zyra-pdp-spec-val">Do not bleach</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Translucency</span><span class="zyra-pdp-spec-val">Opaque / Lined where needed</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shipping --}}
            <div class="tab-pane fade" id="shipping-tab-pane" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="zyra-pdp-ship-card">
                            <div class="zyra-pdp-ship-icon"><i class="bi bi-box-seam"></i></div>
                            <h6 class="zyra-pdp-ship-title">Shipping Policy</h6>
                            <p class="text-muted small mb-2">Orders are dispatched within 24–48 hours from our Mumbai fulfillment center.</p>
                            <ul class="zyra-pdp-ship-list">
                                <li><i class="bi bi-check2"></i> Metro Cities: 2–4 business days</li>
                                <li><i class="bi bi-check2"></i> Rest of India: 4–7 business days</li>
                                <li><i class="bi bi-check2"></i> Free standard shipping on all orders</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="zyra-pdp-ship-card">
                            <div class="zyra-pdp-ship-icon"><i class="bi bi-arrow-repeat"></i></div>
                            <h6 class="zyra-pdp-ship-title">Returns & Exchanges</h6>
                            <p class="text-muted small mb-2">We want you to love your purchase. If the fit is not right, simply initiate an exchange from your order portal.</p>
                            <ul class="zyra-pdp-ship-list">
                                <li><i class="bi bi-check2"></i> Reverse pickup provided at no extra charge</li>
                                <li><i class="bi bi-check2"></i> Item must have original tags intact and remain unworn</li>
                                <li><i class="bi bi-check2"></i> Refunds processed within 48 hours of return receipt</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reviews --}}
            <div class="tab-pane fade" id="reviews-tab-pane" role="tabpanel">
                <div class="row g-4 align-items-start">
                    <div class="col-md-3 text-center zyra-pdp-review-summary">
                        <div class="zyra-pdp-review-big">{{ $product['rating'] }}</div>
                        <div class="text-warning fs-5 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= (int)round($product['rating']))
                                    <i class="bi bi-star-fill"></i>
                                @elseif($i - 0.5 <= $product['rating'])
                                    <i class="bi bi-star-half"></i>
                                @else
                                    <i class="bi bi-star"></i>
                                @endif
                            @endfor
                        </div>
                        <p class="text-muted small">Based on {{ $product['reviews'] }} authentic ratings</p>
                    </div>
                    <div class="col-md-9">
                        <div class="zyra-pdp-review-list">
                            @forelse($reviews ?? [] as $review)
                                <div class="zyra-pdp-review-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $review->customer_name }}</strong>
                                        <span class="text-warning small">
                                            @for($i = 1; $i <= 5; $i++)<i class="bi bi-star{{ $i <= (int) $review->rating ? '-fill' : '' }}"></i>@endfor
                                        </span>
                                    </div>
                                    <small class="text-muted">@if($review->is_verified)Verified Buyer · @endif{{ $review->created_at?->diffForHumans() }}</small>
                                    @if($review->comment)
                                        <p class="text-muted small mt-2 mb-0">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="zyra-pdp-review-item">
                                    <p class="text-muted small mb-1"><strong>No reviews yet</strong> — be the first to share your experience!</p>
                                    <small class="text-muted">Bought this item? Rate it from your <a href="{{ route('my-orders') }}">My Orders</a> page.</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══════════════════ Related Products ═══════════════════ --}}
    @if(!empty($relatedProducts))
    <div class="zyra-pdp-related mt-5 pt-5 border-top">
        <div class="zyra-pdp-editorial-header text-center mb-4">
            <span class="zyra-pdp-eyebrow">COMPLETE THE LOOK</span>
            <h2 class="zyra-pdp-section-title">You May Also Like</h2>
            <div class="zyra-pdp-header-rule"></div>
        </div>
        <div class="row g-4">
            @foreach($relatedProducts as $rel)
                <div class="col-6 col-md-3">
                    <x-product-card :product="$rel" />
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════════════ Recently Viewed ═══════════════════ --}}
    @if(!empty($recentlyViewed))
    <div class="zyra-pdp-recent mt-5 pt-5 border-top">
        <div class="zyra-pdp-editorial-header text-center mb-4">
            <span class="zyra-pdp-eyebrow">BROWSING HISTORY</span>
            <h2 class="zyra-pdp-section-title">Recently Viewed</h2>
            <div class="zyra-pdp-header-rule"></div>
        </div>
        <div class="row g-4">
            @foreach($recentlyViewed as $recent)
                <div class="col-6 col-md-3">
                    <x-product-card :product="$recent" />
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- ═══════════════════ Size Chart Modal (redesigned) ═══════════════════ --}}
<div class="modal fade" id="sizeChartModal" tabindex="-1" aria-labelledby="sizeChartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="zyra-sc-heading">
                    <span class="zyra-size-chart-brand">ZYRA <span>FIT GUIDE</span></span>
                    <h5 class="modal-title" id="sizeChartModalLabel">Size &amp; Fit Guide</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="zyra-sc-tip-row">
                    <span class="zyra-sc-tip"><i class="bi bi-arrows-expand"></i> Chest</span>
                    <span class="zyra-sc-tip"><i class="bi bi-arrows-collapse"></i> Waist</span>
                    <span class="zyra-sc-tip"><i class="bi bi-arrow-left-right"></i> Hip</span>
                </div>
                <ul class="nav zyra-sc-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#scKurti" type="button" role="tab"><i class="bi bi-person-standing me-1"></i> Kurti</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#scPant" type="button" role="tab"><i class="bi bi-person-walking me-1"></i> Pant</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="scKurti" role="tabpanel">
                        <table class="table zyra-sc-table text-center align-middle">
                            <thead><tr><th>Size</th><th>Chest</th><th>Waist</th><th>Hip</th></tr></thead>
                            <tbody>
                                <tr><td>XXS</td><td>32</td><td>28</td><td>35</td></tr>
                                <tr><td>XS</td><td>34</td><td>30</td><td>37</td></tr>
                                <tr><td>S</td><td>36</td><td>32</td><td>39</td></tr>
                                <tr><td>M</td><td>38</td><td>34</td><td>41</td></tr>
                                <tr><td>L</td><td>40</td><td>36</td><td>43</td></tr>
                                <tr><td>XL</td><td>42</td><td>38</td><td>45</td></tr>
                                <tr><td>XXL</td><td>44</td><td>40</td><td>47</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="scPant" role="tabpanel">
                        <table class="table zyra-sc-table text-center align-middle">
                            <thead><tr><th>Size</th><th>Length</th><th>Waist</th><th>Ankle</th></tr></thead>
                            <tbody>
                                <tr><td>XXS</td><td>35½</td><td>23</td><td>10</td></tr>
                                <tr><td>XS</td><td>36</td><td>24</td><td>10</td></tr>
                                <tr><td>S</td><td>36</td><td>26</td><td>11</td></tr>
                                <tr><td>M</td><td>37</td><td>28</td><td>12</td></tr>
                                <tr><td>L</td><td>37½</td><td>29</td><td>12</td></tr>
                                <tr><td>XL</td><td>37½</td><td>29</td><td>12</td></tr>
                                <tr><td>XXL</td><td>38</td><td>31</td><td>13</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="zyra-sc-length-row">
                    <div class="zyra-sc-length"><i class="bi bi-rulers"></i> Kurti Length <strong>44"</strong></div>
                    <div class="zyra-sc-length"><i class="bi bi-rulers"></i> Maxi Length <strong>46"</strong></div>
                </div>
                <p class="zyra-sc-footnote"><i class="bi bi-info-circle me-1"></i> Measurements are in inches. For in-between sizes, choose the larger size.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.ZYRA_CURRENT_PRODUCT = @json($product);
    let selectedDupatta = true;
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.pd-dupatta-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.pd-dupatta-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedDupatta = btn.getAttribute('data-dupatta') === '1';
                const priceDisplay = document.getElementById('pdPriceDisplay');
                if (priceDisplay) {
                    priceDisplay.textContent = '₹' + (selectedDupatta
                        ? {{ json_encode((float) $product['price']) }}
                        : {{ json_encode((float) $product['without_dupatta_price']) }});
                }
            });
        });
    });
</script>
@endpush

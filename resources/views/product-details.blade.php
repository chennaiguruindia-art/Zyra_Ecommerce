@extends('layouts.app')

@section('title', $product['name'] . ' | ZYRA Lifestyle')
@section('meta_description', $product['description'])
@section('og_title', $product['name'] . ' - ₹' . $product['price'])
@section('og_image', $product['image'])
@section('og_type', 'product')

@php
    $categoryName = (string) ($product['category'] ?? '');
    $categorySlug = (string) ($product['category_slug'] ?? '');
    $breadcrumbs = [['label' => 'Shop', 'url' => route('shop')]];
    $schemaBreadcrumbs = [
        ['name' => 'Home', 'url' => \App\Support\Seo::url('/')],
        ['name' => 'Shop', 'url' => \App\Support\Seo::url('/shop')],
    ];

    if ($categoryName !== '' && $categorySlug !== '') {
        $breadcrumbs[] = [
            'label' => $categoryName,
            'url' => route('category', $categorySlug),
        ];
        $schemaBreadcrumbs[] = array_merge(
            ['name' => $categoryName],
            ['url' => \App\Support\Seo::url('/category/' . $categorySlug)]
        );
    }

    $breadcrumbs[] = ['label' => $product['name'], 'url' => ''];
    $schemaBreadcrumbs[] = ['name' => $product['name']];
    $schemaProduct = $product;
    $schemaProduct['in_stock'] = $product['stock'] ?? false;
@endphp

@push('schema')
    {!! \App\Support\Seo::breadcrumbSchema($schemaBreadcrumbs) !!}
    {!! \App\Support\Seo::productSchema($schemaProduct) !!}
@endpush

@section('content')

<x-breadcrumb :items="$breadcrumbs" />

<div class="container py-5">

    {{-- ═══════════════════ HERO: Gallery + Buy Panel ═══════════════════ --}}
    @php
        $mainImg = $product['image'] ?? '';
        $sizes = $product['sizes'] ?? [];
        $sizeStock = $product['size_stock'] ?? [];
        $stockUnits = (int) ($product['stock_units'] ?? 0);
        $availableSizes = [];

        foreach ($sizes as $size) {
            $sizeQty = array_key_exists($size, $sizeStock) ? (int) $sizeStock[$size] : $stockUnits;
            if ($sizeQty > 0 && !empty($product['stock'])) {
                $availableSizes[] = $size;
            }
        }

        $defaultSize = $availableSizes[0] ?? (empty($sizes) && !empty($product['stock']) && $stockUnits > 0 ? 'M' : null);
        $canPurchase = empty($sizes)
            ? !empty($product['stock']) && $stockUnits > 0
            : !empty($availableSizes);
        $productPrice = (float) ($product['price'] ?? 0);
        $withoutDupattaPrice = isset($product['without_dupatta_price'])
            ? (float) $product['without_dupatta_price']
            : null;
        $oldPrice = isset($product['old_price']) ? (float) $product['old_price'] : null;
        $dupattaPriceDifference = $withoutDupattaPrice !== null
            ? max(0, $productPrice - $withoutDupattaPrice)
            : 0;
        $withoutDupattaOldPrice = $oldPrice !== null
            ? max($withoutDupattaPrice ?? 0, $oldPrice - $dupattaPriceDifference)
            : null;
        $withoutDupattaDiscount = $withoutDupattaPrice !== null
            && $withoutDupattaOldPrice !== null
            && $withoutDupattaOldPrice > $withoutDupattaPrice
            ? (int) round((($withoutDupattaOldPrice - $withoutDupattaPrice) / $withoutDupattaOldPrice) * 100)
            : 0;
        $canChooseDupatta = !empty($product['dupatta_enabled'])
            && $withoutDupattaPrice !== null
            && $withoutDupattaPrice < $productPrice;
        $shortDesc = mb_strimwidth(strip_tags((string) ($product['description'] ?? '')), 0, 140, '…');
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
                        <button type="button" class="zyra-thumb-item {{ $idx === 0 ? 'active' : '' }}"
                            data-img-src="{{ $resolvedImg }}" aria-label="View product image {{ $idx + 1 }}"
                            aria-pressed="{{ $idx === 0 ? 'true' : 'false' }}">
                            <img src="{{ $resolvedImg }}" alt="">
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT: Buy Panel --}}
        <div class="col-lg-6">
            <div class="zyra-pdp-buy">

                <div class="zyra-pdp-eyebrow">{{ $product['category'] }} / {{ $product['subcategory'] }}</div>
                <h1 class="zyra-pdp-title">{{ $product['name'] }}</h1>

                {{-- SKU Row --}}
                <div class="zyra-pdp-rating-row">
                    <span class="text-muted">SKU {{ $product['sku'] }}</span>
                </div>

                {{-- Price --}}
                <div class="zyra-pdp-price-box">
                    <span id="pdPriceDisplay" class="zyra-pdp-price"
                        data-price-with-dupatta="{{ $productPrice }}"
                        data-price-without-dupatta="{{ $withoutDupattaPrice ?? $productPrice }}">₹{{ $productPrice }}</span>
                    @if($oldPrice !== null && $oldPrice > $productPrice)
                        <span id="pdOldPriceDisplay" class="zyra-pdp-old-price"
                            data-old-price-with-dupatta="{{ $oldPrice }}"
                            data-old-price-without-dupatta="{{ $withoutDupattaOldPrice }}">₹{{ $oldPrice }}</span>
                    @endif
                    @if($product['discount'] > 0)
                        <span id="pdDiscountBadge" class="zyra-pdp-discount-badge"
                            data-discount-with-dupatta="{{ (int) $product['discount'] }}"
                            data-discount-without-dupatta="{{ $withoutDupattaDiscount }}">SAVE {{ $product['discount'] }}%</span>
                    @endif
                    <div class="zyra-pdp-tax-note">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>Free shipping
                    </div>
                </div>

                {{-- Short description --}}
                <p class="zyra-pdp-short">{{ $shortDesc }}</p>

                <div class="zyra-pdp-divider"></div>

                {{-- Color Selection --}}
                @if(count($product['colors'] ?? []) > 1)
                <div class="zyra-pdp-option-group">
                    <label class="zyra-pdp-label">
                        Color: <span id="selectedColorName">{{ $product['colors'][0] }}</span>
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product['colors'] as $idx => $color)
                            @php
                                $code = $product['color_codes'][$idx] ?? '#1a1a1a';
                                if (!is_string($code) || !preg_match('/\A#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})\z/', $code)) {
                                    $code = '#1a1a1a';
                                }
                            @endphp
                            <button type="button"
                                class="zyra-pdp-swatch pd-color-swatch {{ $idx === 0 ? 'active' : '' }}"
                                aria-pressed="{{ $idx === 0 ? 'true' : 'false' }}"
                                style="--swatch-color: {{ $code }}; background-color: var(--swatch-color);"
                                data-color="{{ $color }}"
                                title="{{ $color }}">
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
                            Size: <span id="selectedSizeName">{{ $defaultSize ?? 'Unavailable' }}</span>
                        </label>
                        <button type="button" class="zyra-pdp-sizechart-link" data-bs-toggle="modal" data-bs-target="#sizeChartModal">
                            <i class="bi bi-rulers me-1"></i>Size Chart
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($sizes as $size)
                            @php
                                $sizeQty = array_key_exists($size, $sizeStock) ? (int) $sizeStock[$size] : $stockUnits;
                                $sizeSoldOut = $sizeQty <= 0 || empty($product['stock']);
                            @endphp
                            <div class="position-relative d-inline-block">
                                <button type="button"
                                    class="zyra-pdp-size pd-size-btn {{ $size === $defaultSize ? 'active' : '' }}"
                                    data-size="{{ $size }}"
                                    aria-pressed="{{ $size === $defaultSize ? 'true' : 'false' }}"
                                    @if($sizeSoldOut) disabled @endif>
                                    {{ $size }}
                                </button>
                                @if($sizeSoldOut)
                                    <span class="zyra-pdp-size-soldout"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @unless($canPurchase)
                        <p class="text-danger small mt-2 mb-0" role="status">Currently out of stock.</p>
                    @endunless
                </div>

                {{-- Dupatta Option --}}
                @if($canChooseDupatta)
                <div class="zyra-pdp-option-group">
                    <label class="zyra-pdp-label">Dupatta Option</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="zyra-pdp-chip pd-dupatta-btn active" data-dupatta="1" aria-pressed="true">
                            <i class="bi bi-check-circle-fill me-1"></i>With Dupatta
                            <span class="zyra-pdp-chip-price">₹{{ $productPrice }}</span>
                        </button>
                        <button type="button" class="zyra-pdp-chip pd-dupatta-btn" data-dupatta="0" aria-pressed="false">
                            Without Dupatta
                            <span class="zyra-pdp-chip-price">₹{{ $withoutDupattaPrice }}</span>
                        </button>
                    </div>
                    <small class="zyra-pdp-hint"><i class="bi bi-tag me-1"></i>Choose "Without Dupatta" and save ₹{{ $dupattaPriceDifference }}</small>
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

                        <button type="button" class="zyra-pdp-add-to-cart" id="pdAddToCartBtn"
                            data-product-id="{{ (int) $product['id'] }}" @disabled(!$canPurchase)>
                            <i class="bi bi-bag-plus me-1"></i> Add to Cart
                        </button>

                        <button type="button" class="zyra-pdp-buy-now" data-product-id="{{ (int) $product['id'] }}"
                            @disabled(!$canPurchase)>
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
                <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-tab-pane" type="button" role="tab" aria-controls="specs-tab-pane" aria-selected="true">Specifications & Care</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping-tab-pane" type="button" role="tab" aria-controls="shipping-tab-pane" aria-selected="false">Shipping & Returns</button>
            </li>
        </ul>

        <div class="zyra-pdp-tab-content tab-content">

            {{-- Specifications --}}
            <div class="tab-pane fade show active" id="specs-tab-pane" role="tabpanel" aria-labelledby="specs-tab">
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
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Wash</span><span class="zyra-pdp-spec-val">{{ $product['care'] ?? 'Dry clean for the first wash. For subsequent washes, gently hand wash in cold water with mild detergent. Avoid wringing; if machine washing, use a delicate cycle.' }}</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Ironing</span><span class="zyra-pdp-spec-val">Warm iron on reverse side</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Bleach</span><span class="zyra-pdp-spec-val">Do not bleach</span></div>
                            <div class="zyra-pdp-spec-row"><span class="zyra-pdp-spec-key">Translucency</span><span class="zyra-pdp-spec-val">Opaque / Lined where needed</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shipping --}}
            <div class="tab-pane fade" id="shipping-tab-pane" role="tabpanel" aria-labelledby="shipping-tab">
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
                        <button class="nav-link active" id="sc-kurti-tab" data-bs-toggle="tab" data-bs-target="#scKurti" type="button" role="tab" aria-controls="scKurti" aria-selected="true"><i class="bi bi-person-standing me-1"></i> Kurti</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sc-pant-tab" data-bs-toggle="tab" data-bs-target="#scPant" type="button" role="tab" aria-controls="scPant" aria-selected="false"><i class="bi bi-person-walking me-1"></i> Pant</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="scKurti" role="tabpanel" aria-labelledby="sc-kurti-tab">
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
                    <div class="tab-pane fade" id="scPant" role="tabpanel" aria-labelledby="sc-pant-tab">
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

    // Meta Pixel – ViewContent (product detail page)
    window.zyraMeta && window.zyraMeta.track('ViewContent', {
        content_ids: [String(@json($product['id'] ?? ''))],
        content_type: 'product',
        content_name: @json($product['name'] ?? ''),
        content_category: @json($product['category'] ?? ''),
        value: {{ (float) ($product['price'] ?? 0) }},
        currency: 'INR'
    });

    let selectedDupatta = true;
    document.addEventListener('DOMContentLoaded', () => {
        const buyPanel = document.querySelector('.zyra-pdp-buy');
        if (!buyPanel) return;

        const thumbnails = document.querySelectorAll('.zyra-thumb-item');
        thumbnails.forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', () => {
                thumbnails.forEach(item => item.setAttribute('aria-pressed', 'false'));
                thumbnail.setAttribute('aria-pressed', 'true');
                document.getElementById('productMainImage').alt = @json($product['name'] ?? '') + ' image ' + (index + 1);
            });
        });

        buyPanel.querySelectorAll('.pd-color-swatch').forEach(btn => {
            btn.addEventListener('click', () => {
                buyPanel.querySelectorAll('.pd-color-swatch').forEach(swatch => {
                    swatch.classList.remove('active');
                    swatch.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
                document.getElementById('selectedColorName').textContent = btn.dataset.color;
            });
        });

        buyPanel.querySelectorAll('.pd-size-btn:not(:disabled)').forEach(btn => {
            btn.addEventListener('click', () => {
                buyPanel.querySelectorAll('.pd-size-btn').forEach(sizeButton => {
                    sizeButton.classList.remove('active');
                    sizeButton.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
                document.getElementById('selectedSizeName').textContent = btn.dataset.size;
            });
        });

        buyPanel.querySelectorAll('.pd-dupatta-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                buyPanel.querySelectorAll('.pd-dupatta-btn').forEach(option => {
                    option.classList.remove('active');
                    option.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
                selectedDupatta = btn.dataset.dupatta === '1';

                const priceDisplay = document.getElementById('pdPriceDisplay');
                const price = Number(priceDisplay.dataset[selectedDupatta ? 'priceWithDupatta' : 'priceWithoutDupatta']);
                priceDisplay.textContent = '₹' + price.toLocaleString('en-IN', { maximumFractionDigits: 2 });

                const oldPriceDisplay = document.getElementById('pdOldPriceDisplay');
                if (oldPriceDisplay) {
                    const oldPrice = Number(oldPriceDisplay.dataset[selectedDupatta ? 'oldPriceWithDupatta' : 'oldPriceWithoutDupatta']);
                    oldPriceDisplay.hidden = !Number.isFinite(oldPrice) || oldPrice <= price;
                    if (!oldPriceDisplay.hidden) {
                        oldPriceDisplay.textContent = '₹' + oldPrice.toLocaleString('en-IN', { maximumFractionDigits: 2 });
                    }
                }

                const discountBadge = document.getElementById('pdDiscountBadge');
                if (discountBadge) {
                    const discount = Number(discountBadge.dataset[selectedDupatta ? 'discountWithDupatta' : 'discountWithoutDupatta']);
                    discountBadge.hidden = !Number.isFinite(discount) || discount <= 0;
                    if (!discountBadge.hidden) {
                        discountBadge.textContent = 'SAVE ' + discount + '%';
                    }
                }
            });
        });

        const addToCart = (button, buyNow = false) => {
            const size = buyPanel.querySelector('.pd-size-btn.active')?.dataset.size || 'M';
            const color = buyPanel.querySelector('.pd-color-swatch.active')?.dataset.color || 'Standard';
            const quantity = Number.parseInt(document.getElementById('pdQuantityInput')?.value, 10) || 1;
            const added = ZyraCart.addToCart(Number(button.dataset.productId), size, color, quantity, null, selectedDupatta);

            if (buyNow && added) {
                setTimeout(() => {
                    window.location.href = @json(route('checkout'));
                }, 300);
            }
        };

        document.getElementById('pdAddToCartBtn')?.addEventListener('click', event => addToCart(event.currentTarget));
        buyPanel.querySelector('.zyra-pdp-buy-now')?.addEventListener('click', event => addToCart(event.currentTarget, true));
    });
</script>
@endpush

@extends('layouts.app')

@section('title', $product['name'] . ' | ZYRA Fashion')
@section('meta_description', $product['description'])
@section('og_title', $product['name'] . ' - ₹' . $product['price'])
@section('og_image', $product['image'])

@section('content')

<!-- Breadcrumb -->
<x-breadcrumb :items="[
    ['label' => 'Shop', 'url' => route('shop')],
    ['label' => $product['category'], 'url' => route('category', strtolower($product['category']))],
    ['label' => $product['name'], 'url' => '']
]" />

<div class="container py-5">
    <div class="row g-5">
        
        <!-- Left: Image Gallery with Zoom & Thumbnails -->
        <div class="col-lg-6">
            @php
                $mainImg = (str_starts_with($product['image'], 'http://') || str_starts_with($product['image'], 'https://')) ? $product['image'] : asset('storage/' . $product['image']);
            @endphp
            <div class="zyra-gallery-main-view">
                <img id="productMainImage" src="{{ $mainImg }}" alt="{{ $product['name'] }}" loading="eager">
                @if($product['discount'] > 0)
                    <div class="zyra-badge-stack">
                        <span class="badge-zyra-discount fs-6">{{ $product['discount'] }}% OFF</span>
                    </div>
                @endif
            </div>

            <!-- Thumbnails -->
            <div class="zyra-thumb-strip">
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

        <!-- Right: Product Information & Purchase Controls -->
        <div class="col-lg-6">
            <span class="text-uppercase small fw-bold text-muted letter-spacing-1">{{ $product['category'] }} / {{ $product['subcategory'] }}</span>
            <h1 class="h2 fw-bold mt-1 mb-2">{{ $product['name'] }}</h1>
            
            <!-- Rating & SKU -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="d-flex align-items-center gap-1 text-warning">
                    <span>{!! str_repeat('★', (int)round($product['rating'])) . str_repeat('☆', 5 - (int)round($product['rating'])) !!}</span>
                    <span class="text-dark fw-bold small ms-1">{{ $product['rating'] }}</span>
                </div>
                <span class="text-muted small">|</span>
                <span class="text-muted small">{{ $product['reviews'] }} Customer Reviews</span>
                <span class="text-muted small">|</span>
                <span class="text-muted small">SKU: <strong>{{ $product['sku'] }}</strong></span>
            </div>

            <!-- Pricing Box -->
            <div class="p-3 bg-light rounded-3 mb-3">
                <div class="d-flex align-items-baseline gap-3">
                    <span class="fs-2 fw-bold text-dark">₹{{ $product['price'] }}</span>
                    @if($product['old_price'])
                        <span class="fs-5 text-muted text-decoration-line-through">₹{{ $product['old_price'] }}</span>
                    @endif
                    @if($product['discount'] > 0)
                        <span class="badge bg-danger fs-6">{{ $product['discount'] }}% OFF</span>
                    @endif
                </div>
                <div class="text-muted small mt-1">
                    <i class="bi bi-check2-circle text-success me-1"></i> MRP inclusive of all taxes
                </div>
            </div>

            <!-- Stock Availability -->
            <div class="mb-3">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="bi bi-box-seam me-1"></i> In Stock & Ready to Ship
                </span>
            </div>

            <!-- Color Selection -->
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase mb-2">
                    Color: <span id="selectedColorName" class="fw-normal text-muted">{{ $product['colors'][0] ?? 'Default' }}</span>
                </label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($product['colors'] as $idx => $color)
                        @php
                            $code = $product['color_codes'][$idx] ?? '#1a1a1a';
                        @endphp
                        <button type="button" class="color-swatch-btn pd-color-swatch {{ $idx === 0 ? 'active' : '' }}" 
                            style="background-color: {{ $code }};" 
                            data-color="{{ $color }}" 
                            title="{{ $color }}"
                            onclick="document.querySelectorAll('.pd-color-swatch').forEach(b => b.classList.remove('active')); this.classList.add('active'); document.getElementById('selectedColorName').textContent = '{{ $color }}';">
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Size Selection & Size Chart Modal Trigger -->
            <div class="mb-4">
                @php
    $defaultSize = null;
    foreach ($product['sizes'] as $size) {
        $sizeQty = isset($product['size_stock'][$size]) ? (int) $product['size_stock'][$size] : null;
        if ($sizeQty === null || $sizeQty > 0) { $defaultSize = $size; break; }
    }
    $defaultSize = $defaultSize ?? ($product['sizes'][0] ?? 'M');
@endphp
<div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold small text-uppercase m-0">
                        Size: <span id="selectedSizeName" class="fw-normal text-muted">{{ $defaultSize }}</span>
                    </label>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" data-bs-toggle="modal" data-bs-target="#sizeChartModal">
                        <i class="bi bi-rulers me-1"></i> Size Chart
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($product['sizes'] as $idx => $size)
                        @php
                            $sizeQty = isset($product['size_stock'][$size]) ? (int) $product['size_stock'][$size] : null;
                            $sizeSoldOut = $sizeQty !== null && $sizeQty <= 0;
                        @endphp
                        <div>
                            <button type="button" class="btn btn-outline-dark pd-size-btn {{ $size === $defaultSize ? 'active' : '' }}" 
                                data-size="{{ $size }}"
                                @if($sizeSoldOut) disabled @endif
                                onclick="document.querySelectorAll('.pd-size-btn').forEach(b => b.classList.remove('active')); this.classList.add('active'); document.getElementById('selectedSizeName').textContent = '{{ $size }}';">
                                {{ $size }}
                            </button>
                            @if($sizeSoldOut)
                                <div class="small text-danger mt-1 text-center">Out of stock</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quantity & Actions -->
            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center">
                    <span class="small fw-bold text-uppercase me-2">Qty:</span>
                    <x-quantity-selector :value="1" id="pdQuantityInput" />
                </div>
                
                <!-- Add to Cart -->
                <button type="button" class="btn btn-zyra-primary flex-grow-1 py-2" id="pdAddToCartBtn" onclick="
                    const size = document.querySelector('.pd-size-btn.active')?.getAttribute('data-size') || 'M';
                    const color = document.querySelector('.pd-color-swatch.active')?.getAttribute('data-color') || 'Standard';
                    const qty = parseInt(document.getElementById('pdQuantityInput')?.value) || 1;
                    ZyraCart.addToCart({{ (int) $product['id'] }}, size, color, qty);
                ">
                    <i class="bi bi-bag-plus me-1"></i> Add to Cart
                </button>

                <!-- Buy Now Button -->
                <button type="button" class="btn btn-zyra-secondary py-2" onclick="
                    const size = document.querySelector('.pd-size-btn.active')?.getAttribute('data-size') || 'M';
                    const color = document.querySelector('.pd-color-swatch.active')?.getAttribute('data-color') || 'Standard';
                    const qty = parseInt(document.getElementById('pdQuantityInput')?.value) || 1;
                    if (ZyraCart.addToCart({{ (int) $product['id'] }}, size, color, qty)) {
                        setTimeout(() => window.location.href = '{{ route('checkout') }}', 300);
                    }
                ">
                    Buy Now
                </button>

                <!-- Wishlist Toggle -->
                <button type="button" class="btn btn-outline-dark p-2 px-3 zyra-wishlist-btn position-static" data-product-id="{{ $product['id'] }}" onclick="ZyraWishlist.toggleWishlist({{ $product['id'] }}, this)" title="Add to Wishlist">
                    <i class="bi bi-heart fs-5"></i>
                </button>
            </div>

            <!-- Pincode Delivery Checker -->
            <div class="zyra-pincode-box">
                <label class="form-label fw-bold small text-uppercase mb-2">
                    <i class="bi bi-geo-alt me-1"></i> Estimate Delivery Time & Availability
                </label>
                <div class="input-group input-group-sm">
                    <input type="text" id="pincodeInput" class="form-control" placeholder="Enter 6-digit PIN code (e.g. 560001)" maxlength="6">
                    <button class="btn btn-dark" type="button" id="checkPincodeBtn">Check</button>
                </div>
                <div id="pincodeFeedback"></div>
            </div>

            <!-- Assurance Mini-features -->
            <div class="row g-2 border-top pt-3 text-muted small">
                <div class="col-6"><i class="bi bi-arrow-counterclockwise text-dark me-1"></i> 15 Days Easy Returns</div>
                <div class="col-6"><i class="bi bi-shield-check text-dark me-1"></i> 100% Original Products</div>
                <div class="col-6"><i class="bi bi-cash-coin text-dark me-1"></i> Cash on Delivery Available</div>
                <div class="col-6"><i class="bi bi-truck text-dark me-1"></i> Free Shipping</div>
            </div>

        </div>

    </div>

    <!-- Product Description, Details, Care & Reviews Tabs -->
    <div class="mt-5 pt-4 border-top">
        <ul class="nav nav-tabs justify-content-center border-bottom-0 mb-4" id="productDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-uppercase px-4" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-tab-pane" type="button" role="tab">Description</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase px-4" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-tab-pane" type="button" role="tab">Specifications & Care</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase px-4" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping-tab-pane" type="button" role="tab">Shipping & Returns</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase px-4" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-tab-pane" type="button" role="tab">Reviews ({{ $product['reviews'] }})</button>
            </li>
        </ul>

        <div class="tab-content bg-white p-4 rounded-3 border shadow-sm" id="productDetailTabContent">
            <!-- 1. Description Tab -->
            <div class="tab-pane fade show active" id="desc-tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <h5 class="fw-bold mb-3">About This Design</h5>
                        <p class="text-muted leading-relaxed">{{ $product['description'] }}</p>
                        <p class="text-muted leading-relaxed">
                            Designed specifically for the contemporary woman who appreciates traditional craftsmanship combined with clean, modern silhouettes. Made with breathable lightweight fibers that offer maximum airflow and luxurious all-day comfort.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="p-3 bg-light rounded-3">
                            <h6 class="fw-bold mb-2">Style Highlights</h6>
                            <ul class="small text-muted ps-3 mb-0">
                                <li>Versatile for morning casual or evening gatherings</li>
                                <li>Pre-shrunk and bio-washed fabric</li>
                                <li>Double-stitched seams for longevity</li>
                                <li>Fade-resistant eco-friendly dye</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Specifications Tab -->
            <div class="tab-pane fade" id="specs-tab-pane" role="tabpanel">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-striped small">
                            <tbody>
                                <tr>
                                    <th class="w-40 text-muted">Material</th>
                                    <td>{{ $product['material'] ?? 'Pure Cotton' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Fit</th>
                                    <td>{{ $product['fit'] ?? 'Regular Fit' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Pattern</th>
                                    <td>{{ $product['subcategory'] }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Origin</th>
                                    <td>Crafted with pride in India</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-striped small">
                            <tbody>
                                <tr>
                                    <th class="w-40 text-muted">Care Instructions</th>
                                    <td>{{ $product['care'] ?? 'Gentle Machine Wash' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Ironing</th>
                                    <td>Warm iron on reverse side</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Bleach</th>
                                    <td>Do not bleach</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Translucency</th>
                                    <td>Opaque / Lined where needed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Shipping Tab -->
            <div class="tab-pane fade" id="shipping-tab-pane" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2"><i class="bi bi-box-seam me-1"></i> Shipping Policy</h6>
                        <p class="small text-muted mb-2">Orders are dispatched within 24–48 hours from our Mumbai fulfillment center.</p>
                        <ul class="small text-muted ps-3">
                            <li>Metro Cities: 2–4 business days delivery</li>
                            <li>Rest of India: 4–7 business days delivery</li>
                            <li>Free standard shipping on all orders</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2"><i class="bi bi-arrow-repeat me-1"></i> Secure Delivery</h6>
                        <p class="small text-muted mb-2">We want you to love your purchase. If the fit is not right, simply initiate an exchange from your order portal.</p>
                        <ul class="small text-muted ps-3">
                            <li>Reverse pickup provided at no extra charge</li>
                            <li>Item must have original tags intact and remain unworn</li>
                            <li>Refunds processed within 48 hours of return receipt</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 4. Reviews Tab -->
            <div class="tab-pane fade" id="reviews-tab-pane" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-4 text-center border-end">
                        <div class="display-4 fw-bold text-dark">{{ $product['rating'] }}</div>
                        <div class="text-warning fs-5 mb-1">
                            {!! str_repeat('★', (int)round($product['rating'])) . str_repeat('☆', 5 - (int)round($product['rating'])) !!}
                        </div>
                        <p class="text-muted small">Based on {{ $product['reviews'] }} authentic ratings</p>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-column gap-3">
                            <div class="border-bottom pb-3">
                                <div class="d-flex justify-content-between">
                                    <strong>Meera Deshmukh</strong>
                                    <span class="text-warning small">★★★★★</span>
                                </div>
                                <small class="text-muted">Verified Buyer &bull; 2 weeks ago</small>
                                <p class="small text-muted mt-2 mb-0">The fabric handfeel is remarkably soft. True to size and colors matched the photography exactly!</p>
                            </div>
                            <div class="border-bottom pb-3">
                                <div class="d-flex justify-content-between">
                                    <strong>Swati Sen</strong>
                                    <span class="text-warning small">★★★★☆</span>
                                </div>
                                <small class="text-muted">Verified Buyer &bull; 1 month ago</small>
                                <p class="small text-muted mt-2 mb-0">Very well made. Stitching and finishing are high grade. Would definitely recommend sizing up if you prefer a relaxed silhouette.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if(!empty($relatedProducts))
        <div class="mt-5 pt-4">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="text-uppercase small fw-bold text-muted">Complete The Look</span>
                    <h3 class="fw-bold mt-1">Related Products</h3>
                </div>
                <a href="{{ route('category', strtolower($product['category'])) }}" class="btn btn-sm btn-zyra-outline">
                    View More {{ $product['category'] }}
                </a>
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

    <!-- Recently Viewed Products -->
    @if(!empty($recentlyViewed))
        <div class="mt-5 pt-4 border-top">
            <h3 class="fw-bold mb-4">Recently Viewed Products</h3>
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

<!-- Size Chart Modal -->
<div class="modal fade" id="sizeChartModal" tabindex="-1" aria-labelledby="sizeChartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="sizeChartModalLabel">ZYRA Standard Size Guide (Inches)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered text-center small align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Size</th>
                            <th>Bust (in)</th>
                            <th>Waist (in)</th>
                            <th>Hip (in)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>XS</td><td>32</td><td>26</td><td>35</td></tr>
                        <tr><td>S</td><td>34</td><td>28</td><td>37</td></tr>
                        <tr><td>M</td><td>36</td><td>30</td><td>39</td></tr>
                        <tr><td>L</td><td>38</td><td>32</td><td>41</td></tr>
                        <tr><td>XL</td><td>40</td><td>34</td><td>43</td></tr>
                        <tr><td>XXL</td><td>42</td><td>36</td><td>45</td></tr>
                    </tbody>
                </table>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i> Measurements indicate body size, not garment dimensions. For in-between sizes, choose the larger size for relaxed styling.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.ZYRA_CURRENT_PRODUCT = @json($product);
</script>
@endpush

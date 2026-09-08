@extends('layouts.app')

@section('title', 'Women\'s Tops - Casual, Crop, Office & Party Wear | ZYRA')
@section('meta_description', 'Discover chic women\'s tops at ZYRA. From breathable cotton peplums and crop tops to linen formal shirts and satin camisoles.')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Shop', 'url' => route('shop')],
    ['label' => 'Tops', 'url' => '']
]" />

<div class="py-4" style="background-color: var(--zyra-bg-beige);">
    <div class="container">
        <div class="row align-items-center py-2">
            <div class="col-md-8">
                <span class="badge bg-dark text-white px-2 py-1 mb-2">Collection</span>
                <h1 class="display-6 fw-bold mb-1">Women's Tops</h1>
                <p class="text-muted mb-0">From effortless weekend crops and crisp formal linen shirts to night-out lurex shimmer tops.</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="badge bg-white text-dark border">Casual Tops</span>
                    <span class="badge bg-white text-dark border">Crop Tops</span>
                    <span class="badge bg-white text-dark border">Printed Tops</span>
                    <span class="badge bg-white text-dark border">Office Wear</span>
                    <span class="badge bg-white text-dark border">Party Tops</span>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="text-muted small">Showing {{ count($products) }} exclusive styles</span>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-3">
            <x-filter-sidebar />
        </div>
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <span id="shopProductCount" class="text-muted small">Showing {{ count($products) }} products</span>
                <div class="d-flex align-items-center gap-2">
                    <label for="shopSortSelect" class="small text-muted text-nowrap">Sort By:</label>
                    <select id="shopSortSelect" class="form-select form-select-sm" style="width: 180px;">
                        <option value="featured">Featured</option>
                        <option value="newest">Newest</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="rating">Rating</option>
                    </select>
                </div>
            </div>

            <div id="shopActiveFiltersBar" class="zyra-active-filters-bar"></div>

            <div id="shopProductGrid" class="row g-4" data-category-slug="tops">
                @foreach($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>

            <div id="shopEmptyState" class="text-center py-5" style="display: none;">
                <div class="text-muted mb-3 fs-1"><i class="bi bi-funnel"></i></div>
                <h5 class="fw-bold mb-2">No matching tops found</h5>
                <p class="text-muted small mb-3">Adjust your filters to discover more tops.</p>
                <button type="button" class="btn btn-sm btn-zyra-primary" onclick="ZyraApp.resetShopFilters()">Clear Filters</button>
            </div>
        </div>
    </div>
</div>

@endsection

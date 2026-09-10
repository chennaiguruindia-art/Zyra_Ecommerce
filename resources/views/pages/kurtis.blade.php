@extends('layouts.app')

@section('title', 'Women\'s Kurtis - Handblock Cotton, Anarkalis & Chikankari | ZYRA')
@section('meta_description', 'Discover handcrafted Indian kurtis at ZYRA. Authentic Bagru block prints, celebratory 32-kali Anarkalis, and Lucknowi Chikankari.')



@push('schema')
    {!! \App\Support\Seo::breadcrumbSchema([
        ['name' => 'Home', 'url' => \App\Support\Seo::url('/')],
        ['name' => 'Shop', 'url' => \App\Support\Seo::url('/shop')],
        ['name' => 'Kurtis'],
    ]) !!}
@endpush

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Shop', 'url' => route('shop')],
    ['label' => 'Kurtis', 'url' => '']
]" />

<div class="py-4" style="background-color: var(--zyra-bg-beige);">
    <div class="container">
        <div class="row align-items-center py-2">
            <div class="col-md-8">
                <span class="badge bg-dark text-white px-2 py-1 mb-2">Ethnic Masterpieces</span>
                <h1 class="display-6 fw-bold mb-1">Women's Kurtis</h1>
                <p class="text-muted mb-0">Handspun organic cotton, artisan block prints, flowing Anarkalis, and Chikankari embroidery.</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="badge bg-white text-dark border">Handblock Kurtis</span>
                    <span class="badge bg-white text-dark border">Anarkali Flare</span>
                    <span class="badge bg-white text-dark border">Chikankari</span>
                    <span class="badge bg-white text-dark border">Straight Office Kurtis</span>
                    <span class="badge bg-white text-dark border">Mirror Work Party</span>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="text-muted small">Showing {{ count($products) }} handcrafted styles</span>
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

            <div id="shopProductGrid" class="row g-4" data-category-slug="kurtis">
                @foreach($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>

            <div id="shopEmptyState" class="text-center py-5" style="display: none;">
                <div class="text-muted mb-3 fs-1"><i class="bi bi-funnel"></i></div>
                <h5 class="fw-bold mb-2">No matching kurtis found</h5>
                <button type="button" class="btn btn-sm btn-zyra-primary" onclick="ZyraApp.resetShopFilters()">Clear Filters</button>
            </div>
        </div>
    </div>
</div>

@endsection

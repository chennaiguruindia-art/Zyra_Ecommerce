@extends('layouts.app')

@section('title', 'Shop Modern Women\'s Clothing & Ethnic Wear | ZYRA Lifestyle')
@section('meta_description', 'Explore ZYRA Lifestyle\'s complete women\'s fashion collection. Shop tops, leggings, kurtis, maxi dresses, nightwear online. Filter by size, color, and price.')

@push('schema')
    {!! \App\Support\Seo::breadcrumbSchema([
        ['name' => 'Home', 'url' => \App\Support\Seo::url('/')],
        ['name' => 'Shop'],
    ]) !!}
    {!! \App\Support\Seo::websiteSchema() !!}
    {!! \App\Support\Seo::itemListSchema('Women\'s Fashion Collection - ZYRA Lifestyle', $products ?? []) !!}
@endpush

@section('content')

<!-- Breadcrumb -->
<x-breadcrumb :items="[['label' => 'Shop All Products', 'url' => '']]" />

@php
    $activeFilter = request()->input('filter', '');
    $toolbarTitle = match ($activeFilter) {
        'new' => 'New Arrivals',
        'sale' => 'Sale',
        'trending' => 'Trending',
        default => 'All Products',
    };
    $activeCategory = request()->input('category', '');
    $chips = [['slug' => '', 'name' => 'All']];
    foreach ($navCategories ?? [] as $navCat) {
        $chips[] = ['slug' => $navCat['slug'], 'name' => $navCat['name']];
    }
@endphp

<div class="container py-4">
    <div class="row g-4">

        <!-- Left Filter Sidebar (desktop) / Bottom-Sheet Drawer (mobile) -->
        <div class="col-lg-3 zyra-filter-col" id="shopFilterCol">
            <div class="zyra-filter-sheet-header d-lg-none">
                <h5 class="fw-bold m-0"><i class="bi bi-funnel me-1"></i> Filters</h5>
                <button type="button" class="btn-close" aria-label="Close" onclick="ZyraApp.toggleMobileFilters(false)"></button>
            </div>
            <x-filter-sidebar />
            <button type="button" class="btn btn-zyra-primary w-100 mt-3 d-lg-none" onclick="ZyraApp.toggleMobileFilters(false)">
                Done <i class="bi bi-check2 ms-1"></i>
            </button>
        </div>

        <!-- Mobile Filter Backdrop -->
        <div class="zyra-filter-backdrop" id="shopFilterBackdrop" onclick="ZyraApp.toggleMobileFilters(false)"></div>

        <!-- Right Product Section -->
        <div class="col-lg-9">

            <!-- Mobile App-Style Toolbar -->
            <div class="zyra-mobile-toolbar d-lg-none">
                <button type="button" class="zyra-mobile-tool-btn" id="mobileFilterBtn" onclick="ZyraApp.toggleMobileFilters(true)">
                    <i class="bi bi-funnel"></i>
                    <span>Filter</span>
                </button>

                <div class="zyra-mobile-tool-title">
                    <div class="zyra-mobile-tool-title-main">{{ $toolbarTitle }}</div>
                    <span id="shopProductCount" class="text-muted">Showing {{ count($products) }} products</span>
                </div>

                <button type="button" class="zyra-mobile-tool-btn" id="mobileSortBtn" onclick="ZyraApp.toggleMobileSort(true)">
                    <i class="bi bi-arrow-down-up"></i>
                    <span>Sort</span>
                </button>
            </div>

            <!-- Mobile Category Chips -->
            <div class="zyra-cat-chips d-lg-none" id="mobileCatChips">
                @foreach($chips as $chip)
                    <button type="button" class="zyra-cat-chip {{ $chip['slug'] === $activeCategory ? 'active' : '' }}"
                        data-cat="{{ $chip['slug'] }}" onclick="ZyraApp.selectMobileChip(this)">
                        {{ $chip['name'] }}
                    </button>
                @endforeach
            </div>

            <!-- Desktop Toolbar -->
            <div class="d-none d-lg-flex justify-content-between align-items-center pb-3 mb-3 border-bottom gap-2">
                <div>
                    <h1 class="h4 fw-bold mb-1">{{ $toolbarTitle }}</h1>
                    <span class="text-muted small" id="shopProductCountDesktop">Showing {{ count($products) }} products</span>
                </div>

                <!-- Sorting Dropdown -->
                <div class="d-flex align-items-center gap-2">
                    <label for="shopSortSelect" class="small text-muted text-nowrap">Sort By:</label>
                    <select id="shopSortSelect" class="form-select form-select-sm" style="width: 190px;">
                        <option value="featured" selected>Featured</option>
                        <option value="newest">Newest Arrivals</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="rating">Best Rated</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>
            </div>

            <!-- Sort Bottom Sheet -->
            <div class="zyra-sort-sheet" id="mobileSortSheet">
                <div class="zyra-sort-sheet-handle"></div>
                <h6 class="zyra-sort-sheet-title">Sort By</h6>
                <div class="zyra-sort-options" id="mobileSortOptions">
                    <button type="button" class="zyra-sort-option active" data-sort="featured">Featured</button>
                    <button type="button" class="zyra-sort-option" data-sort="newest">Newest Arrivals</button>
                    <button type="button" class="zyra-sort-option" data-sort="price-low">Price: Low to High</button>
                    <button type="button" class="zyra-sort-option" data-sort="price-high">Price: High to Low</button>
                    <button type="button" class="zyra-sort-option" data-sort="rating">Best Rated</button>
                    <button type="button" class="zyra-sort-option" data-sort="popular">Most Popular</button>
                </div>
            </div>

            <!-- Active Filter Badges Bar -->
            <div id="shopActiveFiltersBar" class="zyra-active-filters-bar"></div>

            <!-- Product Grid -->
            <div id="shopProductGrid" class="row g-3 g-sm-4">
                @foreach($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>

            <!-- Empty Results State -->
            <div id="shopEmptyState" class="text-center py-5" style="display: none;">
                <div class="text-muted mb-3 fs-1">
                    <i class="bi bi-funnel"></i>
                </div>
                <h5 class="fw-bold mb-2">No matching products found</h5>
                <p class="text-muted small mb-4">Try adjusting your filter criteria or clear all filters to see more results.</p>
                <button type="button" class="btn btn-sm btn-zyra-primary" onclick="ZyraApp.resetShopFilters()">
                    Clear All Filters
                </button>
            </div>

        </div>

    </div>
</div>

@endsection
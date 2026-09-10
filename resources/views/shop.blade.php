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

<div class="container py-4">
    <div class="row g-4">
        
        <!-- Left Filter Sidebar -->
        <div class="col-lg-3">
            <x-filter-sidebar />
        </div>

        <!-- Right Product Grid Section -->
        <div class="col-lg-9">
            
            <!-- Shop Header Toolbar -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center pb-3 mb-3 border-bottom gap-2">
                <div>
                    <h1 class="h4 fw-bold mb-1">Women's Fashion Collection</h1>
                    <span id="shopProductCount" class="text-muted small">Showing {{ count($products) }} products</span>
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

            <!-- Active Filter Badges Bar -->
            <div id="shopActiveFiltersBar" class="zyra-active-filters-bar"></div>

            <!-- Product Grid -->
            <div id="shopProductGrid" class="row g-4 transition-opacity">
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

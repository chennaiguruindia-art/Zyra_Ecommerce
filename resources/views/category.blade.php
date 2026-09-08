@extends('layouts.app')

@section('title', ($currentCategory['name'] ?? 'Category') . ' | ZYRA Fashion')
@section('meta_description', $currentCategory['description'] ?? 'Shop our exclusive women collection.')

@section('content')

<!-- Breadcrumb -->
<x-breadcrumb :items="[
    ['label' => 'Shop', 'url' => route('shop')],
    ['label' => $currentCategory['name'], 'url' => '']
]" />

<!-- Category Hero Banner -->
<div class="py-4" style="background-color: var(--zyra-bg-beige);">
    <div class="container">
        <div class="row align-items-center py-3">
            <div class="col-md-8">
                <span class="text-uppercase small fw-bold text-muted">Category</span>
                <h1 class="display-6 fw-bold mt-1 mb-2">{{ $currentCategory['name'] }}</h1>
                <p class="text-muted mb-0">{{ $currentCategory['description'] }}</p>
                
                @if(!empty($currentCategory['subcategories']))
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @foreach($currentCategory['subcategories'] as $sub)
                            <span class="badge bg-white text-dark border px-3 py-2 fw-medium" style="font-size: 0.8rem;">
                                {{ $sub }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-dark text-white p-2 px-3 fs-6">
                    {{ count($products) }} Styles
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        
        <!-- Left Filter Sidebar -->
        <div class="col-lg-3">
            <x-filter-sidebar />
        </div>

        <!-- Right Product Grid -->
        <div class="col-lg-9">
            <!-- Toolbar -->
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

            <!-- Active filters -->
            <div id="shopActiveFiltersBar" class="zyra-active-filters-bar"></div>

            <!-- Products Grid -->
            <div id="shopProductGrid" class="row g-4" data-category-slug="{{ $currentCategory['slug'] ?? '' }}">
                @forelse($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">No products found in this category.</p>
                        <a href="{{ route('shop') }}" class="btn btn-sm btn-zyra-primary">Return to Shop</a>
                    </div>
                @endforelse
            </div>

            <div id="shopEmptyState" class="text-center py-5" style="display: none;">
                <div class="text-muted mb-3 fs-1"><i class="bi bi-funnel"></i></div>
                <h5 class="fw-bold mb-2">No matching products</h5>
                <p class="text-muted small mb-3">Adjust your filters to see more styles.</p>
                <button type="button" class="btn btn-sm btn-zyra-primary" onclick="ZyraApp.resetShopFilters()">
                    Clear Filters
                </button>
            </div>
        </div>

    </div>
</div>

@endsection

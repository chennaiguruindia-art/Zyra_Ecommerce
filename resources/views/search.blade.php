@extends('layouts.app')

@section('title', 'Search Products | ZYRA Fashion')
@section('meta_description', 'Search and find women\'s tops, cotton leggings, kurtis, maxi dresses and nightwear at ZYRA.')

@section('content')

<x-breadcrumb :items="[['label' => 'Search', 'url' => '']]" />

<div class="container py-5">
    
    <!-- Search Query Bar -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-7 text-center">
            <h1 class="h3 fw-bold mb-3">Search Collection</h1>
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-white border-end-0 pe-2 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="searchPageInput" class="form-control border-start-0 ps-1" placeholder="Type a product name, category, or style..." autocomplete="off">
            </div>
            <div class="d-flex justify-content-center gap-2 mt-3 small text-muted">
                <span>Popular:</span>
                <a href="/search?q=kurti" class="badge bg-light text-dark border text-decoration-none">Kurti</a>
                <a href="/search?q=cotton" class="badge bg-light text-dark border text-decoration-none">Cotton</a>
                <a href="/search?q=maxi" class="badge bg-light text-dark border text-decoration-none">Maxi Dress</a>
                <a href="/search?q=crop%20top" class="badge bg-light text-dark border text-decoration-none">Crop Top</a>
                <a href="/search?q=leggings" class="badge bg-light text-dark border text-decoration-none">Leggings</a>
            </div>
        </div>
    </div>

    <!-- Search Results Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <span class="text-muted small">Showing results for: <strong id="searchQueryDisplay" class="text-dark">All Products</strong></span>
        </div>
        <span id="searchResultCount" class="badge bg-dark text-white p-2 px-3">Loading...</span>
    </div>

    <!-- Results Container (Populated by search.js) -->
    <div id="searchResultsContainer"></div>

    <!-- Empty Search State -->
    <div id="searchEmptyState" class="text-center py-5 my-4" style="display: none;">
        <div class="mb-3 text-muted" style="font-size: 3.5rem;">
            <i class="bi bi-search-heart"></i>
        </div>
        <h4 class="fw-bold mb-2">No products found</h4>
        <p class="text-muted small mb-4">
            We couldn't find any items matching your search query. Try checking your spelling or explore our popular categories.
        </p>
        <a href="{{ route('shop') }}" class="btn btn-zyra-primary px-4 py-2">
            View All Products <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

</div>

@endsection

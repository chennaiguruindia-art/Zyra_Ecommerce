@extends('layouts.app')

@section('title', 'My Wishlist | ZYRA Fashion')
@section('meta_description', 'View and manage your saved favorite styles in your ZYRA wishlist.')

@section('content')

<x-breadcrumb :items="[['label' => 'My Wishlist', 'url' => '']]" />

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold mb-1">My Wishlist</h1>
            <span id="wishlistItemsCount" class="text-muted small">0 Items</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="ZyraWishlist.clearWishlist()">
            <i class="bi bi-trash3 me-1"></i> Clear Wishlist
        </button>
    </div>

    <!-- Dynamic Wishlist Grid Populated by wishlist.js -->
    <div id="wishlistGridContainer"></div>

    <!-- Empty Wishlist State -->
    <div id="wishlistEmptyState" class="text-center py-5 my-4" style="display: none;">
        <div class="mb-3 text-muted" style="font-size: 3.5rem;">
            <i class="bi bi-heart"></i>
        </div>
        <h4 class="fw-bold mb-2">Your wishlist is waiting for something beautiful.</h4>
        <p class="text-muted small mb-4 max-w-md mx-auto">
            Save items that catch your eye now, and easily revisit or move them to your bag anytime.
        </p>
        <a href="{{ route('shop') }}" class="btn btn-zyra-primary px-4 py-2">
            Continue Shopping <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

</div>

@endsection

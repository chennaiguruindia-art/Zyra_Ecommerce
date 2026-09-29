@extends('layouts.app')

@section('title', 'My Wishlist | ZYRA Lifestyle')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'View and manage your saved favorite styles in your ZYRA wishlist.')

@section('content')

<x-breadcrumb :items="[['label' => 'My Wishlist', 'url' => '']]" />

<div class="container py-5">

    <style>
    .wl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 20px; }
    .wl-card-btns { display: flex; flex-direction: column; gap: 8px; margin-top: 10px; }
    .wl-move { border: none; background: #8d5a5a; color: #fff; font-weight: 700; font-size: .82rem; border-radius: 999px; padding: 10px 12px; cursor: pointer; width: 100%; }
    .wl-move:hover { background: #6f4444; }
    .wl-move:disabled { opacity: .5; cursor: not-allowed; }
    .wl-remove { border: none; background: none; color: #999; font-size: .78rem; cursor: pointer; }
    .wl-remove:hover { color: #d6336c; }
    .wl-stock { font-size: .76rem; margin-top: 4px; }
    @media (max-width: 575.98px) {
        .wl-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    }
    </style>

    <div id="wishlistToolbar" class="d-flex justify-content-end gap-2 mb-3" style="display: none;">
        <button type="button" class="btn btn-sm btn-dark rounded-pill px-3" onclick="ZyraWishlist.moveAllToBag()"><i class="bi bi-bag-check me-1"></i> Move all to Bag</button>
        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="ZyraWishlist.clearWishlist()"><i class="bi bi-trash3 me-1"></i> Clear</button>
    </div>

    <!-- Dynamic Wishlist Grid Populated by wishlist.js -->
    <div id="wishlistGridContainer"></div>

    <!-- Empty Wishlist State -->
    <div id="wishlistEmptyState" class="text-center py-5 my-4" style="display: none;">
        <!-- <div class="mb-3 text-muted" style="font-size: 3.5rem;">
            <i class="bi bi-heart"></i>
        </div> -->
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

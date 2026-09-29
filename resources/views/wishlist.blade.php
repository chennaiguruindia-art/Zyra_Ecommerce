@extends('layouts.app')

@section('title', 'My Wishlist | ZYRA Lifestyle')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'View and manage your saved favorite styles in your ZYRA wishlist.')

@section('content')

<x-breadcrumb :items="[['label' => 'My Wishlist', 'url' => '']]" />

<div class="container py-5">

    <style>
    .wl-banner { background: linear-gradient(120deg, #fdf2f3 0%, #fbe4e6 55%, #f6d9dc 100%); border: 1px solid #f3d3d7; border-radius: 20px; padding: 26px 28px; position: relative; overflow: hidden; }
    .wl-banner::after { content: '\2665'; position: absolute; right: 18px; top: 50%; transform: translateY(-50%) rotate(-8deg); font-size: 7rem; color: rgba(141,90,90,.12); line-height: 1; pointer-events: none; }
    .wl-banner h1 { font-family: 'Playfair Display', serif; font-weight: 700; margin: 0; }
    .wl-save-pill { display: inline-flex; align-items: center; gap: 6px; background: #038d2c; color: #fff; font-size: .8rem; font-weight: 700; border-radius: 999px; padding: 6px 14px; }
    .wl-row { display: flex; gap: 16px; background: #fff; border: 1px solid #ece5e0; border-radius: 16px; padding: 14px; transition: box-shadow .2s ease, transform .2s ease; }
    .wl-row:hover { box-shadow: 0 10px 26px rgba(0,0,0,.08); transform: translateY(-2px); }
    .wl-img { position: relative; flex: 0 0 110px; width: 110px; height: 140px; border-radius: 12px; overflow: hidden; background: #faf8f6; display: block; }
    .wl-img img { width: 100%; height: 100%; object-fit: cover; }
    .wl-heart { position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; border-radius: 50%; border: none; background: rgba(255,255,255,.94); color: #d6336c; font-size: .95rem; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
    .wl-off { position: absolute; left: 0; bottom: 8px; background: #d6336c; color: #fff; font-size: .68rem; font-weight: 800; padding: 3px 9px 3px 7px; border-radius: 0 999px 999px 0; }
    .wl-mid { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
    .wl-cat { font-size: .68rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #a98f8f; }
    .wl-name { font-weight: 600; font-size: .95rem; margin: 2px 0 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .wl-name a { color: inherit; text-decoration: none; }
    .wl-stars { color: #f5a623; font-size: .78rem; }
    .wl-stars small { color: #999; }
    .wl-price { display: flex; align-items: baseline; gap: 7px; margin-top: auto; padding-top: 6px; flex-wrap: wrap; }
    .wl-price .now { font-size: 1.1rem; font-weight: 800; }
    .wl-price s { font-size: .8rem; color: #999; }
    .wl-price .off { font-size: .76rem; font-weight: 700; color: #038d2c; }
    .wl-stock { font-size: .74rem; margin-top: 2px; }
    .wl-side { flex: 0 0 170px; display: flex; flex-direction: column; gap: 8px; justify-content: center; }
    .wl-move { border: none; background: #8d5a5a; color: #fff; font-weight: 700; font-size: .82rem; border-radius: 999px; padding: 10px 12px; cursor: pointer; }
    .wl-move:hover { background: #6f4444; }
    .wl-move:disabled { opacity: .5; cursor: not-allowed; }
    .wl-remove { border: none; background: none; color: #999; font-size: .78rem; cursor: pointer; }
    .wl-remove:hover { color: #d6336c; }
    @media (max-width: 575.98px) {
        .wl-row { flex-wrap: wrap; gap: 12px; }
        .wl-img { flex-basis: 96px; width: 96px; height: 124px; }
        .wl-side { flex: 1 1 100%; flex-direction: row; }
        .wl-move { flex: 1; }
        .wl-banner::after { font-size: 4.5rem; }
    }
    </style>

    <div class="wl-banner mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap position-relative" style="z-index:1;">
            <div class="me-auto">
                <h1 class="h3">My Wishlist</h1>
                <span id="wishlistItemsCount" class="text-muted small">0 Items</span>
            </div>
            <div id="wishlistSummary" class="d-flex align-items-center gap-2 flex-wrap"></div>
            <button type="button" class="btn btn-sm btn-outline-danger bg-white" onclick="ZyraWishlist.clearWishlist()">
                <i class="bi bi-trash3 me-1"></i> Clear
            </button>
        </div>
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

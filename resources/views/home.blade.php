@extends('layouts.app')

@section('title', 'ZYRA | Modern Festive & Ethnic Women\'s Fashion - Kurtis, Maxis & Tops')
@section('meta_description', 'Discover ZYRA\'s handcrafted festive collection. Pure cotton, handloom silk & hand block printed kurtis, fluid maxi silhouettes, and luxury tops.')

@push('schema')
    {!! \App\Support\Seo::organizationSchema() !!}
    {!! \App\Support\Seo::websiteSchema() !!}
    {!! \App\Support\Seo::localBusinessSchema() !!}
@endpush

@push('styles')
<style>
/* ==========================================================================
   ZYRA - Bulletproof Festive & Responsive Index Page Styles
   Ensures layout never collapses regardless of browser cache
   ========================================================================== */

:root {
    --festive-burgundy: #7D1D32;
    --festive-burgundy-dark: #581020;
    --festive-bg: #FAF6F2;
    --festive-card-bg: #FFFFFF;
    --festive-cream: #FAF3EB;
    --festive-peach: #FAF0E8;
    --festive-text: #1E1A18;
    --festive-muted: #6E6762;
    --festive-border: #EFE4DA;
    --festive-gold: #D48D3B;
}

.festive-index-wrapper {
    background-color: var(--festive-bg);
    min-height: 100vh;
    padding-top: 0;
    padding-bottom: 3rem;
}

/* ── Hero Banner Carousel: Foxtale-Style Full-Width Edge-to-Edge Slider ── */
.festive-hero-fullwidth {
    width: 100%;
    margin: 0;
    padding: 0;
    position: relative;
    overflow: hidden;
    background-color: #FAF6F2;
    line-height: 0;
}

.festive-hero-carousel {
    position: relative;
    width: 100%;
    border-radius: 0;
    overflow: hidden;
    background-color: #FAF6F2;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.festive-carousel-slide {
    position: relative;
    width: 100%;
    overflow: hidden;
}

#zyraHeroCarousel .carousel-item {
    transition: transform 0.65s cubic-bezier(0.25, 1, 0.5, 1);
}

.festive-hero-img {
    display: block;
    width: 100%;
    height: auto;
    object-fit: cover;
    object-position: center;
    border-radius: 0;
    transition: transform 0.4s ease;
}

/* Glassmorphic Navigation Arrows (Foxtale Style) */
.festive-carousel-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.16);
    color: #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    z-index: 10;
    cursor: pointer;
    transition: all 0.22s ease;
    text-decoration: none;
    opacity: 0.92;
    line-height: 1;
}

.festive-carousel-arrow:hover {
    background: #ffffff;
    color: #000000;
    transform: translateY(-50%) scale(1.08);
    opacity: 1;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.24);
}

.festive-carousel-prev {
    left: 24px;
}

.festive-carousel-next {
    right: 24px;
}

/* Foxtale-Style Capsule Indicators */
.festive-carousel-indicators {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 10;
    margin: 0;
    padding: 6px 14px;
    background: rgba(0, 0, 0, 0.32);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border-radius: 50px;
    line-height: 1;
}

.festive-carousel-indicator {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    border: none;
    padding: 0;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.festive-carousel-indicator.active {
    width: 32px;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
}

@media (max-width: 767.98px) {
    .festive-carousel-arrow {
        width: 32px;
        height: 32px;
        font-size: 1rem;
    }
    .festive-carousel-prev { left: 8px; }
    .festive-carousel-next { right: 8px; }
    .festive-carousel-indicators {
        bottom: 10px;
        padding: 4px 10px;
        gap: 6px;
    }
    .festive-carousel-indicator.active {
        width: 20px;
    }
}

/* ── Mobile Search ────────────────────────────────────────────────── */
.festive-search-container {
    width: 100%;
    margin: 0 0 1.25rem;
}

.festive-search-pill {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    width: 100%;
    min-height: 48px;
    padding: 5px 6px 5px 15px;
    border: 1px solid var(--festive-border);
    border-radius: 999px;
    background: var(--festive-card-bg);
    box-shadow: 0 3px 12px rgba(67, 43, 30, 0.07);
    color: var(--festive-muted);
    text-decoration: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.festive-search-pill:focus-within {
    border-color: var(--festive-burgundy);
    box-shadow: 0 0 0 3px rgba(125, 29, 50, 0.1);
}

.festive-search-pill > .bi-search {
    flex: 0 0 auto;
    font-size: 0.95rem;
}

.festive-search-pill input {
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
    height: 36px;
    padding: 0;
    border: 0;
    outline: 0;
    background: transparent;
    color: var(--festive-text);
    font: inherit;
    font-size: 0.875rem;
}

.festive-search-pill input::placeholder {
    color: #817973;
    opacity: 1;
}

.festive-search-btn {
    display: inline-flex;
    flex: 0 0 38px;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--festive-burgundy);
    color: #fff;
    font-size: 0.95rem;
    text-decoration: none;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.festive-search-btn:hover,
.festive-search-btn:focus-visible {
    background: var(--festive-burgundy-dark);
    color: #fff;
    transform: scale(1.04);
}

/* ── Section Header ──────────────────────────────────────────────── */
.festive-section-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 14px;
}

.festive-section-title {
    font-family: var(--zyra-font-heading);
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--festive-text);
    margin: 0;
}

.festive-section-sub {
    font-size: 0.8rem;
    color: var(--festive-muted);
    display: block;
    margin-top: 2px;
}

.festive-section-link {
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: var(--festive-burgundy);
    text-transform: uppercase;
    text-decoration: none;
    transition: color 0.2s ease;
}

.festive-section-link:hover {
    color: var(--festive-burgundy-dark);
    text-decoration: underline;
}

/* ── Curated Categories Horizontal Circles ───────────────────────── */
.festive-cat-strip {
    display: flex;
    align-items: flex-start;
    gap: 18px;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    padding: 4px 2px 14px;
}

.festive-cat-strip::-webkit-scrollbar {
    display: none;
}

.festive-cat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    flex: 0 0 auto;
    width: 76px;
    transition: transform 0.2s ease;
}

.festive-cat-item:hover {
    transform: translateY(-3px);
}

.festive-cat-circle {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    border: 2px solid #EFEAE4;
    padding: 2px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
    flex-shrink: 0;
}

.festive-cat-circle img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}

.festive-cat-item:hover .festive-cat-circle {
    border-color: var(--festive-burgundy);
    box-shadow: 0 4px 12px rgba(125, 29, 50, 0.15);
}

.festive-cat-circle-sale {
    background-color: #FCECE9;
    border-color: #F8D3CB;
    color: var(--festive-burgundy);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.festive-cat-circle-sale i {
    font-size: 1.15rem;
    line-height: 1;
}

.festive-cat-circle-sale span {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

.festive-cat-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--festive-text);
    margin-top: 6px;
    text-align: center;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 76px;
}

/* ── Heritage Craft Highlight Card ───────────────────────────────── */
.festive-craft-wrap {
    margin: 1.25rem 0 2rem;
}

.festive-craft-card {
    background-color: var(--festive-cream);
    border: 1px solid var(--festive-border);
    border-radius: 18px;
    padding: 22px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

.festive-craft-left {
    flex: 1;
}

.festive-craft-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1.1px;
    color: var(--festive-burgundy);
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}

.festive-craft-title {
    font-family: var(--zyra-font-heading);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--festive-text);
    line-height: 1.25;
    margin-bottom: 6px;
}

.festive-craft-desc {
    font-size: 0.82rem;
    color: var(--festive-muted);
    line-height: 1.45;
    margin-bottom: 12px;
}

.festive-craft-link {
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: var(--festive-burgundy);
    text-transform: uppercase;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: gap 0.2s ease;
}

.festive-craft-link:hover {
    color: var(--festive-burgundy-dark);
    gap: 9px;
}

.festive-craft-thumb {
    width: 95px;
    height: 95px;
    border-radius: 14px;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    display: block;
}

/* ── Customer Review Card ────────────────────────────────────────── */
.festive-review-wrap {
    margin: 1.75rem 0 2.5rem;
}

.festive-review-card {
    background-color: var(--festive-peach);
    border: 1px solid #F3E2D5;
    border-radius: 18px;
    padding: 22px 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.festive-review-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.festive-review-stars {
    color: var(--festive-gold);
    font-size: 0.85rem;
    letter-spacing: 2px;
}

.festive-review-badge {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 1.1px;
    color: #7D736D;
    text-transform: uppercase;
}

.festive-review-quote {
    font-family: var(--zyra-font-display);
    font-style: italic;
    font-size: 0.98rem;
    line-height: 1.55;
    color: #2C2624;
    margin-bottom: 14px;
}

.festive-review-author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.festive-review-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #F4D3DA;
    color: var(--festive-burgundy);
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.festive-review-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: #2C2624;
}
</style>
@endpush

@section('content')
<div class="festive-index-wrapper">

    <!-- 1. Full-Width Edge-to-Edge Hero Banner Carousel (Banner 1, 2, 3 only) -->
    @php
        $bannerFiles = [
            [
                'image' => asset('images/banners/Banner 1.png'),
                'title' => 'Festive Kurti Collection 2026',
                'subtitle' => 'Handcrafted pure cotton & designer silhouettes',
                'link' => route('pages.kurtis'),
            ],
            [
                'image' => asset('images/banners/Banner 2.png'),
                'title' => 'Celebration Festive Wear - Exclusive Offers',
                'subtitle' => 'Explore luxury ethnic wear crafted for celebrations',
                'link' => route('shop'),
            ],
            [
                'image' => asset('images/banners/Banner 3.png'),
                'title' => 'Luxury Festive Maxis & Modern Kurtis',
                'subtitle' => 'Elegance in every fold - shop trending styles',
                'link' => route('pages.maxi'),
            ],
        ];
    @endphp

    <div class="festive-hero-fullwidth">
        <div id="zyraHeroCarousel" class="carousel slide festive-hero-carousel">
            
            <!-- Indicators (Foxtale Style) -->
            @if(count($bannerFiles) > 1)
                <div class="festive-carousel-indicators">
                    @foreach($bannerFiles as $idx => $b)
                        <button type="button" 
                                data-bs-target="#zyraHeroCarousel" 
                                data-bs-slide-to="{{ $idx }}" 
                                class="festive-carousel-indicator {{ $idx === 0 ? 'active' : '' }}" 
                                aria-current="{{ $idx === 0 ? 'true' : 'false' }}" 
                                aria-label="Slide {{ $idx + 1 }}"></button>
                    @endforeach
                </div>
            @endif

            <!-- Carousel Slides -->
            <div class="carousel-inner">
                @foreach($bannerFiles as $idx => $b)
                    <div class="carousel-item {{ $idx === 0 ? 'active' : '' }} festive-carousel-slide">
                        <a href="{{ $b['link'] }}" class="d-block text-decoration-none" title="{{ $b['title'] }}">
                            <img src="{{ $b['image'] }}" 
                                 alt="{{ $b['title'] }} - {{ $b['subtitle'] }}" 
                                 class="festive-hero-img w-100" 
                                 loading="{{ $idx === 0 ? 'eager' : 'lazy' }}" 
                                 @if($idx === 0) fetchpriority="high" @endif>
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- Glassmorphic Navigation Arrows (Foxtale Style) -->
            @if(count($bannerFiles) > 1)
                <button class="festive-carousel-arrow festive-carousel-prev" type="button" data-bs-target="#zyraHeroCarousel" data-bs-slide="prev" aria-label="Previous Slide">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="festive-carousel-arrow festive-carousel-next" type="button" data-bs-target="#zyraHeroCarousel" data-bs-slide="next" aria-label="Next Slide">
                    <i class="bi bi-chevron-right"></i>
                </button>
            @endif
        </div>
    </div>

    <!-- Main Content Container (Categories, Collections, Highlights) -->
    <div class="container px-3 px-md-4 pt-3 pt-md-4">

        <!-- Mobile In-Page Search Bar (Mobile devices only) -->
        <div class="festive-search-container d-md-none">
            <form action="{{ route('shop') }}" method="GET" class="festive-search-pill" id="homeSearchForm">
                <i class="bi bi-search text-muted"></i>
                <input type="text"
                       name="q"
                       id="homeSearchPillInput"
                       placeholder="Search products, styles..."
                       autocomplete="off">
                <a href="{{ route('shop') }}" class="festive-search-btn" title="Filter Catalog" aria-label="Browse and filter products">
                    <i class="bi bi-sliders"></i>
                </a>
            </form>
        </div>

        <!-- 3. Curated Categories Horizontal Strip -->
        <section class="festive-categories mb-4">
            <div class="festive-section-header">
                <div>
                    <h2 class="festive-section-title">Curated Categories</h2>
                </div>
                <a href="{{ route('shop') }}" class="festive-section-link">ALL CRAFTS</a>
            </div>

            <div class="festive-cat-strip">
                <!-- Kurtis -->
                <a href="{{ route('pages.kurtis') }}" class="festive-cat-item">
                    <div class="festive-cat-circle">
                        <img src="{{ asset('images/categories/kurthi.jpg') }}" alt="Kurtis" loading="lazy">
                    </div>
                    <span class="festive-cat-label">Kurtis</span>
                </a>

                <!-- Tops & Tunics -->
                <a href="{{ route('pages.tops') }}" class="festive-cat-item">
                    <div class="festive-cat-circle">
                        <img src="{{ asset('images/categories/tops.jpg') }}" alt="Tops & Tunics" loading="lazy">
                    </div>
                    <span class="festive-cat-label">Tops & Tunics</span>
                </a>

                <!-- Maxi Dresses -->
                <a href="{{ route('pages.maxi') }}" class="festive-cat-item">
                    <div class="festive-cat-circle">
                        <img src="{{ asset('images/categories/maxi.jpg') }}" alt="Maxi Dresses" loading="lazy">
                    </div>
                    <span class="festive-cat-label">Maxi Dresses</span>
                </a>

                <!-- Co-ords -->
                <a href="{{ route('category', 'co-ords') }}" class="festive-cat-item">
                    <div class="festive-cat-circle">
                        <img src="{{ asset('images/categories/co-ords.jpg') }}" alt="Co-ords" loading="lazy">
                    </div>
                    <span class="festive-cat-label">Co-ords</span>
                </a>

                <!-- Special Sale / Under ₹999 -->
                <a href="{{ route('shop') }}?filter=sale" class="festive-cat-item">
                    <div class="festive-cat-circle festive-cat-circle-sale">
                        <i class="bi bi-tag-fill"></i>
                        <span>SALE</span>
                    </div>
                    <span class="festive-cat-label">Under ₹999</span>
                </a>

                <!-- Dupatta -->
                <a href="{{ route('pages.duppata') }}" class="festive-cat-item">
                    <div class="festive-cat-circle">
                        <img src="{{ asset('images/categories/kurthi.jpg') }}" alt="Dupatta" loading="lazy">
                    </div>
                    <span class="festive-cat-label">Dupatta</span>
                </a>

                <!-- Leggings -->
                <a href="{{ route('pages.leggings') }}" class="festive-cat-item">
                    <div class="festive-cat-circle">
                        <img src="{{ asset('images/categories/leggings.jpg') }}" alt="Leggings" loading="lazy">
                    </div>
                    <span class="festive-cat-label">Leggings</span>
                </a>

                <!-- Nightwear -->
                <a href="{{ route('pages.nightwear') }}" class="festive-cat-item">
                    <div class="festive-cat-circle">
                        <img src="{{ asset('images/categories/nightwear.jpg') }}" alt="Nightwear" loading="lazy">
                    </div>
                    <span class="festive-cat-label">Nightwear</span>
                </a>
            </div>
        </section>

        <!-- 4. Heritage Craft Highlight Banner -->
        <div class="festive-craft-wrap">
            <div class="festive-craft-card">
                <div class="festive-craft-left">
                    <span class="festive-craft-eyebrow">
                        <i class="bi bi-patch-check-fill"></i> HERITAGE CRAFT
                    </span>
                    <h3 class="festive-craft-title">Pure Cotton &amp; Hand Block Prints</h3>
                    <p class="festive-craft-desc">
                        Sustainably dyed by master artisans using organic woodblock pigments.
                    </p>
                    <a href="{{ route('shop') }}?filter=craft" class="festive-craft-link">
                        READ ARTISAN STORY <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <img src="{{ asset('images/banners/Handcrafted.png') }}" alt="Hand block print artisan craft" class="festive-craft-thumb" loading="lazy">
            </div>
        </div>

        <!-- 5a. Best Sellers Section (shown only when products have is_best_seller enabled in Global Settings) -->
        @if(!empty($bestSellers) && count($bestSellers) > 0)
        <section class="festive-trending mb-4">
            <div class="festive-section-header">
                <div>
                    <h2 class="festive-section-title">Best Sellers</h2>
                    <span class="festive-section-sub">Most loved by our customers</span>
                </div>
                <a href="{{ route('shop') }}?sort=popular" class="festive-section-link">View All ({{ count($bestSellers) }})</a>
            </div>

            <div class="row g-3 g-md-4">
                @foreach($bestSellers as $item)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$item" />
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- 5b. Trending Now Section (shown only when products have is_trending enabled in Global Settings) -->
        @if(!empty($trending) && count($trending) > 0)
        <section class="festive-trending mb-4">
            <div class="festive-section-header">
                <div>
                    <h2 class="festive-section-title">Trending Now</h2>
                    <span class="festive-section-sub">Handpicked for modern festive elegance</span>
                </div>
                <a href="{{ route('shop') }}" class="festive-section-link">View All ({{ count($allProducts) }})</a>
            </div>

            <div class="row g-3 g-md-4">
                @foreach($trending as $item)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$item" />
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Fallback: show latest products if neither best sellers nor trending are enabled -->
        @if(empty($bestSellers) && empty($trending))
        <section class="festive-trending mb-4">
            <div class="festive-section-header">
                <div>
                    <h2 class="festive-section-title">New Arrivals</h2>
                    <span class="festive-section-sub">Fresh additions to our collection</span>
                </div>
                <a href="{{ route('shop') }}" class="festive-section-link">View All ({{ count($allProducts) }})</a>
            </div>
            @if(!empty($allProducts))
                <div class="row g-3 g-md-4">
                    @foreach(array_slice($allProducts, 0, 8) as $item)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$item" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 bg-white rounded-4 border">
                    <i class="bi bi-bag-heart text-muted display-6"></i>
                    <h5 class="fw-bold text-dark mt-2">New Season In Production</h5>
                    <p class="text-muted small mb-3">Browse our existing catalog or check back shortly.</p>
                    <a href="{{ route('shop') }}" class="btn btn-sm btn-dark rounded-pill px-4">Browse Shop</a>
                </div>
            @endif
        </section>
        @endif


        <!-- 7. Interactive Catalog Promo Banner -->
        <div class="my-4 p-4 rounded-4 text-white" style="background: linear-gradient(135deg, #7D1D32 0%, #4E0E1D 100%); box-shadow: 0 10px 30px rgba(125, 29, 50, 0.2);">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.8px;">
                        <i class="bi bi-book-half me-1 text-danger"></i> Official Lookbook &amp; Catalog
                    </span>
                    <h3 class="font-heading fw-bold mt-2 mb-1" style="font-size: clamp(1.4rem, 3vw, 2rem);">
                        Download or Browse ZYRA's Festive Catalog
                    </h3>
                    <p class="mb-0 text-white-50" style="font-size: 0.9rem;">
                        Explore our complete collection with high-resolution imagery, fabrics, SKUs, available sizes, and direct order links.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ url('/catelog') }}" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-dark shadow-sm d-inline-flex align-items-center gap-2">
                        <span>Open Catalog</span> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 8. Fashion Gallery / Instagram -->
        <section class="py-4 my-2">
            <div class="text-center mb-3">
                <span class="text-uppercase small fw-bold text-muted letter-spacing-1">Tag Us @zyralifestyle46</span>
                <h3 class="festive-section-title mt-1">Fashion Gallery</h3>
                <p class="text-muted small">Share your festive style moments using #Zyralifestyle</p>
            </div>
            <x-instagram-gallery :items="$instagramItems" />
        </section>

        <!-- 9. Trust Pillars -->
        <div class="row g-3 py-4 text-center border-top border-bottom my-3">
            <div class="col-6 col-md-3">
                <i class="bi bi-truck fs-3 text-dark"></i>
                <h6 class="fw-bold mt-2 mb-1" style="font-size: 0.9rem;">Free Shipping</h6>
                <p class="text-muted small mb-0">On all orders above ₹999</p>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-patch-check fs-3 text-dark"></i>
                <h6 class="fw-bold mt-2 mb-1" style="font-size: 0.9rem;">Pure Fabrics</h6>
                <p class="text-muted small mb-0">Breathable organic mulmul &amp; chanderi</p>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-arrow-repeat fs-3 text-dark"></i>
                <h6 class="fw-bold mt-2 mb-1" style="font-size: 0.9rem;">Easy Exchanges</h6>
                <p class="text-muted small mb-0">Hassle-free 7-day policy</p>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-shield-lock fs-3 text-dark"></i>
                <h6 class="fw-bold mt-2 mb-1" style="font-size: 0.9rem;">Secure Checkout</h6>
                <p class="text-muted small mb-0">UPI, Net Banking &amp; Cards</p>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
// Continuous Left-to-Right auto-sliding & mobile touch swipe support
document.addEventListener('DOMContentLoaded', function () {
    const carouselEl = document.getElementById('zyraHeroCarousel');
    if (!carouselEl) return;

    // Initialize Bootstrap carousel instance
    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl, {
        interval: false,
        wrap: true,
        keyboard: true
    });

    // Continuous auto-sliding timer (moves in the opposite direction)
    let autoSlideTimer = null;
    const slideIntervalMs = 3500;

    function startAutoSlide() {
        stopAutoSlide();
        autoSlideTimer = setInterval(function () {
            if (!document.hidden) {
                carousel.next(); // Slides to next (opposite direction)
            }
        }, slideIntervalMs);
    }

    function stopAutoSlide() {
        if (autoSlideTimer) {
            clearInterval(autoSlideTimer);
            autoSlideTimer = null;
        }
    }

    // Start auto slide immediately
    startAutoSlide();

    // Pause on mouse hover, resume on mouse leave
    carouselEl.addEventListener('mouseenter', stopAutoSlide);
    carouselEl.addEventListener('mouseleave', startAutoSlide);

    // Mobile touch swipe gestures
    let touchStartX = 0;
    let touchEndX = 0;

    carouselEl.addEventListener('touchstart', function (e) {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoSlide();
    }, { passive: true });

    carouselEl.addEventListener('touchend', function (e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        startAutoSlide();
    }, { passive: true });

    function handleSwipe() {
        const threshold = 35;
        if (touchEndX > touchStartX + threshold) {
            // Swiped left-to-right
            carousel.prev();
        } else if (touchEndX < touchStartX - threshold) {
            // Swiped right-to-left
            carousel.next();
        }
    }
});
</script>
@endpush

@endsection

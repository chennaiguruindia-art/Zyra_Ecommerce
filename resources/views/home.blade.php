@extends('layouts.app')

@section('title', 'ZYRA | Elevate Your Everyday Style - Modern Women\'s Fashion')
@section('meta_description', 'Discover modern Indian women\'s clothing at ZYRA. Premium tops, cotton leggings, handprinted kurtis, maxi dresses, and luxury nightwear.')

@section('content')

<!-- 1. Hero Banner Section -->
<section class="zyra-hero-section">
    <div class="container">
        <div class="row align-items-center">
            @php
                $heroTitle = $heroBanner?->title ?? 'Elevate Your Everyday Style';
                $heroSubtitle = $heroBanner?->subtitle ?? 'Discover effortless fashion designed for every mood. From handblock printed ethnic kurtis to breathable morning essentials, embrace timeless elegance crafted for modern living.';
                $heroBadge = $heroBanner?->badge ?? 'New Season Arrival 2026';
                $heroLink = $heroBanner?->link ?? route('shop');
                $heroBtnText = $heroBanner?->button_text ?? 'SHOP NOW';
                $heroImg = $heroBanner?->image_url ?? 'https://images.unsplash.com/photo-1534126511673-b6899657816a?auto=format&fit=crop&w=1000&q=80';
            @endphp
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="zyra-hero-tag">{{ $heroBadge }}</span>
                <h1 class="zyra-hero-title">{{ $heroTitle }}</h1>
                <p class="zyra-hero-desc">
                    {{ $heroSubtitle }}
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ $heroLink }}" class="btn btn-zyra-primary">
                        {{ $heroBtnText }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="{{ route('shop') }}?filter=trending" class="btn btn-zyra-outline">
                        EXPLORE COLLECTION
                    </a>
                </div>
                <!-- Trust mini-bar -->
                <div class="d-flex align-items-center gap-4 mt-4 pt-2 text-muted small justify-content-center justify-content-lg-start">
                    <div><i class="bi bi-star-fill text-warning me-1"></i> 4.9/5 Rating</div>
                    <div><i class="bi bi-truck text-dark me-1"></i> Free Shipping Over ₹999</div>
                    <div><i class="bi bi-arrow-clockwise text-dark me-1"></i> Easy 15-Day Return</div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="zyra-hero-image-wrapper">
                    <div class="zyra-hero-img-box">
                        <img src="{{ $heroImg }}" alt="{{ $heroTitle }}">
                        <!-- Floating Badge -->
                        <div class="zyra-hero-badge-float">
                            <div class="rounded-circle bg-dark text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-percent fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-bold small text-dark">Special Launch Offer</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Use Code: <strong>WELCOME10</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Shop By Category Section -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="text-uppercase small fw-bold text-muted letter-spacing-1">Curated Collections</span>
            <h2 class="display-6 fw-bold mt-1">Shop By Category</h2>
            <div class="mx-auto" style="width: 60px; height: 2px; background-color: var(--zyra-secondary);"></div>
        </div>

        <div class="row g-4">
            @foreach($categories as $category)
                <div class="col-6 col-md-4 col-lg">
                    <x-category-card :category="$category" />
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 3. New Arrivals Section -->
<section class="py-5" style="background-color: var(--zyra-bg-light);">
    <div class="container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="text-uppercase small fw-bold text-muted">Fresh Off The Loom</span>
                <h2 class="display-6 fw-bold mt-1">New Arrivals</h2>
            </div>
            <a href="{{ route('shop') }}?filter=new" class="btn btn-zyra-outline btn-sm mt-3 mt-md-0">
                View All New Arrivals <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($newArrivals as $item)
                <div class="col-6 col-md-4 col-lg-3">
                    <x-product-card :product="$item" />
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 4. Best Sellers Section -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="text-uppercase small fw-bold text-muted">Most Loved By Shoppers</span>
                <h2 class="display-6 fw-bold mt-1">Best Sellers</h2>
            </div>
            <a href="{{ route('shop') }}?sort=popular" class="btn btn-zyra-outline btn-sm mt-3 mt-md-0">
                Explore Best Sellers <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach(array_slice($bestSellers, 0, 8) as $item)
                <div class="col-6 col-md-4 col-lg-3">
                    <x-product-card :product="$item" />
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 5. Promotional Full-Width Banner -->
<section class="py-4">
    <div class="container">
        <div class="zyra-promo-banner">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="zyra-promo-badge">Limited Time Celebration</span>
                    <h2 class="zyra-promo-title">Fresh Looks. Effortless Style.</h2>
                    <p class="zyra-promo-text">
                        Flat 30% OFF on Selected Styles. Upgrade your wardrobe with our premium cotton kurtis, chic peplum tops, and flowy maxi gowns.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('shop') }}?filter=sale" class="btn btn-zyra-primary">
                            SHOP SALE NOW
                        </a>
                        <span class="d-inline-flex align-items-center text-muted small">
                            <i class="bi bi-clock me-1"></i> Offer valid while stocks last
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 d-none d-lg-block text-center">
                    <div class="p-3 bg-white rounded-3 shadow-sm d-inline-block text-start">
                        <div class="text-uppercase small text-muted fw-bold">Use Coupon At Checkout</div>
                        <div class="fs-3 fw-bold text-danger my-1">SAVE20</div>
                        <div class="small text-muted">Extra 20% Instant Discount on All Orders</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. Trending Collection Section -->
<section class="py-5" style="background-color: var(--zyra-bg-light);">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="text-uppercase small fw-bold text-muted">Seasonal Spotlights</span>
            <h2 class="display-6 fw-bold mt-1">Trending Collection</h2>
            <div class="mx-auto" style="width: 60px; height: 2px; background-color: var(--zyra-secondary);"></div>
        </div>

        <div class="row g-4">
            @php
                $trendingItems = array_slice($trending, 0, 4);
            @endphp
            @foreach($trendingItems as $item)
                <div class="col-6 col-md-4 col-lg-3">
                    <x-product-card :product="$item" />
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 7. Why Shop With Us Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container py-3">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="zyra-feature-box">
                    <div class="zyra-feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h5 class="zyra-feature-title">Free Shipping</h5>
                    <p class="zyra-feature-desc">On all prepaid & COD orders above ₹999 across India.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="zyra-feature-box">
                    <div class="zyra-feature-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h5 class="zyra-feature-title">Secure Payments</h5>
                    <p class="zyra-feature-desc">Encrypted 256-bit SSL checkout with UPI, Cards & COD.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="zyra-feature-box">
                    <div class="zyra-feature-icon">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </div>
                    <h5 class="zyra-feature-title">Easy Returns</h5>
                    <p class="zyra-feature-desc">Hassle-free 15-day doorstep exchange and return policy.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="zyra-feature-box">
                    <div class="zyra-feature-icon">
                        <i class="bi bi-gem"></i>
                    </div>
                    <h5 class="zyra-feature-title">Quality Fashion</h5>
                    <p class="zyra-feature-desc">Bio-washed breathable fabrics tailored for everyday comfort.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 8. Customer Reviews Section -->
<section class="py-5" style="background-color: var(--zyra-bg-beige);">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="text-uppercase small fw-bold text-muted">Real Experiences</span>
            <h2 class="display-6 fw-bold mt-1">What Our Customers Say</h2>
            <div class="mx-auto" style="width: 60px; height: 2px; background-color: var(--zyra-secondary);"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="zyra-review-card">
                    <div class="zyra-review-stars">★★★★★</div>
                    <p class="zyra-review-text">
                        "The Handblock Printed Kurti is sensational. Breathable pure cotton, exact tailoring, and the wooden block print looks richer in person!"
                    </p>
                    <div class="zyra-reviewer-info">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80" alt="Priya Sharma" class="zyra-reviewer-avatar">
                        <div>
                            <div class="zyra-reviewer-name">Priya Sharma</div>
                            <div class="zyra-reviewer-tag"><i class="bi bi-patch-check-fill"></i> Verified Buyer</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="zyra-review-card">
                    <div class="zyra-review-stars">★★★★★</div>
                    <p class="zyra-review-text">
                        "Finally found 4-way stretch leggings that do not become transparent! High waist holds comfortably without digging in. Already ordered 3 more pairs."
                    </p>
                    <div class="zyra-reviewer-info">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=150&q=80" alt="Ananya Verma" class="zyra-reviewer-avatar">
                        <div>
                            <div class="zyra-reviewer-name">Ananya Verma</div>
                            <div class="zyra-reviewer-tag"><i class="bi bi-patch-check-fill"></i> Verified Buyer</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="zyra-review-card">
                    <div class="zyra-review-stars">★★★★★</div>
                    <p class="zyra-review-text">
                        "Wore the Botanical Floral Maxi Dress to a daytime wedding celebration. Received endless compliments! Lightweight, romantic drape and perfect length."
                    </p>
                    <div class="zyra-reviewer-info">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=150&q=80" alt="Rhea Sen" class="zyra-reviewer-avatar">
                        <div>
                            <div class="zyra-reviewer-name">Rhea Sen</div>
                            <div class="zyra-reviewer-tag"><i class="bi bi-patch-check-fill"></i> Verified Buyer</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="zyra-review-card">
                    <div class="zyra-review-stars">★★★★★</div>
                    <p class="zyra-review-text">
                        "The modal cotton night suits feel like luxury clouds. Delivery in Bengaluru arrived in just 2 days. ZYRA is definitely my new go-to store!"
                    </p>
                    <div class="zyra-reviewer-info">
                        <img src="https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?auto=format&fit=crop&w=150&q=80" alt="Kavita Nair" class="zyra-reviewer-avatar">
                        <div>
                            <div class="zyra-reviewer-name">Kavita Nair</div>
                            <div class="zyra-reviewer-tag"><i class="bi bi-patch-check-fill"></i> Verified Buyer</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 9. Instagram/Fashion Gallery Section -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="text-center mb-4">
            <span class="text-uppercase small fw-bold text-muted">Tag Us @ZYRA_FASHION</span>
            <h2 class="display-6 fw-bold mt-1">Fashion Gallery</h2>
            <p class="text-muted small">Share your style moments using #ZyraWoman</p>
        </div>

        <x-instagram-gallery :items="$instagramItems" />
    </div>
</section>

<!-- 10. Newsletter Section -->
<section class="py-5" style="background-color: var(--zyra-bg-light);">
    <div class="container py-3">
        <div class="zyra-newsletter-box">
            <h2>Stay in Style</h2>
            <p>Subscribe for new arrivals, exclusive offers, styling lookbooks, and private secret sales.</p>
            <form class="newsletter-form newsletter-input-group">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit" class="btn-subscribe">Subscribe</button>
            </form>
            <div class="text-white-50 small mt-3">
                <i class="bi bi-lock-fill me-1"></i> No spam ever. Unsubscribe with 1-click anytime.
            </div>
        </div>
    </div>
</section>

@endsection

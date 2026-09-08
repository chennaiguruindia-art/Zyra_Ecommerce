@extends('layouts.app')

@section('title', 'About Us | The ZYRA Story & Heritage')
@section('meta_description', 'Discover the story behind ZYRA - modern Indian women\'s fashion crafted with sustainable breathable fabrics and timeless artisan silhouettes.')

@section('content')

<x-breadcrumb :items="[['label' => 'About Us', 'url' => '']]" />

<!-- Hero Brand Story -->
<section class="py-5" style="background-color: var(--zyra-bg-beige);">
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="text-uppercase small fw-bold text-muted">Our Heritage</span>
                <h1 class="display-5 fw-bold mt-1 mb-3">Redefining Everyday Luxury for Modern Women</h1>
                <p class="text-muted fs-5 leading-relaxed">
                    ZYRA was born from a simple realization: Indian women shouldn't have to compromise between effortless everyday comfort and graceful, head-turning elegance.
                </p>
                <p class="text-muted leading-relaxed">
                    Rooted in Jaipur’s rich textile heritage and shaped by contemporary cosmopolitan silhouettes, our collections celebrate the duality of the modern Indian woman — poised at work, spirited at celebrations, and at ease at home.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="rounded-4 overflow-hidden shadow-lg">
                    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=800&q=80" alt="ZYRA Brand Story" class="w-100 h-100 object-fit-cover">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Mission & Vision -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 order-lg-2">
                <span class="text-uppercase small fw-bold text-muted">Our Purpose</span>
                <h2 class="display-6 fw-bold mt-1 mb-3">Our Mission: Fashion That Feels Like Freedom</h2>
                <p class="text-muted leading-relaxed">
                    Every piece in our catalog begins with natural, skin-loving textiles — combed organic cotton, feather-light mulmul, handwoven chanderi, and fluid modal rayon. We engineer silhouettes that flatter every body shape, celebrate Indian skin tones, and move naturally with you throughout your day.
                </p>
                <div class="d-flex flex-column gap-3 mt-4">
                    <div class="d-flex gap-3">
                        <div class="rounded-circle bg-light p-3 text-dark d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-heart-pulse fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Ethical Artisan Sourcing</h6>
                            <p class="small text-muted mb-0">We collaborate directly with generational weavers in Sanganer and Bagru, supporting fair wages and preserving age-old woodblock traditions.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="rounded-circle bg-light p-3 text-dark d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-feather fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Breathable Climate-First Fabrics</h6>
                            <p class="small text-muted mb-0">Engineered specifically for warm tropical climates with high breathability, sweat-wicking softness, and non-restrictive 4-way flexibility.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="rounded-4 overflow-hidden shadow">
                    <img src="https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=800&q=80" alt="Artisan Craftsmanship" class="w-100 h-100 object-fit-cover">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5" style="background-color: var(--zyra-bg-light);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-uppercase small fw-bold text-muted">Why Shoppers Trust Us</span>
            <h2 class="display-6 fw-bold mt-1">The ZYRA Promise</h2>
            <div class="mx-auto" style="width: 60px; height: 2px; background-color: var(--zyra-secondary);"></div>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="fs-1 text-dark mb-3"><i class="bi bi-award"></i></div>
                    <h5 class="fw-bold mb-2">Unmatched Craftsmanship</h5>
                    <p class="text-muted small mb-0">Every seam is precision-locked and double-inspected so your favorite garment looks pristine season after season.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="fs-1 text-dark mb-3"><i class="bi bi-tag"></i></div>
                    <h5 class="fw-bold mb-2">Accessible Luxury</h5>
                    <p class="text-muted small mb-0">Direct-to-consumer model ensures boutique-quality tailoring and luxury fabrics without middleman retail markups.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="fs-1 text-dark mb-3"><i class="bi bi-stars"></i></div>
                    <h5 class="fw-bold mb-2">Inclusive Sizing</h5>
                    <p class="text-muted small mb-0">Thoughtfully patterned sizes from XS to XXL tailored specifically around realistic Indian body measurements.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 bg-white text-center">
    <div class="container py-3">
        <h3 class="display-6 fw-bold mb-3">Ready to find your signature look?</h3>
        <p class="text-muted mb-4 max-w-md mx-auto">Explore over 40+ curated designs across casual tops, stretch leggings, handblock kurtis, and nightwear.</p>
        <a href="{{ route('shop') }}" class="btn btn-zyra-primary px-4 py-3">Explore Collection</a>
    </div>
</section>

@endsection

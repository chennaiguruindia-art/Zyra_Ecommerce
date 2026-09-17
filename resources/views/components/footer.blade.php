<!-- Footer Section -->
<footer class="zyra-footer">
    <div class="container">
        <div class="row g-4">
            
            <!-- Brand Column -->
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('home') }}" class="zyra-brand-logo zyra-footer-brand text-white">
                    <img src="{{ asset('images/logo/white_logo.png') }}" alt="ZYRA" class="zyra-logo-image zyra-logo-image-footer">
                </a>
                <p class="text-muted small mb-3 pe-lg-3">
                    Redefining modern Indian women's everyday wardrobe with timeless silhouettes, breathable fabrics, and effortless elegance.
                </p>
                <div class="zyra-social-links">
                    <a href="https://www.instagram.com/zyraofficial46/" target="_blank" rel="noopener noreferrer" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.facebook.com/profile.php?id=61591489273734" target="_blank" rel="noopener noreferrer" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.youtube.com/@ZyraOfficial46" target="_blank" rel="noopener noreferrer" title="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <!-- Customer Service -->
            <div class="col-lg-3 col-6">
                <h6 class="zyra-footer-title">Customer Service</h6>
                <ul class="zyra-footer-links">
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                    <li><a href="{{ route('faq') }}#shipping">Shipping Information</a></li>
                    <li><a href="{{ route('faq') }}#returns">Returns & Exchange</a></li>
                    <li><a href="{{ route('contact') }}">Track Order</a></li>
                </ul>
            </div>

            <!-- Shop Categories -->
            <div class="col-lg-2 col-6">
                <h6 class="zyra-footer-title">Shop</h6>
                <ul class="zyra-footer-links">
                    @foreach($navCategories ?? [] as $navCat)
                        @php
                            $catSlug = $navCat['slug'] ?? '';
                            $catRoute = \Illuminate\Support\Facades\Route::has('pages.' . $catSlug) ? route('pages.' . $catSlug) : route('category', $catSlug);
                        @endphp
                        <li><a href="{{ $catRoute }}">{{ $navCat['name'] ?? '' }}</a></li>
                    @endforeach
                    <li><a href="{{ route('shop') }}?filter=new">New Arrivals</a></li>
                    <li><a href="{{ route('shop') }}?filter=sale">Sale</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div class="col-lg-4 col-md-6">
                <h6 class="zyra-footer-title">Stay In Touch</h6>
                <p class="small text-muted mb-3">
                    Subscribe to receive VIP access to new collection launches, styling tips, and private discount offers.
                </p>
                <form class="newsletter-form mb-3">
                    <div class="input-group input-group-sm">
                        <input type="email" class="form-control" placeholder="Enter your email" required>
                        <button class="btn btn-secondary" type="submit">Join</button>
                    </div>
                </form>
                <div class="small text-muted">
                    <i class="bi bi-envelope me-1"></i> hello@shopwithzyra.in &nbsp;|&nbsp; <i class="bi bi-telephone me-1"></i> +91 9884125555
                </div>
            </div>

        </div>

        <!-- Bottom Copyright -->
        <div class="zyra-footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div>
                &copy; {{ date('Y') }} ZYRA. All Rights Reserved. Designed with care for modern women.
            </div>
            <div class="zyra-visit-counter d-flex align-items-center gap-3 small">
                <span title="Visits today"><i class="bi bi-eye me-1"></i> Today: {{ number_format($siteVisitStats['today'] ?? 0) }}</span>
                <span title="Total visits"><i class="bi bi-graph-up me-1"></i> Total: {{ number_format($siteVisitStats['total'] ?? 0) }}</span>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('about') }}" class="text-muted">About Us</a>
                <a href="{{ route('privacy-policy') }}" class="text-muted">Privacy Policy</a>
                <a href="{{ route('contact') }}" class="text-muted">Contact Us</a>
            </div>
        </div>
    </div>
</footer>

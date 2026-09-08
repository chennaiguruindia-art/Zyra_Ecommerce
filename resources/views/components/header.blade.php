<!-- Top Announcement Bar -->
<div class="zyra-announcement-bar text-center">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="d-none d-md-inline-block"><i class="bi bi-geo-alt me-1"></i> Pan-India Express Delivery</span>
        <span class="mx-auto mx-md-0 fw-semibold">Free Shipping on Orders Above ₹999 | Easy 15-Day Returns</span>
        <span class="d-none d-md-inline-block">
            <a href="{{ route('seller.dashboard') }}" class="text-white text-decoration-none fw-semibold">
                <i class="bi bi-shop me-1 text-warning"></i> Seller Hub
            </a>
        </span>
    </div>
</div>

<!-- Main Sticky Header -->
<header class="zyra-header">
    <div class="container py-3">
        <div class="d-flex align-items-center justify-content-between">
            
            <!-- Mobile Menu Toggle Button -->
            <button class="btn d-lg-none p-0 border-0 fs-3 text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#zyraMobileMenu" aria-controls="zyraMobileMenu">
                <i class="bi bi-list"></i>
            </button>

            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="zyra-brand-logo">
                <img src="{{ asset('images/logo/Zyra _logo.png') }}" alt="ZYRA" class="zyra-logo-image">
            </a>

            <!-- Desktop Primary Navigation -->
            <nav class="d-none d-lg-flex align-items-center gap-1">
                <a href="{{ route('home') }}" class="zyra-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('shop') }}" class="zyra-nav-link {{ request()->routeIs('shop') && !request()->has('filter') ? 'active' : '' }}">Shop</a>
                @foreach($navCategories ?? [] as $navCat)
                    @php
                        $catSlug = $navCat['slug'] ?? '';
                        $catRoute = \Illuminate\Support\Facades\Route::has('pages.' . $catSlug) ? route('pages.' . $catSlug) : route('category', $catSlug);
                        $isActive = request()->is('category/' . $catSlug) || request()->is($catSlug);
                    @endphp
                    <a href="{{ $catRoute }}" class="zyra-nav-link {{ $isActive ? 'active' : '' }}">{{ $navCat['name'] ?? '' }}</a>
                @endforeach
                <a href="{{ route('shop') }}?filter=new" class="zyra-nav-link {{ request()->input('filter') === 'new' ? 'active' : '' }}">New Arrivals</a>
                <a href="{{ route('shop') }}?filter=sale" class="zyra-nav-link sale-link {{ request()->input('filter') === 'sale' ? 'active' : '' }}">Sale</a>
            </nav>

            <!-- Right-Side Action Icons -->
            <div class="zyra-header-icons d-flex align-items-center gap-2">
                <!-- Search Trigger with Dropdown Input -->
                <div class="header-search-container d-none d-sm-block">
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <input type="text" id="headerSearchInput" class="form-control rounded-start-pill ps-3" placeholder="Search kurtis, tops..." autocomplete="off">
                        <button class="btn btn-outline-secondary rounded-end-pill pe-3" type="button" onclick="window.location.href='/search?q=' + encodeURIComponent(document.getElementById('headerSearchInput').value)">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <!-- Live Search Overlay Dropdown -->
                    <div id="headerSearchDropdown" class="header-search-dropdown"></div>
                </div>

                <!-- Wishlist Icon with Dynamic Badge -->
                <a href="{{ route('wishlist') }}" class="icon-btn" title="Wishlist">
                    <i class="bi bi-heart"></i>
                    <span class="zyra-badge-count wishlist-count-badge" style="display: none;">0</span>
                </a>

                <!-- Account Modal / Link -->
                <a href="#" class="icon-btn d-none d-sm-inline-flex" title="My Account" data-bs-toggle="modal" data-bs-target="#zyraAccountModal">
                    <i class="bi bi-person"></i>
                </a>

                <!-- Cart Icon with Dynamic Badge (triggers Mini Cart) -->
                <button type="button" class="icon-btn" data-bs-toggle="offcanvas" data-bs-target="#zyraMiniCart" aria-controls="zyraMiniCart" title="Shopping Cart">
                    <i class="bi bi-bag"></i>
                    <span class="zyra-badge-count cart-count-badge" style="display: none;">0</span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Offcanvas Navigation Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="zyraMobileMenu" aria-labelledby="zyraMobileMenuLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title zyra-brand-logo fs-3" id="zyraMobileMenuLabel">
            <img src="{{ asset('images/logo/Zyra _logo.png') }}" alt="ZYRA" class="zyra-logo-image zyra-logo-image-mobile">
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Mobile Live Search -->
        <div class="mb-4">
            <div class="input-group">
                <input type="text" id="mobileSearchInput" class="form-control" placeholder="Search female fashion...">
                <button class="btn btn-dark" type="button" onclick="window.location.href='/search?q=' + encodeURIComponent(document.getElementById('mobileSearchInput').value)">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>

        <ul class="nav flex-column gap-2 mb-4">
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link text-dark fw-bold border-bottom pb-2">Home</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('shop') }}" class="nav-link text-dark fw-bold border-bottom pb-2">All Products</a>
            </li>
            @foreach($navCategories ?? [] as $navCat)
                @php
                    $catSlug = $navCat['slug'] ?? '';
                    $catRoute = \Illuminate\Support\Facades\Route::has('pages.' . $catSlug) ? route('pages.' . $catSlug) : route('category', $catSlug);
                @endphp
                <li class="nav-item">
                    <a href="{{ $catRoute }}" class="nav-link text-dark fw-medium border-bottom pb-2">{{ $navCat['name'] ?? '' }}</a>
                </li>
            @endforeach
            <li class="nav-item">
                <a href="{{ route('shop') }}?filter=new" class="nav-link text-dark fw-medium border-bottom pb-2">New Arrivals</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('shop') }}?filter=sale" class="nav-link text-danger fw-bold border-bottom pb-2">Sale (Up to 40% OFF)</a>
            </li>
        </ul>

        <div class="d-grid gap-2 mb-4">
            <a href="{{ route('wishlist') }}" class="btn btn-outline-dark btn-sm d-flex justify-content-between align-items-center">
                <span><i class="bi bi-heart me-2"></i> My Wishlist</span>
                <span class="badge bg-secondary wishlist-count-badge">0</span>
            </a>
            <a href="{{ route('cart') }}" class="btn btn-outline-dark btn-sm d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bag me-2"></i> View Cart</span>
                <span class="badge bg-dark cart-count-badge">0</span>
            </a>
            <a href="{{ route('seller.dashboard') }}" class="btn btn-dark btn-sm d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shop me-2 text-warning"></i> Seller Hub</span>
                <span class="badge bg-warning text-dark">Partner Portal</span>
            </a>
        </div>

        <div class="border-top pt-3 small text-muted">
            <p class="mb-1"><i class="bi bi-telephone me-2"></i> +91 98765 43210</p>
            <p class="mb-1"><i class="bi bi-envelope me-2"></i> care@zyrafashion.com</p>
            <p class="mb-0"><i class="bi bi-clock me-2"></i> Mon - Sat: 9:00 AM - 8:00 PM</p>
        </div>
    </div>
</div>

<!-- Account Preview Modal -->
<div class="modal fade" id="zyraAccountModal" tabindex="-1" aria-labelledby="zyraAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="fs-1 text-muted mb-2"><i class="bi bi-person-circle"></i></div>
                <h5 class="fw-bold mb-1">Welcome to ZYRA</h5>
                <p class="text-muted small mb-3">Login to track orders, save shipping addresses, and manage your wishlist.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-zyra-primary" onclick="ZyraApp.showToast('Login/Auth enabled in Phase 2 with MySQL backend.', 'info')">Sign In</button>
                    <button type="button" class="btn btn-zyra-outline" onclick="ZyraApp.showToast('Registration enabled in Phase 2 with MySQL backend.', 'info')">Create Account</button>
                </div>
            </div>
        </div>
    </div>
</div>

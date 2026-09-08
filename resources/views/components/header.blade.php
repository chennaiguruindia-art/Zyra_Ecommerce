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

                <!-- User Profile & Authentication Section -->
                <div class="dropdown zyra-auth-dropdown">
                    @auth
                        <button class="btn btn-link text-decoration-none d-flex align-items-center gap-2 p-1 text-dark dropdown-toggle" type="button" id="headerUserDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #e5e5e5; border-radius: 50px; padding: 4px 10px !important;">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width: 28px; height: 28px; object-fit: cover; border-radius: 50%;">
                            @else
                                <span class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="small fw-semibold d-none d-md-inline text-truncate" style="max-width: 100px;">{{ explode(' ', auth()->user()->name)[0] }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2 mt-2" aria-labelledby="headerUserDropdown" style="min-width: 220px; border-radius: 12px;">
                            <li class="px-3 py-2 border-bottom bg-light">
                                <div class="fw-bold small text-dark">{{ auth()->user()->name }}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
                                <span class="badge {{ auth()->user()->isSeller() ? 'bg-warning text-dark' : 'bg-dark text-white' }} mt-1" style="font-size: 0.65rem;">
                                    {{ auth()->user()->isSeller() ? 'Seller' : 'Verified Customer' }}
                                </span>
                            </li>
                            <li><a class="dropdown-item small py-2 d-flex align-items-center gap-2" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear"></i> My Profile</a></li>
                            <li><a class="dropdown-item small py-2 d-flex align-items-center gap-2" href="{{ route('wishlist') }}"><i class="bi bi-heart"></i> My Wishlist</a></li>
                            <li><a class="dropdown-item small py-2 d-flex align-items-center gap-2" href="{{ route('cart') }}"><i class="bi bi-bag"></i> My Cart</a></li>
                            @if(auth()->user()->isSeller())
                                <li><a class="dropdown-item small py-2 d-flex align-items-center gap-2 text-primary" href="{{ route('seller.dashboard') }}"><i class="bi bi-shop"></i> Seller Hub</a></li>
                            @endif
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item small py-2 text-danger d-flex align-items-center gap-2 border-0 bg-transparent w-100">
                                        <i class="bi bi-box-arrow-right"></i> Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    @else
                        <button class="btn btn-link text-decoration-none d-flex align-items-center gap-1 p-1 text-dark dropdown-toggle" type="button" id="headerGuestDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person fs-5"></i>
                            <span class="small fw-semibold d-none d-md-inline">Sign In</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-3 mt-2" aria-labelledby="headerGuestDropdown" style="min-width: 240px; border-radius: 12px;">
                            <li class="mb-2">
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Welcome to ZYRA</h6>
                                <p class="text-muted" style="font-size: 0.75rem; margin-bottom: 12px;">Login to manage orders, wishlist & fast checkout.</p>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('login') }}" class="btn btn-zyra-primary btn-sm py-2">Login / Sign In</a>
                                    <a href="{{ route('register') }}" class="btn btn-zyra-outline btn-sm py-2">Create Account</a>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li><a class="dropdown-item small py-1 px-1 text-muted d-flex align-items-center gap-2" href="{{ route('wishlist') }}"><i class="bi bi-heart"></i> My Wishlist</a></li>
                            <li><a class="dropdown-item small py-1 px-1 text-muted d-flex align-items-center gap-2" href="{{ route('cart') }}"><i class="bi bi-bag"></i> My Cart</a></li>
                            <li><a class="dropdown-item small py-1 px-1 text-muted d-flex align-items-center gap-2" href="{{ route('seller.dashboard') }}"><i class="bi bi-shop"></i> Seller Portal</a></li>
                        </ul>
                    @endauth
                </div>

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
            @auth
                <div class="p-3 bg-light rounded-3 mb-2">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if (auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 50%;">
                        @else
                            <span class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        @endif
                        <div>
                            <div class="fw-bold small text-dark">{{ auth()->user()->name }}</div>
                            <div class="text-muted small" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-dark flex-grow-1">Profile</a>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Sign Out</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="p-3 bg-light rounded-3 mb-2 text-center">
                    <div class="fw-bold small mb-1">Welcome to ZYRA</div>
                    <p class="text-muted small mb-2" style="font-size: 0.75rem;">Login for faster checkout & saved items</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-zyra-primary flex-grow-1">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-zyra-outline flex-grow-1">Register</a>
                    </div>
                </div>
            @endauth

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
    </div>
</div>

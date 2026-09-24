<!-- Gold Promo Bar -->
<div class="zyra-top-promo">NEW SEASON IS HERE! Discover Elegant Kurtis, Dresses &amp; More &#x1F496;</div>

<style>
.zyra-top-promo { background: #F5B301; color: #3a2b00; text-align: center; font-size: .8rem; font-weight: 600; letter-spacing: .4px; padding: 7px 12px; }
.zyra-header-row { display: flex; align-items: center; gap: 16px; padding: 14px 0; }
.zyra-shipto { font-size: .8rem; color: #444; white-space: nowrap; }
.zyra-shipto small { color: #888; }
.zyra-search-pill { flex: 1; max-width: 560px; margin: 0 auto; }
.zyra-search-pill .zyra-search-wrap { position: relative; }
.zyra-search-pill input { width: 100%; border: 1px solid #e2ddd6; border-radius: 999px; padding: 10px 18px 10px 44px; font-size: .9rem; background: #faf9f7; outline: none; }
.zyra-search-pill input:focus { border-color: #8d5a5a; background: #fff; }
.zyra-search-pill .zyra-search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #777; pointer-events: none; }
.zyra-header-icons .icon-btn { position: relative; }
.zyra-header-icons .zyra-badge-count { position: absolute; top: -7px; right: -9px; background: #F5B301; color: #3a2b00; font-size: .65rem; font-weight: 700; min-width: 18px; height: 18px; line-height: 18px; text-align: center; border-radius: 999px; padding: 0 4px; }
.zyra-catnav { border-top: 1px solid #f0ebe4; }
.zyra-catnav-inner { display: flex; align-items: center; gap: 26px; overflow-x: auto; scrollbar-width: none; }
.zyra-catnav-inner::-webkit-scrollbar { display: none; }
.zyra-catnav-inner a { font-size: .78rem; font-weight: 600; letter-spacing: .8px; color: #333; text-decoration: none; padding: 12px 2px; white-space: nowrap; text-transform: uppercase; border-bottom: 2px solid transparent; }
.zyra-catnav-inner a:hover { color: #8d5a5a; }
.zyra-catnav-inner a.cat-active { color: #8d5a5a; border-bottom-color: #8d5a5a; }
.zyra-catnav-inner a.cat-hot { background: #8d5a5a; color: #fff; padding: 6px 14px; border-bottom: none; }
.zyra-catnav-inner a.cat-hot:hover { color: #fff; opacity: .9; }
</style>

<!-- Main Sticky Header -->
<header class="zyra-header">
    <div class="container">
        <div class="zyra-header-row">

            <!-- Mobile Menu Toggle Button -->
            <button class="btn d-lg-none p-0 border-0 fs-3 text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#zyraMobileMenu" aria-controls="zyraMobileMenu">
                <i class="bi bi-list"></i>
            </button>

            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="zyra-brand-logo">
                <img src="{{ asset('images/logo/Zyra _logo.png') }}" alt="ZYRA" class="zyra-logo-image">
            </a>

            <!-- Ship-to (desktop) -->
            <span class="zyra-shipto d-none d-xl-inline">Ship to &#x1F1EE;&#x1F1F3; <strong>India (&#x20B9;)</strong> <small>&#x25BE;</small></span>

            <!-- Center Search Pill (desktop) -->
            <div class="zyra-search-pill header-search-container d-none d-md-block">
                <div class="zyra-search-wrap">
                    <i class="bi bi-search zyra-search-icon"></i>
                    <input type="text" id="headerSearchInput" placeholder="Search kurtis, tops and dresses" autocomplete="off">
                </div>
                <!-- Live Search Overlay Dropdown -->
                <div id="headerSearchDropdown" class="header-search-dropdown"></div>
            </div>

            <!-- Right-Side Action Icons -->
            <div class="zyra-header-icons d-flex align-items-center gap-2 ms-auto">

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
                            <li><a class="dropdown-item small py-2 d-flex align-items-center gap-2" href="{{ route('my-orders') }}"><i class="bi bi-receipt"></i> My Orders</a></li>
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

    <!-- Category Nav Row (desktop) -->
    <nav class="zyra-catnav d-none d-lg-block">
        <div class="container">
            <div class="zyra-catnav-inner">
                <a href="{{ route('home') }}#new-arrivals" class="cat-hot">New In</a>
                @foreach($navCategories ?? [] as $navCat)
                    @php
                        $catSlug = $navCat['slug'] ?? '';
                        $catRoute = \Illuminate\Support\Facades\Route::has('pages.' . $catSlug) ? route('pages.' . $catSlug) : route('category', $catSlug);
                        $catActive = request()->is('category/' . $catSlug) || request()->is($catSlug);
                    @endphp
                    <a href="{{ $catRoute }}" class="{{ $catActive ? 'cat-active' : '' }}">{{ $navCat['name'] ?? '' }}</a>
                @endforeach
                <a href="{{ route('pages.duppata') }}" class="{{ request()->is('duppata') ? 'cat-active' : '' }}">Dupatta</a>
                <a href="{{ route('home') }}#best-sellers">Top Seller</a>
                <a href="{{ route('shop') }}?filter=sale" class="cat-hot {{ request()->input('filter') === 'sale' ? 'cat-active' : '' }}">Sale</a>
            </div>
        </div>
    </nav>
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

            <!-- Zyra Collection Group -->
            <li class="nav-item mt-1">
                <span class="text-uppercase small fw-bold text-muted"><i class="bi bi-grid-3x3-gap me-1"></i> Zyra Collection</span>
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
                <a href="{{ route('pages.duppata') }}" class="nav-link text-dark fw-medium border-bottom pb-2">Dupatta</a>
            </li>

            <li class="nav-item mt-2">
                <a href="{{ route('home') }}#best-sellers" class="nav-link text-dark fw-bold border-bottom pb-2"><i class="bi bi-trophy me-2"></i> Top Seller</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('home') }}#new-arrivals" class="nav-link text-dark fw-bold border-bottom pb-2"><i class="bi bi-stars me-2"></i> New Arrival</a>
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
                        <a href="{{ route('my-orders') }}" class="btn btn-sm btn-outline-dark flex-grow-1">Orders</a>
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
        </div>
    </div>
</div>

<!-- Gold Promo Bar (rotating messages) -->
<div class="zyra-top-promo">
    <div class="zyra-promo-track" id="zyraPromoTrack">
        <span class="zyra-promo-msg active">NEW SEASON IS HERE! Discover Elegant Kurtis, Dresses &amp; More</span>
        <span class="zyra-promo-msg">PAN-WORLD SHIPPING &mdash; Delivery In 3&ndash;5 Days</span>
        <span class="zyra-promo-msg">FREE SHIPPING ON ALL ORDERS </span>
        <span class="zyra-promo-msg">FAST DELIVERY AVAILABLE</span>
    </div>
</div>
<script>
(function () {
    const msgs = document.querySelectorAll('#zyraPromoTrack .zyra-promo-msg');
    if (msgs.length < 2) return;
    let i = 0;
    setInterval(function () {
        msgs[i].classList.remove('active');
        i = (i + 1) % msgs.length;
        msgs[i].classList.add('active');
    }, 4000);
})();
</script>

<style>
.zyra-top-promo { background: #5d2c11; color: #FFFF; font-size: .8rem; font-weight: 600; letter-spacing: .4px; padding: 7px 12px; }
.zyra-promo-track { display: grid; text-align: center; }
.zyra-promo-msg { grid-area: 1 / 1; opacity: 0; transform: translateY(10px); transition: opacity .6s ease, transform .6s ease; }
.zyra-promo-msg.active { opacity: 1; transform: none; }
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
<style>
#zyraMobileMenu { width: min(340px, 88vw); }
.mnav-head { background: linear-gradient(135deg, #2b2323 0%, #8d5a5a 100%); padding: 18px 18px 16px; }
.mnav-head img { height: 34px; width: auto; }
.mnav-head .btn-close { background-color: #fff; border-radius: 50%; width: 26px; height: 26px; font-size: .7rem; }
.mnav-tag { color: rgba(255,255,255,.75); font-size: .7rem; letter-spacing: 2px; text-transform: uppercase; margin-top: 2px; }
.mnav-search { padding: 12px 16px 4px; background: #fff; }
.mnav-search .input-group { border: 1px solid #e8e0d8; border-radius: 999px; overflow: hidden; }
.mnav-search input { border: none; font-size: .85rem; padding: 9px 6px 9px 16px; }
.mnav-search input:focus { box-shadow: none; }
.mnav-search .btn { background: #18181b; color: #fff; border: none; padding: 0 16px; }
.mnav-link { display: flex; align-items: center; gap: 12px; padding: 11px 18px; color: #2b2323; text-decoration: none; font-weight: 600; font-size: .9rem; border-bottom: 1px solid #f4f0eb; }
.mnav-link:hover { color: #8d5a5a; background: #faf8f6; }
.mnav-ic { flex: 0 0 34px; width: 34px; height: 34px; border-radius: 12px; background: #faf0f0; color: #8d5a5a; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; }
.mnav-link .bi-chevron-right { margin-left: auto; color: #c9bcb4; font-size: .8rem; }
.mnav-sec { font-size: .68rem; font-weight: 800; letter-spacing: 1.5px; color: #a98f8f; padding: 16px 18px 4px; text-transform: uppercase; }
.mnav-sale { color: #d6336c !important; }
.mnav-sale .mnav-ic { background: #fdeef3; color: #d6336c; }
.mnav-user { margin: 14px 16px; background: #faf8f6; border: 1px solid #f0e6e1; border-radius: 16px; padding: 14px; }
.mnav-btn { display: flex; align-items: center; justify-content: space-between; width: 100%; border: 1px solid #e5d9d3; background: #fff; border-radius: 12px; padding: 10px 14px; font-size: .85rem; font-weight: 600; color: #2b2323; text-decoration: none; margin-top: 8px; }
.mnav-foot { padding: 4px 18px 22px; font-size: .78rem; color: #8a7a74; }
.mnav-foot a { color: #8d5a5a; font-weight: 700; text-decoration: none; }
</style>
<div class="offcanvas offcanvas-start p-0" tabindex="-1" id="zyraMobileMenu" aria-labelledby="zyraMobileMenuLabel">
    <div class="mnav-head">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <img src="{{ asset('images/logo/white_logo.png') }}" alt="ZYRA" id="zyraMobileMenuLabel">
                <div class="mnav-tag">Feel Beautiful Everyday</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body p-0">
        <div class="mnav-search">
            <div class="input-group">
                <input type="text" id="mobileSearchInput" class="form-control border-0" placeholder="Search kurtis, tops, dresses...">
                <button class="btn" type="button" onclick="window.location.href='/search?q=' + encodeURIComponent(document.getElementById('mobileSearchInput').value)">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>

        <a href="{{ route('home') }}" class="mnav-link"><span class="mnav-ic"><i class="bi bi-house"></i></span> Home <i class="bi bi-chevron-right"></i></a>

        <div class="mnav-sec">Zyra Collection</div>
        <a href="{{ route('shop') }}" class="mnav-link"><span class="mnav-ic"><i class="bi bi-grid"></i></span> All Products <i class="bi bi-chevron-right"></i></a>
        @foreach($navCategories ?? [] as $navCat)
            @php
                $mCatSlug = $navCat['slug'] ?? '';
                $mCatRoute = \Illuminate\Support\Facades\Route::has('pages.' . $mCatSlug) ? route('pages.' . $mCatSlug) : route('category', $mCatSlug);
            @endphp
            <a href="{{ $mCatRoute }}" class="mnav-link"><span class="mnav-ic"><i class="bi bi-bag"></i></span> {{ $navCat['name'] ?? '' }} <i class="bi bi-chevron-right"></i></a>
        @endforeach
        <a href="{{ route('pages.duppata') }}" class="mnav-link"><span class="mnav-ic"><i class="bi bi-bag"></i></span> Dupatta <i class="bi bi-chevron-right"></i></a>

        <div class="mnav-sec">Discover</div>
        <a href="{{ route('home') }}#best-sellers" class="mnav-link"><span class="mnav-ic"><i class="bi bi-trophy"></i></span> Top Seller <i class="bi bi-chevron-right"></i></a>
        <a href="{{ route('home') }}#new-arrivals" class="mnav-link"><span class="mnav-ic"><i class="bi bi-stars"></i></span> New Arrival <i class="bi bi-chevron-right"></i></a>
        <a href="{{ route('shop') }}?filter=sale" class="mnav-link mnav-sale"><span class="mnav-ic"><i class="bi bi-lightning-charge"></i></span> Sale · Up to 40% OFF <i class="bi bi-chevron-right"></i></a>

        <div class="mnav-user">
            @auth
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
            @else
                <div class="fw-bold small mb-1">Welcome to ZYRA</div>
                <p class="text-muted small mb-2" style="font-size: 0.75rem;">Login for faster checkout &amp; saved items</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-sm btn-zyra-primary flex-grow-1">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-zyra-outline flex-grow-1">Register</a>
                </div>
            @endauth
            <a href="{{ route('wishlist') }}" class="mnav-btn"><span><i class="bi bi-heart me-2"></i> My Wishlist</span><span class="badge bg-secondary wishlist-count-badge">0</span></a>
            <a href="{{ route('cart') }}" class="mnav-btn"><span><i class="bi bi-bag me-2"></i> View Cart</span><span class="badge bg-dark cart-count-badge">0</span></a>
        </div>

        <div class="mnav-foot">
            Need help? Call <a href="tel:+919884125555">+91 98841 25555</a> or <a href="{{ route('contact') }}">contact us</a>.
        </div>
    </div>
</div>

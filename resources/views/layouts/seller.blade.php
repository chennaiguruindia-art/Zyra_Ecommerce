<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo.png') }}">

    <!-- SEO & Social Meta Tags -->
    <title>@yield('title', 'Seller Panel | ZYRA')</title>
    <meta name="description" content="@yield('meta_description', 'ZYRA Seller Panel - manage your products, inventory, orders and sales.')">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/seller.css') }}?v={{ time() }}">

    @stack('styles')
</head>
<body class="seller-panel-body">

    <!-- Seller Top Bar (Clean, Luxury White Background with high-contrast brand styling) -->
    <header class="seller-topbar">
        <div class="seller-topbar-inner px-3 px-md-4">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-dark d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sellerSidebarCollapse" aria-expanded="false" aria-controls="sellerSidebarCollapse" title="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <a href="{{ route('seller.dashboard') }}" class="seller-brand" title="ZYRA Seller Hub Dashboard">
                    <img src="{{ asset('images/logo/Zyra _logo.png') }}" alt="ZYRA" class="seller-logo-image">
                    <span class="seller-hub-pill">SELLER HUB</span>
                </a>
            </div>

            <div class="d-flex align-items-center gap-2 gap-md-3">
                <a href="{{ route('home') }}" class="seller-topbar-link" title="Open customer-facing online boutique">
                    <i class="bi bi-shop"></i>
                    <span class="d-none d-md-inline">Buyer Storefront</span>
                </a>

                <a href="{{ route('seller.products.add') }}" class="btn btn-zyra-primary d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i>
                    <span>Add Product</span>
                </a>

                <div class="dropdown">
                    <button class="btn seller-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-md-inline fw-semibold">{{ auth()->user()?->name ?? 'Ananya Boutique' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold text-dark small">{{ auth()->user()?->name ?? 'Ananya Boutique' }}</div>
                            <div class="text-muted smaller">{{ auth()->user()?->email ?? 'partner@zyrafashion.com' }}</div>
                            <span class="badge bg-success-subtle text-success mt-1">Verified Seller Partner</span>
                        </li>
                        <li><a class="dropdown-item py-2" href="{{ route('seller.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('seller.settings') }}"><i class="bi bi-gear me-2"></i> Store Settings</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('home') }}" target="_blank"><i class="bi bi-box-arrow-up-right me-2"></i> View Live Storefront</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="{{ route('home') }}">
                                <i class="bi bi-box-arrow-right me-2"></i> Exit Seller Hub
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Seller Flex Layout (Zero overlap guaranteed) -->
    <div class="seller-wrapper">
        <!-- Seller Sidebar -->
        @include('components.seller-sidebar')

        <!-- Main Panel Content -->
        <main class="seller-main">
            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer class="seller-footer text-center text-muted py-3">
        <small>&copy; {{ date('Y') }} ZYRA Seller Hub &bull; All rights reserved. &bull; <a href="{{ route('home') }}" class="text-muted text-decoration-none">Back to ZYRA Shopping Store</a></small>
    </footer>

    <!-- Dynamic Toast Container -->
    <div id="zyraToastContainer" class="zyra-toast-container"></div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- Keep sticky topbar height in sync -->
    <script>
        (function () {
            var topbar = document.querySelector('.seller-topbar');
            if (!topbar) return;

            var syncTopbarHeight = function () {
                var h = Math.round(topbar.getBoundingClientRect().height);
                if (h > 0) {
                    document.body.style.setProperty('--seller-topbar-h', h + 'px');
                }
            };

            syncTopbarHeight();
            window.addEventListener('resize', syncTopbarHeight);
            window.addEventListener('load', syncTopbarHeight);
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(syncTopbarHeight);
            }
        })();
    </script>

    <!-- Application Modules -->
    <script src="{{ asset('js/cart.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/seller.js') }}?v={{ time() }}"></script>

    @stack('scripts')
</body>
</html>

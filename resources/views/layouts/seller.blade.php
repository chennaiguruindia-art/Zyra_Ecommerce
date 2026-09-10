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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/seller.css') }}">

    @stack('styles')
</head>
<body class="seller-panel-body">

    <!-- Seller Top Bar -->
    <header class="seller-topbar">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-light d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sellerSidebarCollapse" aria-expanded="false" aria-controls="sellerSidebarCollapse">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <a href="{{ route('seller.dashboard') }}" class="seller-brand">
                    <img src="{{ asset('images/logo/Zyra _logo.png') }}" alt="ZYRA" class="seller-logo-image"> <span>Seller Hub</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="seller-topbar-link" title="View Customer Storefront">
                    <i class="bi bi-shop"></i> <span class="d-none d-md-inline">Buyer Storefront</span>
                </a>
                <a href="{{ route('seller.products.add') }}" class="btn btn-sm btn-zyra-primary d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Add Product
                </a>
                <div class="dropdown">
                    <button class="btn seller-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-md-inline">{{ auth()->user()?->name ?? 'Ananya Boutique' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><span class="dropdown-item-text small text-muted">{{ auth()->user()?->email ?? 'partner@zyrafashion.com' }}</span></li>
                        <li><span class="badge bg-success-subtle text-success ms-3 mb-2">Verified Partner</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('seller.settings') }}"><i class="bi bi-gear me-2"></i> Store Settings</a></li>
                        <li><a class="dropdown-item" href="{{ route('home') }}"><i class="bi bi-box-arrow-up-right me-2"></i> View Buyer Website</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('home') }}">
                                <i class="bi bi-box-arrow-right me-2"></i> Exit Seller Hub
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Seller Sidebar -->
            @include('components.seller-sidebar')

            <!-- Main Panel Content -->
            <main class="col-lg-10 col-xl-10 col-12 seller-main">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="seller-footer text-center text-muted py-3">
        <small>&copy; {{ date('Y') }} ZYRA Seller Hub. All rights reserved. &bull; <a href="{{ route('home') }}" class="text-muted text-decoration-none">Back to ZYRA Shopping Store</a></small>
    </footer>

    <!-- Dynamic Toast Container -->
    <div id="zyraToastContainer" class="zyra-toast-container"></div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- Application Modules -->
    <script src="{{ asset('js/cart.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/seller.js') }}?v=20260910c"></script>

    @stack('scripts')
</body>
</html>
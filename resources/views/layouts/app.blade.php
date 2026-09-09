<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO & Social Meta Tags -->
    <title>@yield('title', 'ZYRA | Modern Indian Women\'s Fashion & Clothing Store')</title>
    <meta name="description" content="@yield('meta_description', 'Discover chic women\'s fashion at ZYRA. Premium tops, cotton leggings, elegant kurtis, breezy maxi dresses, and luxury nightwear designed for effortless style.')">
    <meta name="keywords" content="women fashion, indian clothing, kurtis, tops, leggings, maxi dresses, nightwear, ZYRA fashion">
    <meta property="og:title" content="@yield('og_title', 'ZYRA | Elevate Your Everyday Style')">
    <meta property="og:description" content="@yield('og_description', 'Shop effortlessly chic modern Indian women\'s clothing with free shipping on all orders.')">
    <meta property="og:image" content="https://images.unsplash.com/photo-1534126511673-b6899657816a?auto=format&fit=crop&w=1200&q=80">
    <meta property="og:type" content="website">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

    @stack('styles')
</head>
<body data-auth="{{ auth()->check() ? 'true' : 'false' }}">

    <!-- Header Component -->
    @include('components.header')

    <!-- Main View Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer Component -->
    @include('components.footer')

    <!-- Offcanvas Mini Cart Drawer -->
    @include('components.mini-cart')

    <!-- Quick View Modal -->
    @include('components.quick-view-modal')

    <!-- Dynamic Toast Notification Container -->
    <div id="zyraToastContainer" class="zyra-toast-container"></div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Application Modules -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/cart.js') }}?v=20260908d"></script>
    <script src="{{ asset('js/wishlist.js') }}"></script>
    <script src="{{ asset('js/checkout.js') }}?v=20260908d"></script>
    <script src="{{ asset('js/search.js') }}"></script>

    @stack('scripts')
</body>
</html>

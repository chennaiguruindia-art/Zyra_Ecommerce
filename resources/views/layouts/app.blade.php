<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo.png') }}">

    <!-- SEO & Social Meta Tags -->
    <title>@yield('title', config('seo.site_name') . ' | ' . config('seo.tagline'))</title>
    <meta name="description" content="@yield('meta_description', config('seo.default_description'))">
    <meta name="keywords" content="{{ config('seo.default_keywords') }}">
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <meta name="author" content="{{ config('seo.site_name') }}">
    <meta name="theme-color" content="#635454">
    <meta name="geo.region" content="{{ config('seo.geo.region') }}">
    <meta name="geo.placename" content="{{ config('seo.geo.placename') }}">
    <meta name="geo.position" content="{{ config('seo.geo.position') }}">
    <meta name="ICBM" content="{{ config('seo.geo.icbm') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ \App\Support\Seo::canonical() }}">

    <!-- Open Graph -->
    <meta property="og:site_name" content="{{ config('seo.site_name') }}">
    <meta property="og:locale" content="{{ config('seo.locale') }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', config('seo.site_name') . ' | ' . config('seo.tagline'))">
    <meta property="og:description" content="@yield('og_description', config('seo.default_description'))">
    <meta property="og:image" content="@yield('og_image', \App\Support\Seo::asset('images/logo/Zyra _logo.png'))">
    <meta property="og:url" content="{{ \App\Support\Seo::canonical() }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
    <meta name="twitter:site" content="@zyraofficial46">
    <meta name="twitter:title" content="@yield('og_title', config('seo.site_name') . ' | ' . config('seo.tagline'))">
    <meta name="twitter:description" content="@yield('og_description', config('seo.default_description'))">
    <meta name="twitter:image" content="@yield('og_image', \App\Support\Seo::asset('images/logo/Zyra _logo.png'))">

    <!-- Structured Data (JSON-LD) -->
    @stack('schema')

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}">

    @stack('styles')

    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ config('meta.pixel_id') }}');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ config('meta.pixel_id') }}&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->

    <!-- ZYRA Meta event helper – every event passes through this wrapper so
         Purchase can share an event id with the server side Conversions API. -->
    <script>
    window.zyraMeta = window.zyraMeta || {
        track(eventName, data, eventId) {
            if (typeof fbq !== 'function') return false;
            try {
                if (eventId) {
                    fbq('track', eventName, data || {}, { eventID: eventId });
                } else {
                    fbq('track', eventName, data || {});
                }
                return true;
            } catch (e) {
                return false;
            }
        },
        purchaseEventId(orderNumber) {
            return 'zyra-purchase-' + orderNumber;
        }
    };
    </script>
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

    <!-- Shop Assistant Chatbot Widget -->
    @include('components.chatbot-widget')

    <!-- Dynamic Toast Notification Container -->
    <div id="zyraToastContainer" class="zyra-toast-container"></div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Application Modules -->
    <script src="{{ asset('js/app.js') }}?v=20260913a"></script>
    <script src="{{ asset('js/cart.js') }}?v=20260911c"></script>
    <script src="{{ asset('js/wishlist.js') }}"></script>
    <script src="{{ asset('js/checkout.js') }}?v=20260908d"></script>
    <script src="{{ asset('js/search.js') }}?v=20260918a"></script>
    <script src="{{ asset('js/analytics.js') }}?v=20260924a"></script>

    @stack('scripts')
</body>
</html>

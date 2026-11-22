<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('translation.loader', function ($loader, $app) {
            if ($loader instanceof \App\Translation\SafeFileLoader) {
                return $loader;
            }

            $safe = new \App\Translation\SafeFileLoader($app['files'], $loader->paths());

            foreach ($loader->jsonPaths() as $path) {
                $safe->addJsonPath($path);
            }

            foreach ($loader->namespaces() as $namespace => $hint) {
                $safe->addNamespace($namespace, $hint);
            }

            return $safe;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            static $navCategories = null;
            if ($navCategories === null) {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
                        $navCategories = \App\Models\Category::query()
                            ->active()
                            ->with('subcategories')
                            ->withCount('products')
                            ->get()
                            ->map(fn ($c) => $c->toNavArray())
                            ->all();
                    }
                } catch (\Throwable $e) {
                    $navCategories = [];
                }
            }
            $view->with('navCategories', $navCategories ?? []);
        });
    }

    /**
     * Configure high-performance rate limiters designed for concurrent shoppers.
     * Prevents false blocks for multiple users on the same WiFi/Carrier NAT (Jio, Airtel).
     */
    protected function configureRateLimiting(): void
    {
        // Generates a distinct client key per shopper so 100+ concurrent users never throttle each other
        $clientKey = function (Request $request): string {
            if ($user = $request->user()) {
                return 'u:' . $user->id;
            }
            if ($request->hasSession() && ($sessId = $request->session()->getId())) {
                return 's:' . $sessId;
            }
            $uaHash = substr(md5($request->userAgent() . '|' . ($request->header('accept-language') ?? '')), 0, 8);
            return 'ip:' . $request->ip() . ':' . $uaHash;
        };

        // 1. General Web Browsing: 180 requests/min per client (smooth for 100+ concurrent customers)
        RateLimiter::for('web', function (Request $request) use ($clientKey) {
            if ($request->is('webhooks/*')) {
                return Limit::none();
            }

            return Limit::perMinute(180)
                ->by($clientKey($request))
                ->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 10;
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You are browsing very fast. Please wait ' . $retryAfter . ' seconds.',
                            'retry_after' => (int) $retryAfter,
                        ], Response::HTTP_TOO_MANY_REQUESTS, $headers);
                    }
                    return response()->view('errors.429', [
                        'retryAfter' => $retryAfter,
                    ], Response::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });

        // 2. API Global: 120 requests/min per client
        RateLimiter::for('api', function (Request $request) use ($clientKey) {
            return Limit::perMinute(120)
                ->by($clientKey($request))
                ->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 10;
                    return response()->json([
                        'success' => false,
                        'message' => 'API rate limit exceeded. Please retry in ' . $retryAfter . ' seconds.',
                        'retry_after' => (int) $retryAfter,
                    ], Response::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });

        // 3. Checkout & Order Placement: 20 attempts/min per client
        // Prevents double-submitting payments or bot card testing, while ensuring legitimate customers purchase instantly
        RateLimiter::for('checkout', function (Request $request) use ($clientKey) {
            return Limit::perMinute(20)
                ->by('checkout:' . $clientKey($request))
                ->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 5;
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment/order request is being processed. Please wait ' . $retryAfter . ' seconds.',
                        'retry_after' => (int) $retryAfter,
                    ], Response::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });

        // 4. Authentication (Login / Register / Password Reset): 10 attempts/min per email + IP
        // Thwarts brute-force attacks without blocking innocent shoppers on the same WiFi/network
        RateLimiter::for('auth', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', '')));
            $key = 'auth:' . ($email ?: 'guest') . '|' . $request->ip();
            return Limit::perMinute(10)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 60;
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Too many login attempts. Please wait ' . $retryAfter . ' seconds before trying again.',
                            'retry_after' => (int) $retryAfter,
                        ], Response::HTTP_TOO_MANY_REQUESTS, $headers);
                    }
                    return back()->withInput($request->only('email'))->withErrors([
                        'email' => 'Too many attempts. Please try again in ' . $retryAfter . ' seconds.',
                    ]);
                });
        });

        // 5. Product Search: 60 queries/min per client
        RateLimiter::for('search', function (Request $request) use ($clientKey) {
            return Limit::perMinute(60)
                ->by('search:' . $clientKey($request))
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Search rate limit reached. Please wait a few seconds.',
                    ], Response::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });
    }
}

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile App & REST API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the ApplicationBuilder with the "api" prefix.
| Perfect for Flutter, React Native, iOS, Android, and SPA frontends.
|
*/

// ==========================================
// 1. PUBLIC STOREFRONT & HOME
// ==========================================
Route::get('/home', [HomeController::class, 'index'])->name('api.home');
Route::get('/categories', [ProductController::class, 'categories'])->name('api.categories');
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{idOrSlug}', [ProductController::class, 'show'])->name('api.products.show');
Route::get('/products/{id}/quick-view', [ProductController::class, 'quickView'])->name('api.products.quick-view');

// ==========================================
// 2. SEARCH
// ==========================================
Route::get('/search', [SearchController::class, 'search'])->name('api.search');
Route::get('/search/live', [SearchController::class, 'live'])->name('api.search.live');

// ==========================================
// 3. AUTHENTICATION (PUBLIC)
// ==========================================
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.forgot-password');

// ==========================================
// 4. CUSTOMER PROFILE (PROTECTED)
// ==========================================
Route::middleware('auth.api')->group(function () {
    Route::get('/user', [AuthController::class, 'profile'])->name('api.user');
    Route::get('/profile', [AuthController::class, 'profile'])->name('api.profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('api.profile.update');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('api.password.change');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Customer order history
    Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('api.orders.show');
});

// Guest-accessible order tracking by order number
Route::get('/order-tracking/{orderNumber}', [OrderController::class, 'show'])->name('api.orders.track');

// ==========================================
// 5. SHOPPING CART & WISHLIST & CHECKOUT
// ==========================================
Route::middleware('auth.api.optional')->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('api.cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('api.cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('api.cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('api.cart.remove');
    Route::post('/cart/sync', [CartController::class, 'sync'])->name('api.cart.sync');
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('api.cart.apply-coupon');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('api.wishlist.index');
    Route::get('/wishlist/ids', [WishlistController::class, 'ids'])->name('api.wishlist.ids');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('api.wishlist.toggle');

    // Checkout & Payments
    Route::get('/checkout/summary', [CheckoutController::class, 'summary'])->name('api.checkout.summary');
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('api.checkout.place-order');
    Route::post('/checkout/verify-payment', [CheckoutController::class, 'verifyPayment'])->name('api.checkout.verify-payment');
});

// ==========================================
// 6. SUPPORT, CHATBOT & TRACKING
// ==========================================
Route::post('/contact', [SupportController::class, 'contact'])->name('api.contact');
Route::post('/chatbot', [SupportController::class, 'chatbot'])->name('api.chatbot');
Route::post('/track', [SupportController::class, 'track'])->name('api.track');

// ==========================================
// 7. SELLER APP APIs (SELLER PROTECTED)
// ==========================================
Route::prefix('seller')->middleware(['auth.api', 'seller'])->group(function () {
    Route::get('/products', [SellerController::class, 'apiProducts'])->name('api.seller.products');
    Route::get('/orders', [SellerController::class, 'apiOrders'])->name('api.seller.orders');
});

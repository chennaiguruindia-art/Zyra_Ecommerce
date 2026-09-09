<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\GlobalSettingController;
use App\Http\Controllers\InstagramController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicStorageController;

Route::get('/media/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('media.public');

Route::get('/storage/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.public');

Route::get('/uploads/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('uploads.public');

Route::middleware('auth')->get('/dashboard', function () {
    $user = Auth::user();
    if ($user instanceof User && $user->isSeller()) {
        return redirect()->route('seller.dashboard');
    }
    return view('dashboard');
})->name('dashboard');

Route::prefix('seller')->name('seller.')->group(function () {
    Route::get('/', [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/products/add', [SellerController::class, 'addProduct'])->name('products.add');
    Route::get('/products', [SellerController::class, 'products'])->name('products');
    Route::get('/products/{id}/edit', [SellerController::class, 'editProduct'])->name('products.edit');
    Route::get('/sales', [SellerController::class, 'sales'])->name('sales');
    Route::get('/inventory', [SellerController::class, 'inventory'])->name('inventory');
    Route::get('/analytics', [SellerController::class, 'analytics'])->name('analytics');
    Route::get('/settings', [SellerController::class, 'settings'])->name('settings');

    Route::get('/api/products', [SellerController::class, 'apiProducts'])->name('api.products');
    Route::get('/api/orders', [SellerController::class, 'apiOrders'])->name('api.orders');
    Route::post('/products', [SellerController::class, 'storeProduct'])->name('products.store');
    Route::post('/products/{id}/update', [SellerController::class, 'updateProduct'])->name('products.update');
    Route::post('/products/{id}/delete', [SellerController::class, 'destroyProduct'])->name('products.destroy');
    Route::post('/products/{id}/inventory', [SellerController::class, 'updateInventory'])->name('products.inventory');
    Route::post('/orders/{id}/status', [SellerController::class, 'updateOrderStatus'])->name('orders.status');
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog.json', [CatalogController::class, 'json'])->name('catalog.json');

Route::get('/global_setting', [GlobalSettingController::class, 'index'])->name('global_setting');
Route::post('/global_setting/toggle', [GlobalSettingController::class, 'toggle'])->name('global_setting.toggle');

Route::get('/instagram', [InstagramController::class, 'index'])->name('instagram');
Route::post('/instagram', [InstagramController::class, 'store'])->name('instagram.store');
Route::delete('/instagram/{id}', [InstagramController::class, 'destroy'])->name('instagram.destroy');

Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/shop/filter', [ShopController::class, 'filter'])->name('shop.filter');
Route::get('/category/{category}', [ShopController::class, 'category'])->name('category');

Route::get('/tops', [ShopController::class, 'shortcut'])->defaults('slug', 'tops')->name('pages.tops');
Route::get('/leggings', [ShopController::class, 'shortcut'])->defaults('slug', 'leggings')->name('pages.leggings');
Route::get('/kurtis', [ShopController::class, 'shortcut'])->defaults('slug', 'kurtis')->name('pages.kurtis');
Route::get('/maxi', [ShopController::class, 'shortcut'])->defaults('slug', 'maxi')->name('pages.maxi');
Route::get('/nightwear', [ShopController::class, 'shortcut'])->defaults('slug', 'nightwear')->name('pages.nightwear');

Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.details');
Route::get('/product/{id}/quick-view', [ProductController::class, 'quickView'])->name('product.quick-view');

// Authenticated customer routes (Checkout placement only; cart & wishlist are public)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place-order');
    Route::post('/checkout/payment/verify', [CheckoutController::class, 'verifyPayment'])->name('checkout.payment.verify');
});

Route::middleware('auth')->get('/my-orders', [OrderController::class, 'myOrders'])->name('my-orders');

Route::get('/wishlist', fn () => view('wishlist'))->name('wishlist');
Route::get('/cart', fn () => view('cart'))->name('cart');

Route::get('/wishlist/items', [WishlistController::class, 'items'])->name('wishlist.items');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/sync', [CartController::class, 'sync'])->name('cart.sync');
Route::get('/cart/items', [CartController::class, 'items'])->name('cart.items');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.remove-coupon');

Route::get('/order-success/{order_number}', [OrderController::class, 'success'])->name('order.success');
Route::get('/order-success', fn () => view('order-success'))->name('order.success.fallback');

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/live', [SearchController::class, 'live'])->name('search.live');

Route::get('/about', fn () => view('about'))->name('about');
Route::get('/contact', fn () => view('contact'))->name('contact');
Route::get('/faq', fn () => view('faq'))->name('faq');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

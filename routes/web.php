<?php

use App\Http\Controllers\Storefront\StorefrontAuthController;
use App\Http\Controllers\Storefront\StorefrontCartController;
use App\Http\Controllers\Storefront\StorefrontCheckoutController;
use App\Http\Controllers\Storefront\StorefrontController;
use App\Http\Controllers\Storefront\StorefrontWishlistController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';

Route::get('/', [StorefrontController::class, 'home'])->name('store.home');
Route::get('/invoice-preview/{template}', [StorefrontController::class, 'invoicePreview'])->name('store.invoice-preview');
Route::get('/language/{locale}', [StorefrontController::class, 'switchLanguage'])->name('store.language');
Route::get('/email-preview/{template}', [StorefrontController::class, 'emailPreview'])->name('store.email-preview');
Route::get('/category/{category:slug}', [StorefrontController::class, 'category'])->name('store.category');
Route::get('/product/{product}', [StorefrontController::class, 'product'])->name('store.product');

Route::post('/store/login', [StorefrontAuthController::class, 'login'])->name('store.auth.login');
Route::post('/store/register', [StorefrontAuthController::class, 'register'])->name('store.auth.register');
Route::post('/store/logout', [StorefrontAuthController::class, 'logout'])->name('store.auth.logout');
Route::post('/cart/add', [StorefrontCartController::class, 'add'])->name('store.cart.add');
Route::post('/cart/update', [StorefrontCartController::class, 'update'])->name('store.cart.update');
Route::post('/cart/remove/{lineKey}', [StorefrontCartController::class, 'remove'])->where('lineKey', '[0-9:]+')->name('store.cart.remove');
Route::post('/cart/clear', [StorefrontCartController::class, 'clear'])->name('store.cart.clear');
Route::post('/wishlist/add', [StorefrontWishlistController::class, 'add'])->name('store.wishlist.add');
Route::post('/wishlist/remove/{productId}', [StorefrontWishlistController::class, 'remove'])->name('store.wishlist.remove');
Route::post('/wishlist/toggle', [StorefrontWishlistController::class, 'toggle'])->name('store.wishlist.toggle');
Route::post('/checkout/place-order', [StorefrontCheckoutController::class, 'placeOrder'])->name('store.checkout.place-order');

Route::get('/{page}', [StorefrontController::class, 'page'])->name('store.page');


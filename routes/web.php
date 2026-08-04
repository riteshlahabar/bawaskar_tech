<?php

use App\Http\Controllers\Storefront\StorefrontController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';

Route::get('/', [StorefrontController::class, 'home'])->name('store.home');
Route::get('/invoice-preview/{template}', [StorefrontController::class, 'invoicePreview'])->name('store.invoice-preview');
Route::get('/language/{locale}', [StorefrontController::class, 'switchLanguage'])->name('store.language');
Route::get('/email-preview/{template}', [StorefrontController::class, 'emailPreview'])->name('store.email-preview');
Route::get('/category/{category:slug}', [StorefrontController::class, 'category'])->name('store.category');
Route::get('/product/{product}', [StorefrontController::class, 'product'])->name('store.product');
Route::get('/{page}', [StorefrontController::class, 'page'])->name('store.page');

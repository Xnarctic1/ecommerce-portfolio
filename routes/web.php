<?php

use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::livewire('/products', 'pages::products')->name('products');

Route::livewire('/admin', 'pages::admin.dashboard')
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');

Route::livewire('/admin/products/create', 'pages::admin.create-product')
    ->middleware(['auth', 'admin'])
    ->name('admin.products.create');

Route::livewire('/admin/products', 'pages::admin.products')
    ->middleware(['auth', 'admin'])
    ->name('admin.products');

Route::livewire('/admin/products/{product}/edit', 'pages::admin.edit-product')
    ->middleware(['auth', 'admin'])
    ->name('admin.products.edit');

Route::livewire('/admin/categories', 'pages::admin.categories')
    ->middleware(['auth', 'admin'])
    ->name('admin.categories');

Route::livewire('/admin/categories/create', 'pages::admin.create-category')
    ->middleware(['auth', 'admin'])
    ->name('admin.categories.create');

Route::livewire('/admin/categories/{category}/edit', 'pages::admin.edit-category')
    ->middleware(['auth', 'admin'])
    ->name('admin.categories.edit');

Route::livewire('/admin/orders/{order}', 'pages::admin.order-show')
    ->middleware(['auth', 'admin'])
    ->name('admin.orders.show');

Route::livewire('/products/{product}', 'pages::product-show')
    ->name('products.show');

Route::livewire('/cart', 'pages::cart')
    ->name('cart');

Route::livewire('/wishlist', 'pages::wishlist')
    ->middleware('auth')
    ->name('wishlist');

Route::livewire('/account', 'pages::account')
    ->middleware('auth')
    ->name('account');

Route::livewire('/orders', 'pages::orders')
    ->middleware('auth')
    ->name('orders');

Route::livewire('/orders/{order}', 'pages::order-show')
    ->middleware('auth')
    ->name('orders.show');

Route::livewire('/admin/orders', 'pages::admin.orders')
    ->middleware('auth', 'admin')
    ->name('admin.orders');

Route::livewire('/checkout', 'pages::checkout')
    ->middleware('auth')
    ->name('checkout');

Route::get('/checkout/success', [StripeCheckoutController::class, 'success'])
    ->middleware('auth')
    ->name('checkout.success');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = request()->user();

        if ($user && $user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('account');
    })->name('dashboard');
});

require __DIR__.'/settings.php';

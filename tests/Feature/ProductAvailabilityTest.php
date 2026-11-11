<?php

use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->product = Product::create([
        'name' => 'Availability test product',
        'price' => 20,
        'stock' => 5,
        'is_active' => true,
    ]);
});

test('active products can be viewed and added to the cart', function () {
    $this->get(route('products.show', $this->product))->assertOk();

    Livewire::test('pages::product-show', ['product' => $this->product])
        ->set('quantity', 1)
        ->call('addToCart')
        ->assertHasNoErrors();

    expect(session('cart')[$this->product->id]['quantity'])->toBe(1);
});

test('inactive products return not found on their direct URL', function () {
    $this->product->update(['is_active' => false]);

    $this->get(route('products.show', $this->product))->assertNotFound();
});

test('a product deactivated after opening its page cannot be added to the cart', function () {
    $page = Livewire::test('pages::product-show', ['product' => $this->product]);
    $this->product->update(['is_active' => false]);

    $page->call('addToCart')->assertHasErrors(['stock']);

    expect(session('cart', []))->toBe([]);
});

test('checkout rejects unavailable saved cart items before contacting Stripe', function (bool $deleted) {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['cart' => [
        $this->product->id => ['name' => $this->product->name, 'price' => 20, 'quantity' => 1],
    ]]);

    $page = Livewire::test('pages::checkout')
        ->set('customer_name', 'Test Customer')
        ->set('email', $user->email)
        ->set('address', '1 Test Street')
        ->set('city', 'Valletta')
        ->set('postal_code', 'VLT 1000')
        ->set('country', 'MT');

    if ($deleted) {
        $this->product->delete();
    } else {
        $this->product->update(['is_active' => false]);
    }

    // No usable key: reaching Stripe would fail instead of returning validation errors.
    config(['services.stripe.secret' => null]);
    $page->call('checkout')->assertHasErrors(['stock'])->assertNoRedirect();
    $this->assertDatabaseCount('orders', 0);
})->with([false, true]);

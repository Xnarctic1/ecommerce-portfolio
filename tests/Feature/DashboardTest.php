<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('customers are redirected to their account', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('account'));
});

test('administrators are redirected to the admin dashboard', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('customers cannot access admin pages', function (string $route) {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get(route($route))->assertForbidden();
})->with(['admin.dashboard', 'admin.products', 'admin.categories', 'admin.orders']);

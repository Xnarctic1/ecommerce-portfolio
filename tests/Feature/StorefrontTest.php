<?php

test('the home page redirects to the product catalogue', function () {
    $this->get('/')->assertRedirect(route('products'));
});

test('guests can browse the product catalogue', function () {
    $this->get(route('products'))->assertOk()->assertSee('Discover products');
});

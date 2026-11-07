<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Product;

new #[Layout('layouts.public')] #[Title('Cart')] class extends Component
{

    public function cart(): array
{
        return session()->get('cart', []);
}

    public function total(): float
{
    $total = 0;
    foreach ($this->cart() as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;

}

public function increaseQuantity(int $productId): void

{
    $cart = $this->cart();
    if (! isset($cart[$productId])) {
        return;
    }

    $product = Product::findOrFail($productId);
    if ($cart[$productId]['quantity'] >= $product->stock) {
        $this->addError(
            'stock',
            "Only {$product->stock} units of {$product->name} are available."
        );
        return;
    }

    $cart[$productId]['quantity']++;
    session()->put('cart', $cart);
    $cartCount = collect($cart)->sum('quantity');
    $this->dispatch('cart-updated', count: $cartCount);
    $this->resetErrorBag('stock');
}

public function decreaseQuantity(int $productId): void
{
    $cart = $this->cart();
    if (! isset($cart[$productId])) {
        return;
    }
    if ($cart[$productId]['quantity'] > 1) {
        $cart[$productId]['quantity']--;
    } else {
        unset($cart[$productId]);
    }
    session()->put('cart', $cart);
    $cartCount = collect($cart)->sum('quantity');
    $this->dispatch('cart-updated', count: $cartCount);
}

public function removeItem(int $productId): void
{
    $cart = $this->cart();
    unset($cart[$productId]);
    session()->put('cart', $cart);
    $cartCount = collect($cart)->sum('quantity');
    $this->dispatch('cart-updated', count: $cartCount);
}

};

?>

<div style="max-width: 1000px; margin: 0 auto; padding: 24px;">    
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 6px;">
            Shopping Cart
        </h1>

        <p style="color: #666;">
            Review your items before checkout.
        </p>
    </div>

    @error('stock')
        <div
            style="
                margin-bottom: 20px;
                padding: 12px 14px;
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: 6px;
                color: #b91c1c;
            "
        >
            {{ $message }}
        </div>
    @enderror

    @forelse ($this->cart() as $productId => $item)
        <div
            wire:key="cart-item-{{ $productId }}"
            class="cart-item"
            style="
                border: 1px solid #e5e5e5;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 16px;
                display: flex;
                justify-content: space-between;
                gap: 24px;
                flex-wrap: wrap;
                align-items: center;
            "
        >
            <div class="cart-item-info" style="flex: 1; min-width: 220px;">
                <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 6px;">
                    {{ $item['name'] }}
                </h2>

                <p style="color: #555;">
                    €{{ number_format($item['price'], 2) }} each
                </p>

                <button
                    type="button"
                    wire:click="removeItem({{ $productId }})"
                    wire:confirm="Remove {{ $item['name'] }} from your cart?"
                    style="
                        margin-top: 12px;
                        color: #b91c1c;
                        background: transparent;
                        border: none;
                        padding: 0;
                        cursor: pointer;
                        font-weight: 600;
                    "
                >
                    Remove
                </button>
            </div>

            <div class="cart-item-quantity" style="display: flex; align-items: center; gap: 10px;">
                <button
                    type="button"
                    wire:click="decreaseQuantity({{ $productId }})"
                    style="
                        width: 38px;
                        height: 38px;
                        border: 1px solid #ccc;
                        border-radius: 6px;
                        background: white;
                    "
                >
                    −
                </button>

                <span style="min-width: 34px; text-align: center; font-weight: 600;">
                    {{ $item['quantity'] }}
                </span>

                <button
                    type="button"
                    wire:click="increaseQuantity({{ $productId }})"
                    style="
                        width: 38px;
                        height: 38px;
                        border: 1px solid #ccc;
                        border-radius: 6px;
                        background: white;
                    "
                >
                    +
                </button>
            </div>

            <div class="cart-item-subtotal" style="min-width: 120px; text-align: right;">
                <p style="font-size: 13px; color: #777;">
                    Subtotal
                </p>

                <strong style="font-size: 18px;">
                    €{{ number_format($item['price'] * $item['quantity'], 2) }}
                </strong>
            </div>
        </div>

    @empty

        <div
            style="
                border: 1px solid #e5e5e5;
                border-radius: 10px;
                padding: 40px 20px;
                text-align: center;
            "
        >
            <h2 style="font-size: 22px; font-weight: 700;">
                Your cart is empty
            </h2>

            <p style="color: #666; margin-top: 8px;">
                Add some products to get started.
            </p>

            <a
                href="{{ route('products') }}"
                style="
                    display: inline-block;
                    margin-top: 18px;
                    background: black;
                    color: white;
                    padding: 10px 16px;
                    border-radius: 6px;
                    text-decoration: none;
                "
            >
                Browse Products
            </a>
        </div>

    @endforelse

    @if (count($this->cart()) > 0)
        <div
            class="cart-summary"
            style="
                margin-top: 28px;
                border-top: 2px solid #222;
                padding-top: 20px;
                display: flex;
                justify-content: space-between;
                gap: 20px;
                flex-wrap: wrap;
                align-items: center;
            "
        >
            <div>
                <p style="color: #666; margin-bottom: 4px;">
                    Order total
                </p>

                <strong style="font-size: 28px;">
                    €{{ number_format($this->total(), 2) }}
                </strong>
            </div>

            <a
                class="cart-checkout-link"
                href="{{ route('checkout') }}"
                style="
                    display: inline-block;
                    background: black;
                    color: white;
                    padding: 13px 22px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 600;
                "
            >
                Proceed to Checkout →
            </a>
        </div>
    @endif
</div>

<style>
    @media (max-width: 700px) {

        .cart-page {
            padding: 18px 14px 40px !important;
        }

        .cart-item {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px 12px !important;
            padding: 16px !important;
            align-items: center !important;
        }

        .cart-item-info {
            grid-column: 1 / -1;
            min-width: 0 !important;
        }

        .cart-item-info h2 {
            font-size: 18px !important;
            overflow-wrap: anywhere;
        }

        .cart-item-quantity {
            grid-column: 1;
            gap: 8px !important;
        }

        .cart-item-quantity button {
            cursor: pointer;
            flex-shrink: 0;
        }

        .cart-item-subtotal {
            grid-column: 2;
            min-width: 0 !important;
            text-align: right !important;
        }

        .cart-summary {
            flex-direction: column;
            align-items: stretch !important;
            gap: 18px !important;
        }

        .cart-checkout-link {
            display: block !important;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }
    }
</style>
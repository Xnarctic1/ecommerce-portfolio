<?php

use App\Models\Wishlist;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Wishlist')] class extends Component
{
    public function refreshWishlist(): void
    {
        unset($this->wishlistItems);
    }

    #[Computed(cache: false)]
    public function wishlistItems()
    {
        return Wishlist::query()
            ->with('product.category')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    public function removeFromWishlist(int $productId): void
    {
        Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->delete();

        unset($this->wishlistItems);
    }
};
?>

<div class="wishlist-page">

    <div class="wishlist-header">
        <p class="wishlist-kicker">
            Your saved items
        </p>

        <h1 class="wishlist-title">
            Wishlist
        </h1>
    </div>

    @if ($this->wishlistItems->isEmpty())

        <div class="wishlist-empty-state">
            <div class="wishlist-empty-icon">
                ♡
            </div>

            <h2 class="wishlist-empty-title">
                Your wishlist is empty
            </h2>

            <p class="wishlist-description">
                Save products you like and they will appear here for you to find later.
            </p>

            <a
                class="wishlist-browse"
                href="{{ route('products') }}"
                wire:navigate
            >
                Browse Products
            </a>
        </div>

    @else

        <div class="wishlist-grid">

            @foreach ($this->wishlistItems as $wishlistItem)

                @php
                    $product = $wishlistItem->product;
                @endphp

                @if ($product)

                    <article
                        class="wishlist-card"
                        wire:key="wishlist-{{ $wishlistItem->id }}"
                    >

                        <a
                            class="wishlist-image"
                            href="{{ route('products.show', $product) }}"
                            wire:navigate
                        >

                            @if ($product->image)

                                <img
                                    class="store-product-image"
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                >

                            @else

                                <div class="wishlist-image-placeholder">
                                    No image
                                </div>

                            @endif

                        </a>

                        <div class="wishlist-content">

                            <p class="wishlist-category">
                                {{ $product->category?->name ?? 'Uncategorized' }}
                            </p>

                            <a
                                class="wishlist-product-link"
                                href="{{ route('products.show', $product) }}"
                                wire:navigate
                            >
                                <h2 class="wishlist-product-title">
                                    {{ $product->name }}
                                </h2>
                            </a>

                            <p class="wishlist-price">
                                €{{ number_format($product->price, 2) }}
                            </p>

                            <p class="wishlist-stock {{ $product->stock > 0 ? 'wishlist-in-stock' : 'wishlist-out-of-stock' }}">
                                {{
                                    $product->stock > 0
                                        ? $product->stock . ' in stock'
                                        : 'Out of stock'
                                }}
                            </p>

                            <div class="wishlist-actions">

                                <a
                                    class="wishlist-view"
                                    href="{{ route('products.show', $product) }}"
                                    wire:navigate
                                >
                                    View Product
                                </a>

                                <button
                                    class="wishlist-remove"
                                    type="button"
                                    wire:click="
                                        removeFromWishlist(
                                            {{ $product->id }}
                                        )
                                    "
                                    wire:confirm="
                                        Remove this product from your wishlist?
                                    "
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </article>

                @endif

            @endforeach
        </div>
    @endif
</div>

@script
<script>
    document.addEventListener('livewire:navigated', () => {
        $wire.refreshWishlist();
    });
</script>
@endscript

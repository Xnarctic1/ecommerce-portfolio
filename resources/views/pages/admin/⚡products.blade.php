<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] #[Title('Admin Products')] class extends Component
{
    use WithPagination;

    public string $stockFilter = 'all';
    public string $search = '';
    #[Computed]
    public function products()
    {
        return Product::with('category')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->stockFilter === 'in_stock', function ($query) {
                $query->where('stock', '>', 5);
            })
            ->when($this->stockFilter === 'low_stock', function ($query) {
                $query->whereBetween('stock', [1, 5]);
            })
            ->when($this->stockFilter === 'out_of_stock', function ($query) {
                $query->where('stock', 0);
            })
            ->latest()
            ->paginate(10);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStockFilter(): void
    {
        $this->resetPage();
    }

    public function increaseStock(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->increment('stock');
    }

    public function decreaseStock(int $productId): void
    {
        $product = Product::findOrFail($productId);

        if ($product->stock > 0) {
            $product->decrement('stock');
        }
    }

    public function toggleActive(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $product->update([
            'is_active' => ! $product->is_active,
        ]);
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $product->delete();
    }
};

?>

<div class="admin-products-page">
    <div class="admin-products-shell">

        <div class="admin-products-header">
            <div>
                <p class="admin-kicker">ADMIN</p>
                <h1>Products</h1>
                <p class="admin-subtitle">
                    Manage inventory, availability and product visibility.
                </p>
            </div>

            <a
                href="{{ route('admin.products.create') }}"
                class="admin-primary-button"
            >
                + Create Product
            </a>
        </div>

        <div class="admin-toolbar">

            <div class="admin-field admin-search-field">
                <label for="search">Search products</label>

                <input
                    id="search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by product name..."
                >
            </div>

            <div class="admin-field">
                <label for="stockFilter">Stock filter</label>

                <select
                    id="stockFilter"
                    wire:model.live="stockFilter"
                >
                    <option value="all">All products</option>
                    <option value="in_stock">In stock</option>
                    <option value="low_stock">Low stock</option>
                    <option value="out_of_stock">Out of stock</option>
                </select>
            </div>

        </div>

        <div class="admin-products-list">

            @forelse ($this->products as $product)

                <article
                    class="admin-product-card"
                    wire:key="product-{{ $product->id }}"
                >

                    <div class="admin-product-main">

                        <div class="admin-product-image-wrap">

                            @if ($product->image)

                                <img
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    class="admin-product-image"
                                >

                            @else

                                <div class="admin-product-image-placeholder">
                                    No image
                                </div>

                            @endif

                        </div>

                        <div class="admin-product-info">

                            <div class="admin-product-title-row">

                                <div>
                                    <div class="admin-product-category">
                                        {{ $product->category?->name ?? 'Uncategorized' }}
                                    </div>

                                    <h2>
                                        {{ $product->name }}
                                    </h2>
                                </div>

                                <div class="admin-product-price">
                                    €{{ number_format($product->price, 2) }}
                                </div>

                            </div>

                            <div class="admin-product-meta-grid">

                                <div class="admin-meta-item">

                                    <span class="admin-meta-label">
                                        Stock
                                    </span>

                                    <div class="admin-stock-row">

                                        <strong>
                                            {{ $product->stock }}
                                        </strong>

                                        @if ($product->stock === 0)

                                            <span class="admin-badge admin-badge-danger">
                                                Out of stock
                                            </span>

                                        @elseif ($product->stock <= 5)

                                            <span class="admin-badge admin-badge-warning">
                                                Low stock
                                            </span>

                                        @else

                                            <span class="admin-badge admin-badge-success">
                                                In stock
                                            </span>

                                        @endif

                                    </div>

                                </div>

                                <div class="admin-meta-item">

                                    <span class="admin-meta-label">
                                        Visibility
                                    </span>

                                    @if ($product->is_active)

                                        <span class="admin-badge admin-badge-success">
                                            Active
                                        </span>

                                    @else

                                        <span class="admin-badge admin-badge-muted">
                                            Inactive
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="admin-product-actions">

                        <div class="admin-stock-controls">

                            <button
                                type="button"
                                wire:click="decreaseStock({{ $product->id }})"
                                @disabled($product->stock <= 0)
                                class="admin-icon-button"
                            >
                                −1
                            </button>

                            <button
                                type="button"
                                wire:click="increaseStock({{ $product->id }})"
                                class="admin-icon-button"
                            >
                                +1
                            </button>

                        </div>

                        <div class="admin-action-buttons">

                            <a
                                href="{{ route('admin.products.edit', $product) }}"
                                class="admin-secondary-button"
                            >
                                Edit
                            </a>

                            <button
                                type="button"
                                wire:click="toggleActive({{ $product->id }})"
                                class="admin-secondary-button"
                            >
                                {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                            </button>

                            <button
                                type="button"
                                wire:click="deleteProduct({{ $product->id }})"
                                wire:confirm="Are you sure you want to delete {{ $product->name }}?"
                                class="admin-danger-button"
                            >
                                Delete
                            </button>

                        </div>

                    </div>

                </article>

            @empty

                <div class="admin-empty-state">

                    <h2>
                        No products found
                    </h2>

                    <p>
                        Try changing the search or stock filter.
                    </p>

                </div>

            @endforelse

        </div>

        <div class="admin-pagination">
            {{ $this->products->links() }}
        </div>

    </div>
</div>
<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Category;
use Livewire\WithPagination;

new #[Layout('layouts.public')] #[Title('Products')] class extends Component
{   
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public string $sort = 'newest';

      #[Computed]
    public function products()
    {
        return Product::query()
        ->with('category')
        ->where('is_active', true)

        ->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })
        ->when($this->category, function ($query) {
            $query->where('category_id', $this->category);
        })
        ->when($this->sort === 'price_asc', function ($query) {
            $query->orderBy('price');
        })
        ->when($this->sort === 'price_desc', function ($query) {
            $query->orderByDesc('price');
        })
        ->when($this->sort === 'name_asc', function ($query) {
            $query->orderBy('name');
        })
        ->when($this->sort === 'newest', function ($query) {
            $query->latest();
        })
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->paginate(8);
    }

        #[Computed]
        public function categories()
        {
            return Category::orderBy('name')->get();
        }

        public function updatedSearch(): void
        {
            $this->resetPage();
        }

        public function updatedCategory(): void
        {
            $this->resetPage();
        }

        public function updatedSort(): void
        {
            $this->resetPage();
        }

}
?>

<div class="products-page">
    <div class="products-shell">

        {{-- PAGE HEADER --}}
        <section class="products-header">
            <div>
                <p class="products-kicker">
                    Store
                </p>

                <h1 class="products-title">
                    Discover products
                </h1>

                <p class="products-subtitle">
                    Browse the latest pieces, filter by category and find exactly what you are looking for.
                </p>
            </div>

            <div class="products-count">
                {{ $this->products->total() }} products
            </div>
        </section>


        {{-- FILTERS --}}
        <section
            class="products-filters"
            x-data="{ filtersOpen: false }"
        >
            {{-- MOBILE FILTER TOGGLE --}}
            <button
                type="button"
                class="products-filter-toggle"
                @click="filtersOpen = !filtersOpen"
                :aria-expanded="filtersOpen.toString()"
                aria-controls="products-filter-fields"
            >
                <span>☰ &nbsp; Filters &amp; sorting</span>

                <span
                    x-text="filtersOpen ? '−' : '+'"
                    aria-hidden="true"
                ></span>
            </button>

            {{-- FILTER FIELDS --}}
            <div
                id="products-filter-fields"
                class="products-filter-fields"
                :class="{ 'is-open': filtersOpen }"
            >
                <div>
                    <label
                        class="products-filter-label"
                        for="search"
                    >
                        Search
                    </label>

                    <input
                        class="products-search"
                        id="search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search products..."
                    >
                </div>

                <div>
                    <label
                        class="products-filter-label"
                        for="category"
                    >
                        Category
                    </label>

                    <select
                        class="products-select"
                        id="category"
                        wire:model.live="category"
                    >
                        <option value="">All categories</option>

                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="products-filter-label"
                        for="sort"
                    >
                        Sort by
                    </label>

                    <select
                        class="products-select"
                        id="sort"
                        wire:model.live="sort"
                    >
                        <option value="newest">Newest</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="name_asc">Name: A to Z</option>
                    </select>
                </div>
            </div>
        </section>


        {{-- PRODUCTS GRID --}}
        <section class="products-grid">
            @forelse ($this->products as $product)

                <article
                    class="product-card"
                    wire:key="product-{{ $product->id }}"
                >

                    {{-- PRODUCT IMAGE --}}
                    <a
                        class="product-image-link"
                        href="{{ route('products.show', $product) }}"
                    >
                        <div class="product-image-area">
                            @if ($product->image)

                                <img
                                    class="store-product-image"
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                >

                            @else

                                <div class="product-image-placeholder">
                                    No image
                                </div>

                            @endif

                            <div class="product-category-badge">
                                {{ $product->category?->name ?? 'Uncategorized' }}
                            </div>
                        </div>
                    </a>


                    {{-- PRODUCT CONTENT --}}
                    <div class="product-content">
                        <div class="product-card-heading">
                            <h2 class="product-title">
                                {{ $product->name }}
                            </h2>

                            <p class="product-price">
                                €{{ number_format($product->price, 2) }}
                            </p>
                        </div>


                        <div class="product-rating">
    @if ($product->reviews_count > 0)

        <span class="product-rating-stars">
            @for ($star = 1; $star <= 5; $star++)

                {{
                    $star <= round(
                        $product->reviews_avg_rating
                    )
                        ? '★'
                        : '☆'
                }}

            @endfor
        </span>

        <span class="product-review-count">
            {{
                number_format(
                    (float) $product->reviews_avg_rating,
                    1
                )
            }}

            ({{ $product->reviews_count }})
        </span>

    @else

        <span class="product-no-reviews">
            No reviews yet
        </span>

    @endif
</div>


                        {{-- DESCRIPTION --}}
                        <p class="product-description">
                            {{ $product->description }}
                        </p>


                        <div class="product-actions">

                            {{-- STOCK --}}
                            @if ($product->stock === 0)

                                <div class="product-stock-out">
                                    <span class="product-stock-dot"></span>

                                    Out of stock
                                </div>

                            @elseif ($product->stock <= 5)

                                <div class="product-stock-low">
                                    <span class="product-stock-dot"></span>

                                    Only {{ $product->stock }} left
                                </div>

                            @else

                                <div class="product-stock-in">
                                    <span class="product-stock-dot"></span>

                                    In stock
                                </div>

                            @endif


                            {{-- VIEW BUTTON --}}
                            <a
                                class="product-view-button"
                                href="{{ route('products.show', $product) }}"
                            >
                                View product
                            </a>
                        </div>
                    </div>
                </article>

            @empty

                <div class="products-empty-state">
                    <p class="products-empty-title">
                        No products found
                    </p>

                    <p class="products-empty-description">
                        Try changing your search or filters.
                    </p>
                </div>

            @endforelse
        </section>


        {{-- PAGINATION --}}
        <div class="products-pagination">
            {{ $this->products->links() }}
        </div>
    </div>


    {{-- RESPONSIVE DESIGN --}}
</div>

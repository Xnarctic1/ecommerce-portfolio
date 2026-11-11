<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\Wishlist;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Product')] class extends Component
{
    public Product $product;
    public int $quantity = 1;
    public string $successMessage = '';
    public int $rating = 5;
    public string $comment = '';
    public string $reviewSuccessMessage = '';
    public bool $editingReview = false;


    public function mount(Product $product): void
    {
        abort_unless($product->is_active, 404);
        $this->product = $product->load('images');
    }

    #[Computed]
    public function reviews()
    {
        return Review::query()
            ->with('user')
            ->where('product_id', $this->product->id)
            ->latest()
            ->get();
    }

    #[Computed]
    public function averageRating(): float
    {
        return (float) Review::where(
            'product_id',
            $this->product->id
        )->avg('rating');
    }

    #[Computed]
    public function reviewCount(): int
    {
    return Review::where('product_id', $this->product->id)->count();
    }

    #[Computed]
    public function userReview(): ?Review
    {
        if (! auth()->check()) {
            return null;
        }
    return Review::where('product_id', $this->product->id)
        ->where('user_id', auth()->id())
        ->first();
    }

    #[Computed]
    public function canReview(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()
            ->orders()
            ->where('payment_status', 'paid')
            ->whereHas('items',function ($query) {
                    $query->where('product_id',$this->product->id);
                })
            ->exists();
    }

    #[Computed]
    public function isWishlisted(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return Wishlist::where('user_id',auth()->id())
            ->where('product_id', $this->product->id)
            ->exists();
    }

    public function toggleWishlist(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');
        return;
        }

        $wishlist = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $this->product->id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();

        } else {

            Wishlist::create(['user_id' => auth()->id(),
            'product_id' => $this->product->id,]);
        }

        unset( $this->isWishlisted );
    }

    public function submitReview(): void
    {
        if (! auth()->check()) {
            abort(403);
        }

        if (! $this->canReview) {
            $this->addError('review', 'Only customers who purchased this product can leave a review.');
            return;
        }

        if ($this->userReview) {
            $this->addError('review', 'You have already reviewed this product.');
            return;
        }

        $validated = $this->validate([
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'comment' => [
                'nullable',
            'string',
            'max:1000',
            ],
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $this->product->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?: null,
        ]);

        $this->rating = 5;
        $this->comment = '';
        $this->reviewSuccessMessage =
            'Thank you for your review.';

        unset(
            $this->reviews,
            $this->averageRating,
            $this->reviewCount,
            $this->userReview
            );
    }


    public function startEditingReview(): void
    {
        if (! $this->userReview) {
            return;
        }

        $this->rating = $this->userReview->rating;
        $this->comment = $this->userReview->comment ?? '';
        $this->reviewSuccessMessage = '';
        $this->editingReview = true;
    }

    public function cancelEditingReview(): void
    {
        $this->rating = 5;
        $this->comment = '';
        $this->editingReview = false;
        $this->resetValidation();
    }

    public function updateReview(): void
    {
        if (
            ! auth()->check()
            || ! $this->userReview
        ) {
            abort(403);
        }

        $validated = $this->validate([
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
                ],
                'comment' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $this->userReview->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?: null,
        ]);

        $this->editingReview = false;
        $this->rating = 5;
        $this->comment = '';
        $this->reviewSuccessMessage = 'Your review has been updated.';

        unset(
            $this->reviews,
            $this->averageRating,
            $this->reviewCount,
            $this->userReview
        );
    }

    public function deleteReview(): void
    {
        if (
            ! auth()->check()
            || ! $this->userReview
        ) {
            abort(403);
        }

        $this->userReview->delete();
        $this->editingReview = false;
        $this->rating = 5;
        $this->comment = '';
        $this->reviewSuccessMessage = 'Your review has been deleted.';

        unset(
            $this->reviews,
            $this->averageRating,
            $this->reviewCount,
            $this->userReview
        );
    }

    public function addToCart(): void
    {
        $this->product->refresh();

        if (! $this->product->is_active) {
            $this->addError('stock', 'This product is no longer available.');

            return;
        }

        if ($this->product->stock <= 0) {
            $this->addError('stock','This product is out of stock.');
        return;
        }

        $this->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $this->product->stock,
            ],
        ]);

        $cart = session()->get('cart', []);
        $productId = $this->product->id;
        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + $this->quantity;
        if ($newQuantity > $this->product->stock) {
            $this->addError('stock','You cannot add more than the available stock.');
        return;
        }

        $cart[$productId]['quantity'] = $newQuantity;
            } else {
                $cart[$productId] = [
                    'name' => $this->product->name,
                    'price' => $this->product->price,
                    'quantity' => $this->quantity,
                ];
                    }

        session()->put('cart', $cart);
        $cartCount = collect($cart)->sum('quantity');
        $this->dispatch(
            'cart-updated',
            count: $cartCount
        );
        $this->successMessage = $this->quantity . ' item(s) added to your cart.';
    }
};
?>

<div class="product-page" style="max-width: 1200px; margin: 0 auto; padding: 24px;">
    <a
        href="{{ route('products') }}"
        style="display: inline-block; margin-bottom: 24px; color: #444; text-decoration: none;"
    >
        ← Back to products
    </a>

    <div
        class="product-details-grid"
        style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            align-items: start;
        "
    >
    @php
    $galleryPaths = collect([$product->image])
        ->concat($product->images->pluck('path'))
        ->filter()
        ->unique()
        ->values();

    $galleryUrls = $galleryPaths
        ->map(fn ($path) => asset('storage/' . $path))
        ->all();
@endphp

<div>
    @if (count($galleryUrls) > 0)

        <div
        x-data="{

        images: @js($galleryUrls),

        activeIndex: 0,

        viewerOpen: false,

        zoomX: 50,

        zoomY: 50,

        zooming: false,

touchStartX: null,
touchStartY: null,

handleTouchStart(event) {
    if (event.touches.length !== 1) return;

    this.touchStartX = event.touches[0].clientX;
    this.touchStartY = event.touches[0].clientY;
},

handleTouchEnd(event) {
    if (
        this.touchStartX === null ||
        this.touchStartY === null ||
        event.changedTouches.length !== 1
    ) return;

    const deltaX = event.changedTouches[0].clientX - this.touchStartX;
    const deltaY = event.changedTouches[0].clientY - this.touchStartY;

    this.touchStartX = null;
    this.touchStartY = null;

    if (
        this.images.length < 2 ||
        Math.abs(deltaX) < 50 ||
        Math.abs(deltaX) <= Math.abs(deltaY)
    ) return;

    if (deltaX < 0) {
        this.nextImage();
    } else {
        this.previousImage();
    }
},

nextImage() {

            this.activeIndex = (this.activeIndex + 1) % this.images.length;

        },

        previousImage() {

            this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length;

        },

        updateZoom(event) {

            const rect = event.currentTarget.getBoundingClientRect();

            this.zoomX = Math.max(

                0,

                Math.min(100, ((event.clientX - rect.left) / rect.width) * 100)

            );

            this.zoomY = Math.max(

                0,

                Math.min(100, ((event.clientY - rect.top) / rect.height) * 100)

            );

        },

        openViewer() {

            this.zooming = false;

            this.viewerOpen = true;

        },

        closeViewer() {

            this.viewerOpen = false;

        }

    }"

    class="product-gallery"

    @keydown.window.escape="closeViewer()"

    @keydown.window.arrow-right="if (viewerOpen) nextImage()"

    @keydown.window.arrow-left="if (viewerOpen) previousImage()"
        >

<button
    type="button"
    class="product-gallery-main"
    aria-label="Open full-screen image viewer"
    @click="openViewer()"
    @mousemove="updateZoom($event)"
    @mouseenter="zooming = true"
    @mouseleave="zooming = false"
    @touchstart.passive="handleTouchStart($event)"
    @touchend.passive="handleTouchEnd($event)"
>

    <img

        :src="images[activeIndex]"

        alt="{{ $product->name }}"

        class="product-gallery-main-image"

        :class="{ 'is-zoomed': zooming }"

        :style="zooming ? `transform-origin: ${zoomX}% ${zoomY}%;` : ''"

    >

    <span class="product-gallery-zoom-hint">

        Click to enlarge ⤢

    </span>

</button>



            <div class="product-gallery-thumbnails" x-show="images.length > 1">

                <template x-for="(image, index) in images" :key="index">

                    <button
                        type="button"
                        class="product-gallery-thumbnail"
                        :class="{ 'is-active': activeIndex === index }"
                        @click="activeIndex = index"
                        :aria-label="`Show product image ${index + 1}`"
                        :aria-pressed="activeIndex === index"
                    >
                        <img :src="image" alt="Product thumbnail">
                    </button>

                </template>
                </div>

            {{-- FULL-SCREEN IMAGE VIEWER --}}

            <div class="product-gallery-viewer-host">
                <div
                    x-show="viewerOpen"
                    x-cloak
                    x-transition.opacity
                    class="product-gallery-lightbox"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Product image viewer"
                    @click.self="closeViewer()"
                    @touchstart.passive="handleTouchStart($event)"
                    @touchend.passive="handleTouchEnd($event)"

                >

                    <button
                        type="button"
                        class="product-gallery-lightbox-close"
                        aria-label="Close image viewer"
                        @click="closeViewer()"
                        >
                        ×
                    </button>

                    <button
                        x-show="images.length > 1"
                        type="button"
                        class="product-gallery-lightbox-arrow previous"
                        aria-label="Previous image"
                        @click="previousImage()"
                    >
                        ‹
                    </button>

                    <img
                        :src="images[activeIndex]"
                        alt="{{ $product->name }}"
                        class="product-gallery-lightbox-image"
                    >
                    <button
                        x-show="images.length > 1"
                        type="button"
                        class="product-gallery-lightbox-arrow next"
                        aria-label="Next image"
                        @click="nextImage()"
                    >

                        ›
                    </button>

                    <span
                        class="product-gallery-lightbox-counter"
                        x-text="`${activeIndex + 1} / ${images.length}`"
                    ></span>

                </div>
            </div>
        </div>
    @else
            
         <div>No image</div>

    @endif
</div>

        <div class="product-details-info">
            <p style="font-size: 14px; color: #777; margin-bottom: 8px;">
                {{ $product->category?->name ?? 'Uncategorized' }}
            </p>

<h1 class="product-details-title" style="font-size: 36px; font-weight: 700; line-height: 1.15; margin-bottom: 16px;">                {{ $product->name }}
            </h1>

            <p style="font-size: 28px; font-weight: 700; margin-bottom: 20px;">
                €{{ number_format($product->price, 2) }}
            </p>

            <p style="color: #555; line-height: 1.7; margin-bottom: 20px;">
                {{ $product->description }}
            </p>

            @if ($product->stock === 0)
                <p style="color: #b91c1c; font-weight: 600; margin-bottom: 20px;">
                    Out of stock
                </p>
            @elseif ($product->stock <= 5)
                <p style="color: #b45309; font-weight: 600; margin-bottom: 20px;">
                    Only {{ $product->stock }} left in stock
                </p>
            @else
                <p style="color: #166534; font-weight: 600; margin-bottom: 20px;">
                    In stock — {{ $product->stock }} available
                </p>
            @endif

            @if ($product->stock > 0)
                <div style="margin-top: 20px;">
                    <label for="quantity" style="display: block; font-weight: 600; margin-bottom: 6px;">
                        Quantity
                    </label>

                    {{-- DESKTOP QUANTITY INPUT --}}
<input
    id="quantity"
    type="number"
    min="1"
    max="{{ $product->stock }}"
    wire:model="quantity"
    class="quantity-desktop-input"
    style="
        width: 70px;
        box-sizing: border-box;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
    "
>

{{-- MOBILE QUANTITY SELECTOR --}}
<select
    id="quantity-mobile"
    class="quantity-mobile-select"
    wire:model.number="quantity"
    aria-label="Select quantity"
>
    @for ($amount = 1; $amount <= $product->stock; $amount++)
        <option value="{{ $amount }}">
            {{ $amount }}
        </option>
    @endfor
</select>
                </div>

                <button
                    type="button"
                    wire:click="addToCart"
                    style="
                        margin-top: 18px;
                        width: 100%;
                        background: black;
                        color: white;
                        padding: 13px 18px;
                        border-radius: 6px;
                        font-weight: 600;
                    "
                >
                    Add to Cart
                </button>
            @endif

            @auth

                <button
                    type="button"
                    wire:click="toggleWishlist"
                    style="
                        margin-top: 12px;
                        width: 100%;
                        background: white;
                        color: #111;
                        padding: 13px 18px;
                        border: 1px solid #111;
                        border-radius: 6px;
                        font-weight: 600;
                        cursor: pointer;
                    "
                >
                    {{
                        $this->isWishlisted
                            ? '♥ In Wishlist'
                            : '♡ Add to Wishlist'
                    }}
                </button>

            @else

                <a
                    href="{{ route('login') }}"
                    style="
                        margin-top: 12px;
                        width: 100%;
                        display: block;
                        box-sizing: border-box;
                        text-align: center;
                        text-decoration: none;
                        background: white;
                        color: #111;
                        padding: 13px 18px;
                        border: 1px solid #111;
                        border-radius: 6px;
                        font-weight: 600;
                    "
                >
                    ♡ Log in to add to Wishlist
                </a>

            @endauth

            @error('stock')
                <p style="color: #b91c1c; margin-top: 12px;">
                    {{ $message }}
                </p>
            @enderror

            @if ($successMessage)
                <div
                    style="
                        margin-top: 16px;
                        padding: 12px 14px;
                        background: #f0fdf4;
                        border: 1px solid #bbf7d0;
                        border-radius: 6px;
                        color: #166534;
                        font-weight: 600;
                    "
                >
                    ✓ {{ $successMessage }}
                </div>
            @endif
        </div>
    </div>

    {{-- CUSTOMER REVIEWS --}}
<section
    class="product-reviews"
    style="
        margin-top: 64px;
        padding-top: 40px;
        border-top: 1px solid #e5e5e5;
    "
>
    <div
        style="
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        "
    >
        <div>
            <p
                style="
                    margin: 0 0 8px;
                    color: #777;
                    font-size: 12px;
                    font-weight: 700;
                    letter-spacing: .12em;
                    text-transform: uppercase;
                "
            >
                Customer Reviews
            </p>

            <h2
                style="
                    margin: 0;
                    font-size: 28px;
                    font-weight: 700;
                "
            >
                What customers think
            </h2>
        </div>

        <div style="text-align: right;">

            <div
                style="
                    font-size: 24px;
                    letter-spacing: 2px;
                    color: #111;
                "
            >
                @for ($star = 1; $star <= 5; $star++)
                    {{ $star <= round($this->averageRating) ? '★' : '☆' }}
                @endfor
            </div>

            <p
                style="
                    margin: 5px 0 0;
                    color: #777;
                    font-size: 13px;
                "
            >
                @if ($this->reviewCount > 0)

                    {{ number_format(
                        $this->averageRating,
                        1
                    ) }}

                    from {{ $this->reviewCount }}

                    {{
                        $this->reviewCount === 1
                            ? 'review'
                            : 'reviews'
                    }}

                @else

                    No reviews yet

                @endif
            </p>

        </div>
    </div>


    {{-- REVIEW FORM --}}
    @auth

        @if (
            $this->canReview
            && ! $this->userReview
        )

            <form
                wire:submit="submitReview"
                style="
                    max-width: 720px;
                    margin-bottom: 40px;
                    padding: 24px;
                    border: 1px solid #e5e5e5;
                    border-radius: 12px;
                    background: #fafafa;
                "
            >

                <h3
                    style="
                        margin: 0 0 20px;
                        font-size: 18px;
                    "
                >
                    Write a review
                </h3>


                {{-- RATING --}}
                <div style="margin-bottom: 20px;">

                    <label
                        for="review-rating"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                        "
                    >
                        Rating
                    </label>

                    <select
                        id="review-rating"
                        wire:model="rating"
                        style="
                            width: 100%;
                            padding: 11px 12px;
                            border: 1px solid #ccc;
                            border-radius: 6px;
                            background: white;
                        "
                    >
                        <option value="5">
                            ★★★★★ — Excellent
                        </option>

                        <option value="4">
                            ★★★★☆ — Very good
                        </option>

                        <option value="3">
                            ★★★☆☆ — Good
                        </option>

                        <option value="2">
                            ★★☆☆☆ — Fair
                        </option>

                        <option value="1">
                            ★☆☆☆☆ — Poor
                        </option>
                    </select>

                    @error('rating')
                        <p
                            style="
                                margin: 7px 0 0;
                                color: #b91c1c;
                                font-size: 13px;
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- COMMENT --}}
                <div style="margin-bottom: 20px;">

                    <label
                        for="review-comment"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                        "
                    >
                        Review
                    </label>

                    <textarea
                        id="review-comment"
                        wire:model="comment"
                        rows="5"
                        maxlength="1000"
                        placeholder="Tell other customers what you think about this product..."
                        style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #ccc;
                            border-radius: 6px;
                            resize: vertical;
                        "
                    ></textarea>

                    @error('comment')
                        <p
                            style="
                                margin: 7px 0 0;
                                color: #b91c1c;
                                font-size: 13px;
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                @error('review')
                    <p
                        style="
                            margin: 0 0 16px;
                            color: #b91c1c;
                            font-size: 13px;
                        "
                    >
                        {{ $message }}
                    </p>
                @enderror


                <button
                    type="submit"
                    style="
                        padding: 12px 20px;
                        border: 0;
                        border-radius: 6px;
                        background: #111;
                        color: white;
                        font-weight: 700;
                        cursor: pointer;
                    "
                >
                    Submit Review
                </button>

            </form>


        @elseif ($this->userReview)

    @if ($editingReview)

        <form
            wire:submit="updateReview"
            style="
                max-width: 720px;
                margin-bottom: 40px;
                padding: 24px;
                border: 1px solid #e5e5e5;
                border-radius: 12px;
                background: #fafafa;
            "
        >

            <h3
                style="
                    margin: 0 0 20px;
                    font-size: 18px;
                "
            >
                Edit your review
            </h3>


            <div style="margin-bottom: 20px;">
                <label
                    for="edit-review-rating"
                    style="
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 600;
                    "
                >
                    Rating
                </label>

                <select
                    id="edit-review-rating"
                    wire:model="rating"
                    style="
                        width: 100%;
                        padding: 11px 12px;
                        border: 1px solid #ccc;
                        border-radius: 6px;
                        background: white;
                    "
                >
                    <option value="5">
                        ★★★★★ — Excellent
                    </option>

                    <option value="4">
                        ★★★★☆ — Very good
                    </option>

                    <option value="3">
                        ★★★☆☆ — Good
                    </option>

                    <option value="2">
                        ★★☆☆☆ — Fair
                    </option>

                    <option value="1">
                        ★☆☆☆☆ — Poor
                    </option>
                </select>

                @error('rating')
                    <p
                        style="
                            margin: 7px 0 0;
                            color: #b91c1c;
                            font-size: 13px;
                        "
                    >
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <div style="margin-bottom: 20px;">

                <label
                    for="edit-review-comment"
                    style="
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 600;
                    "
                >
                    Review
                </label>

                <textarea
                    id="edit-review-comment"
                    wire:model="comment"
                    rows="5"
                    maxlength="1000"
                    style="
                        width: 100%;
                        padding: 12px;
                        border: 1px solid #ccc;
                        border-radius: 6px;
                        resize: vertical;
                    "
                ></textarea>

                @error('comment')
                    <p
                        style="
                            margin: 7px 0 0;
                            color: #b91c1c;
                            font-size: 13px;
                        "
                    >
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <div
                style="
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                "
            >

                <button
                    type="submit"
                    style="
                        padding: 12px 20px;
                        border: 0;
                        border-radius: 6px;
                        background: #111;
                        color: white;
                        font-weight: 700;
                        cursor: pointer;
                    "
                >
                    Save Changes
                </button>


                <button
                    type="button"
                    wire:click="cancelEditingReview"
                    style="
                        padding: 12px 20px;
                        border: 1px solid #ccc;
                        border-radius: 6px;
                        background: white;
                        color: #333;
                        font-weight: 600;
                        cursor: pointer;
                    "
                >
                    Cancel
                </button>

            </div>

        </form>

    @else

        <div
            style="
                max-width: 720px;
                margin-bottom: 32px;
                padding: 16px;
                border: 1px solid #dbe7dc;
                border-radius: 8px;
                background: #f3faf4;
            "
        >

            <p
                style="
                    margin: 0 0 14px;
                    color: #166534;
                    font-size: 14px;
                "
            >
                ✓ You have reviewed this product.
            </p>


            <div
                style="
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                "
            >

                <button
                    type="button"
                    wire:click="startEditingReview"
                    style="
                        padding: 9px 14px;
                        border: 1px solid #111;
                        border-radius: 6px;
                        background: white;
                        color: #111;
                        font-weight: 600;
                        cursor: pointer;
                    "
                >
                    Edit Review
                </button>


                <button
                    type="button"
                    wire:click="deleteReview"
                    wire:confirm="Are you sure you want to delete your review?"
                    style="
                        padding: 9px 14px;
                        border: 1px solid #fecaca;
                        border-radius: 6px;
                        background: white;
                        color: #b91c1c;
                        font-weight: 600;
                        cursor: pointer;
                    "
                >
                    Delete Review
                </button>

            </div>

        </div>

    @endif


        @else

            <p
                style="
                    margin: 0 0 32px;
                    color: #777;
                    font-size: 14px;
                "
            >
                Purchase this product to leave a verified review.
            </p>

        @endif


    @else

        <p
            style="
                margin: 0 0 32px;
                color: #777;
                font-size: 14px;
            "
        >
            <a
                href="{{ route('login') }}"
                style="
                    color: #111;
                    font-weight: 600;
                "
            >
                Log in
            </a>

            to leave a review after purchasing this product.
        </p>

    @endauth


    {{-- SUCCESS MESSAGE --}}
    @if ($reviewSuccessMessage)

        <div
            style="
                max-width: 720px;
                margin-bottom: 32px;
                padding: 14px 16px;
                border: 1px solid #bbf7d0;
                border-radius: 8px;
                background: #f0fdf4;
                color: #166534;
                font-weight: 600;
            "
        >
            ✓ {{ $reviewSuccessMessage }}
        </div>

    @endif


    {{-- EXISTING REVIEWS --}}
    <div>

        @forelse ($this->reviews as $review)

            <article
                style="
                    padding: 24px 0;
                    border-top: 1px solid #e5e5e5;
                "
            >

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        gap: 20px;
                        flex-wrap: wrap;
                    "
                >

                    <div>

                        <strong
                            style="
                                display: block;
                                margin-bottom: 5px;
                            "
                        >
                            {{ $review->user->name }}
                        </strong>

                        <span
                            style="
                                color: #166534;
                                font-size: 12px;
                                font-weight: 700;
                            "
                        >
                            ✓ Verified Purchase
                        </span>

                    </div>


                    <div style="text-align: right;">

                        <div
                            style="
                                font-size: 16px;
                                letter-spacing: 1px;
                            "
                        >
                            @for (
                                $star = 1;
                                $star <= 5;
                                $star++
                            )

                                {{
                                    $star <= $review->rating
                                        ? '★'
                                        : '☆'
                                }}

                            @endfor
                        </div>

                        <span
                            style="
                                color: #888;
                                font-size: 12px;
                            "
                        >
                            {{
                                $review
                                    ->created_at
                                    ->format('d M Y')
                            }}
                        </span>
                    </div>
                </div>


                @if ($review->comment)
                    <p
                        style="
                            max-width: 800px;
                            margin: 16px 0 0;
                            color: #444;
                            line-height: 1.7;
                        "
                    >
                        {{ $review->comment }}
                    </p>

                @endif
            </article>
        @empty

            <div
                style="
                    padding: 32px 0;
                    border-top: 1px solid #e5e5e5;
                    color: #777;
                "
            >
                No customer reviews yet.
                Be the first verified buyer to leave one.
            </div>

        @endforelse

    </div>
</section>

</div>


<style>

    /* HIDE ALPINE ELEMENTS UNTIL READY */
[x-cloak] {
    display: none !important;
}

/* IMAGE ZOOM */
.product-gallery-main {
    position: relative;
    border: 0;
    cursor: zoom-in;
    text-align: center;
}

.product-gallery-main-image {
    transition: transform 180ms ease;
}

@media (hover: hover) and (pointer: fine) {
    .product-gallery-main-image.is-zoomed {
        transform: scale(1.8);
    }
}

.product-gallery-zoom-hint {
    position: absolute;
    right: 12px;
    bottom: 12px;
    padding: 6px 10px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.9);
    color: #222;
    font-size: 12px;
    pointer-events: none;
}

/* FULL-SCREEN VIEWER */
.product-gallery-lightbox {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.94);
    padding: 60px 64px;
    box-sizing: border-box;
}

.product-gallery-viewer-host {

    position: static;

}

.product-gallery-lightbox-image {
    display: block;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.product-gallery-lightbox-close,
.product-gallery-lightbox-arrow {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    color: white;
    font-size: 30px;
    cursor: pointer;
}

.product-gallery-lightbox-close {
    top: 16px;
    right: 16px;
}

.product-gallery-lightbox-arrow.previous {
    left: 12px;
}

.product-gallery-lightbox-arrow.next {
    right: 12px;
}

.product-gallery-lightbox-counter {
    position: absolute;
    bottom: 20px;
    color: white;
    font-size: 14px;
}

@media (max-width: 600px) {
    .product-gallery-lightbox {
        padding: 64px 48px;
    }

    .product-gallery-lightbox-arrow.previous {
        left: 2px;
    }

    .product-gallery-lightbox-arrow.next {
        right: 2px;
    }
}

    .product-gallery-main {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        aspect-ratio: 1 / 1;
        max-height: 600px;
        padding: 24px;
        box-sizing: border-box;
        background: #f8f8f8;
        border-radius: 12px;
        overflow: hidden;
    }

    .product-gallery-main-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .product-gallery-thumbnails {
        display: flex;
        gap: 10px;
        margin-top: 14px;
        overflow-x: auto;
        padding: 2px 2px 8px;
    }

    .product-gallery-thumbnail {
        flex: 0 0 76px;
        width: 76px;
        height: 76px;
        border: 2px solid #dedede;
        border-radius: 9px;
        padding: 6px;
        background: #fff;
        cursor: pointer;
    }

    .product-gallery-thumbnail.is-active,
    .product-gallery-thumbnail:hover {
        border-color: #111;
    }

    .product-gallery-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    @media (max-width: 600px) {
        .product-gallery-main {
            padding: 16px;
        }

        .product-gallery-thumbnail {
            flex-basis: 64px;
            width: 64px;
            height: 64px;
        }
    }

    /* PRODUCT DETAILS RESPONSIVE LAYOUT */

.product-details-grid > * {
    min-width: 0;
}

.product-details-info {
    min-width: 0;
}

.product-details-info p {
    overflow-wrap: anywhere;
}

@media (max-width: 700px) {

    .product-page {
        padding: 16px 14px 40px !important;
    }

    .product-details-grid {
        grid-template-columns: minmax(0, 1fr) !important;
        gap: 24px !important;
    }

    .product-details-title {
        font-size: 28px !important;
        line-height: 1.2 !important;
        overflow-wrap: anywhere;
    }

    .product-gallery-main {
        max-height: none;
    }

    .product-gallery-zoom-hint {
        font-size: 11px;
    }

    .product-reviews {
        margin-top: 40px !important;
        padding-top: 28px !important;
    }

    .product-reviews-title {
        font-size: 24px !important;
    }

    .product-reviews form {
        max-width: 100%;
        box-sizing: border-box;
        padding: 16px !important;
    }

    .product-reviews textarea,
    .product-reviews select {
        max-width: 100%;
        box-sizing: border-box;
    }

    /* MOBILE QUANTITY PICKER */

.quantity-mobile-select {

    display: none;

}

@media (max-width: 700px) {

    .quantity-desktop-input {

        display: none !important;

    }

    .quantity-mobile-select {

        display: block;

        width: 110px;

        max-width: 100%;

        box-sizing: border-box;

        padding: 11px 12px;

        border: 1px solid #ccc;

        border-radius: 8px;

        background: #fff;

        color: #111;

        font-size: 16px;

        cursor: pointer;

    }

}


</style>

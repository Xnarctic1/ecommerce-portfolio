<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Format;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin')] #[Title('Edit Product')] class extends Component
{
    public Product $product;
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public int $stock = 0;
    public ?int $category_id = null;
    public bool $is_active = true;
    public $image = null;
    public array $galleryImages = [];

    use WithFileUploads;

    public function mount(Product $product): void
    {
        $this->product = $product->load('images');
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->price = (string) $product->price;
        $this->stock = $product->stock;
        $this->category_id = $product->category_id;
        $this->is_active = (bool) $product->is_active;
    }

    public function categories()
    {
        return Category::all();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'galleryImages' => ['array'],
            'galleryImages.*' => ['image', 'max:2048'],
        ]);

        $existingCount = $this->product->images()->count();
        if ($existingCount + count($this->galleryImages) > 8) {
            $this->addError(
                'galleryImages',
                'A product can have at most 8 additional gallery images.'
            );
            return;
        }

    $imagePath = $this->product->image;
    if ($this->image) {
        $imagePath = $this->image->store('products', 'public');
    }

    $this->product->update([
        'name' => $validated['name'],
        'description' => $validated['description'],
        'price' => $validated['price'],
        'stock' => $validated['stock'],
        'category_id' => $validated['category_id'],
        'is_active' => $validated['is_active'],
        'image' => $imagePath,
    ]);

    if ($this->image && $this->product->wasChanged('image')) {
        // Delete the old main image only after saving the replacement.
        // Gallery images are stored separately.
    }

    $nextSortOrder = (int) (
        $this->product->images()->max('sort_order') ?? -1
    ) + 1;

    foreach ($this->galleryImages as $upload) {
        $galleryImage = Image::decodePath($upload->getRealPath());
        $galleryImage->scaleDown(
            width: 900,
            height: 900
        );

        $galleryPath = 'products/' . Str::uuid() . '.webp';
        Storage::disk('public')->put(
            $galleryPath,
            $galleryImage
                ->encodeUsingFormat(Format::WEBP, quality: 85)
                ->toString()
        );

        $this->product->images()->create([
            'path' => $galleryPath,
            'sort_order' => $nextSortOrder++,
        ]);
    }

    $this->reset('image', 'galleryImages');
    $this->product->refresh()->load('images');
    session()->flash('success', 'Product updated successfully.');
    }

    public function setMainImage(int $imageId): void
    {
        // Find the selected gallery image.
        $galleryImage = $this->product->images()->findOrFail($imageId);
        
        // Remember both image paths.
        $oldMainPath = $this->product->image;
        $newMainPath = $galleryImage->path;
        
        // Make the selected image the main image.
        $this->product->update(['image' => $newMainPath,]);

        // Move the previous main image into the gallery.
        if ($oldMainPath) {
            $galleryImage->update([
                'path' => $oldMainPath,
                ]);
                
                } else {
                    $galleryImage->delete();
                    }

        // Refresh the images displayed on the page.
        $this->product->refresh()->load('images');
        session()->flash(
            'success',
            'Main product image updated successfully.'
            );
    }

    public function deleteGalleryImage(int $imageId): void
    {
    $image = $this->product->images()->findOrFail($imageId);
    Storage::disk('public')->delete($image->path);
    $image->delete();
    $this->product->load('images');
    }

    public function moveGalleryImage(int $imageId, string $direction): void

    {
        if (!in_array($direction, ['up', 'down'], true)) {
           return;
        }

    $images = $this->product->images()->get()->values();
    $index = $images->search(
        fn ($image) => $image->id === $imageId
    );

    if ($index === false) {
        return;
    }

    $swapIndex = $direction === 'up'
        ? $index - 1
        : $index + 1;

    if (!isset($images[$swapIndex])) {
        return;
    }

    $current = $images[$index];
    $other = $images[$swapIndex];
    $currentOrder = $current->sort_order;
    $otherOrder = $other->sort_order;
    $current->update([
        'sort_order' => $otherOrder,
    ]);

    $other->update([
        'sort_order' => $currentOrder,
    ]);
    $this->product->load('images');
}


};

?>

<div class="admin-edit-page">
    <div class="admin-edit-shell">

        {{-- HEADER --}}
        <div class="admin-edit-header">
            <div>
                <p class="admin-edit-kicker">ADMIN</p>

                <h1>Edit Product</h1>

                <p class="admin-edit-subtitle">
                    Update product details, stock, category and visibility.
                </p>
            </div>

            <a
                href="{{ route('admin.products') }}"
                class="admin-back-button"
            >
                ← Back to Products
            </a>
        </div>


        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="admin-success-message">
                {{ session('success') }}
            </div>
        @endif


        <form wire:submit="save" class="admin-edit-grid">

            {{-- LEFT SIDE --}}
            <section class="admin-edit-card admin-edit-main-card">

                <div class="admin-section-header">
                    <div>
                        <h2>Product details</h2>

                        <p>
                            Update the main information shown to customers.
                        </p>
                    </div>
                </div>


                <div class="admin-form-grid">

                    {{-- PRODUCT NAME --}}
                    <div class="admin-form-field admin-form-field-full">

                        <label for="name">
                            Product name
                        </label>

                        <input
                            id="name"
                            type="text"
                            wire:model="name"
                            placeholder="Enter product name"
                        >

                        @error('name')
                            <p class="admin-field-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- DESCRIPTION --}}
                    <div class="admin-form-field admin-form-field-full">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            wire:model="description"
                            rows="6"
                            placeholder="Describe the product..."
                        ></textarea>

                        @error('description')
                            <p class="admin-field-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- PRICE --}}
                    <div class="admin-form-field">

                        <label for="price">
                            Price
                        </label>

                        <div class="admin-input-prefix-wrap">

                            <span>€</span>

                            <input
                                id="price"
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model="price"
                            >

                        </div>

                        @error('price')
                            <p class="admin-field-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- STOCK --}}
                    <div class="admin-form-field">

                        <label for="stock">
                            Stock
                        </label>

                        <input
                            id="stock"
                            type="number"
                            min="0"
                            wire:model="stock"
                        >

                        @error('stock')
                            <p class="admin-field-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- CATEGORY --}}
                    <div class="admin-form-field">

                        <label for="category_id">
                            Category
                        </label>

                        <select
                            id="category_id"
                            wire:model="category_id"
                        >

                            <option value="">
                                Uncategorized
                            </option>

                            @foreach ($this->categories() as $category)

                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>

                            @endforeach

                        </select>

                        @error('category_id')
                            <p class="admin-field-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- ACTIVE --}}
                    <div class="admin-form-field admin-active-field">

                        <label class="admin-toggle-row">

                            <input
                                type="checkbox"
                                wire:model="is_active"
                            >

                            <span class="admin-toggle-copy">

                                <strong>
                                    Active product
                                </strong>

                                <small>
                                    Visible to customers in the storefront
                                </small>

                            </span>

                        </label>

                    </div>

                </div>
            </section>


            {{-- RIGHT SIDEBAR --}}
            <aside class="admin-edit-sidebar">


                {{-- IMAGE CARD --}}
                <section class="admin-edit-card">

                    <div class="admin-section-header">
                        <div>
                            <h2>Product image</h2>

                            <p>
                                Current image and replacement upload.
                            </p>
                        </div>
                    </div>


                    <div class="admin-image-panel">

                        {{-- NEW IMAGE PREVIEW --}}
                        @if ($image)

                            <img
                                src="{{ $image->temporaryUrl() }}"
                                alt="New product image preview"
                                class="admin-product-preview"
                            >

                            <span class="admin-image-status">
                                New image preview
                            </span>


                        {{-- CURRENT IMAGE --}}
                        @elseif ($product->image)

                            <img
                                src="{{ asset('storage/' . $product->image) }}"
                                alt="{{ $product->name }}"
                                class="admin-product-preview"
                            >

                            <span class="admin-image-status">
                                Current image
                            </span>


                        {{-- NO IMAGE --}}
                        @else

                            <div class="admin-image-placeholder">
                                No product image
                            </div>

                        @endif

                    </div>


                    <label
                        for="image"
                        class="admin-upload-button"
                    >
                        Choose new image
                    </label>


                    <input
                        id="image"
                        type="file"
                        wire:model="image"
                        accept="image/*"
                        class="admin-file-input"
                    >


                    <p class="admin-upload-help">
                        JPG, PNG or WebP. Maximum 2 MB.
                    </p>


                    @error('image')
                        <p class="admin-field-error">
                            {{ $message }}
                        </p>
                    @enderror

                    <div class="admin-gallery-section">

    <div class="admin-section-header">

        <div>

            <h2>Gallery images</h2>

            <p>Manage up to 8 additional images.</p>

        </div>

    </div>

    {{-- EXISTING GALLERY IMAGES --}}

    @if ($product->images->isNotEmpty())

        <div class="admin-gallery-grid">

            @foreach ($product->images as $galleryImage)

                <div

                    class="admin-gallery-item"

                    wire:key="gallery-image-{{ $galleryImage->id }}"

                >

                    <img

                        src="{{ asset('storage/' . $galleryImage->path) }}"

                        alt="Gallery image"

                    >

                    <div class="admin-gallery-controls">
                        <button
                        type="button"
                        wire:click="setMainImage({{ $galleryImage->id }})"
                        wire:confirm="Make this the main product image?"
                        aria-label="Set as main image"
                        title="Set as main image"
                        >
                        ★
                        </button>

                        <button
                            type="button"
                            wire:click="moveGalleryImage({{ $galleryImage->id }}, 'up')"
                            aria-label="Move image left"
                        >
                            ←
                        </button>

                        <button
                            type="button"
                            wire:click="moveGalleryImage({{ $galleryImage->id }}, 'down')"
                            aria-label="Move image right"
                        >
                            →
                        </button>

                        <button
                            type="button"
                            wire:click="deleteGalleryImage({{ $galleryImage->id }})"
                            wire:confirm="Delete this gallery image?"
                            aria-label="Delete image"
                        >
                            ×
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <p class="admin-upload-help">
            No additional gallery images yet.
        </p>

    @endif
    {{-- UPLOAD NEW GALLERY IMAGES --}}
    <label
        for="gallery-images"
        class="admin-upload-button"
    >
        Add gallery images
    </label>

    <input
        id="gallery-images"
        type="file"
        wire:model="galleryImages"
        accept="image/*"
        multiple
        class="admin-file-input"
    >

    <div
        wire:loading
        wire:target="galleryImages"
        class="admin-upload-help"
    >
        Uploading images...
    </div>

    {{-- NEW IMAGE PREVIEWS --}}

    @if (count($galleryImages))
        <div class="admin-gallery-grid">

            @foreach ($galleryImages as $index => $upload)
                <div
                    class="admin-gallery-item"
                    wire:key="gallery-upload-{{ $index }}"
                >

                    <img
                        src="{{ $upload->temporaryUrl() }}"
                        alt="New gallery image preview"
                    >
                </div>

            @endforeach
        </div>

    @endif

    @error('galleryImages')
        <p class="admin-field-error">
            {{ $message }}
        </p>

    @enderror

    @error('galleryImages.*')
        <p class="admin-field-error">
            {{ $message }}
        </p>

    @enderror

</div>
                </section>

                {{-- SUMMARY CARD --}}
                <section class="admin-edit-card admin-summary-card">

                    <div class="admin-section-header">
                        <div>
                            <h2>Summary</h2>

                            <p>
                                Quick overview before saving.
                            </p>
                        </div>
                    </div>


                    <div class="admin-summary-list">

                        <div>
                            <span>Product</span>

                            <strong>
                                {{ $name ?: 'Untitled product' }}
                            </strong>
                        </div>


                        <div>
                            <span>Price</span>

                            <strong>
                                €{{ number_format((float) ($price ?: 0), 2) }}
                            </strong>
                        </div>


                        <div>
                            <span>Stock</span>

                            <strong>
                                {{ $stock }}
                            </strong>
                        </div>


                        <div>
                            <span>Status</span>

                            <strong>
                                {{ $is_active ? 'Active' : 'Inactive' }}
                            </strong>
                        </div>

                    </div>

                </section>

            </aside>


            {{-- BOTTOM ACTIONS --}}
            <div class="admin-form-actions">

                <a
                    href="{{ route('admin.products') }}"
                    class="admin-cancel-button"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="admin-save-button"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>
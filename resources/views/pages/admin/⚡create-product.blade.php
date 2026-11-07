<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Format;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $description = '';
    public string $price = '';
    public int $stock = 0;
    public ?int $category_id = null;
    public $image = null;
    public array $galleryImages = [];

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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
            'galleryImages' => ['array', 'max:8'],
            'galleryImages.*' => ['image', 'max:2048'],
        ]);

        $imagePath = null;

    if ($this->image) {
        $image = Image::decodePath(
            $this->image->getRealPath()
        );

    $image->scaleDown(
        width: 900,
        height: 900
    );


    $filename =

        Str::uuid() . '.webp';

    $imagePath =

        'products/' . $filename;

    Storage::disk('public')->put(

        $imagePath,

        $image
            ->encodeUsingFormat(
                Format::WEBP,
                quality: 85
            )
        ->toString()
    );

}

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
            'image' => $imagePath,
            ]);

            foreach ($this->galleryImages as $index => $upload) {
                $galleryImage = Image::decodePath(
                    $upload->getRealPath()
                );

                $galleryImage->scaleDown(
                    width: 900,
                    height: 900
                );

                $galleryPath = 'products/' . Str::uuid() . '.webp';
                Storage::disk('public')->put(
                    $galleryPath,
                    $galleryImage
                        ->encodeUsingFormat(
                            Format::WEBP,
                            quality: 85
                        )
                        ->toString()
                    );

                $product->images()->create([
                    'path' => $galleryPath,
                    'sort_order' => $index,
                ]);
}

        $this->reset(
            'name',
            'description',
            'price',
            'stock',
            'category_id',
            'image',
            'galleryImages'
        );
        

        session()->flash(
            'success',
            'Product created successfully.'
        );
    }
};

?>

<div class="admin-create-page">
    <div class="admin-create-shell">

        <div class="admin-create-header">
            <div>
                <p class="admin-create-kicker">ADMIN</p>

                <h1>Create Product</h1>

                <p class="admin-create-subtitle">
                    Add a new product to your store and configure its inventory.
                </p>
            </div>

            <a
                href="{{ route('admin.products') }}"
                class="admin-back-button"
            >
                ← Back to Products
            </a>
        </div>


        @if (session('success'))
            <div class="admin-success-message">
                {{ session('success') }}
            </div>
        @endif


        <form
            wire:submit="save"
            class="admin-create-grid"
        >

            {{-- LEFT SIDE --}}
            <section class="admin-create-card admin-create-main-card">

                <div class="admin-section-header">
                    <div>
                        <h2>Product details</h2>

                        <p>
                            Enter the information customers will see in the storefront.
                        </p>
                    </div>
                </div>


                <div class="admin-form-grid">

                    {{-- NAME --}}
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
                                placeholder="0.00"
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
                    <div class="admin-form-field admin-form-field-full">

                        <label for="category_id">
                            Category
                        </label>

                        <select
                            id="category_id"
                            wire:model="category_id"
                        >

                            <option value="">
                                Select a category
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

                </div>

            </section>


            {{-- RIGHT SIDE --}}
            <aside class="admin-create-sidebar">

                {{-- IMAGE --}}
                <section class="admin-create-card">

                    <div class="admin-section-header">
                        <div>
                            <h2>Product image</h2>

                            <p>
                                Upload the main image shown for this product.
                            </p>
                        </div>
                    </div>


                    <div class="admin-image-panel">

                        @if ($image)

                            <img
                                src="{{ $image->temporaryUrl() }}"
                                alt="Product image preview"
                                class="admin-product-preview"
                            >

                            <span class="admin-image-status">
                                Image preview
                            </span>

                        @else

                            <div class="admin-image-placeholder">

                                <span class="admin-image-placeholder-icon">
                                    +
                                </span>

                                <strong>
                                    No image selected
                                </strong>

                                <small>
                                    Choose an image below
                                </small>

                            </div>

                        @endif

                    </div>


                    <label
                        for="image"
                        class="admin-upload-button"
                    >
                        Choose image
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
                        Images are automatically resized to a consistent square format.
                    </p>


                    @error('image')
                        <p class="admin-field-error">
                            {{ $message }}
                        </p>
                    @enderror

    <div class="admin-product-gallery-section">
    <div class="admin-section-header">
        <div>
            <h2>Gallery images</h2>
            <p>Select up to 8 additional images (2 MB each).</p>
        </div>
    </div>

    <label
        for="gallery-images"
        class="admin-upload-button"
    >
        Choose gallery images
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

    @if (count($galleryImages))

        <div class="admin-gallery-preview-grid">

            @foreach ($galleryImages as $index => $galleryImage)

                <img
                    wire:key="gallery-preview-{{ $index }}"
                    src="{{ $galleryImage->temporaryUrl() }}"
                    alt="Gallery preview {{ $index + 1 }}"
                >

            @endforeach

        </div>

    @endif

    @error('galleryImages')
        <p class="admin-field-error">{{ $message }}</p>
    @enderror

    @error('galleryImages.*')
        <p class="admin-field-error">{{ $message }}</p>
    @enderror

</div>

                </section>


                {{-- SUMMARY --}}
                <section class="admin-create-card admin-summary-card">

                    <div class="admin-section-header">
                        <div>
                            <h2>Summary</h2>

                            <p>
                                Quick overview of the new product.
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

                    </div>

                </section>

            </aside>


            {{-- BUTTONS --}}
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
                    Create Product
                </button>

            </div>

        </form>

    </div>
</div>
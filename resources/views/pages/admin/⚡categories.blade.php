<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin')] #[Title('Admin Categories')] class extends Component
{
    #[Computed]
    public function categories()
    {
        return Category::withCount('products')
            ->orderBy('name')
            ->get();
    }

    public function deleteCategory(int $categoryId): void
{
    $category = Category::withCount('products')
        ->findOrFail($categoryId);

    if ($category->products_count > 0) {
        $this->addError(
            'category',
            'You cannot delete a category that contains products.'
        );

        return;
    }

    $category->delete();
}



};
?>

<div class="admin-categories-page">

    <div class="admin-categories-shell">

        {{-- HEADER --}}
        <div class="admin-categories-header">

            <div>
                <p class="admin-categories-kicker">
                    ADMIN
                </p>

                <h1>
                    Categories
                </h1>

                <p class="admin-categories-subtitle">
                    Organise and manage your product catalogue.
                </p>
            </div>


            <a
                href="{{ route('admin.categories.create') }}"
                class="admin-create-category"
            >
                + Create Category
            </a>

        </div>


        {{-- DELETE ERROR --}}
        @error('category')

            <div class="admin-category-error">

                <span>!</span>

                <div>
                    <strong>Category cannot be deleted</strong>

                    <p>
                        {{ $message }}
                    </p>
                </div>

            </div>

        @enderror


        {{-- COLUMN HEADINGS --}}
        @if ($this->categories->isNotEmpty())

            <div class="admin-category-columns">

                <span>Category</span>

                <span>Products</span>

                <span>Actions</span>

            </div>

        @endif


        {{-- CATEGORIES --}}
        <div class="admin-category-list">

            @forelse ($this->categories as $category)

                <article
                    class="admin-category-row"
                    wire:key="category-{{ $category->id }}"
                >

                    {{-- NAME / SLUG --}}
                    <div class="admin-category-info">

                        <div class="admin-category-icon">
                            {{ strtoupper(substr($category->name, 0, 1)) }}
                        </div>


                        <div>

                            <h2>
                                {{ $category->name }}
                            </h2>

                            <span>
                                /{{ $category->slug }}
                            </span>

                        </div>

                    </div>


                    {{-- PRODUCTS --}}
                    <div class="admin-category-products">

                        <strong>
                            {{ $category->products_count }}
                        </strong>

                        <span>
                            {{ $category->products_count === 1 ? 'product' : 'products' }}
                        </span>

                    </div>


                    {{-- ACTIONS --}}
                    <div class="admin-category-actions">

                        <a
                            href="{{ route('admin.categories.edit', $category) }}"
                            class="admin-category-edit"
                        >
                            Edit
                        </a>


                        <button
                            type="button"
                            wire:click="deleteCategory({{ $category->id }})"
                            wire:confirm="Are you sure you want to delete {{ $category->name }}?"
                            class="admin-category-delete"
                        >
                            Delete
                        </button>

                    </div>

                </article>


            @empty

                <div class="admin-categories-empty">

                    <div class="admin-empty-icon">
                        +
                    </div>

                    <h2>
                        No categories yet
                    </h2>

                    <p>
                        Create your first category to organise your products.
                    </p>

                    <a
                        href="{{ route('admin.categories.create') }}"
                    >
                        Create Category
                    </a>

                </div>

            @endforelse

        </div>

    </div>

</div>
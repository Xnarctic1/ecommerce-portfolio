<?php

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin')] #[Title('Create Category')] class extends Component
{
    public string $name = '';

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
            ],
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        $this->redirect(
            route('admin.categories'),
            navigate: true
        );
    }
};
?>

<div class="admin-category-form-page">

    <div class="admin-category-form-shell">

        <div class="admin-category-form-header">
            <div>
                <p class="admin-category-kicker">
                    ADMIN / CATEGORIES
                </p>

                <h1>Create Category</h1>

                <p>
                    Add a new category to organise your product catalogue.
                </p>
            </div>
        </div>


        <form wire:submit="save">

            <div class="admin-category-form-grid">

                {{-- CATEGORY DETAILS --}}
                <section class="admin-category-card">

                    <span class="admin-section-label">
                        CATEGORY DETAILS
                    </span>

                    <h2>Information</h2>


                    <div class="admin-field">

                        <label for="name">
                            Category name
                        </label>

                        <input
                            id="name"
                            type="text"
                            wire:model.live="name"
                            placeholder="e.g. Electronics"
                        >

                        @error('name')
                            <p class="admin-field-error">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </section>


                {{-- RIGHT SIDE --}}
                <aside class="admin-category-sidebar">

                    {{-- PREVIEW --}}
                    <section class="admin-category-card">

                        <span class="admin-section-label">
                            PREVIEW
                        </span>


                        <div class="admin-category-preview">

                            <div class="admin-category-preview-icon">
                                {{ $name !== '' ? strtoupper(substr($name, 0, 1)) : 'C' }}
                            </div>


                            <div>

                                <strong>
                                    {{ $name !== '' ? $name : 'Category name' }}
                                </strong>

                                <span>
                                    {{ $name !== '' ? '/' . \Illuminate\Support\Str::slug($name) : '/category-slug' }}
                                </span>

                            </div>

                        </div>


                        <div class="admin-category-note">

                            <span>SLUG</span>

                            <code>
                                {{ $name !== '' ? \Illuminate\Support\Str::slug($name) : 'category-slug' }}
                            </code>

                            <p>
                                The slug will be generated automatically
                                from the category name when you create it.
                            </p>

                        </div>

                    </section>


                    {{-- BUTTONS --}}
                    <div class="admin-category-actions">

                        <a
                            href="{{ route('admin.categories') }}"
                            class="admin-category-cancel"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="admin-category-save"
                        >
                            Create Category
                        </button>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</div>
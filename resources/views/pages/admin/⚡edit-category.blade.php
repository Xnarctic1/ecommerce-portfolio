<?php

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin')] #[Title('Edit Category')] class extends Component
{
    public Category $category;

    public string $name = '';

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->name = $category->name;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name,' . $this->category->id,
            ],
        ]);

        $this->category->update([
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

        {{-- HEADER --}}
        <div class="admin-category-form-header">

            <div>
                <p class="admin-category-kicker">
                    ADMIN / CATEGORIES
                </p>

                <h1>
                    Edit Category
                </h1>

                <p>
                    Update the category name and catalogue organisation.
                </p>
            </div>

        </div>


        <form wire:submit="save">

            <div class="admin-category-form-grid">


                {{-- FORM --}}
                <section class="admin-category-card">

                    <span class="admin-section-label">
                        CATEGORY DETAILS
                    </span>

                    <h2>
                        Information
                    </h2>


                    <div class="admin-field">

                        <label for="name">
                            Category name
                        </label>

                        <input
                            id="name"
                            type="text"
                            wire:model="name"
                            placeholder="e.g. Electronics"
                        >

                        @error('name')

                            <p class="admin-field-error">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </section>


                {{-- SUMMARY --}}
                <aside class="admin-category-sidebar">

                    <section class="admin-category-card">

                        <span class="admin-section-label">
                            CURRENT CATEGORY
                        </span>

                        <div class="admin-category-preview">

                            <div class="admin-category-preview-icon">
                                {{ strtoupper(substr($category->name, 0, 1)) }}
                            </div>


                            <div>

                                <strong>
                                    {{ $category->name }}
                                </strong>

                                <span>
                                    /{{ $category->slug }}
                                </span>

                            </div>

                        </div>


                        <div class="admin-category-note">

                            <span>
                                SLUG
                            </span>

                            <code>
                                {{ $category->slug }}
                            </code>

                            <p>
                                The slug is automatically updated when you save the category name.
                            </p>

                        </div>

                    </section>


                    {{-- ACTIONS --}}
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
                            Save Changes
                        </button>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</div>
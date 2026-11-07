<nav class="admin-navigation">

    {{-- ADMIN BRAND --}}
    <div class="admin-navigation__brand-row">

        <a
            href="{{ route('admin.dashboard') }}"
            class="admin-navigation__brand"
        >
            STORE
        </a>

        <a
            href="{{ route('products') }}"
            class="admin-navigation__store-link"
        >
            View Store
        </a>

    </div>

    {{-- ADMIN LINKS --}}
    <div class="admin-navigation__inner">

        <div class="admin-navigation__label">
            Admin
        </div>

        <div class="admin-navigation__links">

            {{-- DASHBOARD --}}
            <a
                href="{{ route('admin.dashboard') }}"
                class="admin-navigation__link {{ request()->routeIs('admin.dashboard') ? 'admin-navigation__link--active' : '' }}"
            >
                Dashboard
            </a>

            {{-- PRODUCTS --}}
            <a
                href="{{ route('admin.products') }}"
                class="admin-navigation__link {{ request()->routeIs('admin.products*') ? 'admin-navigation__link--active' : '' }}"
            >
                Products
            </a>

            {{-- CATEGORIES --}}
            <a
                href="{{ route('admin.categories') }}"
                class="admin-navigation__link {{ request()->routeIs('admin.categories*') ? 'admin-navigation__link--active' : '' }}"
            >
                Categories
            </a>

            {{-- ORDERS --}}
            <a
                href="{{ route('admin.orders') }}"
                class="admin-navigation__link {{ request()->routeIs('admin.orders*') ? 'admin-navigation__link--active' : '' }}"
            >
                Orders
            </a>

        </div>

    </div>

</nav>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'E-commerce' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @fluxAppearance
</head>

<body class="store-layout">
    @php
        $cart = session()->get('cart', []);
        $cartCount = collect($cart)->sum('quantity');
    @endphp

    <header
        style="
            background: #fff;
            border-bottom: 1px solid #e8e8e8;
            position: sticky;
            top: 0;
            z-index: 50;
        "
    >
        <nav
            class="store-nav"
            style="
                width: calc(100% - 48px);
                max-width: 1240px;
                margin: 0 auto;
                padding: 15px 0;               
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 28px;
            "
        >
            {{-- LOGO --}}
            <a
                href="{{ route('products') }}"
                style="
                    color: #111;
                    text-decoration: none;
                    font-size: 21px;
                    font-weight: 800;
                    letter-spacing: -0.5px;
                    white-space: nowrap;
                "
            >
                STORE
            </a>

            {{-- MOBILE CART SHORTCUT --}}
            <a
                href="{{ route('cart') }}"
                class="store-mobile-cart"
                aria-label="Shopping cart"
            >
            Cart
            <span id="mobile-cart-count">
                {{ $cartCount }}
            </span>
        </a>

            {{-- NAVIGATION --}}
            <div
                class="store-nav-links store-desktop-nav"
                style="
                    display: flex;
                    align-items: center;
                    gap: 24px;
                    margin-left: auto;
                "
            >
                @auth
                    @if (auth()->user()?->is_admin)
                        <a
                            href="{{ route('admin.dashboard') }}"
                            style="
                                display: inline-flex;
                                align-items: center;
                                background: #111;
                                color: #fff;
                                text-decoration: none;
                                padding: 9px 13px;
                                border-radius: 8px;
                                font-size: 14px;
                                font-weight: 700;
                            "
                        >
                            Admin Dashboard
                        </a>
                    @endif
                @endauth

                <a
                    href="{{ route('products') }}"
                    style="
                        color: {{ request()->routeIs('products') || request()->routeIs('products.show') ? '#111' : '#666' }};
                        text-decoration: none;
                        font-size: 14px;
                        font-weight: {{ request()->routeIs('products') || request()->routeIs('products.show') ? '800' : '600' }};
                        padding-bottom: 5px;
                        border-bottom: {{ request()->routeIs('products') || request()->routeIs('products.show') ? '2px solid #111' : '2px solid transparent' }};
                    "
                >
                    Products
                </a>

                @guest
                    <a
                        href="{{ route('login') }}"
                        style="
                            color: #333;
                            text-decoration: none;
                            font-size: 14px;
                            font-weight: 600;
                        "
                    >
                        Log in
                    </a>

                    <a
                        href="{{ route('register') }}"
                        style="
                            color: #333;
                            text-decoration: none;
                            font-size: 14px;
                            font-weight: 600;
                        "
                    >
                        Register
                    </a>
                @endguest

                @auth

                    <a
                        href="{{ route('wishlist') }}"
                        style="
                            color: {{ request()->routeIs('wishlist') ? '#111' : '#666' }};
                            text-decoration: none;
                            font-size: 14px;
                            font-weight: {{ request()->routeIs('wishlist') ? '800' : '600' }};
                            padding-bottom: 5px;
                            border-bottom: {{ request()->routeIs('wishlist') ? '2px solid #111' : '2px solid transparent' }};
                        "
                    >
                        Wishlist
                    </a>    

                    <a
                        href="{{ route('orders') }}"
                        style="
                            color: {{ request()->routeIs('orders') ? '#111' : '#666' }};
                            text-decoration: none;
                            font-size: 14px;
                            font-weight: {{ request()->routeIs('orders') ? '800' : '600' }};
                            padding-bottom: 5px;
                            border-bottom: {{ request()->routeIs('orders') ? '2px solid #111' : '2px solid transparent' }};
                        "
                    >
                        My Orders
                    </a>

                    <a
                    href="{{ route('account') }}"
                    style="
                            color: {{ request()->routeIs('account') ? '#111' : '#666' }};
                            text-decoration: none;
                            font-size: 14px;
                            font-weight: {{ request()->routeIs('account') ? '800' : '600' }};
                            padding-bottom: 5px;
                            border-bottom: {{ request()->routeIs('account') ? '2px solid #111' : '2px solid transparent' }};
                            "
                            >
                            My Account
                        </a>


                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        style="margin: 0;"
                    >
                        @csrf

                        <button
                            type="submit"
                            style="
                                color: {{ request()->routeIs('logout') ? '#111' : '#666' }};
                                text-decoration: none;
                                font-size: 14px;
                                font-weight: {{ request()->routeIs('logout') ? '800' : '600' }};
                                padding-bottom: 5px;
                                border-bottom: {{ request()->routeIs('logout') ? '2px solid #111' : '2px solid transparent' }};
                            "
                        >
                            Logout
                        </button>
                    </form>
                @endauth

                {{-- CART --}}
                <a
                    href="{{ route('cart') }}"
                    style="
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        background: #111;
                        color: #fff;
                        text-decoration: none;
                        padding: 9px 13px;
                        border-radius: 8px;
                        font-size: 14px;
                        font-weight: 700;
                    "
                >
                    Cart

                    <span
                        id="cart-count"
                        style="
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 21px;
                            height: 21px;
                            padding: 0 6px;
                            border-radius: 999px;
                            background: #fff;
                            color: #111;
                            font-size: 12px;
                            font-weight: 800;
                        "
                    >
                        {{ $cartCount }}
                    </span>
                </a>
            </div>

            <button
    type="button"
    class="store-menu-button"
    id="store-menu-button"
    aria-label="Open navigation menu"
    aria-expanded="false"
    aria-controls="store-mobile-nav"
>
    <span></span>
    <span></span>
    <span></span>
</button>

<div class="store-mobile-nav" id="store-mobile-nav">
    @auth
        @if (auth()->user()?->is_admin)
            <a href="{{ route('admin.dashboard') }}">
                Admin Dashboard
            </a>
        @endif
    @endauth

    <a
        href="{{ route('products') }}"
        class="{{ request()->routeIs('products') || request()->routeIs('products.show') ? 'is-active' : '' }}"
    >
        Products
    </a>

    @guest
        <a href="{{ route('login') }}">Log in</a>
        <a href="{{ route('register') }}">Register</a>
    @endguest

    @auth
        <a
            href="{{ route('wishlist') }}"
            class="{{ request()->routeIs('wishlist') ? 'is-active' : '' }}"
        >
            Wishlist
        </a>

        <a
            href="{{ route('orders') }}"
            class="{{ request()->routeIs('orders') || request()->routeIs('orders.show') ? 'is-active' : '' }}"
        >
            My Orders
        </a>

        <a
           href="{{ route('account') }}"
           class="{{ request()->routeIs('account') ? 'is-active' : '' }}"
           >
           My Account
        </a>
        

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @endauth

    <a
        href="{{ route('cart') }}"
        class="{{ request()->routeIs('cart') ? 'is-active' : '' }}"
    >
        Cart ({{ $cartCount }})
    </a>
</div>


        </nav>
    </header>

    {{ $slot }}

    @livewireScripts
    @fluxScripts

    <script>
        window.addEventListener('cart-updated', event => {

    const cartCount = document.getElementById('cart-count');

    const mobileCartCount = document.getElementById('mobile-cart-count');

    if (cartCount) {

        cartCount.textContent = event.detail.count;

    }

    if (mobileCartCount) {

        mobileCartCount.textContent = event.detail.count;

    }

});
        

        const setupStoreMenu = () => {
            const button = document.getElementById('store-menu-button');
            const menu = document.getElementById('store-mobile-nav');
            if (!button || !menu || button.dataset.ready === 'true') {
                return;
            }
            button.dataset.ready = 'true';

            // OPEN / CLOSE MOBILE MENU
button.addEventListener('click', () => {

    const isOpen = menu.classList.toggle('is-open');

    button.setAttribute(
        'aria-expanded',
        isOpen ? 'true' : 'false'
    );

});

// CLOSE MENU WHEN CLICKING OUTSIDE
document.addEventListener('click', (event) => {

    if (
        !menu.contains(event.target) &&
        !button.contains(event.target)
    ) {
        menu.classList.remove('is-open');

        button.setAttribute('aria-expanded', 'false');
    }

});

// CLOSE MENU WITH ESCAPE
document.addEventListener('keydown', (event) => {

    if (event.key === 'Escape') {

        menu.classList.remove('is-open');

        button.setAttribute('aria-expanded', 'false');

    }

});

            
            
        };
    document.addEventListener('DOMContentLoaded', setupStoreMenu);
    document.addEventListener('livewire:navigated', setupStoreMenu);
    </script>
</body>
</html>
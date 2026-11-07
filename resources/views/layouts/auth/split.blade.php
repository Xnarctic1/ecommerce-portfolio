<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>

<body
    style="
        margin: 0;
        min-height: 100vh;
        background: #f7f7f7;
        color: #111;
        font-family: Arial, Helvetica, sans-serif;
    "
>
    <div
        style="
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        "
        class="auth-wrapper"
    >

        {{-- LEFT SIDE --}}
        <section
            style="
                background: #111;
                color: #fff;
                padding: 48px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-height: 100vh;
                box-sizing: border-box;
            "
            class="auth-left"
        >
            <a
                href="{{ route('products') }}"
                wire:navigate
                style="
                    color: #fff;
                    text-decoration: none;
                    font-size: 24px;
                    font-weight: 800;
                    letter-spacing: -0.5px;
                "
            >
                STORE
            </a>

            <div style="max-width: 520px;">
                <p
                    style="
                        margin: 0 0 14px;
                        color: #aaa;
                        font-size: 13px;
                        font-weight: 700;
                        letter-spacing: 0.1em;
                    "
                >
                    YOUR ACCOUNT
                </p>

                <h2
                    style="
                        margin: 0;
                        font-size: 46px;
                        line-height: 1.08;
                        font-weight: 800;
                        letter-spacing: -1.5px;
                    "
                >
                    Shopping made simple.
                </h2>

                <p
                    style="
                        margin: 22px 0 0;
                        color: #bbb;
                        font-size: 16px;
                        line-height: 1.7;
                        max-width: 440px;
                    "
                >
                    Sign in to track your orders, manage your account
                    and continue shopping from where you left off.
                </p>
            </div>

            <div
                style="
                    color: #777;
                    font-size: 13px;
                "
            >
                © {{ date('Y') }} STORE
            </div>
        </section>


        {{-- RIGHT SIDE --}}
        <section
            style="
                background: #f7f7f7;
                padding: 48px 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-sizing: border-box;
            "
        >
            <div
                style="
                    width: 100%;
                    max-width: 500px;
                "
            >

                {{-- MOBILE LOGO --}}
                <div
                    class="mobile-store-logo"
                    style="
                        display: none;
                        margin-bottom: 32px;
                        text-align: center;
                    "
                >
                    <a
                        href="{{ route('products') }}"
                        wire:navigate
                        style="
                            color: #111;
                            text-decoration: none;
                            font-size: 22px;
                            font-weight: 800;
                            letter-spacing: -0.5px;
                        "
                    >
                        STORE
                    </a>
                </div>

                {{ $slot }}

            </div>
        </section>

    </div>


    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts


    <style>
        @media (max-width: 900px) {
            .auth-wrapper {
                grid-template-columns: 1fr !important;
            }

            .auth-left {
                display: none !important;
            }

            .mobile-store-logo {
                display: block !important;
            }
        }

        @media (max-width: 600px) {
            .auth-wrapper section {
                padding: 28px 20px !important;
            }
        }
    </style>

</body>
</html>
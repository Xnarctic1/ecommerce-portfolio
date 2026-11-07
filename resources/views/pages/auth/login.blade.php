<x-layouts::auth :title="__('Log in')">
    <div style="max-width: 460px; margin: 0 auto; width: 100%;">
        <div style="margin-bottom: 30px; text-align: center;">
            <p style="margin: 0 0 8px; color: #777; font-size: 13px; font-weight: 700; letter-spacing: 0.08em;">
                WELCOME BACK
            </p>

            <h1 style="margin: 0; font-size: 34px; font-weight: 800; line-height: 1.1; color: #111;">
                Log in to your account
            </h1>

            <p style="margin: 10px 0 0; color: #737373; font-size: 15px;">
                Enter your email and password to continue.
            </p>
        </div>

        <div
            style="
                background: #fff;
                border: 1px solid #e7e7e7;
                border-radius: 14px;
                padding: 28px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            "
        >
            <x-auth-session-status class="text-center" :status="session('status')" />

            <form
                method="POST"
                action="{{ route('login.store') }}"
                style="display: flex; flex-direction: column; gap: 20px;"
            >
                @csrf

                <div>
                    <label
                        for="email"
                        style="
                            display: block;
                            margin-bottom: 7px;
                            font-size: 14px;
                            font-weight: 700;
                            color: #222;
                        "
                    >
                        Email address
                    </label>

                    <input
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@example.com"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('email')
                        <p style="margin-top: 6px; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            gap: 16px;
                            align-items: center;
                            margin-bottom: 7px;
                        "
                    >
                        <label
                            for="password"
                            style="
                                font-size: 14px;
                                font-weight: 700;
                                color: #222;
                            "
                        >
                            Password
                        </label>

                        @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                style="
                                    color: #555;
                                    text-decoration: none;
                                    font-size: 13px;
                                    font-weight: 600;
                                "
                            >
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="Password"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('password')
                        <p style="margin-top: 6px; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <label
                    style="
                        display: flex;
                        align-items: center;
                        gap: 9px;
                        color: #444;
                        font-size: 14px;
                        cursor: pointer;
                    "
                >
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                        style="width: 16px; height: 16px;"
                    >

                    Remember me
                </label>

                <button
                    type="submit"
                    data-test="login-button"
                    style="
                        width: 100%;
                        background: #111;
                        color: #fff;
                        border: 0;
                        border-radius: 8px;
                        padding: 13px 16px;
                        font-size: 15px;
                        font-weight: 700;
                        cursor: pointer;
                    "
                >
                    Log in
                </button>
            </form>
        </div>

        <div
            style="
                text-align: center;
                margin-top: 22px;
                color: #666;
                font-size: 14px;
            "
        >
            <span>Don't have an account?</span>

            <a
                href="{{ route('register') }}"
                style="
                    margin-left: 4px;
                    color: #111;
                    font-weight: 700;
                    text-decoration: none;
                "
            >
                Create account
            </a>
        </div>
    </div>
</x-layouts::auth>
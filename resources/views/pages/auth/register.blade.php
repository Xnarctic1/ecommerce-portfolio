<x-layouts::auth :title="__('Register')">
    <div style="max-width: 460px; margin: 0 auto; width: 100%;">

        <div style="margin-bottom: 30px; text-align: center;">
            <p style="margin: 0 0 8px; color: #777; font-size: 13px; font-weight: 700; letter-spacing: 0.08em;">
                JOIN STORE
            </p>

            <h1 style="margin: 0; font-size: 34px; font-weight: 800; line-height: 1.1; color: #111;">
                Create your account
            </h1>

            <p style="margin: 10px 0 0; color: #737373; font-size: 15px;">
                Enter your details below to get started.
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
            <x-auth-session-status
                class="text-center"
                :status="session('status')"
            />

            <form
                method="POST"
                action="{{ route('register.store') }}"
                style="display: flex; flex-direction: column; gap: 20px;"
            >
                @csrf

                {{-- NAME --}}
                <div>
                    <label
                        for="name"
                        style="
                            display: block;
                            margin-bottom: 7px;
                            font-size: 14px;
                            font-weight: 700;
                            color: #222;
                        "
                    >
                        Full name
                    </label>

                    <input
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Full name"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                            box-sizing: border-box;
                        "
                    >

                    @error('name')
                        <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- EMAIL --}}
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
                        autocomplete="email"
                        placeholder="email@example.com"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                            box-sizing: border-box;
                        "
                    >

                    @error('email')
                        <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- PASSWORD --}}
                <div>
                    <label
                        for="password"
                        style="
                            display: block;
                            margin-bottom: 7px;
                            font-size: 14px;
                            font-weight: 700;
                            color: #222;
                        "
                    >
                        Password
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Password"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                            box-sizing: border-box;
                        "
                    >

                    @error('password')
                        <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- CONFIRM PASSWORD --}}
                <div>
                    <label
                        for="password_confirmation"
                        style="
                            display: block;
                            margin-bottom: 7px;
                            font-size: 14px;
                            font-weight: 700;
                            color: #222;
                        "
                    >
                        Confirm password
                    </label>

                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                            box-sizing: border-box;
                        "
                    >
                </div>


                {{-- SUBMIT --}}
                <button
                    type="submit"
                    data-test="register-user-button"
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
                    Create account
                </button>
            </form>
        </div>


        {{-- LOGIN LINK --}}
        <div
            style="
                text-align: center;
                margin-top: 22px;
                color: #666;
                font-size: 14px;
            "
        >
            <span>Already have an account?</span>

            <a
                href="{{ route('login') }}"
                wire:navigate
                style="
                    margin-left: 4px;
                    color: #111;
                    font-weight: 700;
                    text-decoration: none;
                "
            >
                Log in
            </a>
        </div>

    </div>
</x-layouts::auth>
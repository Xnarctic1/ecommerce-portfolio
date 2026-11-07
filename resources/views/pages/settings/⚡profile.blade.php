<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.public')] #[Title('Profile settings')] class extends Component {    
use ProfileValidationRules;

    public string $name = '';
    public string $email = '';
    public string $address = '';
    public string $city = '';
    public string $postal_code = '';
    public string $country = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->address = $user->address ?? '';
        $this->city = $user->city ?? '';
        $this->postal_code = $user->postal_code ?? '';
        $this->country = $user->country ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    public function updateAddress(): void
    {
        $validated = $this->validate([
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $user->address = $validated['address'];
        $user->city = $validated['city'];
        $user->postal_code = $validated['postal_code'];
        $user->country = $validated['country'];

        $user->save();

        Flux::toast(
            variant: 'success',
            text: __('Address updated.')
        );
    }



    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    } 
};

?>

<section style="background: #f7f7f7; min-height: 100vh; padding: 48px 24px; box-sizing: border-box;">
    <div style="max-width: 900px; margin: 0 auto;">

        <div style="margin-bottom: 30px;">
            <p style="margin: 0 0 8px; color: #777; font-size: 13px; font-weight: 700; letter-spacing: 0.08em;">
                YOUR ACCOUNT
            </p>

            <h1 style="margin: 0; color: #111; font-size: 36px; font-weight: 800; letter-spacing: -1px;">
                Account settings
            </h1>

            <p style="margin: 10px 0 0; color: #777; font-size: 15px;">
                Manage your personal information and account preferences.
            </p>
        </div>

        <div
            style="
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-bottom: 24px;
            "
        >
            <a
                href="{{ route('profile.edit') }}"
                wire:navigate
                style="
                    background: #111;
                    color: #fff;
                    text-decoration: none;
                    padding: 10px 15px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 700;
                "
            >
                Profile
            </a>

            <a
                href="{{ route('security.edit') }}"
                wire:navigate
                style="
                    background: #fff;
                    color: #333;
                    text-decoration: none;
                    padding: 10px 15px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 700;
                "
            >
                Security
            </a>

            <div
    x-data
    style="
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 4px;
    "
>
    <button
        type="button"
        @click="$flux.appearance = 'light'"
        title="Light mode"
        style="width: 32px; height: 32px; border: 0; background: transparent; cursor: pointer;"
    >
        ☀
    </button>

    <button
        type="button"
        @click="$flux.appearance = 'dark'"
        title="Dark mode"
        style="width: 32px; height: 32px; border: 0; background: transparent; cursor: pointer;"
    >
        ☾
    </button>

    <button
        type="button"
        @click="$flux.appearance = 'system'"
        title="System preference"
        style="width: 32px; height: 32px; border: 0; background: transparent; cursor: pointer;"
    >
        ◉
    </button>
</div>

        </div>

        <div
            style="
                background: #fff;
                border: 1px solid #e5e5e5;
                border-radius: 14px;
                padding: 32px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            "
        >
            <div style="margin-bottom: 26px;">
                <h2 style="margin: 0; color: #111; font-size: 22px; font-weight: 800;">
                    Profile information
                </h2>

                <p style="margin: 8px 0 0; color: #777; font-size: 14px;">
                    Update your name and email address.
                </p>
            </div>

            <form wire:submit="updateProfileInformation" style="display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <label
                        for="name"
                        style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;"
                    >
                        Name
                    </label>

                    <input
                        id="name"
                        wire:model="name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('name')
                        <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="email"
                        style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;"
                    >
                        Email address
                    </label>

                    <input
                        id="email"
                        wire:model="email"
                        type="email"
                        required
                        autocomplete="email"
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('email')
                        <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <button
                        type="submit"
                        data-test="update-profile-button"
                        style="
                            background: #111;
                            color: #fff;
                            border: 0;
                            border-radius: 8px;
                            padding: 12px 20px;
                            font-size: 14px;
                            font-weight: 700;
                            cursor: pointer;
                        "
                    >
                        Save changes
                    </button>
                </div>
            </form>
        </div>

        <div
    style="
        margin-top: 24px;
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 14px;
        padding: 32px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
    "
>
    <h2 style="margin: 0; font-size: 22px; font-weight: 800;">
    Address
</h2>

<p style="margin: 8px 0 0; color: #777; font-size: 14px;">
    Your default shipping address.
</p>

<form
    wire:submit="updateAddress"
    style="display: flex; flex-direction: column; gap: 20px; margin-top: 24px;"
>
    <div>
        <label
            for="address"
            style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;"
        >
            Address
        </label>

        <input
            id="address"
            wire:model="address"
            type="text"
            autocomplete="street-address"
            required
            style="width: 100%; box-sizing: border-box; border: 1px solid #d7d7d7; border-radius: 8px; padding: 12px 14px; font-size: 15px;"
        >

        @error('address')
            <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">

        <div>
            <label
                for="city"
                style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;"
            >
                City
            </label>

            <input
                id="city"
                wire:model="city"
                type="text"
                autocomplete="address-level2"
                required
                style="width: 100%; box-sizing: border-box; border: 1px solid #d7d7d7; border-radius: 8px; padding: 12px 14px; font-size: 15px;"
            >

            @error('city')
                <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label
                for="postal_code"
                style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;"
            >
                Postal code
            </label>

            <input
                id="postal_code"
                wire:model="postal_code"
                type="text"
                autocomplete="postal-code"
                required
                style="width: 100%; box-sizing: border-box; border: 1px solid #d7d7d7; border-radius: 8px; padding: 12px 14px; font-size: 15px;"
            >

            @error('postal_code')
                <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                    {{ $message }}
                </p>
            @enderror
        </div>

    </div>

    <div>
        <label
            for="country"
            style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;"
        >
            Country
        </label>

        <input
            id="country"
            wire:model="country"
            type="text"
            autocomplete="country-name"
            required
            style="width: 100%; box-sizing: border-box; border: 1px solid #d7d7d7; border-radius: 8px; padding: 12px 14px; font-size: 15px;"
        >

        @error('country')
            <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <button
            type="submit"
            style="background: #111; color: #fff; border: 0; border-radius: 8px; padding: 12px 20px; font-size: 14px; font-weight: 700; cursor: pointer;"
        >
            Save address
        </button>
    </div>
</form>
</div>

<div
    style="
        margin-top: 24px;
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 14px;
        padding: 32px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
    "
>
    <h2 style="margin: 0; font-size: 22px; font-weight: 800;">
        Payment method
    </h2>

    <p style="margin: 8px 0 0; color: #777; font-size: 14px;">
        Your saved payment method for faster checkout.
    </p>

    <div
        style="
            margin-top: 24px;
            padding: 18px;
            background: #f7f7f7;
            border-radius: 10px;
            color: #777;
            font-size: 14px;
        "
    >
        No payment method saved yet.
    </div>
</div>

        @if ($this->showDeleteUser)

            <div
                style="
                    margin-top: 24px;
                    background: #fff;
                    border: 1px solid #eadada;
                    border-radius: 14px;
                    padding: 32px;
                "
            >
                <livewire:pages::settings.delete-user-form />
            </div>
        @endif

    </div>
</section>

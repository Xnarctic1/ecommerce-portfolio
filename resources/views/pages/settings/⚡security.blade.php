<?php

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.public')] #[Title('Security settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';



    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {

    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Password updated.'));
    }


}; ?>

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

        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;">
            <a
                href="{{ route('profile.edit') }}"
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
                Profile
            </a>

            <a
                href="{{ route('security.edit') }}"
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
                Security
            </a>

            <a
                href="{{ route('appearance.edit') }}"
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
                Appearance
            </a>
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
                    Update password
                </h2>

                <p style="margin: 8px 0 0; color: #777; font-size: 14px;">
                    Use a strong, unique password to keep your account secure.
                </p>
            </div>

            <form method="POST" wire:submit="updatePassword" style="display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <label for="current_password" style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;">
                        Current password
                    </label>

                    <input
                        id="current_password"
                        wire:model="current_password"
                        type="password"
                        required
                        autocomplete="current-password"
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

                    @error('current_password')
                        <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password" style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;">
                        New password
                    </label>

                    <input
                        id="password"
                        wire:model="password"
                        type="password"
                        required
                        autocomplete="new-password"
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

                    @error('password')
                        <p style="margin: 6px 0 0; color: #b91c1c; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" style="display: block; margin-bottom: 7px; color: #222; font-size: 14px; font-weight: 700;">
                        Confirm password
                    </label>

                    <input
                        id="password_confirmation"
                        wire:model="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
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
                </div>

                <div>
                    <button
                        type="submit"
                        data-test="update-password-button"
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
                        Save password
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>
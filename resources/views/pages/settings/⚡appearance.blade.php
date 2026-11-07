<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('layouts.public')] #[Title('Appearance settings')] class extends Component {

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

            <a
                href="{{ route('appearance.edit') }}"
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
                    Appearance
                </h2>

                <p style="margin: 8px 0 0; color: #777; font-size: 14px;">
                    Choose how the STORE interface looks on this device.
                </p>
            </div>

            <div style="max-width: 420px;">
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>
            </div>
        </div>

    </div>
</section>

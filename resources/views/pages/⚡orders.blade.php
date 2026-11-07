<?php

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('My Orders')] class extends Component
{
    #[Computed]
    public function orders()
    {
        return Order::where('user_id', auth()->id())
            ->latest()
            ->get();
    }
};

?>

<div style="max-width: 1000px; margin: 0 auto; padding: 40px 24px 60px;">
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 36px; font-weight: 700; line-height: 1.1; margin: 0;">
            My Orders
        </h1>

        <p style="margin-top: 8px; color: #737373; font-size: 15px;">
            View your previous purchases and track their status.
        </p>
    </div>

    @forelse ($this->orders as $order)

        @php
            $statusStyles = match ($order->status) {
                'pending' => 'background:#fef3c7; color:#92400e;',
                'processing' => 'background:#dbeafe; color:#1d4ed8;',
                'shipped' => 'background:#ede9fe; color:#6d28d9;',
                'completed' => 'background:#dcfce7; color:#166534;',
                default => 'background:#f3f4f6; color:#374151;',
            };
        @endphp

        <div
            style="
                background: #fff;
                border: 1px solid #e7e7e7;
                border-radius: 14px;
                padding: 22px;
                margin-bottom: 18px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            "
        >
            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    gap: 20px;
                    flex-wrap: wrap;
                    align-items: flex-start;
                "
            >
                <div>
                    <p style="margin: 0 0 6px; color: #8a8a8a; font-size: 13px; font-weight: 600;">
                        ORDER
                    </p>

                    <h2 style="margin: 0; font-size: 22px; font-weight: 700;">
                        #{{ $order->id }}
                    </h2>

                    <p style="margin: 8px 0 0; color: #666; font-size: 14px;">
                        {{ $order->created_at->format('d M Y, H:i') }}
                    </p>
                </div>

                <span
                    style="
                        {{ $statusStyles }}
                        display: inline-block;
                        padding: 7px 11px;
                        border-radius: 999px;
                        font-size: 13px;
                        font-weight: 700;
                        text-transform: capitalize;
                    "
                >
                    {{ $order->status }}
                </span>
            </div>

            <div
                style="
                    margin-top: 22px;
                    padding-top: 18px;
                    border-top: 1px solid #ececec;
                    display: flex;
                    justify-content: space-between;
                    gap: 20px;
                    flex-wrap: wrap;
                    align-items: center;
                "
            >
                <div>
                    <p style="margin: 0 0 4px; color: #777; font-size: 13px;">
                        Order total
                    </p>

                    <strong style="font-size: 24px;">
                        €{{ number_format($order->total, 2) }}
                    </strong>
                </div>

                <a
                    href="{{ route('orders.show', $order) }}"
                    style="
                        display: inline-block;
                        background: #111;
                        color: #fff;
                        padding: 11px 16px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-size: 14px;
                        font-weight: 700;
                    "
                >
                    View order →
                </a>
            </div>
        </div>

    @empty

        <div
            style="
                border: 1px solid #e7e7e7;
                border-radius: 14px;
                padding: 48px 24px;
                text-align: center;
                background: #fff;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            "
        >
            <div style="font-size: 36px; margin-bottom: 14px;">
                🛍️
            </div>

            <h2 style="font-size: 22px; font-weight: 700; margin: 0;">
                No orders yet
            </h2>

            <p style="color: #737373; margin-top: 8px;">
                When you place an order, it will appear here.
            </p>

            <a
                href="{{ route('products') }}"
                style="
                    display: inline-block;
                    margin-top: 20px;
                    background: #111;
                    color: #fff;
                    padding: 11px 16px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: 700;
                "
            >
                Browse products
            </a>
        </div>

    @endforelse
</div>
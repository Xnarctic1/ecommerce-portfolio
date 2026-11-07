<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('My Account')] class extends Component
{
    public function with(): array
    {
        $orders = Order::query()
            ->where('user_id', auth()->id());

        return [
            'totalOrders' => (clone $orders)->count(),

            'totalSpent' => (clone $orders)
                ->where('payment_status', 'paid')
                ->sum('total'),

            'activeOrders' => (clone $orders)
                ->whereIn('status', ['pending', 'processing', 'shipped'])
                ->count(),

            'recentOrders' => (clone $orders)
                ->with(['items.product.images'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
};

?>

<div style="max-width: 1000px; margin: 0 auto; padding: 40px 24px 60px;">

    {{-- HEADER --}}
    <div
        style="
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 32px;
        "
    >
        <div>
            <h1
                style="
                    font-size: 36px;
                    font-weight: 700;
                    line-height: 1.1;
                    margin: 0;
                    color: #111;
                "
            >
                My Account
            </h1>

            <p style="margin-top: 8px; color: #737373; font-size: 15px;">
                Welcome back, {{ auth()->user()->name }}.
                Here's an overview of your account.
            </p>
        </div>

        <a
            href="{{ route('profile.edit') }}"
            style="
                display: inline-block;
                border: 1px solid #dedede;
                background: #fff;
                color: #111;
                padding: 11px 16px;
                border-radius: 8px;
                text-decoration: none;
                font-size: 14px;
                font-weight: 700;
            "
        >
            Account settings →
        </a>
    </div>


    {{-- STATISTICS --}}
    <div
        style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 32px;
        "
    >

        {{-- TOTAL ORDERS --}}
        <div
            style="
                background: #fff;
                border: 1px solid #e7e7e7;
                border-radius: 14px;
                padding: 22px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            "
        >
            <p
                style="
                    margin: 0 0 10px;
                    color: #777;
                    font-size: 13px;
                    font-weight: 600;
                "
            >
                TOTAL ORDERS
            </p>

            <strong style="font-size: 30px; font-weight: 700; color: #111;">
                {{ $totalOrders }}
            </strong>
        </div>


        {{-- TOTAL SPENT --}}
        <div
            style="
                background: #fff;
                border: 1px solid #e7e7e7;
                border-radius: 14px;
                padding: 22px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            "
        >
            <p
                style="
                    margin: 0 0 10px;
                    color: #777;
                    font-size: 13px;
                    font-weight: 600;
                "
            >
                TOTAL SPENT
            </p>

            <strong style="font-size: 30px; font-weight: 700; color: #111;">
                €{{ number_format((float) $totalSpent, 2) }}
            </strong>
        </div>


        {{-- ACTIVE ORDERS --}}
        <div
            style="
                background: #fff;
                border: 1px solid #e7e7e7;
                border-radius: 14px;
                padding: 22px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            "
        >
            <p
                style="
                    margin: 0 0 10px;
                    color: #777;
                    font-size: 13px;
                    font-weight: 600;
                "
            >
                ACTIVE ORDERS
            </p>

            <strong style="font-size: 30px; font-weight: 700; color: #111;">
                {{ $activeOrders }}
            </strong>
        </div>

    </div>


    {{-- RECENT ORDERS HEADER --}}
    <div
        style="
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        "
    >
        <div>
            <h2
                style="
                    font-size: 24px;
                    font-weight: 700;
                    color: #111;
                    margin: 0;
                "
            >
                Recent Orders
            </h2>

            <p style="margin: 6px 0 0; color: #737373; font-size: 14px;">
                Your latest purchases and their status.
            </p>
        </div>

        <a
            href="{{ route('orders') }}"
            style="
                color: #111;
                font-size: 14px;
                font-weight: 700;
                text-decoration: underline;
                text-underline-offset: 3px;
            "
        >
            View all orders →
        </a>
    </div>


    {{-- RECENT ORDERS LIST --}}
    @forelse ($recentOrders as $order)

        @php
            $statusStyles = match (strtolower((string) $order->status)) {
                'pending' => 'background:#fef3c7; color:#92400e;',
                'processing' => 'background:#dbeafe; color:#1d4ed8;',
                'shipped' => 'background:#ede9fe; color:#6d28d9;',
                'completed', 'delivered' => 'background:#dcfce7; color:#166534;',
                'cancelled', 'canceled' => 'background:#fee2e2; color:#991b1b;',
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

            {{-- ORDER HEADER --}}
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
                    <p
                        style="
                            margin: 0 0 6px;
                            color: #8a8a8a;
                            font-size: 13px;
                            font-weight: 600;
                        "
                    >
                        ORDER
                    </p>

                    <h3
                        style="
                            margin: 0;
                            font-size: 22px;
                            font-weight: 700;
                            color: #111;
                        "
                    >
                        #{{ $order->id }}
                    </h3>

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


            {{-- PRODUCT THUMBNAILS --}}
            <div
                style="
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin-top: 18px;
                "
            >
                @foreach ($order->items->take(4) as $item)

                    @php
                        $product = $item->product;

                        $imagePath = $product?->image
                            ?: $product?->images->first()?->path;
                    @endphp

                    <div
                        title="{{ $item->product_name }}"
                        style="
                            width: 70px;
                            height: 70px;
                            border: 1px solid #e7e7e7;
                            border-radius: 10px;
                            background: #fafafa;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            overflow: hidden;
                        "
                    >
                        @if ($imagePath)

                            <img
                                src="{{ asset('storage/' . $imagePath) }}"
                                alt="{{ $item->product_name }}"
                                loading="lazy"
                                style="
                                    width: 100%;
                                    height: 100%;
                                    object-fit: contain;
                                "
                            >

                        @else

                            <span
                                style="
                                    font-size: 11px;
                                    color: #999;
                                    text-align: center;
                                "
                            >
                                No image
                            </span>

                        @endif
                    </div>

                @endforeach

                @if ($order->items->count() > 4)

                    <span
                        style="
                            display: flex;
                            align-items: center;
                            font-size: 13px;
                            color: #777;
                        "
                    >
                        +{{ $order->items->count() - 4 }} more
                    </span>

                @endif
            </div>


            {{-- ORDER FOOTER --}}
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

                    <strong style="font-size: 24px; color: #111;">
                        €{{ number_format((float) $order->total, 2) }}
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

        {{-- EMPTY STATE --}}
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

            <h3 style="font-size: 22px; font-weight: 700; margin: 0;">
                No orders yet
            </h3>

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
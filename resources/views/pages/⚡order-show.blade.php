<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Order')] class extends Component
{
    public Order $order;

   public function mount(Order $order): void
   {
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }
    $this->order = $order->load('items.product.images');
    }
};

?>

<div style="max-width: 1050px; margin: 0 auto; padding: 40px 24px 60px;">

    @php
    $statusStyles = match ($order->status) {
        'pending' => 'background:#fef3c7; color:#92400e;',
        'processing' => 'background:#dbeafe; color:#1d4ed8;',
        'shipped' => 'background:#ede9fe; color:#6d28d9;',
        'completed' => 'background:#dcfce7; color:#166534;',
        'cancelled' => 'background:#fee2e2; color:#991b1b;',
        default => 'background:#f3f4f6; color:#374151;',
    };

    $orderSteps = [
        'pending' => 1,
        'processing' => 2,
        'shipped' => 3,
        'completed' => 4,
    ];
    $currentStep = $orderSteps[$order->status] ?? 0;
    @endphp

    <a
        href="{{ route('orders') }}"
        style="
            display:inline-flex;
            align-items:center;
            gap:6px;
            margin-bottom:20px;
            color:#555;
            text-decoration:none;
            font-size:14px;
            font-weight:500;
        "
    >
        ← Back to my orders
    </a>

    <div
        style="
            display:flex;
            justify-content:space-between;
            gap:24px;
            flex-wrap:wrap;
            align-items:flex-start;
            margin-bottom:32px;
        "
    >
        <div>
            <p style="margin:0 0 8px; color:#777; font-size:14px; font-weight:600;">
                ORDER DETAILS
            </p>

            <h1 style="font-size:36px; font-weight:700; line-height:1.1; margin:0;">
                Order #{{ $order->id }}
            </h1>

            <p style="margin:10px 0 0; color:#666; font-size:15px;">
                Placed on {{ $order->created_at->format('d M Y') }}.
            </p>
        </div>

        <span
            style="
                {{ $statusStyles }}
                display:inline-block;
                padding:8px 12px;
                border-radius:999px;
                font-size:13px;
                font-weight:700;
                text-transform:capitalize;
            "
        >
            {{ $order->status }}
        </span>
    </div>


    @if ($order->status === 'cancelled')

    <div
        style="
            margin-bottom:28px;
            padding:18px 20px;
            background:#fef2f2;
            border:1px solid #fecaca;
            border-radius:14px;
            color:#991b1b;
        "
    >
        <strong style="display:block; margin-bottom:4px;">
            Order cancelled
        </strong>

        <span style="font-size:14px;">
            This order has been cancelled.
        </span>
    </div>

@else
    <div
        class="order-progress"
        style="
            background:#fff;
            border:1px solid #e7e7e7;
            border-radius:14px;
            padding:24px;
            margin-bottom:28px;
            box-shadow:0 8px 24px rgba(0,0,0,0.04);
        "
    >

        @foreach ([
            1 => 'Order placed',
            2 => 'Processing',
            3 => 'Shipped',
            4 => 'Completed',
        ] as $step => $label)

            <div class="order-progress-step">

                <div
                    class="order-progress-circle"
                    style="
                        {{ $currentStep >= $step
                            ? 'background:#111; color:#fff; border-color:#111;'
                            : 'background:#fff; color:#999; border-color:#d6d6d6;' }}
                    "
                >
                    {{ $currentStep >= $step ? '✓' : $step }}
                </div>

                <span
                    style="
                        font-size:13px;
                        font-weight:700;
                        {{ $currentStep >= $step
                            ? 'color:#111;'
                            : 'color:#999;' }}
                    "
                >
                    {{ $label }}
                </span>

            </div>
            @if ($step < 4)
                <div
                    class="order-progress-line"
                    style="
                        {{ $currentStep > $step
                            ? 'background:#111;'
                            : 'background:#e5e5e5;' }}
                    "
                ></div>
            @endif
        @endforeach
    </div>
@endif


    <div
        class="order-details-grid"
        style="
            display:grid;
            grid-template-columns:minmax(0, 1fr) 340px;
            gap:28px;
            align-items:start;
        "
    >

        {{-- LEFT SIDE --}}
        <div>

            {{-- ITEMS --}}
            <div
                style="
                    background:#fff;
                    border:1px solid #e7e7e7;
                    border-radius:14px;
                    padding:24px;
                    box-shadow:0 8px 24px rgba(0,0,0,0.04);
                    margin-bottom:22px;
                "
            >

                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        gap:20px;
                        align-items:center;
                        margin-bottom:20px;
                    "
                >
                    <h2 style="font-size:21px; font-weight:700; margin:0;">
                        Items
                    </h2>

                    <span style="color:#777; font-size:14px;">
                        {{ $order->items->sum('quantity') }} item(s)
                    </span>
                </div>


                <div style="display:flex; flex-direction:column;">

                   @foreach ($order->items as $item)

    @php
        $product = $item->product;
        $imagePath = $product?->image ?: $product?->images->first()?->path;
    @endphp

    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:16px;
        padding:17px 0;
        border-top:1px solid #eeeeee;
    ">

        <div style="display:flex; align-items:center; gap:14px; min-width:0;">

            <div style="
                width:72px;
                height:72px;
                flex:0 0 72px;
                display:flex;
                align-items:center;
                justify-content:center;
                overflow:hidden;
                background:#fafafa;
                border:1px solid #e7e7e7;
                border-radius:10px;
            ">
                @if ($imagePath)
                    <img
                        src="{{ asset('storage/' . $imagePath) }}"
                        alt="{{ $item->product_name }}"
                        loading="lazy"
                        style="width:100%; height:100%; object-fit:contain;"
                    >
                @else
                    <span style="font-size:11px; color:#999; text-align:center;">
                        No image
                    </span>
                @endif
            </div>

            <div style="min-width:0;">
                <h3 style="
                    font-size:16px;
                    font-weight:700;
                    margin:0 0 5px;
                    overflow-wrap:anywhere;
                ">
                    {{ $item->product_name }}
                </h3>

                <p style="margin:0; color:#777; font-size:14px;">
                    €{{ number_format((float) $item->price, 2) }}
                    × {{ $item->quantity }}
                </p>
            </div>

        </div>

        <strong style="white-space:nowrap; font-size:16px;">
            €{{ number_format((float) $item->subtotal, 2) }}
        </strong>

    </div>

@endforeach 

                </div>
            </div>


            {{-- SHIPPING --}}
            <div
                style="
                    background:#fff;
                    border:1px solid #e7e7e7;
                    border-radius:14px;
                    padding:24px;
                    box-shadow:0 8px 24px rgba(0,0,0,0.04);
                "
            >

                <h2 style="font-size:21px; font-weight:700; margin:0 0 20px;">
                    Shipping information
                </h2>

                <div
                    style="
                        display:grid;
                        grid-template-columns:repeat(2, minmax(0, 1fr));
                        gap:20px;
                    "
                >

                    <div>
                        <p style="margin:0 0 5px; color:#888; font-size:12px; font-weight:700;">
                            NAME
                        </p>

                        <p style="margin:0; font-weight:600;">
                            {{ $order->customer_name }}
                        </p>
                    </div>


                    <div>
                        <p style="margin:0 0 5px; color:#888; font-size:12px; font-weight:700;">
                            EMAIL
                        </p>

                        <p style="margin:0; font-weight:600; overflow-wrap:anywhere;">
                            {{ $order->email }}
                        </p>
                    </div>


                    <div style="grid-column:1 / -1;">
                        <p style="margin:0 0 5px; color:#888; font-size:12px; font-weight:700;">
                            ADDRESS
                        </p>

                        <p style="margin:0; font-weight:600;">
                            {{ $order->address }}
                        </p>
                    </div>


                    <div>
                        <p style="margin:0 0 5px; color:#888; font-size:12px; font-weight:700;">
                            CITY
                        </p>

                        <p style="margin:0; font-weight:600;">
                            {{ $order->city }}
                        </p>
                    </div>


                    <div>
                        <p style="margin:0 0 5px; color:#888; font-size:12px; font-weight:700;">
                            POSTAL CODE
                        </p>

                        <p style="margin:0; font-weight:600;">
                            {{ $order->postal_code }}
                        </p>
                    </div>


                    <div>
                        <p style="margin:0 0 5px; color:#888; font-size:12px; font-weight:700;">
                            COUNTRY
                        </p>

                        <p style="margin:0; font-weight:600;">
                            {{ $order->country }}
                        </p>
                    </div>

                </div>
            </div>

        </div>


        {{-- RIGHT SUMMARY --}}
        <div
            style="
                background:#111;
                color:#fff;
                border-radius:14px;
                padding:26px;
                position:sticky;
                top:24px;
                box-shadow:0 12px 30px rgba(0,0,0,0.12);
            "
        >

            <h2 style="font-size:21px; font-weight:700; margin:0 0 22px;">
                Order summary
            </h2>


            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    gap:20px;
                    padding-bottom:15px;
                    color:#cfcfcf;
                    font-size:14px;
                "
            >
                <span>Order number</span>

                <strong style="color:#fff;">
                    #{{ $order->id }}
                </strong>
            </div>


            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    gap:20px;
                    padding:15px 0;
                    border-top:1px solid rgba(255,255,255,0.12);
                    color:#cfcfcf;
                    font-size:14px;
                "
            >
                <span>Date</span>

                <strong style="color:#fff;">
                    {{ $order->created_at->format('d M Y') }}
                </strong>
            </div>


            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    gap:20px;
                    padding:15px 0;
                    border-top:1px solid rgba(255,255,255,0.12);
                    color:#cfcfcf;
                    font-size:14px;
                "
            >
                <span>Status</span>

                <strong style="color:#fff; text-transform:capitalize;">
                    {{ $order->status }}
                </strong>
            </div>


                        <div
                style="
                    display:flex;
                    justify-content:space-between;
                    gap:20px;
                    padding:15px 0;
                    border-top:1px solid rgba(255,255,255,0.12);
                    color:#cfcfcf;
                    font-size:14px;
                "
            >
                <span>Payment</span>

                <strong style="color:#fff; text-transform:capitalize;">
                    {{ $order->payment_status ?? 'Pending' }}
                </strong>
            </div>


            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    gap:20px;
                    margin-top:6px;
                    padding-top:20px;
                    border-top:1px solid rgba(255,255,255,0.2);
                "
            >
                <span style="color:#d4d4d4;">
                    Total
                </span>

                <strong style="font-size:27px;">
                    €{{ number_format($order->total, 2) }}
                </strong>
            </div>


            <a
                href="{{ route('products') }}"
                style="
                    display:block;
                    margin-top:24px;
                    background:#fff;
                    color:#111;
                    padding:13px 16px;
                    border-radius:8px;
                    text-align:center;
                    text-decoration:none;
                    font-size:14px;
                    font-weight:700;
                "
            >
                Continue shopping
            </a>

        </div>

    </div>
</div>


<style>
    .order-progress {
    display:flex;
    align-items:flex-start;
}

.order-progress-step {
    width:110px;
    flex:0 0 110px;
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:9px;
    text-align:center;
}

.order-progress-circle {
    width:34px;
    height:34px;
    border:2px solid;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:13px;
    font-weight:800;
    box-sizing:border-box;
}

.order-progress-line {
    height:2px;
    flex:1;
    min-width:20px;
    margin-top:16px;
}
    @media (max-width: 850px) {
        .order-details-grid {
            grid-template-columns: 1fr !important;
        }

        .order-details-grid > div:last-child {
            position: static !important;
        }
    }

    @media (max-width: 600px) {
        .order-progress {
    overflow-x:auto;
    padding-bottom:18px !important;
}

.order-progress-step {
    width:90px;
    flex-basis:90px;
}

.order-progress-line {
    min-width:24px;
    flex:0 0 24px;
}
        .order-details-grid > div:first-child > div:last-child > div {
            grid-template-columns: 1fr !important;
        }

        .order-details-grid > div:first-child > div:last-child > div > div {
            grid-column: 1 !important;
        }
    }
</style>
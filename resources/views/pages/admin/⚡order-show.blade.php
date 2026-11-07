<?php

use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin')] #[Title('Admin Order')] class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load('items');
    }

    public function updateStatus(string $status): void
    {
        $allowedStatuses = [
            'pending',
            'processing',
            'shipped',
            'completed',
            'cancelled',
        ];

        if (! in_array($status, $allowedStatuses, true)) {
            return;
        }

        if ($this->order->status === $status) {
            return;
        }

        $this->order->update([
            'status' => $status,
        ]);

        $this->order->refresh();

        if ($this->order->email) {
            Mail::to($this->order->email)->send(
                new OrderStatusUpdated($this->order)
                );
                }
    }
};

?>

<div class="admin-order-page">
    <div class="admin-order-shell">

        <a
            href="{{ route('admin.orders') }}"
            class="admin-back-link"
        >
            ← Back to orders
        </a>


        {{-- HEADER --}}
        <div class="admin-order-header">
            <div>
                <p class="admin-kicker">
                    ADMIN / ORDER
                </p>

                <h1>
                    Order #{{ $order->id }}
                </h1>

                <p class="admin-order-date">
                    Placed {{ $order->created_at->format('d M Y') }}
                    at {{ $order->created_at->format('H:i') }}
                </p>
            </div>


            <div class="admin-header-badges">
                @if ($order->payment_status === 'paid')
                    <span class="admin-badge admin-badge-paid">
                        ✓ Paid
                    </span>

                @elseif ($order->payment_status === 'unpaid')
                    <span class="admin-badge admin-badge-unpaid">
                        Unpaid
                    </span>

                @else
                    <span class="admin-badge admin-badge-neutral">
                        {{ ucfirst($order->payment_status ?? 'Unknown') }}
                    </span>
                @endif


                <span class="admin-badge admin-badge-order">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>


        <div class="admin-order-grid">
            {{-- LEFT COLUMN --}}
            <div class="admin-order-main">
                {{-- PRODUCTS --}}
                <section class="admin-card">
                    <div class="admin-card-header">
                        <div>
                            <span class="admin-section-label">
                                ORDER CONTENTS
                            </span>

                            <h2>Products</h2>
                        </div>

                        <span class="admin-item-count">
                            {{ $order->items->sum('quantity') }}
                            {{ $order->items->sum('quantity') === 1 ? 'item' : 'items' }}
                        </span>
                    </div>


                    <div class="admin-products">
                        @foreach ($order->items as $item)
                            <div class="admin-product-row">
                                <div class="admin-product-info">
                                    <strong>
                                        {{ $item->product_name }}
                                    </strong>
                                    <span>
                                        €{{ number_format($item->price, 2) }}
                                        ×
                                        {{ $item->quantity }}
                                    </span>
                                </div>
                                <strong class="admin-product-subtotal">
                                    €{{ number_format($item->subtotal, 2) }}
                                </strong>
                            </div>
                        @endforeach
                    </div>


                    <div class="admin-order-total">
                        <span>Total</span>
                        <strong>
                            €{{ number_format($order->total, 2) }}
                        </strong>
                    </div>
                </section>
                {{-- CUSTOMER --}}
                <section class="admin-card">
                    <div class="admin-card-header">
                        <div>
                            <span class="admin-section-label">
                                CUSTOMER
                            </span>
                            <h2>Shipping details</h2>
                        </div>
                    </div>


                    <div class="admin-shipping-grid">
                        <div class="admin-detail">
                            <span>Name</span>
                            <strong>
                                {{ $order->customer_name }}
                            </strong>
                        </div>


                        <div class="admin-detail">
                            <span>Email</span>
                            <strong>
                                {{ $order->email }}
                            </strong>
                        </div>


                        <div class="admin-detail admin-detail-wide">
                            <span>Address</span>
                            <strong>
                                {{ $order->address }}
                            </strong>
                        </div>


                        <div class="admin-detail">
                            <span>City</span>
                            <strong>
                                {{ $order->city }}
                            </strong>
                        </div>


                        <div class="admin-detail">
                            <span>Postal code</span>
                            <strong>
                                {{ $order->postal_code }}
                            </strong>
                        </div>


                        <div class="admin-detail admin-detail-wide">
                            <span>Country</span>
                            <strong>
                                {{ $order->country }}
                            </strong>
                        </div>
                    </div>
                </section>
            </div>


            {{-- RIGHT COLUMN --}}
            <aside class="admin-order-sidebar">


                {{-- ORDER STATUS --}}
                <section class="admin-card">
                    <span class="admin-section-label">
                        FULFILMENT
                    </span>
                    <h2>Order status</h2>
                    <label
                        for="status"
                        class="admin-input-label"
                    >
                        Current status
                    </label>

                    <select
                        id="status"
                        wire:change="updateStatus($event.target.value)"
                        class="admin-status-select"
                    >
                        <option
                            value="pending"
                            @selected($order->status === 'pending')
                        >
                            Pending
                        </option>

                        <option
                            value="processing"
                            @selected($order->status === 'processing')
                        >
                            Processing
                        </option>

                        <option
                            value="shipped"
                            @selected($order->status === 'shipped')
                        >
                            Shipped
                        </option>

                        <option
                            value="completed"
                            @selected($order->status === 'completed')
                        >
                            Completed
                        </option>

                        <option
                            value="cancelled"
                            @selected($order->status === 'cancelled')
                        >
                            Cancelled
                        </option>
                    </select>
                </section>

                {{-- PAYMENT --}}
                <section class="admin-card">
                    <span class="admin-section-label">
                        PAYMENT
                    </span>
                    <h2>Payment details</h2>
                    <div class="admin-payment-row">
                        <span>Status</span>

                        @if ($order->payment_status === 'paid')
                            <strong class="admin-payment-paid">
                                ✓ Paid
                            </strong>
                        @elseif ($order->payment_status === 'unpaid')
                            <strong class="admin-payment-unpaid">
                                Unpaid
                            </strong>
                        @else
                            <strong>
                                {{ ucfirst($order->payment_status ?? 'Unknown') }}
                            </strong>
                        @endif
                    </div>


                    <div class="admin-payment-row">
                        <span>Amount</span>
                        <strong>
                            €{{ number_format($order->total, 2) }}
                        </strong>
                    </div>


                    @if ($order->stripe_session_id)
                        <div class="admin-stripe-reference">
                            <span>
                                Stripe Checkout Session
                            </span>
                            <code>
                                {{ $order->stripe_session_id }}
                            </code>
                        </div>
                    @else
                        <div class="admin-stripe-reference">
                            <span>
                                Stripe Checkout Session
                            </span>
                            <p>
                                No Stripe reference recorded.
                            </p>
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    </div>

</div>
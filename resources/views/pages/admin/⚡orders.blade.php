<?php

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] #[Title('Admin Orders')] class extends Component
{
    use WithPagination;
    public string $search = '';

    public string $statusFilter = '';
    public string $paymentFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        $search = trim($this->search);
        return Order::query()
            ->with('items')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                    ->where(
                        'customer_name',
                        'like',
                        "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        );

                    if (ctype_digit($search)) {
                        $query->orWhere(
                            'id',
                            (int) $search
                        );
                    }
            });

        })

        ->when(
            $this->statusFilter !== '',
            fn ($query) => $query->where(
                'status',
                $this->statusFilter
            )
        )

        ->when(
            $this->paymentFilter !== '',
            fn ($query) => $query->where(
                'payment_status',
                $this->paymentFilter
            )
        )
        ->latest()
        ->paginate(10);
}

    public function updateStatus(int $orderId, string $status): void
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

        $order = Order::findOrFail($orderId);

        $order->update([
            'status' => $status,
        ]);
    }
};

?>

<div class="admin-orders-page">
    <div class="admin-orders-shell">

        <div class="admin-orders-header">
            <div>
                <p class="admin-orders-kicker">ADMIN</p>

                <h1>Orders</h1>

                <p class="admin-orders-subtitle">
                    Manage customer orders, payments and fulfilment.
                </p>
            </div>
        </div>


    <div class="admin-orders-toolbar">

    {{-- SEARCH --}}
    <div class="admin-orders-search">

        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path
                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
            />
        </svg>

        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Search order, customer or email..."
            aria-label="Search orders"
        >

    </div>


    <div class="admin-orders-toolbar-divider"></div>


    {{-- STATUS FILTER --}}
    <div class="admin-orders-select-wrap">

        <span>
            STATUS
        </span>

        <select
            wire:model.live="statusFilter"
            aria-label="Filter by order status"
        >
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>


    {{-- PAYMENT FILTER --}}
    <div class="admin-orders-select-wrap">

        <span>
            PAYMENT
        </span>

        <select
            wire:model.live="paymentFilter"
            aria-label="Filter by payment status"
        >
            <option value="">All payments</option>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
        </select>

    </div>

</div>


    


        <div class="admin-orders-list">

            @forelse ($this->orders as $order)

                <article
                    class="admin-order-card"
                    wire:key="order-{{ $order->id }}"
                >

                    {{-- TOP ROW --}}
                    <div class="admin-order-top">

                        <div class="admin-order-identity">

                            <div>
                                <span class="admin-order-label">
                                    ORDER
                                </span>

                                <h2>
                                    #{{ $order->id }}
                                </h2>
                            </div>

                            <div class="admin-order-date">
                                {{ $order->created_at->format('d M Y') }}

                                <span>
                                    {{ $order->created_at->format('H:i') }}
                                </span>
                            </div>

                        </div>


                        <div class="admin-order-badges">

                            {{-- PAYMENT STATUS --}}
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


                            {{-- ORDER STATUS --}}
                            <span class="admin-badge admin-badge-status">
                                {{ ucfirst($order->status) }}
                            </span>

                        </div>

                    </div>


                    {{-- INFORMATION --}}
                    <div class="admin-order-content">

                        <div class="admin-order-customer">

                            <span class="admin-info-label">
                                CUSTOMER
                            </span>

                            <strong>
                                {{ $order->customer_name ?? 'Unknown' }}
                            </strong>

                            <span class="admin-customer-email">
                                {{ $order->email ?? 'N/A' }}
                            </span>

                        </div>


                        <div class="admin-order-total">

                            <span class="admin-info-label">
                                TOTAL
                            </span>

                            <strong>
                                €{{ number_format($order->total, 2) }}
                            </strong>

                        </div>


                        <div class="admin-order-status-control">

                            <label for="status-{{ $order->id }}">
                                ORDER STATUS
                            </label>

                            <select
                                id="status-{{ $order->id }}"
                                wire:change="updateStatus(
                                    {{ $order->id }},
                                    $event.target.value
                                )"
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
                        </div>
                    </div>


                    {{-- PRODUCTS --}}
                    <div class="admin-order-products">

                        <div class="admin-products-header">

                            <span class="admin-info-label">
                                PRODUCTS
                            </span>

                            <span>
                                {{ $order->items->sum('quantity') }}
                                {{ $order->items->sum('quantity') === 1 ? 'item' : 'items' }}
                            </span>

                        </div>


                        <div class="admin-product-list">

                            @foreach ($order->items as $item)

                                <div class="admin-product-row">

                                    <div>
                                        <strong>
                                            {{ $item->product_name }}
                                        </strong>

                                        <span>
                                            {{ $item->quantity }}
                                            ×
                                            €{{ number_format($item->price, 2) }}
                                        </span>
                                    </div>

                                    <strong>
                                        €{{ number_format($item->subtotal, 2) }}
                                    </strong>

                                </div>

                            @endforeach

                        </div>

                    </div>


                    {{-- FOOTER --}}
                    <div class="admin-order-footer">

                        <div class="admin-payment-reference">

                            @if ($order->stripe_session_id)

                                <span>
                                    Stripe payment recorded
                                </span>

                            @else

                                <span>
                                    No Stripe payment reference
                                </span>

                            @endif

                        </div>


                        <a
                            href="{{ route('admin.orders.show', $order) }}"
                            class="admin-view-order"
                        >
                            View Order →
                        </a>

                    </div>

                </article>


            @empty

                <div class="admin-empty-orders">

                    <h2>No orders yet</h2>

                    <p>
                        Customer orders will appear here.
                    </p>

                </div>

            @endforelse

        </div>

    </div>
</div>
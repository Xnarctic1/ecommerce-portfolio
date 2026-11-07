<?php

use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin')] #[Title('Admin Dashboard')] class extends Component      
{
    #[Computed]

public function stats(): array
{
    $paidOrders = Order::where('payment_status', 'paid');
    $lifetimeRevenue = (clone $paidOrders)->sum('total');
    $paidOrderCount = (clone $paidOrders)->count();
    return [
        'products' => Product::count(),
        'categories' => Category::count(),
        'low_stock' => Product::whereBetween('stock', [1, 5])->count(),
        'out_of_stock' => Product::where('stock', 0)->count(),
        'orders' => Order::count(),
        'orders_this_month' => Order::whereBetween(
            'created_at',
            [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]
        )->count(),
        'pending_orders' => Order::where(
            'status',
            'pending'
        )->count(),
        'revenue' => $lifetimeRevenue,
        'revenue_this_month' => (clone $paidOrders)
            ->whereBetween(
                'created_at',
               [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]
            )
            ->sum('total'),
        'average_order_value' => $paidOrderCount > 0
            ? $lifetimeRevenue / $paidOrderCount
            : 0,
    ];
}

    #[Computed]

public function revenueLastSevenDays(): array

{
    $startDate = now()->startOfDay()->subDays(6);
    $revenueByDate = Order::query()
        ->where('payment_status', 'paid')
        ->where('created_at', '>=', $startDate)
        ->get(['total', 'created_at'])
        ->groupBy(
            fn (Order $order) =>
                $order->created_at->format('Y-m-d')
        )

        ->map(
            fn ($orders) =>
                (float) $orders->sum('total')
        );

    return collect(range(0, 6))
        ->map(function (
            int $offset
        ) use (
            $startDate,
            $revenueByDate
        ) {
            $date = $startDate
                ->copy()
                ->addDays($offset);
            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'day' => $date->format('d M'),
                'revenue' => (float) (
                    $revenueByDate[
                        $date->format('Y-m-d')
                    ] ?? 0
                ),
            ];
        })
        ->all();
}

    #[Computed]
    public function recentOrders()
    {
        return Order::latest()
        ->limit(5)
        ->get();
    }

};

?>

<div class="admin-dashboard-page">
    <div class="admin-dashboard-shell">
        
        {{-- HEADER --}}
        <div class="admin-dashboard-header">

            <div>
                <p class="admin-dashboard-kicker">
                    ADMIN
                </p>

                <h1>Dashboard</h1>

                <p class="admin-dashboard-subtitle">
                    Overview of your store, inventory and recent orders.
                </p>
            </div>

        </div>


        
        {{-- MAIN STATS --}}
    <div class="admin-stats-grid">

    {{-- REVENUE THIS MONTH --}}
    <div class="admin-stat-card">

        <span>
            REVENUE THIS MONTH
        </span>

        <strong>
            €{{ number_format(
                $this->stats['revenue_this_month'],
                2
            ) }}
        </strong>

        <p>
            Paid revenue for {{ now()->format('F') }}
        </p>

    </div>


    {{-- ORDERS THIS MONTH --}}
    <div class="admin-stat-card">

        <span>
            ORDERS THIS MONTH
        </span>

        <strong>
            {{ $this->stats['orders_this_month'] }}
        </strong>

        <p>
            Orders placed in {{ now()->format('F') }}
        </p>

    </div>


    {{-- AVERAGE ORDER VALUE --}}
    <div class="admin-stat-card">

        <span>
            AVERAGE ORDER VALUE
        </span>

        <strong>
            €{{ number_format(
                $this->stats['average_order_value'],
                2
            ) }}
        </strong>

        <p>
            Average across paid orders
        </p>

    </div>


    {{-- LIFETIME REVENUE --}}
    <div class="admin-stat-card">

        <span>
            LIFETIME REVENUE
        </span>

        <strong>
            €{{ number_format(
                $this->stats['revenue'],
                2
            ) }}
        </strong>

        <p>
            Revenue from all paid orders
        </p>

    </div>


    {{-- TOTAL ORDERS --}}
    <div class="admin-stat-card">

        <span>
            TOTAL ORDERS
        </span>

        <strong>
            {{ $this->stats['orders'] }}
        </strong>

        <p>
            All customer orders
        </p>

    </div>


    {{-- PENDING ORDERS --}}
    <div class="admin-stat-card admin-stat-warning">

        <span>
            PENDING ORDERS
        </span>

        <strong>
            {{ $this->stats['pending_orders'] }}
        </strong>

        <p>
            Orders awaiting processing
        </p>

    </div>
</div>


{{-- REVENUE CHART --}}
@php
    $revenueDays = $this->revenueLastSevenDays;

    $maxRevenue = max(
        collect($revenueDays)
            ->pluck('revenue')
            ->max() ?? 0,
        1
    );

    $sevenDayRevenue = collect($revenueDays)
        ->sum('revenue');
@endphp


<section class="admin-revenue-chart-card">

    {{-- HEADER --}}
    <div class="admin-revenue-chart-header">

        <div>

            <span class="admin-section-label">
                SALES
            </span>

            <h2>
                Revenue — Last 7 Days
            </h2>

        </div>


        <div class="admin-revenue-chart-total">

            <span>
                7 DAY TOTAL
            </span>

            <strong>
                €{{ number_format(
                    $sevenDayRevenue,
                    2
                ) }}
            </strong>

        </div>

    </div>


    {{-- CHART --}}
    <div class="admin-revenue-chart">

        @foreach ($revenueDays as $day)

            @php
                $height = $day['revenue'] > 0
                    ? max(
                        ($day['revenue'] / $maxRevenue) * 100,
                        4
                    )
                    : 0;
            @endphp


            <div class="admin-revenue-day">

                {{-- VALUE --}}
                <div class="admin-revenue-value">

                    @if ($day['revenue'] > 0)

                        €{{ number_format(
                            $day['revenue'],
                            0
                        ) }}

                    @else

                        €0

                    @endif

                </div>


                {{-- BAR --}}
                <div class="admin-revenue-bar-track">

                    <div
                        class="admin-revenue-bar"
                        style="height: {{ $height }}%;"
                        title="{{ $day['day'] }}: €{{ number_format($day['revenue'], 2) }}"
                    ></div>

                </div>


                {{-- DATE --}}
                <div class="admin-revenue-date">

                    <strong>
                        {{ $day['label'] }}
                    </strong>

                    <span>
                        {{ $day['day'] }}
                    </span>

                </div>

            </div>

        @endforeach

    </div>

</section>
        


        {{-- LOWER GRID --}}
        <div class="admin-dashboard-grid">

            {{-- RECENT ORDERS --}}
            <section class="admin-dashboard-card admin-recent-orders">

                <div class="admin-card-header">

                    <div>
                        <span class="admin-section-label">
                            ACTIVITY
                        </span>

                        <h2>
                            Recent Orders
                        </h2>
                    </div>


                    <a href="{{ route('admin.orders') }}">
                        View all →
                    </a>

                </div>


                @forelse ($this->recentOrders as $order)

                    <a
                        href="{{ route('admin.orders.show', $order) }}"
                        class="admin-recent-order"
                    >

                        <div class="admin-recent-order-id">

                            <strong>
                                #{{ $order->id }}
                            </strong>

                            <span>
                                {{ $order->created_at->format('d M Y') }}
                            </span>

                        </div>


                        <div class="admin-recent-customer">

                            <strong>
                                {{ $order->customer_name ?? 'Unknown' }}
                            </strong>

                            <span>
                                {{ $order->email ?? 'No email' }}
                            </span>

                        </div>


                        <div class="admin-recent-status">

                            @if ($order->payment_status === 'paid')

                                <span class="admin-payment-paid">
                                    Paid
                                </span>

                            @elseif ($order->payment_status === 'unpaid')

                                <span class="admin-payment-unpaid">
                                    Unpaid
                                </span>

                            @else

                                <span class="admin-payment-neutral">
                                    {{ ucfirst($order->payment_status ?? 'Unknown') }}
                                </span>

                            @endif

                        </div>


                        <div class="admin-recent-order-status">

                            <span>
                                {{ ucfirst($order->status) }}
                            </span>

                        </div>


                        <strong class="admin-recent-total">
                            €{{ number_format($order->total, 2) }}
                        </strong>

                    </a>


                @empty

                    <div class="admin-dashboard-empty">

                        <strong>No orders yet</strong>

                        <p>
                            Recent customer orders will appear here.
                        </p>

                    </div>

                @endforelse

            </section>


            {{-- RIGHT COLUMN --}}
            <aside class="admin-dashboard-sidebar">


                {{-- CATALOGUE --}}
                <section class="admin-dashboard-card">

                    <span class="admin-section-label">
                        CATALOGUE
                    </span>

                    <h2>
                        Store inventory
                    </h2>


                    <div class="admin-summary-row">

                        <span>Products</span>

                        <strong>
                            {{ $this->stats['products'] }}
                        </strong>

                    </div>


                    <div class="admin-summary-row">

                        <span>Categories</span>

                        <strong>
                            {{ $this->stats['categories'] }}
                        </strong>

                    </div>


                    <div class="admin-summary-row">

                        <span>Low stock</span>

                        <strong>
                            {{ $this->stats['low_stock'] }}
                        </strong>

                    </div>


                    <div class="admin-summary-row">

                        <span>Out of stock</span>

                        <strong>
                            {{ $this->stats['out_of_stock'] }}
                        </strong>

                    </div>

                </section>


                {{-- QUICK ACTIONS --}}
                <section class="admin-dashboard-card">

                    <span class="admin-section-label">
                        QUICK ACTIONS
                    </span>

                    <h2>
                        Manage store
                    </h2>


                    <div class="admin-quick-actions">

                        <a href="{{ route('admin.products') }}">

                            <div>
                                <strong>Products</strong>

                                <span>
                                    Inventory & availability
                                </span>
                            </div>

                            <span>→</span>

                        </a>


                        <a href="{{ route('admin.categories') }}">

                            <div>
                                <strong>Categories</strong>

                                <span>
                                    Catalogue organisation
                                </span>
                            </div>

                            <span>→</span>

                        </a>


                        <a href="{{ route('admin.orders') }}">

                            <div>
                                <strong>Orders</strong>

                                <span>
                                    Payments & fulfilment
                                </span>
                            </div>

                            <span>→</span>

                        </a>

                    </div>

                </section>

            </aside>

        </div>

    </div>

</div>
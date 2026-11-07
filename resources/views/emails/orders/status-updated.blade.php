<x-mail::message>
# @switch($order->status)
@case('pending') Order received @break
@case('processing') Order is being processed @break
@case('shipped') Order has shipped @break
@case('completed') Order completed @break
@case('cancelled') Order cancelled @break
@default Order status updated
@endswitch

Hi {{ $order->customer_name }},

@switch($order->status)
    @case('processing')
Your order **#{{ $order->id }}** is now being processed.

We're preparing your items and will let you know when your order has shipped.
        @break

    @case('shipped')
Your order **#{{ $order->id }}** has shipped and is on its way.
        @break

    @case('completed')
Your order **#{{ $order->id }}** has been completed.

Thank you for shopping with us.
        @break

    @case('cancelled')
Your order **#{{ $order->id }}** has been cancelled.
        @break

    @default
The status of your order **#{{ $order->id }}** has been updated to **{{ ucfirst($order->status) }}**.
@endswitch

<x-mail::button :url="route('orders.show', $order)">
View your order
</x-mail::button>

**Current status:** {{ ucfirst($order->status) }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
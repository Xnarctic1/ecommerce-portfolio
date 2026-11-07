<x-mail::message>
# Thank you for your order, {{ $order->customer_name }}!

Your payment was successful and we've received your order.

**Order #{{ $order->id }}**  
Placed on {{ $order->created_at->format('d M Y') }}

<x-mail::panel>
@foreach ($order->items as $item)
**{{ $item->product_name }}**  
{{ $item->quantity }} × €{{ number_format($item->price, 2) }} — **€{{ number_format($item->subtotal, 2) }}**

@endforeach

**Order total: €{{ number_format($order->total, 2) }}**
</x-mail::panel>

## Shipping address

{{ $order->customer_name }}  
{{ $order->address }}  
{{ $order->postal_code }} {{ $order->city }}  
{{ $order->country }}

<x-mail::button :url="route('orders.show', $order)">
View your order
</x-mail::button>

We'll update your order status as it moves through processing and shipping.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

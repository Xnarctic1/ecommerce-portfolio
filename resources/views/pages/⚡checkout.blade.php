<?php

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\StripeClient;

new #[Layout('layouts.public')] #[Title('Checkout')] class extends Component
{
    public string $customer_name = '';
    public string $email = '';
    public string $address = '';
    public string $city = '';
    public string $postal_code = '';
    public string $country = '';
    

    public function mount(): void
    {
        $user = auth()->user();
        $this->customer_name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->address = $user->address ?? '';
        $this->city = $user->city ?? '';
        $this->postal_code = $user->postal_code ?? '';
        $this->country = $user->country ?? '';
    }

    public function cart(): array
    {
        return session()->get('cart', []);
    }

    public function total(): float
    {
        $total = 0;
        foreach ($this->cart() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

public function checkout(): void
{
    $cart = $this->cart();

    if (empty($cart)) {
        return;
    }

    $validated = $this->validate([
        'customer_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'address' => ['required', 'string', 'max:255'],
        'city' => ['required', 'string', 'max:255'],
        'postal_code' => ['required', 'string', 'max:50'],
        'country' => ['required', 'string', 'max:255'],
    ]);

    $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

    foreach ($cart as $productId => $item) {
        $product = $products->get($productId);

        if (! $product || ! $product->is_active) {
            $this->addError('stock', 'An item in your cart is no longer available. Remove it before checking out.');

            return;
        }

        if ($product->stock < $item['quantity']) {
            $this->addError(
                'stock',
                "Not enough stock for {$product->name}. Available: {$product->stock}."
            );

            return;
        }
    }

    $stripe = new StripeClient(config('services.stripe.secret'));

    $lineItems = [];
    $cartSnapshot = [];

    foreach ($cart as $productId => $item) {
        $product = $products->get($productId);

        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $product->name,
                ],
                'unit_amount' => (int) round($product->price * 100),
            ],
            'quantity' => $item['quantity'],
        ];

    $cartSnapshot[] = [
        'product_id' => $product->id,
        'unit_amount' => (int) round($product->price * 100),
        'quantity' => $item['quantity'],
        ];

    }

    $session = $stripe->checkout->sessions->create([
        'mode' => 'payment',
        'customer_email' => $validated['email'],
        'line_items' => $lineItems,
        'success_url' => route('checkout.success') . '?stripe_session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('checkout'),
        'metadata' => [
            'user_id' => (string) auth()->id(),
            'customer_name' => $validated['customer_name'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'],
            'cart' => json_encode($cartSnapshot),
        ],
    ]);

    $this->redirect($session->url);
}

};

?>

<div class="checkout-page" style="max-width: 1180px; margin: 0 auto; padding: 40px 24px 60px;">    <div style="margin-bottom: 32px;">
        <a
            href="{{ route('cart') }}"
            style="
                display: inline-flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 18px;
                color: #555;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
            "
        >
            ← Back to cart
        </a>

        <h1 class="checkout-title" style="font-size: 36px; font-weight: 700; line-height: 1.1; margin: 0;">            Checkout
        </h1>

        <p style="margin-top: 8px; color: #737373; font-size: 15px;">
            Enter your shipping details and review your order.
        </p>
    </div>

    <div
        class="checkout-grid"
        style="
            display: grid;
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 36px;
            align-items: start;
        "
    >
        <div
            class="checkout-shipping"
            style="
                background: #fff;
                border: 1px solid #e7e7e7;
                border-radius: 14px;
                padding: 28px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            "
        >
            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 22px; font-weight: 700; margin: 0 0 6px;">
                    Shipping information
                </h2>

                <p style="margin: 0; color: #777; font-size: 14px;">
                    We’ll use these details to prepare your order.
                </p>
            </div>

            <div
                style="
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 18px;
                "
            >
                <div style="grid-column: 1 / -1;">
                    <label for="customer_name" style="display: block; font-weight: 600; margin-bottom: 7px;">
                        Full name
                    </label>

                    <input
                        id="customer_name"
                        type="text"
                        wire:model="customer_name"
                        placeholder="John Smith"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('customer_name')
                        <p style="color: #b91c1c; margin-top: 6px; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div style="grid-column: 1 / -1;">
                    <label for="email" style="display: block; font-weight: 600; margin-bottom: 7px;">
                        Email address
                    </label>

                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        placeholder="john@example.com"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('email')
                        <p style="color: #b91c1c; margin-top: 6px; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div style="grid-column: 1 / -1;">
                    <label for="address" style="display: block; font-weight: 600; margin-bottom: 7px;">
                        Street address
                    </label>

                    <input
                        id="address"
                        type="text"
                        wire:model="address"
                        placeholder="123 Main Street"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('address')
                        <p style="color: #b91c1c; margin-top: 6px; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="city" style="display: block; font-weight: 600; margin-bottom: 7px;">
                        City
                    </label>

                    <input
                        id="city"
                        type="text"
                        wire:model="city"
                        placeholder="Valletta"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('city')
                        <p style="color: #b91c1c; margin-top: 6px; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="postal_code" style="display: block; font-weight: 600; margin-bottom: 7px;">
                        Postal code
                    </label>

                    <input
                        id="postal_code"
                        type="text"
                        wire:model="postal_code"
                        placeholder="VLT 1111"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('postal_code')
                        <p style="color: #b91c1c; margin-top: 6px; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div style="grid-column: 1 / -1;">
                    <label for="country" style="display: block; font-weight: 600; margin-bottom: 7px;">
                        Country
                    </label>

                    <input
                        id="country"
                        type="text"
                        wire:model="country"
                        placeholder="Malta"
                        style="
                            width: 100%;
                            border: 1px solid #d7d7d7;
                            border-radius: 8px;
                            padding: 12px 14px;
                            font-size: 15px;
                            outline: none;
                        "
                    >

                    @error('country')
                        <p style="color: #b91c1c; margin-top: 6px; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            @error('stock')
                <div
                    style="
                        margin-top: 20px;
                        padding: 12px 14px;
                        background: #fef2f2;
                        border: 1px solid #fecaca;
                        border-radius: 8px;
                        color: #b91c1c;
                    "
                >
                    {{ $message }}
                </div>
            @enderror

            <button
                type="button"
                wire:click="checkout"
                class="checkout-payment-button"
                style="
                    width: 100%;
                    margin-top: 26px;
                    background: #111;
                    color: #fff;
                    padding: 14px 18px;
                    border: 0;
                    border-radius: 8px;
                    font-size: 15px;
                    font-weight: 700;
                    cursor: pointer;
                "
            >
                Continue to payment
            </button>
        </div>

        <div
            class="checkout-summary"
            style="
                background: #111;
                color: #fff;
                border-radius: 14px;
                padding: 26px;
                position: sticky;
                top: 24px;
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            "
        >
            <h2 style="font-size: 21px; font-weight: 700; margin: 0 0 20px;">
                Order summary
            </h2>

            @if (count($this->cart()))
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach ($this->cart() as $item)
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                gap: 18px;
                                padding-bottom: 16px;
                                border-bottom: 1px solid rgba(255, 255, 255, 0.12);
                            "
                        >
                            <div>
                                <p style="margin: 0; font-weight: 600;">
                                    {{ $item['name'] }}
                                </p>

                                <p style="margin: 4px 0 0; color: #b8b8b8; font-size: 13px;">
                                    Quantity: {{ $item['quantity'] }}
                                </p>
                            </div>

                            <div style="font-weight: 600; white-space: nowrap;">
                                €{{ number_format($item['price'] * $item['quantity'], 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #b8b8b8;">
                    Your cart is empty.
                </p>
            @endif

            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 22px;
                    padding-top: 18px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                "
            >
                <span style="font-size: 15px; color: #d4d4d4;">
                    Total
                </span>

                <strong style="font-size: 26px;">
                    €{{ number_format($this->total(), 2) }}
                </strong>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 900px) {
        .checkout-grid {
            grid-template-columns: 1fr !important;
        }

        .checkout-grid > div:last-child {
            position: static !important;
        }
    }

    @media (max-width: 640px) {

    .checkout-page {
        padding: 20px 14px 44px !important;
    }

    .checkout-title {
        font-size: 29px !important;
    }

    .checkout-grid {
        gap: 20px !important;
    }

    .checkout-shipping {
        padding: 18px !important;
        min-width: 0;
    }

    .checkout-summary {
        padding: 20px !important;
        min-width: 0;
    }

    .checkout-grid > div:first-child > div:nth-child(2) {
        grid-template-columns: 1fr !important;
    }

    .checkout-shipping input {
        box-sizing: border-box;
        min-width: 0;
        max-width: 100%;
        font-size: 16px !important;
    }

    .checkout-shipping > div:nth-child(2) > div {
        min-width: 0;
    }

    .checkout-summary p {
        overflow-wrap: anywhere;
    }

    .checkout-payment-button {
        min-height: 48px;
    }
}
</style>

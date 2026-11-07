<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;


class StripeOrderService
{
    public function createFromCheckoutSession(object $stripeSession): ?Order
    {
        if (($stripeSession->payment_status ?? null) !== 'paid') {
            return null;
        }

        $existingOrder = Order::where(
            'stripe_session_id',
            $stripeSession->id
        )->first();

        if ($existingOrder) {
            return $existingOrder;
        }

        $userId = (int) ($stripeSession->metadata->user_id ?? 0);

        if ($userId <= 0) {
            throw new RuntimeException(
                'Stripe Checkout Session is missing a valid user_id.'
            );
        }

        $cart = json_decode(
            (string) ($stripeSession->metadata->cart ?? ''),
            true
        );
        if (! is_array($cart) || empty($cart)) {
            throw new RuntimeException(
        'Stripe Checkout Session is missing valid cart metadata.'
    );
}

// Verify Stripe currency
if (($stripeSession->currency ?? null) !== 'eur') {
    throw new RuntimeException(
        'Unexpected Stripe payment currency.'
    );
}

// Retrieve the amount paid in cents
$paidAmountCents = (int) ($stripeSession->amount_total ?? -1);

if ($paidAmountCents < 0) {
    throw new RuntimeException(
        'Stripe session is missing its total amount.'
    );
}

$order = DB::transaction(function () use (
            $cart,
            $stripeSession,
            $userId,
            $paidAmountCents
        ) {
            $totalCents = 0;

            $lockedProducts = [];

        foreach ($cart as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitAmountCents = $item['unit_amount'] ?? null;
            
            if (
                $productId <= 0 ||
                $quantity <= 0 ||
                ! is_int($unitAmountCents) ||
                $unitAmountCents < 0
                ) {
                throw new RuntimeException(
                    'Stripe Checkout Session contains invalid cart data.'
                    );
            }

           $product = Product::lockForUpdate()
                ->findOrFail($productId);

           if ($product->stock < $quantity) {
                throw new RuntimeException(
                    "Not enough stock for {$product->name}."
                );
            }

            $lockedProducts[$productId] = $product;

            $totalCents += $unitAmountCents * $quantity;
        }

        if ($totalCents !== $paidAmountCents) {
            throw new RuntimeException(
                'Stripe payment amount does not match the cart snapshot.'
                );
        }


            $order = Order::create([
                'user_id' => $userId,
                'total' => $totalCents / 100,
                'status' => 'pending',
                'customer_name' => $stripeSession->metadata->customer_name,
                'email' => $stripeSession->customer_details->email ?? null,
                'address' => $stripeSession->metadata->address,
                'city' => $stripeSession->metadata->city,
                'postal_code' => $stripeSession->metadata->postal_code,
                'country' => $stripeSession->metadata->country,
                'stripe_session_id' => $stripeSession->id,
                'payment_status' => $stripeSession->payment_status,
                ]);


            foreach ($cart as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];
                $unitPrice = $item['unit_amount'] / 100;
                $product = $lockedProducts[$productId];
                
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $unitPrice * $quantity,
                ]);

            $product->decrement('stock', $quantity);
            }


            return $order;
        });

        $order->loadMissing('items');

if ($order->email) {
    Mail::to($order->email)->send(
        new OrderConfirmation($order)
    );
}

return $order;


    }
}
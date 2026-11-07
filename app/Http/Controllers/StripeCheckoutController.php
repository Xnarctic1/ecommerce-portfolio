<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeCheckoutController extends Controller
{
    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('stripe_session_id');

        if (! $sessionId) {
            return redirect()->route('orders');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $stripeSession = $stripe->checkout->sessions->retrieve(
            $sessionId
        );

        if ($stripeSession->payment_status !== 'paid') {
            return redirect()->route('checkout');
        }

        if (
            (int) ($stripeSession->metadata->user_id ?? 0)
            !== auth()->id()
        ) {
            abort(403);
        }

        $order = Order::where(
            'stripe_session_id',
        $stripeSession->id
            )->first();

        session()->forget('cart');

        if (! $order) {
            return redirect()
                ->route('orders')
                ->with(
                    'status',
                    'Payment received. Your order is being processed.'
                );
        }

        return redirect()->route('orders.show', $order);
        }
}
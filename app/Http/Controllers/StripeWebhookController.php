<?php

namespace App\Http\Controllers;

use App\Services\StripeOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
   public function handle(Request $request,StripeOrderService $stripeOrderService): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (! $signature || ! $webhookSecret) {
            return response('Webhook configuration missing.', 400);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            return response('Invalid webhook signature.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $stripeOrderService->createFromCheckoutSession($session);
            }

        return response('Webhook received.', 200);
    }
}
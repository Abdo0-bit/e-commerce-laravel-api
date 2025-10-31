<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle payment intent succeeded webhook.
     */
    protected function handlePaymentIntentSucceeded(array $payload): void
    {
        $paymentIntent = $payload['data']['object'];

        // Find the order by payment intent ID
        $order = Order::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
        }
    }

    /**
     * Handle payment intent payment failed webhook.
     */
    protected function handlePaymentIntentPaymentFailed(array $payload): void
    {
        $paymentIntent = $payload['data']['object'];

        // Find the order by payment intent ID
        $order = Order::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if ($order) {
            $order->update([
                'payment_status' => 'failed',
            ]);
        }
    }

    /**
     * Handle payment intent requires action webhook.
     */
    protected function handlePaymentIntentRequiresAction(array $payload): void
    {
        $paymentIntent = $payload['data']['object'];

        // Find the order by payment intent ID
        $order = Order::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if ($order) {
            $order->update([
                'payment_status' => 'requires_action',
            ]);
        }
    }
}

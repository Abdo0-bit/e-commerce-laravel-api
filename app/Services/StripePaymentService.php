<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\PaymentServiceInterface;
use Laravel\Cashier\Cashier;
use Stripe\PaymentIntent;

class StripePaymentService implements PaymentServiceInterface
{
    /**
     * Create a payment intent for the given amount and user.
     */
    public function createPaymentIntent(User $user, int $amountInCents, array $metadata = []): PaymentIntent
    {
        // Ensure the user has a Stripe customer ID
        if (! $user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        $payment = $user->pay($amountInCents, [
            'metadata' => $metadata,
        ]);

        return $payment->asStripePaymentIntent();
    }

    /**
     * Confirm a payment intent.
     */
    public function confirmPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->confirm($paymentIntentId);
    }

    /**
     * Retrieve a payment intent.
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Cancel a payment intent.
     */
    public function cancelPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->cancel($paymentIntentId);
    }

    /**
     * Convert amount from dollars to cents.
     */
    public function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert amount from cents to dollars.
     */
    public function toDollars(int $amountInCents): float
    {
        return $amountInCents / 100;
    }
}

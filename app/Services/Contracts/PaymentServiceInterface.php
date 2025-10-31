<?php

namespace App\Services\Contracts;

use App\Models\User;
use Stripe\PaymentIntent;

interface PaymentServiceInterface
{
    /**
     * Create a payment intent for the given amount and user.
     */
    public function createPaymentIntent(User $user, int $amountInCents, array $metadata = []): PaymentIntent;

    /**
     * Confirm a payment intent.
     */
    public function confirmPaymentIntent(string $paymentIntentId): PaymentIntent;

    /**
     * Retrieve a payment intent.
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent;

    /**
     * Cancel a payment intent.
     */
    public function cancelPaymentIntent(string $paymentIntentId): PaymentIntent;

    /**
     * Convert amount from dollars to cents.
     */
    public function toCents(float $amount): int;

    /**
     * Convert amount from cents to dollars.
     */
    public function toDollars(int $amountInCents): float;
}

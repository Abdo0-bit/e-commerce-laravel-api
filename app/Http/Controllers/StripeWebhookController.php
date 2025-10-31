<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

/**
 * @OA\Post(
 *     path="/stripe/webhook",
 *     operationId="stripeWebhook",
 *     tags={"Webhooks"},
 *     summary="Stripe webhook endpoint",
 *     description="Handles Stripe webhook events to automatically update order payment statuses. This endpoint is called by Stripe when payment events occur and requires proper webhook signature verification.",
 *     @OA\RequestBody(
 *         required=true,
 *         description="Stripe webhook payload",
 *         @OA\JsonContent(
 *             @OA\Property(property="type", type="string", example="payment_intent.succeeded", description="Event type"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="object",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="pi_3SOMF4EFDrXcJGWZ18qFfsrp", description="Payment intent ID"),
 *                     @OA\Property(property="amount", type="integer", example=4999, description="Amount in cents"),
 *                     @OA\Property(property="currency", type="string", example="usd", description="Currency code"),
 *                     @OA\Property(property="status", type="string", example="succeeded", description="Payment status")
 *                 )
 *             ),
 *             @OA\Examples(
 *                 example="payment_succeeded",
 *                 summary="Payment Intent Succeeded",
 *                 value={
 *                     "type": "payment_intent.succeeded",
 *                     "data": {
 *                         "object": {
 *                             "id": "pi_3SOMF4EFDrXcJGWZ18qFfsrp",
 *                             "amount": 4999,
 *                             "currency": "usd",
 *                             "status": "succeeded"
 *                         }
 *                     }
 *                 }
 *             ),
 *             @OA\Examples(
 *                 example="payment_failed",
 *                 summary="Payment Intent Failed",
 *                 value={
 *                     "type": "payment_intent.payment_failed",
 *                     "data": {
 *                         "object": {
 *                             "id": "pi_3SOMF4EFDrXcJGWZ18qFfsrp",
 *                             "amount": 4999,
 *                             "currency": "usd",
 *                             "status": "requires_payment_method"
 *                         }
 *                     }
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Webhook processed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Webhook processed successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid webhook signature",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Invalid webhook signature")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order not found for payment intent",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="warning"),
 *             @OA\Property(property="message", type="string", example="Order not found for payment intent")
 *         )
 *     )
 * )
 */
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

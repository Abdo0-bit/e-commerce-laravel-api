<?php

namespace Tests\Feature;

use App\Http\Controllers\StripeWebhookController;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_handles_payment_succeeded(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_method' => 'stripe',
            'payment_status' => 'unpaid',
            'stripe_payment_intent_id' => 'pi_test123',
        ]);

        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'amount' => 2000,
                    'currency' => 'usd',
                    'status' => 'succeeded',
                ],
            ],
        ];

        // Create webhook controller and call handler method directly
        $webhookController = new StripeWebhookController();
        $method = new \ReflectionMethod($webhookController, 'handlePaymentIntentSucceeded');
        $method->setAccessible(true);
        $method->invoke($webhookController, $payload);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
    }

    public function test_webhook_handles_payment_failed(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_method' => 'stripe',
            'payment_status' => 'unpaid',
            'stripe_payment_intent_id' => 'pi_test456',
        ]);

        $payload = [
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test456',
                    'amount' => 2000,
                    'currency' => 'usd',
                    'status' => 'requires_payment_method',
                ],
            ],
        ];

        // Create webhook controller and call handler method directly
        $webhookController = new StripeWebhookController();
        $method = new \ReflectionMethod($webhookController, 'handlePaymentIntentPaymentFailed');
        $method->setAccessible(true);
        $method->invoke($webhookController, $payload);
        
        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
    }

    public function test_webhook_handles_unknown_payment_intent(): void
    {
        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_nonexistent',
                    'amount' => 2000,
                    'currency' => 'usd',
                    'status' => 'succeeded',
                ],
            ],
        ];

        // Create webhook controller and call handler method directly
        $webhookController = new StripeWebhookController();
        $method = new \ReflectionMethod($webhookController, 'handlePaymentIntentSucceeded');
        $method->setAccessible(true);

        // This should not throw any errors even if order is not found
        $method->invoke($webhookController, $payload);
        $this->assertTrue(true);
    }

    public function test_webhook_handles_requires_action(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_method' => 'stripe',
            'payment_status' => 'unpaid',
            'stripe_payment_intent_id' => 'pi_test789',
        ]);

        $payload = [
            'type' => 'payment_intent.requires_action',
            'data' => [
                'object' => [
                    'id' => 'pi_test789',
                    'amount' => 2000,
                    'currency' => 'usd',
                    'status' => 'requires_action',
                ],
            ],
        ];

        // Create webhook controller and call handler method directly
        $webhookController = new StripeWebhookController();
        $method = new \ReflectionMethod($webhookController, 'handlePaymentIntentRequiresAction');
        $method->setAccessible(true);
        $method->invoke($webhookController, $payload);
        
        $order->refresh();
        $this->assertEquals('requires_action', $order->payment_status);
    }
}

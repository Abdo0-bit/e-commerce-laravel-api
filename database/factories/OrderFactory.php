<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_street' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_state' => fake()->state(),
            'shipping_postal_code' => fake()->postcode(),
            'payment_method' => fake()->randomElement(['cod', 'stripe']),
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total_amount' => fake()->randomFloat(2, 20, 500),
        ];
    }

    /**
     * Create an order with Stripe payment method.
     */
    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'stripe',
            'stripe_payment_intent_id' => 'pi_'.fake()->uuid(),
            'stripe_client_secret' => 'pi_'.fake()->uuid().'_secret_'.fake()->uuid(),
            'stripe_payment_metadata' => [
                'payment_intent_id' => 'pi_'.fake()->uuid(),
                'amount' => fake()->numberBetween(1000, 50000), // Amount in cents
                'currency' => 'usd',
            ],
        ]);
    }

    /**
     * Create an order with COD payment method.
     */
    public function cod(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cod',
        ]);
    }
}

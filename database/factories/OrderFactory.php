<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
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
            'user_id'=> User::factory(),
            'first_name'=> fake()->firstName(),
            'last_name'=> fake()->lastName(),
            'shipping_phone'=> fake()->phoneNumber(),
            'shipping_street'  => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_state' => fake()->state(),
            'shipping_postal_code' => fake()->postcode(),
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total_amount' => fake()->randomFloat(2, 20, 500),
        ];
    }
}

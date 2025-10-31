<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\Client\CartServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StripePaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test environment variables
        config(['services.stripe.key' => 'pk_test_example']);
        config(['services.stripe.secret' => 'sk_test_example']);
        config(['broadcasting.default' => 'log']); // Disable broadcasting in tests
    }

    public function test_user_can_create_order_with_cod_payment_method(): void
    {
        // Create test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create test category and product
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 99.99,
            'is_active' => true,
        ]);

        // Authenticate user first
        $this->actingAs($user, 'sanctum');

        // Add product to cart
        $cartService = app(CartServiceInterface::class);
        $cartService->add($product, 2);

        // Verify cart has items
        $cart = $cartService->getCart();
        $this->assertNotEmpty($cart['items'], 'Cart should not be empty after adding product');

        // Create order with COD payment method
        $orderData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'shipping_phone' => '+1234567890',
            'shipping_street' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_state' => 'NY',
            'shipping_postal_code' => '10001',
            'payment_method' => 'cod',
        ];

        $response = $this->postJson('/api/client/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => [
                    'id',
                    'payment_method',
                    'total_amount',
                ],
            ]);

        // Assert order was created with correct payment method
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_stripe_payment_method_is_valid_in_validation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $orderData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'shipping_phone' => '+1234567890',
            'shipping_street' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_state' => 'NY',
            'shipping_postal_code' => '10001',
            'payment_method' => 'stripe', // This should be valid
        ];

        // Create empty cart first
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $cartService = app(CartServiceInterface::class);
        $cartService->add($product, 1);

        $response = $this->postJson('/api/client/orders', $orderData);

        // Should not have validation errors for payment_method but will fail due to Stripe service call
        // So we check that it's not a 422 validation error
        $this->assertNotEquals(422, $response->getStatusCode());
    }

    public function test_order_validation_rejects_invalid_payment_method(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $orderData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'shipping_phone' => '+1234567890',
            'shipping_street' => '123 Main St',
            'shipping_city' => 'New York',
            'payment_method' => 'invalid_method', // This should fail validation
        ];

        $response = $this->postJson('/api/client/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }
}

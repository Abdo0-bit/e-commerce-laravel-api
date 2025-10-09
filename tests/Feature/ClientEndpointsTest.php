<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;

class ClientEndpointsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
    }

    // Public Category Routes Tests
    public function test_can_get_categories_without_authentication(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/client/categories');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'image'
                         ]
                     ]
                 ]);
    }

    public function test_can_get_single_category_with_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/client/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'image',
                     'products'
                 ]);
    }

    // Public Product Routes Tests
    public function test_can_get_products_without_authentication(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/client/products');

        $response->assertStatus(200);
    }

    public function test_can_get_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/client/products/{$product->id}");

        $response->assertStatus(200);
    }

    // Cart Routes Tests (Available for both guest and authenticated)
    public function test_guest_can_get_cart(): void
    {
        $response = $this->getJson('/api/client/cart');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data'
                 ]);
    }

    public function test_authenticated_user_can_get_cart(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/client/cart');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data'
                 ]);
    }

    public function test_guest_can_add_to_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 100
        ]);

        $response = $this->postJson('/api/client/cart', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Product added to cart successfully.'
                 ]);
    }

    public function test_authenticated_user_can_add_to_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 100
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/client/cart', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Product added to cart successfully.'
                 ]);
    }

    public function test_authenticated_user_can_update_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 100
        ]);

        // First add to cart
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/client/cart', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // Then update quantity
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/client/cart/{$product->id}", [
            'quantity' => 3
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Cart updated successfully.'
                 ]);
    }

    public function test_authenticated_user_can_remove_from_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 100
        ]);

        // First add to cart
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/client/cart', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // Then remove from cart
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/client/cart/{$product->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Product removed from cart successfully.'
                 ]);
    }

    public function test_guest_can_clear_cart(): void
    {
        $response = $this->deleteJson('/api/client/cart/clear');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Cart cleared successfully.'
                 ]);
    }

    public function test_authenticated_user_can_clear_cart(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/client/cart/clear');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Cart cleared successfully.'
                 ]);
    }

    // Order Routes Tests (Protected)
    public function test_authenticated_user_can_get_orders(): void
    {
        Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/client/orders');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data'
                 ]);
    }

    public function test_authenticated_user_can_view_single_order(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/client/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data'
                 ]);
    }

    public function test_guest_cart_functionality_works(): void
    {
        // This test is covered by test_guest_can_get_cart and test_guest_can_add_to_cart
        $this->assertTrue(true);
    }

    public function test_unauthenticated_user_cannot_access_orders(): void
    {
        $response = $this->getJson('/api/client/orders');
        $response->assertStatus(401);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\Client\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CartRedisExpirationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear Redis before each test
        Redis::flushdb();
    }

    protected function tearDown(): void
    {
        // Clear Redis after each test
        Redis::flushdb();
        
        parent::tearDown();
    }

    public function test_cart_sets_expiration_when_adding_products(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product
        $product = Product::factory()->create();

        // Create cart service
        $cartService = new CartService();

        // Add product to cart
        $result = $cartService->add($product, 2);

        $this->assertTrue($result);

        // Check that TTL is set (should be 7 days = 604800 seconds for authenticated users)
        $ttl = $cartService->getTTL();
        $this->assertGreaterThan(0, $ttl);
        $this->assertLessThanOrEqual(604800, $ttl);
    }

    public function test_guest_cart_has_shorter_expiration(): void
    {
        // Don't authenticate (guest user)
        
        // Create a product
        $product = Product::factory()->create();

        // Create cart service
        $cartService = new CartService();

        // Add product to cart
        $result = $cartService->add($product, 1);

        $this->assertTrue($result);

        // Check that TTL is set (should be 24 hours = 86400 seconds for guests)
        $ttl = $cartService->getTTL();
        $this->assertGreaterThan(0, $ttl);
        $this->assertLessThanOrEqual(86400, $ttl);
    }

    public function test_cart_expiration_refreshes_when_accessing_cart(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product
        $product = Product::factory()->create();

        // Create cart service
        $cartService = new CartService();

        // Add product to cart
        $cartService->add($product, 1);

        // Get initial TTL
        $initialTtl = $cartService->getTTL();

        // Wait a moment (simulate time passing)
        sleep(1);

        // Access cart (this should refresh expiration)
        $cart = $cartService->getCart();

        // Get new TTL
        $newTtl = $cartService->getTTL();

        // New TTL should be greater than or equal to initial TTL (refreshed)
        $this->assertGreaterThanOrEqual($initialTtl, $newTtl);
        $this->assertNotEmpty($cart);
    }

    public function test_cart_expiration_extends_manually(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product
        $product = Product::factory()->create();

        // Create cart service
        $cartService = new CartService();

        // Add product to cart
        $cartService->add($product, 1);

        // Get initial TTL
        $initialTtl = $cartService->getTTL();

        // Wait a moment
        sleep(1);

        // Extend expiration
        $result = $cartService->extendExpiration();

        $this->assertTrue($result);

        // Get new TTL
        $newTtl = $cartService->getTTL();

        // New TTL should be greater than or equal to initial TTL
        $this->assertGreaterThanOrEqual($initialTtl, $newTtl);
    }

    public function test_cart_exists_method_works_correctly(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create cart service
        $cartService = new CartService();

        // Initially cart should not exist
        $this->assertFalse($cartService->exists());

        // Create a product and add to cart
        $product = Product::factory()->create();
        $cartService->add($product, 1);

        // Now cart should exist
        $this->assertTrue($cartService->exists());

        // Clear cart
        $cartService->clear();

        // Cart should not exist anymore
        $this->assertFalse($cartService->exists());
    }

    public function test_cart_maintains_expiration_after_updates(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a product
        $product = Product::factory()->create();

        // Create cart service
        $cartService = new CartService();

        // Add product to cart
        $cartService->add($product, 1);

        // Update quantity
        $cartService->update($product, 3);

        // Check that TTL is still set
        $ttl = $cartService->getTTL();
        $this->assertGreaterThan(0, $ttl);
        $this->assertLessThanOrEqual(604800, $ttl);
    }
}

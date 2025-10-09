<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminEndpointsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $adminToken;
    protected $userToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        $this->adminToken = $this->admin->createToken('auth_token')->plainTextToken;
        $this->userToken = $this->user->createToken('auth_token')->plainTextToken;
    }

    // Admin Product Routes Tests
    public function test_admin_can_get_all_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/admin/products');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_product(): void
    {
        $category = Category::factory()->create();
        
        $productData = [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'category_id' => $category->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/admin/products', $productData);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('products', [
            'name' => $productData['name'],
            'price' => $productData['price']
        ]);
    }

    public function test_admin_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create();
        
        $updateData = [
            'name' => 'Updated Product Name',
            'description' => $product->description,
            'price' => $product->price,
            'category_id' => $product->category_id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/admin/products/{$product->id}", $updateData);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name'
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    // Admin Category Routes Tests
    public function test_admin_can_get_all_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/admin/categories');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_category(): void
    {
        Storage::fake('public');
        
        $categoryData = [
            'name' => $this->faker->word(),
            'image' => UploadedFile::fake()->image('test-image.jpg'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/admin/categories', $categoryData);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('categories', [
            'name' => $categoryData['name']
        ]);
    }

    public function test_admin_can_view_single_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200);
    }

    public function test_admin_can_update_category(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        
        $updateData = [
            'name' => 'Updated Category Name',
            'image' => UploadedFile::fake()->image('updated-image.jpg'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/admin/categories/{$category->id}", $updateData);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name'
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    // Admin Order Routes Tests
    public function test_admin_can_get_all_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/admin/orders');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_single_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/admin/orders/{$order->id}");

        $response->assertStatus(200);
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $updateData = [
            'status' => 'shipped',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/admin/orders/{$order->id}", $updateData);

        $response->assertStatus(200);
    }

    // Admin Dashboard Test
    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
    }

    // Authorization Tests
    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/admin/products');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_routes(): void
    {
        $response = $this->getJson('/api/admin/products');
        $response->assertStatus(401);
    }

    public function test_fallback_route_returns_404(): void
    {
        $response = $this->getJson('/api/nonexistent-route');
        
        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'Route not found.'
                 ]);
    }
}

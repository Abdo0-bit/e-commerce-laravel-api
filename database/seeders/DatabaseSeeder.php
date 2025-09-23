<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Users
        $users = User::factory(10)->create();

        // optional: test user with known credentials
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2) Categories
        $categories = Category::factory(8)->create();

        // 3) Products
        $products = Product::factory(25)->create();

        // 4) Attach random categories to each product (1 - 3 categories)
        $products->each(function ($product) use ($categories) {
            $product->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        // 5) Create orders for each user (1 - 3 orders each)
        $orders = collect();
        $users->each(function ($user) use (&$orders) {
            $userOrders = Order::factory(rand(1, 3))->create([
                'user_id' => $user->id,
                // set temporary total_amount = 0 so we can compute it reliably after items
                'total_amount' => 0,
            ]);
            $orders = $orders->merge($userOrders);
        });

        // 6) Create order items for each order and compute totals
        $orders->each(function ($order) use ($products) {
            // pick 1-4 random products for this order
            $orderProducts = $products->random(rand(1, 4));

            $orderProducts->each(function ($product) use ($order) {
                $quantity = fake()->numberBetween(1, 3);
                $unitPrice = $product->price;
                $totalPrice = round($quantity * $unitPrice, 2);

                // create OrderItem directly (clean & explicit)
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $product->id,
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            });

            // update order total_amount using DB-sum (more efficient)
            $order->update([
                'total_amount' => $order->orderItems()->sum('total_price'),
            ]);
        });
    }
}

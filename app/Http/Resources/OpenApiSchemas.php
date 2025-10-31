<?php

namespace App\Http\Resources;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="role", type="string", enum={"client", "admin"}, example="client"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Sample Product"),
 *     @OA\Property(property="description", type="string", example="Product description"),
 *     @OA\Property(property="price", type="number", format="float", example=99.99),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string"), example={"image1.jpg", "image2.jpg"}),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     description="Category model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Electronics"),
 *     @OA\Property(property="image", type="string", nullable=true, example="category.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     description="Order model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="shipping_phone", type="string", example="+1234567890"),
 *     @OA\Property(property="shipping_street", type="string", example="123 Main St"),
 *     @OA\Property(property="shipping_city", type="string", example="New York"),
 *     @OA\Property(property="shipping_state", type="string", example="NY"),
 *     @OA\Property(property="shipping_postal_code", type="string", example="10001"),
 *     @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"}, example="pending"),
 *     @OA\Property(property="payment_status", type="string", enum={"pending", "paid", "failed"}, example="pending"),
 *     @OA\Property(property="payment_method", type="string", enum={"cod", "stripe"}, example="stripe"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=199.99),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="CartItem",
 *     type="object",
 *     title="Cart Item",
 *     description="Cart item model",
 *
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Sample Product"),
 *     @OA\Property(property="price", type="number", format="float", example=99.99),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="total", type="number", format="float", example=199.98)
 * )
 *
 * @OA\Schema(
 *     schema="Cart",
 *     type="object",
 *     title="Shopping Cart",
 *     description="Shopping cart model",
 *
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/CartItem")),
 *     @OA\Property(property="total_items", type="integer", example=3),
 *     @OA\Property(property="total_amount", type="number", format="float", example=299.97)
 * )
 */
class OpenApiSchemas
{
    // This class is only for documentation purposes
}

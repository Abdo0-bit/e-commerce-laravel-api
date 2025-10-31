<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.1.0",
 *     title="E-Commerce API with Stripe Payments",
 *     description="A comprehensive e-commerce API with Stripe payment integration, real-time WebSocket features, and comprehensive order management",
 *     @OA\Contact(
 *         email="admin@ecommerce-api.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * @OA\Server(
 *     url="http://e-commerce-api-new.test/api",
 *     description="Local Development Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum token authentication"
 * )
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints"
 * )
 * @OA\Tag(
 *     name="Client - Products",
 *     description="Client product browsing endpoints"
 * )
 * @OA\Tag(
 *     name="Client - Categories",
 *     description="Client category browsing endpoints"
 * )
 * @OA\Tag(
 *     name="Client - Cart",
 *     description="Shopping cart management (guest & authenticated)"
 * )
 * @OA\Tag(
 *     name="Client - Orders",
 *     description="Order management for customers"
 * )
 * @OA\Tag(
 *     name="Admin - Products",
 *     description="Administrative product management"
 * )
 * @OA\Tag(
 *     name="Admin - Categories", 
 *     description="Administrative category management"
 * )
 * @OA\Tag(
 *     name="Admin - Orders",
 *     description="Administrative order management"
 * )
 * @OA\Tag(
 *     name="Admin - Dashboard",
 *     description="Administrative dashboard and statistics"
 * )
 * @OA\Tag(
 *     name="Real-time Events",
 *     description="WebSocket events for real-time functionality"
 * )
 * @OA\Tag(
 *     name="Payments",
 *     description="Stripe payment processing and webhooks"
 * )
 * @OA\Tag(
 *     name="Webhooks",
 *     description="Stripe webhook endpoints for payment status updates"
 * )
 *
 * @OA\Schema(
 *     schema="OrderRequest",
 *     type="object",
 *     title="Order Request",
 *     description="Request body for creating a new order",
 *     required={"first_name", "last_name", "shipping_phone", "shipping_street", "shipping_city", "shipping_state", "shipping_postal_code", "payment_method"},
 *     @OA\Property(property="first_name", type="string", example="John", description="Customer first name"),
 *     @OA\Property(property="last_name", type="string", example="Doe", description="Customer last name"),
 *     @OA\Property(property="shipping_phone", type="string", example="123-456-7890", description="Shipping phone number"),
 *     @OA\Property(property="shipping_street", type="string", example="123 Main St", description="Shipping street address"),
 *     @OA\Property(property="shipping_city", type="string", example="Anytown", description="Shipping city"),
 *     @OA\Property(property="shipping_state", type="string", example="CA", description="Shipping state"),
 *     @OA\Property(property="shipping_postal_code", type="string", example="12345", description="Shipping postal code"),
 *     @OA\Property(
 *         property="payment_method", 
 *         type="string", 
 *         enum={"cod", "stripe"}, 
 *         example="stripe", 
 *         description="Payment method: 'cod' for Cash on Delivery, 'stripe' for credit/debit card payments"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="StripeOrderResponse",
 *     type="object",
 *     title="Stripe Order Response",
 *     description="Response when creating an order with Stripe payment method",
 *     @OA\Property(property="message", type="string", example="Order created successfully."),
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=22),
 *         @OA\Property(property="payment_method", type="string", example="stripe"),
 *         @OA\Property(property="payment_status", type="string", example="unpaid"),
 *         @OA\Property(
 *             property="stripe_client_secret", 
 *             type="string", 
 *             example="pi_3SOMF4EFDrXcJGWZ18qFfsrp_secret_xxx",
 *             description="Use this with Stripe.js to confirm payment on frontend"
 *         ),
 *         @OA\Property(property="stripe_payment_intent_id", type="string", example="pi_3SOMF4EFDrXcJGWZ18qFfsrp"),
 *         @OA\Property(property="total_amount", type="string", example="49.99"),
 *         @OA\Property(property="status", type="string", example="pending"),
 *         @OA\Property(property="first_name", type="string", example="John"),
 *         @OA\Property(property="last_name", type="string", example="Doe"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-31T12:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-31T12:00:00Z")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="StripeWebhookPayload",
 *     type="object",
 *     title="Stripe Webhook Payload",
 *     description="Stripe webhook event payload structure",
 *     @OA\Property(
 *         property="type", 
 *         type="string", 
 *         enum={"payment_intent.succeeded", "payment_intent.payment_failed", "payment_intent.requires_action"},
 *         example="payment_intent.succeeded", 
 *         description="Type of webhook event"
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(
 *             property="object",
 *             type="object",
 *             @OA\Property(property="id", type="string", example="pi_3SOMF4EFDrXcJGWZ18qFfsrp", description="Payment intent ID"),
 *             @OA\Property(property="amount", type="integer", example=4999, description="Amount in cents"),
 *             @OA\Property(property="currency", type="string", example="usd", description="Currency code"),
 *             @OA\Property(
 *                 property="status", 
 *                 type="string", 
 *                 enum={"succeeded", "requires_payment_method", "requires_action"},
 *                 example="succeeded", 
 *                 description="Payment intent status"
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="CartResource",
 *     title="Cart Resource",
 *     description="Shopping cart with items and totals",
 *     type="object",
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="Cart items",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="product_id", type="integer", example=1, description="Product ID"),
 *             @OA\Property(property="name", type="string", example="iPhone 15 Pro", description="Product name"),
 *             @OA\Property(property="price", type="string", example="999.00", description="Product price"),
 *             @OA\Property(property="quantity", type="integer", example=2, description="Item quantity"),
 *             @OA\Property(property="subtotal", type="string", example="1998.00", description="Line item subtotal"),
 *             @OA\Property(property="image", type="string", example="https://example.com/image.jpg", description="Product image URL"),
 *             @OA\Property(property="slug", type="string", example="iphone-15-pro", description="Product slug"),
 *             @OA\Property(property="category", type="string", example="Electronics", description="Product category")
 *         )
 *     ),
 *     @OA\Property(property="total_items", type="integer", example=3, description="Total number of items in cart"),
 *     @OA\Property(property="total_amount", type="string", example="2497.00", description="Total cart amount")
 * )
 */
class OpenApiController extends Controller
{
    //
}
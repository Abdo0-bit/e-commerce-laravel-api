<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="E-Commerce API",
 *     description="A comprehensive e-commerce API with real-time features",
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
 */
class OpenApiController extends Controller
{
    //
}
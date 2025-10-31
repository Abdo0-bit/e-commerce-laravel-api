<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderSummaryResource;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get dashboard statistics",
     *     description="Retrieve key metrics and statistics for the admin dashboard including counts, revenue, and recent orders",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="products_count", type="integer", example=125, description="Total number of products"),
     *             @OA\Property(property="categories_count", type="integer", example=15, description="Total number of categories"),
     *             @OA\Property(property="orders_count", type="integer", example=89, description="Total number of non-canceled orders"),
     *             @OA\Property(property="pending_orders", type="integer", example=12, description="Number of pending orders"),
     *             @OA\Property(property="delivered_orders", type="integer", example=45, description="Number of delivered orders"),
     *             @OA\Property(property="total_revenue", type="string", example="15420.75", description="Total revenue from paid orders"),
     *             @OA\Property(
     *                 property="latest_orders",
     *                 type="array",
     *                 description="5 most recent orders",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=22),
     *                     @OA\Property(property="total_amount", type="string", example="299.99"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="payment_status", type="string", example="unpaid"),
     *                     @OA\Property(property="payment_method", type="string", example="stripe"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-31T14:30:00Z"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="Jane Smith"),
     *                         @OA\Property(property="email", type="string", example="jane@example.com")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'products_count' =>Product::count(),
            'categories_count' => Category::count(),
            'orders_count' => Order::where('status', '!=', 'canceled')->count(),
            'pending_orders'=> Order::where('status', 'pending')->count(),
            'delivered_orders'=> Order::where('status', 'delivered')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'latest_orders' => OrderSummaryResource::collection(Order::with('user')->latest()->take(5)->get()),
        ]);
    }
}

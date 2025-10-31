<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Jobs\DeleteOrderJob;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\OrderResource;
use App\Http\Resources\Admin\OrderSummaryResource;
use App\Events\OrderStatusUpdated;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/orders",
     *     tags={"Admin Orders"},
     *     summary="Get all orders (Admin)",
     *     description="Retrieve a paginated list of all orders for admin management",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by order status", 
     *         @OA\Schema(type="string", enum={"pending", "processing", "shipped", "delivered", "canceled"}, example="pending")
     *     ),
     *     @OA\Parameter(
     *         name="payment_status",
     *         in="query",
     *         description="Filter by payment status",
     *         @OA\Schema(type="string", enum={"unpaid", "paid", "failed", "refunded"}, example="paid")
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="total_amount", type="string", example="199.99"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="payment_status", type="string", example="unpaid"),
     *                     @OA\Property(property="payment_method", type="string", example="stripe"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-31T12:00:00Z"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="first_page_url", type="string", example="http://localhost/api/admin/orders?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=10),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="total", type="integer", example=95)
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('user')->latest()->paginate(10);
        return OrderSummaryResource::collection($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/orders/{order}",
     *     tags={"Admin Orders"},
     *     summary="Get single order (Admin)",
     *     description="Retrieve detailed information about a specific order including all items and customer details",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Order] 999")
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
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('user', 'orderItems.product');
        return new OrderResource($order);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/orders/{order}",
     *     tags={"Admin Orders"},
     *     summary="Update order status",
     *     description="Update order status and payment status. When status is set to 'delivered', payment_status automatically becomes 'paid'. When status is 'canceled', the order is queued for deletion.",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status", 
     *                 type="string", 
     *                 enum={"pending", "processing", "shipped", "delivered", "canceled"}, 
     *                 example="shipped", 
     *                 description="New order status"
     *             ),
     *             @OA\Property(
     *                 property="payment_status", 
     *                 type="string", 
     *                 enum={"unpaid", "paid", "failed", "refunded"}, 
     *                 example="paid", 
     *                 description="Payment status (optional, auto-updated for some order statuses)"
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order updated successfully"),
     *             @OA\Property(property="order", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Order] 999")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="status",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected status is invalid.")
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
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $data = $request->validated();
        $oldStatus = $order->status;

        if(isset($data['status']) && $data['status'] === 'delivered'){
            $data['payment_status'] = 'paid';
        }

        if (isset($data['status'])&& $data['status'] === 'canceled'){
            $days = config('orders.delete_after_days'); 
            DeleteOrderJob::dispatch($order)->delay(now()->addDays($days));
        }
        
        $order->update($data);

        // Broadcast order status update if status changed
        if (isset($data['status']) && $oldStatus !== $data['status']) {
            broadcast(new OrderStatusUpdated($order, $oldStatus));
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => new OrderResource($order->fresh()->load('user', 'orderItems.product')),
        ]);

    }

}

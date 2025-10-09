<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\Client\OrderResource;
use Illuminate\Http\Request;
use App\Services\Client\OrderService;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\OrderStatusRequest;
use App\Events\NewOrderCreated;
use App\Events\OrderStatusUpdated;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = $this->orderService->getUserOrders();
        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            
            // Broadcast new order created event
            broadcast(new NewOrderCreated($order));
            
            return response()->json([
                'message' => 'Order created successfully.',
                'status' => 'success',
                'data'=>new OrderResource($order),
            ], 201);
        } catch (\Exception $e) {
            Log::error('OrderController store error: '.$e->getMessage());
            return response()->json([
                'message' => 'Failed to create order: '.$e->getMessage(),
                'status' => $e->getMessage(),
            ],400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = $this->orderService->getOrderById($id);
        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
                'status' => 'error',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function cancel(OrderStatusRequest $request, string $id)
    {
        $status = $request->validated()['status'];
        try {
            $order = $this->orderService->getOrderById($id);
            $oldStatus = $order->status;
            
            $this->orderService->cancelOrder($id, $status);
            
            // Reload order to get updated status
            $order->refresh();
            
            // Broadcast order status updated event
            broadcast(new OrderStatusUpdated($order, $oldStatus));
            
            return response()->json([
                'message' => 'Order canceled successfully.',
                'status' => 'success',
            ]);
            
        }catch (\Exception $e) {
            Log::error('OrderController update error: '.$e->getMessage());
            return response()->json([
                'message' => 'Failed to cancel order: '.$e->getMessage(),
                'status' => 'error',
            ], 400);
        }
    }


}

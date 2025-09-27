<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Jobs\DeleteOrderJob;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\OrderResource;
use App\Http\Resources\Admin\OrderSummaryResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('user')->latest()->paginate(10);
        return OrderSummaryResource::collection($orders);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('user', 'orderItems.product');
        return new OrderResource($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $data = $request->validated();

        if(isset($data['status']) && $data['status'] === 'delivered'){
            $data['payment_status'] = 'paid';
        }

        if (isset($data['status'])&& $data['status'] === 'canceled'){
            $days = config('orders.delete_after_days'); 
            DeleteOrderJob::dispatch($order)->delay(now()->addDays($days));
        }
        $order->update($data);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => new OrderResource($order->fresh()->load('user', 'orderItems.product')),
        ]);

    }

}

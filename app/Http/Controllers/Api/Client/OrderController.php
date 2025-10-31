<?php

namespace App\Http\Controllers\Api\Client;

use App\Events\NewOrderCreated;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Resources\Client\OrderResource;
use App\Services\Client\OrderService;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentServiceInterface $paymentService
    ) {}

    /**
     * @OA\Get(
     *     path="/client/orders",
     *     operationId="getUserOrders",
     *     tags={"Client - Orders"},
     *     summary="Get user's orders",
     *     description="Retrieves all orders for the authenticated user, including payment status and Stripe payment information when applicable",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Order")
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
    public function index()
    {
        $orders = $this->orderService->getUserOrders();

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/client/orders",
     *     operationId="createOrder",
     *     tags={"Client - Orders", "Payments"},
     *     summary="Create a new order with payment processing",
     *     description="Creates a new order from the current user's cart. Supports both COD and Stripe payment methods. For Stripe payments, returns a client_secret for frontend payment confirmation.",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Order details and payment information",
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "shipping_phone", "shipping_street", "shipping_city", "shipping_state", "shipping_postal_code", "payment_method"},
     *             @OA\Property(property="first_name", type="string", example="John", description="Customer first name"),
     *             @OA\Property(property="last_name", type="string", example="Doe", description="Customer last name"),
     *             @OA\Property(property="shipping_phone", type="string", example="123-456-7890", description="Shipping phone number"),
     *             @OA\Property(property="shipping_street", type="string", example="123 Main St", description="Shipping street address"),
     *             @OA\Property(property="shipping_city", type="string", example="Anytown", description="Shipping city"),
     *             @OA\Property(property="shipping_state", type="string", example="CA", description="Shipping state"),
     *             @OA\Property(property="shipping_postal_code", type="string", example="12345", description="Shipping postal code"),
     *             @OA\Property(
     *                 property="payment_method", 
     *                 type="string", 
     *                 enum={"cod", "stripe"}, 
     *                 example="stripe", 
     *                 description="Payment method: 'cod' for Cash on Delivery, 'stripe' for credit/debit card payments"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order created successfully."),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Order"
     *             ),
     *             @OA\Examples(
     *                 example="stripe_order",
     *                 summary="Stripe Payment Order",
     *                 value={
     *                     "message": "Order created successfully.",
     *                     "status": "success",
     *                     "data": {
     *                         "id": 22,
     *                         "payment_method": "stripe",
     *                         "payment_status": "unpaid",
     *                         "stripe_client_secret": "pi_3SOMF4EFDrXcJGWZ18qFfsrp_secret_xxx",
     *                         "total_amount": "49.99",
     *                         "status": "pending"
     *                     }
     *                 }
     *             ),
     *             @OA\Examples(
     *                 example="cod_order",
     *                 summary="Cash on Delivery Order",
     *                 value={
     *                     "message": "Order created successfully.",
     *                     "status": "success",
     *                     "data": {
     *                         "id": 23,
     *                         "payment_method": "cod",
     *                         "payment_status": "unpaid",
     *                         "stripe_client_secret": null,
     *                         "total_amount": "29.99",
     *                         "status": "pending"
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Cart is empty or invalid data",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart is empty"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="payment_method",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected payment method is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Payment processing error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment processing failed"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     )
     * )
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
                'data' => new OrderResource($order),
            ], 201);
        } catch (\Exception $e) {
            Log::error('OrderController store error: '.$e->getMessage());

            return response()->json([
                'message' => 'Failed to create order: '.$e->getMessage(),
                'status' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = $this->orderService->getOrderById($id);
        if (! $order) {
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

        } catch (\Exception $e) {
            Log::error('OrderController update error: '.$e->getMessage());

            return response()->json([
                'message' => 'Failed to cancel order: '.$e->getMessage(),
                'status' => 'error',
            ], 400);
        }
    }

    /**
     * Confirm Stripe payment for an order.
     */
    public function confirmPayment(Request $request, string $id)
    {
        try {
            $order = $this->orderService->getOrderById($id);

            if (! $order || $order->payment_method !== 'stripe') {
                return response()->json([
                    'message' => 'Order not found or not a Stripe payment.',
                    'status' => 'error',
                ], 404);
            }

            if ($order->payment_status === 'paid') {
                return response()->json([
                    'message' => 'Payment already confirmed.',
                    'status' => 'success',
                    'data' => new OrderResource($order),
                ]);
            }

            // Retrieve the payment intent from Stripe
            $paymentIntent = $this->paymentService->retrievePaymentIntent($order->stripe_payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);

                return response()->json([
                    'message' => 'Payment confirmed successfully.',
                    'status' => 'success',
                    'data' => new OrderResource($order),
                ]);
            } else {
                return response()->json([
                    'message' => 'Payment not yet completed.',
                    'status' => 'pending',
                    'payment_status' => $paymentIntent->status,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('OrderController confirmPayment error: '.$e->getMessage());

            return response()->json([
                'message' => 'Failed to confirm payment: '.$e->getMessage(),
                'status' => 'error',
            ], 400);
        }
    }
}

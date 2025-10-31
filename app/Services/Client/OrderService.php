<?php

namespace App\Services\Client;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Contracts\Client\CartServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private CartServiceInterface $cartService,
        private PaymentServiceInterface $paymentService
    ) {}

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $cart = $this->cartService->getCart();

            if (empty($cart['items'])) {
                throw new \Exception('Cart is empty');
            }

            $paymentMethod = $data['payment_method'] ?? 'cod';
            $user = Auth::user();

            $order = Order::create([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_street' => $data['shipping_street'],
                'shipping_city' => $data['shipping_city'],
                'shipping_state' => $data['shipping_state'] ?? null,
                'shipping_postal_code' => $data['shipping_postal_code'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $paymentMethod,
                'total_amount' => $cart['total'],
            ]);

            // Handle Stripe payment
            if ($paymentMethod === 'stripe') {
                $amountInCents = $this->paymentService->toCents((float) $cart['total']);
                $paymentIntent = $this->paymentService->createPaymentIntent(
                    $user,
                    $amountInCents,
                    [
                        'order_id' => $order->id,
                        'customer_name' => $data['first_name'].' '.$data['last_name'],
                        'customer_email' => $user->email,
                    ]
                );

                $order->update([
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'stripe_client_secret' => $paymentIntent->client_secret,
                    'stripe_payment_metadata' => [
                        'payment_intent_id' => $paymentIntent->id,
                        'amount' => $amountInCents,
                        'currency' => $paymentIntent->currency,
                    ],
                ]);
            }

            foreach ($cart['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // Clear the cart after creating the order
            $this->cartService->clear();

            return $order;
        });
    }

    public function getUserOrders()
    {
        return Order::with('orderItems.product')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOrderById($orderId)
    {
        return Order::with('orderItems.product')
            ->where('user_id', Auth::id())
            ->where('id', $orderId)
            ->firstOrFail();
    }

    public function cancelOrder(int $orderId, string $status)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('id', $orderId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (! $order) {
            throw new \Exception('Order cannot be canceled.');
        }

        $order->status = 'canceled';
        $order->save();

        return true;
    }
}

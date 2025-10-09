<?php

namespace App\Services\Client;

use App\Services\Contracts\Client\CartServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;

class OrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private CartServiceInterface $cartService){}

    public function createOrder(array $data)
    {
        return DB::transaction(function () use($data) {
            $cart = $this->cartService->getCart();
            
            if(empty($cart['items'])){
                throw new \Exception('Cart is empty');
            };
            $order = Order::create([
                'user_id' => Auth::id(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_street' => $data['shipping_street'],
                'shipping_city' => $data['shipping_city'],
                'shipping_state' => $data['shipping_state'] ?? null,
                'shipping_postal_code' => $data['shipping_postal_code'] ?? null,
                'shipping_country' => $data['shipping_country'] ?? 'EG',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? 'cod',
                'total_amount' => $cart['total'],
            ]);

            foreach($cart['items'] as $item){
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price']->price,
                    'total' => $item['total'],
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

    public function cancelOrder(int $orderId , string $status)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('id', $orderId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (!$order) {
            throw new \Exception('Order cannot be canceled.');
        }

        $order->status = 'canceled';
        $order->save();

        return true;
    }

}

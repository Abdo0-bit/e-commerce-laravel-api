<?php

namespace App\Services\Client;

use App\Models\Product;
use App\Services\Contracts\Client\CartServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class CartService implements CartServiceInterface
{
    protected string $key;

    protected int $ttl;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->key = Auth::check() ? 'cart:user:'.Auth::id() : 'cart:guest:'.session()->getId();
        $this->ttl = Auth::check() ? config('cart.ttl', 604800) : config('cart.guest_ttl', 86400);
    }

    /**
     * Set expiration time for the cart key.
     */
    protected function setExpiration(): void
    {
        if ($this->ttl > 0) {
            Redis::expire($this->key, $this->ttl);
        }
    }

    public function add(Product $product, int $quantity = 1): bool
    {
        $lockKey = "lock:{$this->key}";
        $lock = Cache::lock($lockKey, 5); 

        try {
        // Attempt to acquire the lock, waiting up to 5 seconds
            return $lock->block(5, function () use ($product, $quantity) {
                Redis::hincrby($this->key, $product->id, $quantity);
                $this->setExpiration();
                return true;
            });
        } catch (\Exception $e) {
            Log::error('CartService add lock error: ' . $e->getMessage());
            return false;
        }
    }

    public function update(Product $product, int $quantity): void
    {
        $lockKey = "lock:{$this->key}";
        Cache::lock($lockKey, 5)->block(5, function () use ($product, $quantity) {
            if ($quantity <= 0) {
                Redis::hdel($this->key, $product->id);
            } else {
                Redis::hset($this->key, $product->id, $quantity);
            }

            if (Redis::hlen($this->key) > 0) {
                $this->setExpiration();
            }
        });
    }

    public function remove(Product $product): void
    {
        $lockKey = "lock:{$this->key}";
        Cache::lock($lockKey, 5)->block(5, function () use ($product) {
            Redis::hdel($this->key, $product->id);
        });
    }

    public function clear(): void
    {
        try {
            Redis::del($this->key);
        } catch (\Exception $e) {
            Log::error('CartService clear error: '.$e->getMessage());
        }

    }

    public function getCart(): array
    {
        try {
            $cart = Redis::hgetall($this->key);

            if (!empty($cart)) {
                $this->setExpiration();
            }

            $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
            $items = [];
            $total = 0;

            foreach ($cart as $productId => $quantity) {
                if (isset($products[$productId])) {
                    $product = $products[$productId];
                    $itemTotal = $product->price * (int) $quantity;

                    $items[] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'quantity' => (int) $quantity,
                        'total_price' => $itemTotal,
                        'product' => $product,
                    ];

                    $total += $itemTotal;
                }
            }

            return [
                'items' => $items,
                'total' => $total,
                'item_count' => count($items),
            ];
        } catch (\Exception $e) {
            Log::error('CartService getCart error: ' . $e->getMessage());

            return [
                'items' => [],
                'total' => 0,
                'item_count' => 0,
            ];
        }
    }

    public function getTotal(): float
    {
        try {
            $cart = $this->getCart();
            return $cart['total'] ?? 0.0;
        } catch (\Exception $e) {
            Log::error('CartService getTotal error: ' . $e->getMessage());
            return 0.0;
        }
    }



    public function mergeGuestCart(int $userId): void
    {
        try {
            $guestKey = 'cart:guest:'.session()->getId();
            $userKey = 'cart:user:'.$userId;

            $guestCart = Redis::hgetall($guestKey);
            if (! empty($guestCart)) {
                foreach ($guestCart as $productId => $quantity) {
                    Redis::hincrby($userKey, $productId, $quantity);
                }
                Redis::del($guestKey);
                // Set expiration for the merged cart
                $this->setExpiration();
            }
        } catch (\Exception $e) {
            Log::error('CartService mergeGuestCart error: '.$e->getMessage());
        }
    }
    /**
     * Get the time-to-live (TTL) for the cart key in seconds.
     * Returns -1 if key has no expiration, -2 if key doesn't exist.
     */
    public function getTTL(): int
    {
        try {
            return Redis::ttl($this->key);
        } catch (\Exception $e) {
            Log::error('CartService getTTL error: '.$e->getMessage());

            return -2;
        }
    }

    /**
     * Extend the cart expiration time.
     */
    public function extendExpiration(): bool
    {
        try {
            if (Redis::exists($this->key)) {
                $this->setExpiration();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('CartService extendExpiration error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if the cart exists and has not expired.
     */
    public function exists(): bool
    {
        try {
            return Redis::exists($this->key) > 0;
        } catch (\Exception $e) {
            Log::error('CartService exists error: '.$e->getMessage());

            return false;
        }
    }
}

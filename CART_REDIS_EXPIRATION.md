# Cart Redis Expiration Documentation

## Overview

The CartService now includes Redis expiration functionality to automatically clean up old cart data and improve performance.

## Configuration

Cart expiration settings are configured in `config/cart.php`:

```php
return [
    // User cart expiration time (7 days)
    'ttl' => env('CART_TTL', 604800), // seconds

    // Guest cart expiration time (24 hours)
    'guest_ttl' => env('GUEST_CART_TTL', 86400), // seconds
];
```

## Environment Variables

Add these to your `.env` file to customize expiration times:

```env
# Cart expiration for authenticated users (7 days = 604800 seconds)
CART_TTL=604800

# Cart expiration for guest users (24 hours = 86400 seconds)
GUEST_CART_TTL=86400
```

## Features

### Automatic Expiration

-   **Authenticated users**: Cart expires after 7 days (configurable)
-   **Guest users**: Cart expires after 24 hours (configurable)
-   Expiration is automatically set when adding items to cart
-   Expiration is refreshed when accessing cart contents
-   Expiration is maintained when updating cart items

### New Methods

#### `getTTL(): int`

Returns the time-to-live for the cart key in seconds.

-   Returns `-1` if key has no expiration
-   Returns `-2` if key doesn't exist

#### `extendExpiration(): bool`

Manually extends the cart expiration time to the configured TTL.

-   Returns `true` if successful
-   Returns `false` if cart doesn't exist

#### `exists(): bool`

Checks if the cart exists and has not expired.

-   Returns `true` if cart exists
-   Returns `false` if cart doesn't exist or has expired

## Usage Examples

### Basic Usage

```php
use App\Services\Client\CartService;
use App\Models\Product;

$cartService = new CartService();
$product = Product::find(1);

// Add product to cart (automatically sets expiration)
$cartService->add($product, 2);

// Check how much time is left
$ttl = $cartService->getTTL();
echo "Cart expires in {$ttl} seconds";

// Check if cart exists
if ($cartService->exists()) {
    echo "Cart exists and hasn't expired";
}
```

### Extending Expiration

```php
// Extend cart expiration (useful for active users)
if ($cartService->extendExpiration()) {
    echo "Cart expiration extended successfully";
}
```

### Controller Integration

```php
class CartController extends Controller
{
    public function index(CartService $cartService)
    {
        // Check if cart exists before trying to access it
        if (!$cartService->exists()) {
            return response()->json(['message' => 'Cart is empty or expired'], 404);
        }

        $cart = $cartService->getCart(); // This also refreshes expiration
        $ttl = $cartService->getTTL();

        return response()->json([
            'cart' => $cart,
            'expires_in_seconds' => $ttl,
            'expires_in_hours' => round($ttl / 3600, 2),
        ]);
    }

    public function extendSession(CartService $cartService)
    {
        if ($cartService->extendExpiration()) {
            return response()->json(['message' => 'Cart session extended']);
        }

        return response()->json(['message' => 'No cart to extend'], 404);
    }
}
```

## Benefits

1. **Automatic Cleanup**: Old cart data is automatically removed from Redis
2. **Performance**: Reduces Redis memory usage by cleaning up abandoned carts
3. **User Experience**: Different expiration times for authenticated vs guest users
4. **Flexibility**: Configurable expiration times via environment variables
5. **Activity-Based**: Cart expiration refreshes when users interact with their cart

## Redis Commands

The service uses these Redis commands:

-   `EXPIRE`: Set expiration time for cart keys
-   `TTL`: Get remaining time-to-live
-   `EXISTS`: Check if cart key exists

## Testing

Run the cart expiration tests:

```bash
php artisan test --filter=CartRedisExpirationTest
```

This will verify all expiration functionality is working correctly.

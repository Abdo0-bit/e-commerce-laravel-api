# API Usage Guide

This guide provides detailed information on how to use the E-Commerce API with real-time features.

## Authentication

### Register a New User

```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "remember": false
}
```

**Response:**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "client",
        "created_at": "2025-10-09T19:30:00.000000Z"
    },
    "access_token": "1|abc123def456...",
    "token_type": "Bearer"
}
```

### Login User

```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123",
    "remember": false
}
```

### Using Authentication Token

Include the token in the Authorization header for protected endpoints:

```http
Authorization: Bearer 1|abc123def456...
```

## Client Endpoints

### Browse Products

```http
GET /api/client/products
```

**Response:**

```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "Sample Product",
            "description": "Product description",
            "price": 99.99,
            "images": ["image1.jpg"],
            "is_active": true,
            "category": {
                "id": 1,
                "name": "Electronics"
            }
        }
    ]
}
```

### Get Product Details

```http
GET /api/client/products/1
```

### Shopping Cart Operations

#### Add to Cart

```http
POST /api/client/cart
Content-Type: application/json

{
    "product_id": 1,
    "quantity": 2
}
```

#### View Cart

```http
GET /api/client/cart
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "items": [
            {
                "product_id": 1,
                "name": "Sample Product",
                "price": 99.99,
                "quantity": 2,
                "total": 199.98
            }
        ],
        "total_items": 1,
        "total_amount": 199.98
    }
}
```

#### Update Cart Item

```http
PUT /api/client/cart/1
Content-Type: application/json

{
    "quantity": 3
}
```

#### Remove from Cart

```http
DELETE /api/client/cart/1
```

#### Clear Cart

```http
DELETE /api/client/cart/clear
```

### Order Management

#### Create Order (Requires Authentication)

```http
POST /api/client/orders
Authorization: Bearer your-token
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "shipping_phone": "+1234567890",
    "shipping_street": "123 Main St",
    "shipping_city": "New York",
    "shipping_state": "NY",
    "shipping_postal_code": "10001",
    "payment_method": "credit_card"
}
```

#### View User Orders

```http
GET /api/client/orders
Authorization: Bearer your-token
```

#### Get Order Details

```http
GET /api/client/orders/1
Authorization: Bearer your-token
```

#### Cancel Order

```http
PATCH /api/client/orders/1/cancel
Authorization: Bearer your-token
Content-Type: application/json

{
    "status": "cancelled"
}
```

## Admin Endpoints (Requires Admin Role)

### Product Management

#### Create Product

```http
POST /api/admin/products
Authorization: Bearer admin-token
Content-Type: multipart/form-data

name=New Product
description=Product description
price=149.99
images[]=@/path/to/image1.jpg
categories[]=1
is_active=true
```

#### Update Product

```http
PUT /api/admin/products/1
Authorization: Bearer admin-token
Content-Type: application/json

{
    "name": "Updated Product Name",
    "price": 199.99,
    "is_active": true
}
```

#### Delete Product

```http
DELETE /api/admin/products/1
Authorization: Bearer admin-token
```

### Category Management

#### Create Category

```http
POST /api/admin/categories
Authorization: Bearer admin-token
Content-Type: multipart/form-data

name=Electronics
image=@/path/to/category.jpg
is_active=true
```

#### Update Category

```http
PUT /api/admin/categories/1
Authorization: Bearer admin-token
Content-Type: application/json

{
    "name": "Updated Category",
    "is_active": true
}
```

### Order Management

#### View All Orders

```http
GET /api/admin/orders
Authorization: Bearer admin-token
```

#### Update Order Status

```http
PUT /api/admin/orders/1
Authorization: Bearer admin-token
Content-Type: application/json

{
    "status": "shipped"
}
```

#### Dashboard Statistics

```http
GET /api/admin/dashboard
Authorization: Bearer admin-token
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "total_orders": 150,
        "total_revenue": 25000.0,
        "pending_orders": 12,
        "total_products": 50,
        "total_categories": 8
    }
}
```

## Real-time WebSocket Events

### Connecting to WebSocket

#### JavaScript Example (using Laravel Echo)

```javascript
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: "mbdu19pekmgbq2mzpd89",
    wsHost: "localhost",
    wsPort: 8080,
    forceTLS: false,
    enabledTransports: ["ws", "wss"],
});

// Listen for cart updates
Echo.channel("cart." + cartId).listen(".cart.updated", (e) => {
    console.log("Cart updated:", e.cart_data);
    // Update UI with new cart data
});

// Listen for order updates (requires authentication)
Echo.private("user." + userId)
    .listen(".order.created", (e) => {
        console.log("New order created:", e.order_id);
    })
    .listen(".order.status.updated", (e) => {
        console.log("Order status updated:", e);
    });

// Admin: Listen for new orders
Echo.private("admin.orders").listen(".order.created", (e) => {
    console.log("New order for admin:", e);
});
```

### WebSocket Channels

#### Public Channels

-   `cart.{cartId}` - Cart updates for specific cart session

#### Private Channels (requires authentication)

-   `user.{userId}` - User-specific notifications
-   `admin.orders` - Admin-only order notifications

### Event Payloads

#### Cart Updated Event

```json
{
    "cart_id": "session_id_here",
    "cart_data": {
        "items": [...],
        "total_items": 2,
        "total_amount": 199.98
    },
    "user_id": 1,
    "timestamp": "2025-10-09T19:30:00.000000Z"
}
```

#### Order Status Updated Event

```json
{
    "order_id": 1,
    "old_status": "pending",
    "new_status": "shipped",
    "total_amount": 199.99,
    "updated_at": "2025-10-09T19:30:00.000000Z"
}
```

#### New Order Created Event

```json
{
    "order_id": 1,
    "user_id": 1,
    "total_amount": 199.99,
    "status": "pending",
    "payment_status": "pending",
    "created_at": "2025-10-09T19:30:00.000000Z",
    "customer_name": "John Doe"
}
```

## Error Handling

### Standard Error Response Format

```json
{
    "message": "Error description",
    "status": "error"
}
```

### Common HTTP Status Codes

-   `200` - Success
-   `201` - Created
-   `400` - Bad Request
-   `401` - Unauthorized
-   `403` - Forbidden
-   `404` - Not Found
-   `422` - Validation Error
-   `500` - Internal Server Error

### Validation Errors (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

## Rate Limiting

Authentication endpoints have rate limiting:

-   Login: 5 attempts per minute
-   Register: 3 attempts per minute

## Testing with cURL

### Register Example

```bash
curl -X POST http://e-commerce-api-new.test/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Add to Cart Example

```bash
curl -X POST http://e-commerce-api-new.test/api/client/cart \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

### Authenticated Request Example

```bash
curl -X GET http://e-commerce-api-new.test/api/client/orders \
  -H "Authorization: Bearer 1|your-token-here" \
  -H "Accept: application/json"
```

## Postman Collection

Import the provided Postman collection for easier testing:

1. Open Postman
2. Click Import
3. Select the `E-Commerce-API.postman_collection.json` file
4. Import the environment file `E-Commerce-API.postman_environment.json`

The collection includes:

-   All API endpoints
-   Pre-configured requests
-   Authentication setup
-   Test scripts
-   Environment variables

## Need Help?

-   Check the Swagger documentation at `/api/documentation`
-   Review the test files for usage examples
-   Create an issue on GitHub for bugs or questions

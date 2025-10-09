# 🛒 Guest Cart & Authentication Testing Guide

## 🎯 Your Cart System Overview

Your e-commerce API has a sophisticated cart system that supports:

-   **Guest Cart**: Anyone can add/remove items without authentication
-   **Authenticated Cart**: Logged-in users have persistent carts
-   **Cart Merge**: When guests login, their cart merges with their authenticated cart
-   **Checkout Protection**: Only authenticated users can place orders

## 📝 Manual Testing in Postman

### Step 1: Import Collection & Environment

1. **Import Collection**: `E-Commerce-API.postman_collection.json`
2. **Import Environment**: `E-Commerce-Environment.postman_environment.json`
3. **Select Environment**: Choose "E-Commerce API Environment" from dropdown

### Step 2: Test Guest Cart Functionality

#### 🛍️ Guest Shopping (No Authentication)

1. **Browse Products**:

    ```
    GET {{base_url}}/api/client/products
    ```

    - No headers needed
    - Should return list of products

2. **Check Empty Guest Cart**:

    ```
    GET {{base_url}}/api/client/cart
    ```

    - No authentication required
    - Should return empty cart initially

3. **Add Items as Guest**:

    ```
    POST {{base_url}}/api/client/cart
    Content-Type: application/json

    {
      "product_id": 1,
      "quantity": 2
    }
    ```

4. **Add More Items**:

    ```
    POST {{base_url}}/api/client/cart

    {
      "product_id": 3,
      "quantity": 1
    }
    ```

5. **Update Guest Cart**:

    ```
    PUT {{base_url}}/api/client/cart/1

    {
      "quantity": 5
    }
    ```

6. **View Updated Guest Cart**:
    ```
    GET {{base_url}}/api/client/cart
    ```

### Step 3: Test Cart Merge Functionality

#### 🔄 Guest to Authenticated Merge

1. **Add Items as Guest** (from Step 2)

2. **Login to Trigger Merge**:

    ```
    POST {{base_url}}/api/login

    {
      "email": "user@example.com",
      "password": "password123"
    }
    ```

    - Save the `access_token` to `{{auth_token}}`
    - This triggers the `MergeGuestCartListener`

3. **Check Merged Cart**:
    ```
    GET {{base_url}}/api/client/cart
    Authorization: Bearer {{auth_token}}
    ```
    - Should contain guest cart items + any existing authenticated cart items

### Step 4: Test Authenticated Cart

#### 🔐 Authenticated User Cart

1. **Add Items as Authenticated User**:

    ```
    POST {{base_url}}/api/client/cart
    Authorization: Bearer {{auth_token}}

    {
      "product_id": 5,
      "quantity": 3
    }
    ```

2. **Update Authenticated Cart**:

    ```
    PUT {{base_url}}/api/client/cart/5
    Authorization: Bearer {{auth_token}}

    {
      "quantity": 4
    }
    ```

3. **Remove Item from Cart**:
    ```
    DELETE {{base_url}}/api/client/cart/1
    Authorization: Bearer {{auth_token}}
    ```

### Step 5: Test Checkout Protection

#### ✅ Authenticated Checkout (Should Work)

1. **Create Order** (requires authentication):

    ```
    POST {{base_url}}/api/client/orders
    Authorization: Bearer {{auth_token}}

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

#### ❌ Guest Checkout (Should Fail)

1. **Try to Create Order Without Auth**:

    ```
    POST {{base_url}}/api/client/orders
    // No Authorization header

    {
      "first_name": "Guest",
      "last_name": "User",
      // ... other fields
    }
    ```

    - Should return 401 Unauthorized

## 🧪 Complete Testing Scenarios

### Scenario 1: Full Guest-to-User Journey

```
1. Browse products (guest)
2. Add 2-3 items to cart (guest)
3. View cart (guest)
4. Login (triggers merge)
5. View cart (authenticated) - should have merged items
6. Add more items (authenticated)
7. Create order (authenticated)
```

### Scenario 2: Multi-Session Cart Persistence

```
1. Login as user
2. Add items to cart
3. Logout
4. Add different items as guest
5. Login again (same user)
6. Check cart - should have both sets of items
```

### Scenario 3: Cart Management

```
1. Add items (guest or authenticated)
2. Update quantities
3. Remove specific items
4. Clear entire cart
5. Verify cart is empty
```

## 🔍 What to Look For

### Expected Behaviors:

-   ✅ Guests can manage cart without authentication
-   ✅ Authentication preserves cart data
-   ✅ Login merges guest cart with user cart
-   ✅ Checkout requires authentication
-   ✅ Cart operations return proper status messages

### Error Scenarios to Test:

-   ❌ Guest trying to access orders (401)
-   ❌ Invalid product IDs (404/422)
-   ❌ Invalid quantities (422)
-   ❌ Checkout without authentication (401)

## 📊 Sample Responses

### Guest Cart Response:

```json
{
    "status": "success",
    "data": {
        "items": [
            {
                "product_id": 1,
                "name": "Product Name",
                "price": "99.99",
                "quantity": 2,
                "total": "199.98"
            }
        ],
        "total": "199.98",
        "item_count": 2
    }
}
```

### Login Response (with token):

```json
{
    "user": {
        "id": 27,
        "name": "Test User",
        "email": "user@example.com"
    },
    "access_token": "27|abc123...",
    "token_type": "Bearer",
    "expires_at": "2025-10-10T18:17:04.000000Z"
}
```

### Order Creation Success:

```json
{
    "message": "Order created successfully.",
    "status": "success",
    "data": {
        "id": 1,
        "user_id": 27,
        "total_amount": "199.98",
        "status": "pending"
    }
}
```

This comprehensive testing approach will validate your sophisticated cart system with both guest and authenticated functionality! 🎉

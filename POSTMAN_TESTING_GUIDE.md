# E-Commerce API Postman Testing Guide

## 📋 Setup Instructions

### 1. Import Files into Postman

1. Open Postman
2. Import the collection: `E-Commerce-API.postman_collection.json`
3. Import the environment: `E-Commerce-Environment.postman_environment.json`
4. Select the "E-Commerce API Environment" from the environment dropdown

### 2. Test Credentials

-   **Regular User**: `user@example.com` / `password123`
-   **Admin User**: `admin@example.com` / `password123`
-   **Base URL**: `http://e-commerce-api-new.test`

## 🚀 Testing Workflow

### Step 1: Authentication

1. **Register** (optional): Create a new user account
2. **Login**: Use existing credentials to get auth token
    - The login request automatically saves the token to `{{auth_token}}`
    - For admin testing, login with admin credentials and manually copy token to `{{admin_token}}`

### Step 2: Public Endpoints (No Authentication)

-   **Get Categories**: Browse all categories
-   **Get Category by ID**: View category details with products
-   **Get Products**: Browse products with optional filters
-   **Get Product by ID**: View single product details

### Step 3: Client Cart Management (Requires Authentication)

1. **Get Cart**: View current cart contents
2. **Add to Cart**: Add products with quantity
3. **Update Cart Item**: Change quantities
4. **Remove from Cart**: Remove specific items
5. **Clear Cart**: Empty the cart

### Step 4: Client Orders (Requires Authentication)

1. **Get Orders**: View user's order history
2. **Create Order**: Place a new order (requires items in cart)
3. **Get Order by ID**: View order details
4. **Cancel Order**: Cancel pending orders

### Step 5: Admin Endpoints (Requires Admin Authentication)

#### Products Management

-   **Get All Products**: Admin view of all products
-   **Create Product**: Add new products
-   **Update Product**: Modify existing products
-   **Delete Product**: Remove products

#### Categories Management

-   **Get All Categories**: Admin view of all categories
-   **Create Category**: Add new categories (with image upload)
-   **Update Category**: Modify categories (with image upload)
-   **Delete Category**: Remove categories

#### Orders Management

-   **Get All Orders**: View all orders in system
-   **Get Order by ID**: View any order details
-   **Update Order Status**: Change order status (pending, processing, shipped, delivered, cancelled)

#### Dashboard

-   **Get Dashboard**: View analytics and statistics

## 🔧 Variables Used

### Collection Variables

-   `{{base_url}}`: API base URL
-   `{{auth_token}}`: User authentication token
-   `{{admin_token}}`: Admin authentication token
-   `{{user_id}}`: Current user ID

### Environment Variables

Same as collection variables but stored in environment for easy switching between different setups.

## 📝 Testing Tips

1. **Authentication Flow**: Always login first to get tokens
2. **Token Management**:
    - Regular user token goes to `{{auth_token}}`
    - Admin token should be manually set in `{{admin_token}}`
3. **File Uploads**: For category creation/update, use form-data and upload actual image files
4. **Order Testing**: Add items to cart before creating orders
5. **Error Handling**: Check response status and error messages for debugging

## 🎯 Sample Test Scenarios

### Scenario 1: Complete Customer Journey

1. Register/Login as regular user
2. Browse categories and products
3. Add items to cart
4. Update cart quantities
5. Create an order
6. View order details

### Scenario 2: Admin Management

1. Login as admin
2. Create new category with image
3. Create new product in that category
4. View all orders
5. Update order status
6. Check dashboard analytics

### Scenario 3: Error Testing

1. Try accessing admin endpoints as regular user (should get 403)
2. Try accessing protected endpoints without token (should get 401)
3. Try creating order with empty cart
4. Try invalid login credentials

## 📊 Expected Response Formats

### Success Responses

-   **Authentication**: Returns user info + token
-   **Cart Operations**: Returns cart data with items and totals
-   **Orders**: Returns order details with items
-   **Admin Operations**: Returns created/updated resource data

### Error Responses

-   **401 Unauthorized**: Missing or invalid token
-   **403 Forbidden**: Insufficient permissions (non-admin accessing admin routes)
-   **404 Not Found**: Resource not found
-   **422 Validation Error**: Invalid input data

## 🔗 Useful Endpoints for Testing

### Quick Data Check

-   `GET /api/client/categories` - See available categories
-   `GET /api/client/products` - See available products
-   `GET /api/user` - Verify authentication

### Admin Quick Actions

-   `GET /api/admin/dashboard` - Check admin access
-   `GET /api/admin/orders` - See all orders
-   `GET /api/admin/products` - Admin product view

Happy Testing! 🎉

# 🛍️ E-Commerce API - Real-Time Laravel Application with Stripe Payments

A modern, production-ready e-commerce API built with Laravel 12, featuring Stripe payment integration, real-time WebSocket functionality, comprehensive authentication, and interactive API documentation.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.4-blue?style=flat-square&logo=php)
![Stripe](https://img.shields.io/badge/Stripe-Payment-blueviolet?style=flat-square&logo=stripe)
![WebSocket](https://img.shields.io/badge/WebSocket-Laravel%20Reverb-green?style=flat-square)
![API Docs](https://img.shields.io/badge/API%20Docs-Swagger-orange?style=flat-square)
![Real-time](https://img.shields.io/badge/Real--time-Broadcasting-purple?style=flat-square)

## 🚀 Features

### 🔐 **Authentication & Security**

-   User registration and login with Laravel Sanctum
-   Password reset with secure email tokens
-   Email verification system
-   Role-based access control (Admin/User)
-   API token authentication

### 🛒 **E-Commerce Core**

-   Product catalog with categories
-   Shopping cart functionality (guest & authenticated users)
-   Order management system
-   Inventory tracking
-   Order status management

### 💳 **Payment Integration**

-   **Stripe Payment Processing** with Laravel Cashier
-   **Multiple Payment Methods**: Cash on Delivery (COD) & Credit/Debit Cards
-   **Secure Payment Intents** with client-side confirmation
-   **Automatic Payment Status Updates** via webhooks
-   **Real-time Payment Processing** with immediate status feedback
-   **Failed Payment Handling** with proper error reporting
-   **3D Secure Support** for enhanced security

### ⚡ **Real-Time Features**

-   **Live cart synchronization** across devices
-   **Real-time order notifications** for admins
-   **Instant order status updates** for customers
-   **WebSocket broadcasting** with Laravel Reverb
-   **Event-driven architecture**

### 📚 **API Documentation**

-   **Interactive Swagger UI** documentation
-   **Auto-generated Postman collections**
-   **Comprehensive API guides**
-   **WebSocket integration examples**
-   **Testing procedures and samples**

### 📧 **Notifications & Email**

-   Order confirmation emails
-   Order status update notifications
-   Password reset emails
-   Email verification system
-   Professional email templates

## 🛠️ Tech Stack

-   **Backend:** Laravel 12.30.1, PHP 8.4.13
-   **Database:** MySQL with Redis caching
-   **Payments:** Stripe API with Laravel Cashier 16.0.3
-   **Real-time:** Laravel Reverb 1.6.0 (WebSocket)
-   **Authentication:** Laravel Sanctum 4.2.0
-   **Documentation:** L5-Swagger (OpenAPI/Swagger UI)
-   **Email:** Laravel Mail with notification system
-   **Caching:** Redis for sessions, cache, and broadcasting
-   **Testing:** PHPUnit 11.5 with Feature and Unit tests
-   **Development:** Laravel Herd for local environment

## 📋 Installation

### Prerequisites

-   PHP 8.4 or higher
-   Composer
-   Node.js & NPM
-   MySQL
-   Redis
-   Laravel Herd (recommended for local development)

### Setup Steps

1. **Clone the repository**

```bash
git clone https://github.com/Abdo0-bit/e-commerce-laravel-api.git
cd e-commerce-laravel-api
```

2. **Install dependencies**

```bash
composer install
npm install
```

3. **Environment setup**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure your `.env` file**

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e-commerce_api
DB_USERNAME=root
DB_PASSWORD=your_password

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=
REDIS_PORT=6379

# Broadcasting & Caching
BROADCAST_CONNECTION=reverb
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Laravel Reverb WebSocket
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# Email Configuration (for development use 'log')
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Stripe Payment Configuration
STRIPE_KEY=pk_test_your_stripe_publishable_key
STRIPE_SECRET=sk_test_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

5. **Database setup**

```bash
php artisan migrate:fresh --seed
```

6. **Start the services**

```bash
# If using Laravel Herd (Recommended)
# Your site will be automatically available at: http://e-commerce-api-new.test

# WebSocket server (required for real-time features)
php artisan reverb:start

# Asset compilation (if using frontend assets)
npm run dev

# Alternative: Manual Laravel server (if not using Herd)
# php artisan serve
```

## 📖 API Documentation

### Access Documentation

-   **Swagger UI:** `http://e-commerce-api-new.test/api/documentation`
-   **JSON Docs:** `http://e-commerce-api-new.test/docs`
-   **Postman Collection:** Available in repository root

### Key Endpoints

#### Authentication

```http
POST /api/register           # User registration
POST /api/login              # User login
POST /api/logout             # User logout
POST /api/forgot-password    # Send password reset email
POST /api/reset-password     # Reset password with token
```

#### Products & Categories

```http
GET    /api/client/products      # List all products
GET    /api/client/products/{id} # Get single product
GET    /api/client/categories    # List all categories
GET    /api/client/categories/{id} # Get single category
```

#### Shopping Cart

```http
GET    /api/client/cart          # Get cart contents
POST   /api/client/cart          # Add item to cart
PUT    /api/client/cart/{id}     # Update cart item
DELETE /api/client/cart/{id}     # Remove cart item
DELETE /api/client/cart/clear    # Clear entire cart
```

#### Orders (Authenticated)

```http
GET    /api/client/orders        # List user orders
POST   /api/client/orders        # Create new order (checkout)
GET    /api/client/orders/{id}   # Get single order
PATCH  /api/client/orders/{id}/cancel # Cancel order
```

**Order Creation with Payment Methods:**

```json
POST /api/client/orders
{
  "first_name": "John",
  "last_name": "Doe",
  "shipping_phone": "123-456-7890",
  "shipping_street": "123 Main St",
  "shipping_city": "Anytown",
  "shipping_state": "CA",
  "shipping_postal_code": "12345",
  "payment_method": "stripe"  // "stripe" or "cod"
}
```

**Stripe Response Example:**

```json
{
    "data": {
        "id": 22,
        "payment_method": "stripe",
        "payment_status": "unpaid",
        "stripe_client_secret": "pi_3SOMF4EFDrXcJGWZ1...",
        "total_amount": "49.99"
    }
}
```

#### Admin Endpoints

```http
GET    /api/admin/dashboard      # Admin dashboard stats
GET    /api/admin/orders         # All orders management
PUT    /api/admin/orders/{id}    # Update order status
POST   /api/admin/products       # Create product
PUT    /api/admin/products/{id}  # Update product
DELETE /api/admin/products/{id}  # Delete product
```

#### Stripe Webhooks

```http
POST   /api/stripe/webhook       # Stripe webhook endpoint (no auth required)
```

**Handled Webhook Events:**

-   `payment_intent.succeeded` - Updates order to paid status
-   `payment_intent.payment_failed` - Updates order to failed status
-   `payment_intent.requires_action` - Updates order to requires_action status

**Webhook Configuration:**

1. Set up webhook endpoint in Stripe Dashboard: `https://your-domain.com/api/stripe/webhook`
2. Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `payment_intent.requires_action`
3. Copy webhook secret to `STRIPE_WEBHOOK_SECRET` in `.env`

## 🔄 Real-Time Features

### WebSocket Events

The API broadcasts real-time events for live updates:

#### Cart Events

```javascript
// Listen for cart updates
Echo.channel("cart").listen("CartUpdated", (e) => {
    console.log("Cart updated:", e.cart);
    // Update cart UI
});
```

#### Order Events

```javascript
// Listen for order status changes
Echo.private("orders." + userId).listen("OrderStatusUpdated", (e) => {
    console.log("Order status changed:", e.order);
    // Update order status in UI
});

// Admin: Listen for new orders
Echo.private("admin-orders").listen("NewOrderCreated", (e) => {
    console.log("New order received:", e.order);
    // Show notification to admin
});
```

### WebSocket Authentication

Private channels require authentication:

```javascript
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Echo = new Echo({
    broadcaster: "reverb",
    key: "your-app-key",
    wsHost: "127.0.0.1",
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ["ws", "wss"],
    auth: {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    },
});
```

## 🧪 Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthControllerTest.php

# Run with coverage
php artisan test --coverage
```

### Test Categories

-   **Authentication Tests:** Registration, login, password reset
-   **Product Tests:** CRUD operations, filtering, search
-   **Cart Tests:** Add/remove items, guest cart, persistence
-   **Order Tests:** Checkout process, status updates, cancellation
-   **Admin Tests:** Dashboard, order management, product management

## 📁 Project Structure

```
├── app/
│   ├── Events/              # Real-time broadcasting events
│   ├── Http/Controllers/    # API controllers (Admin & Client)
│   ├── Http/Requests/       # Form request validation
│   ├── Http/Resources/      # API resource transformers
│   ├── Models/              # Eloquent models
│   ├── Notifications/       # Email notifications
│   └── Services/            # Business logic services
├── config/
│   ├── broadcasting.php     # WebSocket configuration
│   ├── l5-swagger.php      # API documentation config
│   └── reverb.php          # Laravel Reverb config
├── database/
│   ├── factories/          # Model factories for testing
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── routes/
│   ├── api.php            # API routes
│   ├── channels.php       # WebSocket channel definitions
│   └── web.php           # Web routes (documentation)
├── storage/api-docs/      # Generated API documentation
└── tests/                # PHPUnit tests
```

## 🔧 Configuration

### WebSocket Configuration

Configure Laravel Reverb in `.env`:

```env
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Redis Configuration

For caching and broadcasting:

```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 💳 Stripe Integration Guide

### Frontend Integration with Stripe.js

When creating an order with `payment_method: "stripe"`, you'll receive a `stripe_client_secret`. Use this with Stripe.js to complete the payment:

#### 1. Install Stripe.js

```html
<script src="https://js.stripe.com/v3/"></script>
```

#### 2. Create Payment Form

```javascript
const stripe = Stripe("pk_test_your_stripe_publishable_key");
const elements = stripe.elements();
const cardElement = elements.create("card");
cardElement.mount("#card-element");

// When order is created with payment_method: "stripe"
async function handlePayment(clientSecret) {
    const { error, paymentIntent } = await stripe.confirmCardPayment(
        clientSecret,
        {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: "Customer Name",
                    email: "customer@example.com",
                },
            },
        }
    );

    if (error) {
        console.error("Payment failed:", error);
        // Handle payment error
    } else if (paymentIntent.status === "succeeded") {
        console.log("Payment succeeded!");
        // Redirect to success page
        // The webhook will automatically update the order status
    }
}
```

#### 3. Order Flow

1. **Create Order**: POST to `/api/client/orders` with `payment_method: "stripe"`
2. **Get Client Secret**: Extract `stripe_client_secret` from response
3. **Process Payment**: Use Stripe.js with the client secret
4. **Webhook Updates**: Order status automatically updated via webhooks
5. **Success Handling**: Redirect user to confirmation page

### Payment Status Flow

```
Order Created (payment_status: "unpaid")
        ↓
Customer Pays (Stripe.js)
        ↓
Webhook Received (payment_intent.succeeded)
        ↓
Order Updated (payment_status: "paid", status: "processing")
        ↓
Order Fulfillment
```

### Testing with Stripe Test Cards

```javascript
// Test card numbers for different scenarios:
"4242424242424242"; // Visa - Succeeds
"4000000000000002"; // Visa - Declined
"4000000000003220"; // Visa - 3D Secure required
```

## 📊 Performance Features

-   **Redis Caching:** Fast data retrieval and session management
-   **Database Indexing:** Optimized queries for products and orders
-   **Eager Loading:** Prevent N+1 query problems
-   **API Rate Limiting:** Prevent abuse and ensure stability
-   **WebSocket Efficiency:** Real-time updates without polling
-   **Stripe Optimization:** Efficient payment processing with Laravel Cashier

## 🔒 Security Features

-   **CORS Protection:** Secure cross-origin requests
-   **SQL Injection Prevention:** Eloquent ORM protection
-   **XSS Protection:** Input validation and sanitization
-   **Rate Limiting:** API endpoint protection
-   **Secure Authentication:** Sanctum token-based auth
-   **Password Hashing:** Bcrypt encryption
-   **CSRF Protection:** Form request security
-   **Stripe Security:** PCI-compliant payment processing with webhook signature verification
-   **Payment Data Protection:** Sensitive payment data never stored on server

## 📈 Monitoring & Logging

-   **Laravel Logs:** Comprehensive error and activity logging
-   **API Request Logging:** Track API usage and performance
-   **Real-time Error Tracking:** Immediate error notifications
-   **Database Query Logging:** Monitor and optimize performance

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Abdulrahman** - [GitHub Profile](https://github.com/Abdo0-bit)

---

⭐ **Star this repository if you found it helpful!**

📧 **Questions?** Feel free to open an issue or contact me directly.

🚀 **Ready to build amazing e-commerce experiences!**

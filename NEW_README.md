# E-Commerce API with Real-time Features

A comprehensive Laravel 12 e-commerce API featuring real-time WebSocket functionality, complete CRUD operations, authentication, and extensive API documentation.

## 🚀 Features

### Core E-commerce Functionality

-   ✅ **User Authentication** - Registration, login with Laravel Sanctum
-   ✅ **Product Management** - Full CRUD operations with categories
-   ✅ **Shopping Cart** - Guest and authenticated user support with auto-merge
-   ✅ **Order Processing** - Complete order workflow with status tracking
-   ✅ **Admin Panel** - Administrative control over products, categories, and orders
-   ✅ **Role-based Access** - Client and admin role separation

### Real-time Features (NEW!)

-   🔄 **Live Cart Updates** - Real-time cart synchronization across devices
-   📦 **Order Status Broadcasting** - Instant order status updates
-   🛠️ **Admin Notifications** - Real-time new order alerts for admins
-   📡 **WebSocket Integration** - Laravel Reverb for real-time communication

### API Documentation

-   📚 **Swagger/OpenAPI** - Interactive API documentation
-   🧪 **Postman Collection** - Complete API testing collection
-   📖 **Comprehensive Docs** - Detailed endpoint documentation

## 🛠️ Tech Stack

-   **Backend**: Laravel 12.30.1, PHP 8.4.13
-   **Database**: MySQL with comprehensive indexing
-   **Authentication**: Laravel Sanctum 4.2.0
-   **Real-time**: Laravel Reverb 1.6.0
-   **Caching**: Redis
-   **Queue**: Redis-based job processing
-   **Documentation**: Swagger/OpenAPI, L5-Swagger
-   **Testing**: PHPUnit 11.5.39 with 44 passing tests

## 📋 Quick Start

### Prerequisites

-   PHP 8.4+
-   MySQL
-   Redis
-   Composer
-   Node.js & npm

### Installation

1. **Clone the repository**

```bash
git clone <repository-url>
cd e-commerce-api-new
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

4. **Configure your .env file**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e-commerce_api
DB_USERNAME=root
DB_PASSWORD=your_password

BROADCAST_CONNECTION=reverb
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REVERB_APP_ID=152962
REVERB_APP_KEY=mbdu19pekmgbq2mzpd89
REVERB_APP_SECRET=1cai9whnktwnuetioox8
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

5. **Database setup**

```bash
php artisan migrate
php artisan db:seed
```

6. **Generate API documentation**

```bash
php artisan l5-swagger:generate
```

### Running the Application

1. **Start the Laravel server**

```bash
php artisan serve
```

2. **Start the WebSocket server (for real-time features)**

```bash
php artisan reverb:start
```

3. **Start the queue worker**

```bash
php artisan queue:work
```

Your API will be available at: `http://localhost:8000/api`

## 📚 API Documentation

### Interactive Documentation

-   **Swagger UI**: Visit `/api/documentation` for interactive API docs
-   **JSON Schema**: Available at `/docs/api-docs.json`

### Quick Links

-   **API Base URL**: `http://e-commerce-api-new.test/api` (using Laravel Herd)
-   **Documentation**: `http://e-commerce-api-new.test/api/documentation`
-   **Postman Collection**: Import the generated collection from the repository

## 🔄 Real-time Events

### WebSocket Events

The API broadcasts the following real-time events:

#### Cart Events

-   **Channel**: `cart.{cartId}`
-   **Event**: `cart.updated`
-   **Data**: Cart contents, user info, timestamp

#### Order Events

-   **Channel**: `user.{userId}` (private)
-   **Events**:
    -   `order.created` - New order notification
    -   `order.status.updated` - Status change notifications

#### Admin Events

-   **Channel**: `admin.orders` (private, admin only)
-   **Events**:
    -   `order.created` - New order alerts
    -   `order.status.updated` - Order status changes

### WebSocket Authentication

Private channels require authentication via Laravel Sanctum tokens.

## 🏗️ Architecture

### Directory Structure

```
app/
├── Events/             # Real-time broadcasting events
├── Http/
│   ├── Controllers/
│   │   ├── Api/Admin/  # Admin management endpoints
│   │   └── Api/Client/ # Customer-facing endpoints
│   ├── Requests/       # Form request validation
│   └── Resources/      # API response transformers
├── Models/            # Eloquent models
├── Services/          # Business logic layer
└── Jobs/              # Background job processing

routes/
├── api.php            # API routes
└── channels.php       # WebSocket channel auth
```

### API Structure

-   **Authentication**: `/api/register`, `/api/login`
-   **Client Endpoints**: `/api/client/*` - Product browsing, cart, orders
-   **Admin Endpoints**: `/api/admin/*` - Management operations (requires admin role)

## 🧪 Testing

Run the comprehensive test suite:

```bash
php artisan test
```

**Current Status**: ✅ 44/44 tests passing with 107 assertions

### Test Coverage

-   Authentication flow
-   Product CRUD operations
-   Cart functionality (guest + authenticated)
-   Order processing
-   Admin operations
-   Real-time event broadcasting

## 🔒 Security Features

-   **CSRF Protection**: Built-in Laravel protection
-   **Rate Limiting**: API throttling on authentication endpoints
-   **Input Validation**: Comprehensive request validation
-   **Role-based Access**: Admin/client role separation
-   **Secure Headers**: Production-ready security headers
-   **Token Authentication**: Laravel Sanctum with expiration

## 📈 Performance Features

-   **Database Indexing**: Optimized queries with proper indexing
-   **Redis Caching**: Session and application caching
-   **Queue Processing**: Background job processing
-   **Lazy Loading**: Optimized Eloquent relationships
-   **API Resources**: Consistent response formatting

## 🚀 Deployment

### Production Checklist

-   [ ] Configure proper mail service (replace log driver)
-   [ ] Set up production Redis server
-   [ ] Configure proper logging (replace single with stack)
-   [ ] Set up SSL certificates
-   [ ] Configure production WebSocket server
-   [ ] Set up proper queue worker management
-   [ ] Configure database backups
-   [ ] Set up monitoring and alerting

### Environment Variables (Production)

```env
APP_ENV=production
APP_DEBUG=false
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
MAIL_MAILER=smtp
LOG_LEVEL=warning
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 📞 Support

For questions or issues:

-   Create an issue on GitHub
-   Check the API documentation
-   Review the test files for usage examples

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

**Built with ❤️ using Laravel 12 and modern web technologies**

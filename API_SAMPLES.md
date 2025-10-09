# Sample API Requests for Quick Testing

## Authentication

### Login Request

```json
POST http://e-commerce-api-new.test/api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

### Admin Login

```json
POST http://e-commerce-api-new.test/api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password123"
}
```

## Sample Responses

### Login Success Response

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

### Cart Response

```json
{
    "status": "success",
    "data": {
        "items": [
            {
                "product_id": 1,
                "name": "Product Name",
                "price": 99.99,
                "quantity": 2,
                "total": 199.98
            }
        ],
        "total": 199.98,
        "item_count": 2
    }
}
```

### Products Response

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "Product Name",
            "description": "Product description",
            "price": "99.99",
            "images": ["image1.jpg"],
            "is_active": true,
            "category_id": 1
        }
    ],
    "total": 25
}
```

## Test Data Available

### Users

-   Regular User: `user@example.com` / `password123`
-   Admin User: `admin@example.com` / `password123`

### Database

-   8 Categories available
-   25 Products available
-   Various test users

## Quick Test URLs (GET requests, no auth needed)

-   Categories: http://e-commerce-api-new.test/api/client/categories
-   Products: http://e-commerce-api-new.test/api/client/products
-   Single Category: http://e-commerce-api-new.test/api/client/categories/1
-   Single Product: http://e-commerce-api-new.test/api/client/products/1

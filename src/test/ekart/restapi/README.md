# eKart REST API Documentation

## Overview
RESTful API for eCommerce application with JWT Bearer token authentication.

## Base URL
```
http://localhost:8085/ekart/restapi
```

## Authentication

### Register User
**POST** `/auth.php`

**Request Body:**
```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "securepassword123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user_id": 1,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### Login
**POST** `/auth.php?action=login`

**Request Body:**
```json
{
  "username": "john_doe",
  "password": "securepassword123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "user_id": 1,
  "username": "john_doe",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

---

## User Profile Management

### Get User Profile
**GET** `/users.php?id=1`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
  "id": 1,
  "username": "john_doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "zipcode": "10001",
  "gender": "Male",
  "date_of_birth": "1990-01-01",
  "interests": "Electronics, Gaming",
  "created_at": "2026-02-13 10:00:00"
}
```

### Update User Profile
**PUT** `/users.php?id=1`

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "phone": "+1234567890",
  "address": "456 Oak Ave",
  "city": "Los Angeles",
  "interests": "Gaming, Movies"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "User profile updated successfully"
}
```

### Delete User
**DELETE** `/users.php?id=1`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

---

## Product Management

### Get All Products
**GET** `/products.php`

**Optional Query Parameters:**
- `category` - Filter by category
- `search` - Search in name and description

**Response (200):**
```json
{
  "products": [
    {
      "id": 1,
      "name": "Wireless Mouse",
      "price": 29.99,
      "category": "Electronics",
      "image": "uploads/mouse.jpg",
      "description": "Ergonomic wireless mouse"
    }
  ]
}
```

### Get Single Product
**GET** `/products.php?id=1`

**Response (200):**
```json
{
  "id": 1,
  "name": "Wireless Mouse",
  "price": 29.99,
  "category": "Electronics",
  "image": "uploads/mouse.jpg",
  "description": "Ergonomic wireless mouse"
}
```

### Create Product
**POST** `/products.php`

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "name": "Gaming Headset",
  "price": 79.99,
  "category": "Electronics",
  "image": "uploads/headset.jpg",
  "description": "7.1 surround sound gaming headset"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Product created successfully",
  "product_id": 6
}
```

### Update Product
**PUT** `/products.php?id=1`

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "name": "Wireless Gaming Mouse",
  "price": 34.99
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product updated successfully"
}
```

### Delete Product
**DELETE** `/products.php?id=1`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

---

## Order Management

### Get All Orders
**GET** `/orders.php`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
  "orders": [
    {
      "id": 1,
      "order_number": "ORD-1707823200-5432",
      "full_name": "John Doe",
      "address": "123 Main St, New York, NY",
      "payment_method": "Cash on Delivery",
      "total": 119.97,
      "status": "pending",
      "created_at": "2026-02-13 10:00:00"
    }
  ]
}
```

### Get Single Order
**GET** `/orders.php?id=1`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
  "id": 1,
  "order_number": "ORD-1707823200-5432",
  "full_name": "John Doe",
  "address": "123 Main St",
  "total": 119.97,
  "status": "pending",
  "items": [
    {
      "product_id": 1,
      "product_name": "Wireless Mouse",
      "product_price": 29.99,
      "quantity": 2,
      "price": 29.99
    }
  ]
}
```

### Create Order
**POST** `/orders.php`

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "full_name": "John Doe",
  "address": "123 Main St, New York, NY 10001",
  "payment_method": "Credit Card",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Order created successfully",
  "order_id": 5,
  "order_number": "ORD-1707823200-5432",
  "total": 119.97
}
```

### Update Order
**PUT** `/orders.php?id=1`

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "status": "completed",
  "address": "456 New Address"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Order updated successfully"
}
```

### Cancel Order
**DELETE** `/orders.php?id=1`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
  "success": true,
  "message": "Order cancelled successfully"
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "error": "Invalid request parameters"
}
```

### 401 Unauthorized
```json
{
  "error": "Authorization header missing"
}
```

### 403 Forbidden
```json
{
  "error": "Unauthorized to access this resource"
}
```

### 404 Not Found
```json
{
  "error": "Resource not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal server error message"
}
```

---

## Docker Setup

### Build and Run
```bash
# Navigate to project directory
cd c:\phpapps\src\test\ekart

# Build and start containers
docker-compose up -d --build

# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Remove volumes (reset database)
docker-compose down -v
```

### Access Points
- **Web Application:** http://localhost:8085/ekart
- **REST API:** http://localhost:8085/ekart/restapi
- **MySQL Database:** localhost:3307

### Environment Variables
Configure in `docker-compose.yml`:
- `DB_HOST` - Database host (default: db)
- `DB_NAME` - Database name (default: my_db)
- `DB_USER` - Database user (default: root)
- `DB_PASS` - Database password
- `JWT_SECRET` - Secret key for JWT tokens

---

## Testing with cURL

### Register
```bash
curl -X POST http://localhost:8085/ekart/restapi/auth.php \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","email":"test@example.com","password":"test123"}'
```

### Login
```bash
curl -X POST http://localhost:8085/ekart/restapi/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"test123"}'
```

### Get Products
```bash
curl http://localhost:8085/ekart/restapi/products.php
```

### Create Order (with token)
```bash
curl -X POST http://localhost:8085/ekart/restapi/orders.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"full_name":"John Doe","address":"123 Main St","items":[{"product_id":1,"quantity":2}]}'
```

---

## Testing with Postman

1. Import the API endpoints into Postman
2. Create an environment with variable `base_url` = `http://localhost:8085/ekart/restapi`
3. After login, save the token in environment variable `token`
4. Use `Bearer {{token}}` in Authorization header for protected endpoints

---

## Security Notes

1. **Change JWT_SECRET** in production
2. **Use HTTPS** in production
3. **Validate all inputs** on client and server
4. **Rate limiting** should be implemented
5. **Token expiry** is set to 24 hours
6. Users can only access their own data
7. Passwords are hashed with bcrypt

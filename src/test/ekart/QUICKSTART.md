# Quick Start Guide

## Prerequisites
- Docker Desktop installed
- Port 8085 and 3307 available

## Getting Started

### 1. Build and Run the Docker Containers

```bash
cd c:\phpapps\src\test\ekart
docker-compose up -d --build
```

This will:
- Build the PHP Apache container
- Start MySQL database
- Initialize database with tables
- Import sample products

### 2. Verify Containers are Running

```bash
docker-compose ps
```

You should see:
- ekart-web (port 8085)
- ekart-db (port 3307)

### 3. Test the API

#### Register a User
```bash
curl -X POST http://localhost:8085/ekart/restapi/auth.php ^
  -H "Content-Type: application/json" ^
  -d "{\"username\":\"testuser\",\"email\":\"test@example.com\",\"password\":\"test123\"}"
```

Save the token from the response!

#### Login
```bash
curl -X POST http://localhost:8085/ekart/restapi/auth.php?action=login ^
  -H "Content-Type: application/json" ^
  -d "{\"username\":\"testuser\",\"password\":\"test123\"}"
```

#### Get All Products
```bash
curl http://localhost:8085/ekart/restapi/products.php
```

#### Create an Order (Replace YOUR_TOKEN with actual token)
```bash
curl -X POST http://localhost:8085/ekart/restapi/orders.php ^
  -H "Content-Type: application/json" ^
  -H "Authorization: Bearer YOUR_TOKEN" ^
  -d "{\"full_name\":\"John Doe\",\"address\":\"123 Main St\",\"items\":[{\"product_id\":1,\"quantity\":2}]}"
```

### 4. Access the Web Interface

Open browser: http://localhost:8085/ekart/

### 5. View Logs

```bash
docker-compose logs -f web
docker-compose logs -f db
```

### 6. Stop the Application

```bash
docker-compose down
```

### 7. Reset Everything (including database)

```bash
docker-compose down -v
docker-compose up -d --build
```

## Troubleshooting

### Port already in use
Edit `docker-compose.yml` and change:
```yaml
ports:
  - "8086:80"  # Change 8085 to another port
```

### Database connection failed
Wait 30 seconds for MySQL to fully start, then restart:
```bash
docker-compose restart web
```

### View database
```bash
docker exec -it ekart-db mysql -uroot -proot_password my_db
```

## API Endpoints Summary

### Public (No Auth Required)
- `POST /auth.php` - Register
- `POST /auth.php?action=login` - Login
- `GET /products.php` - List products
- `GET /products.php?id=1` - Get product

### Protected (Bearer Token Required)
- `GET /users.php?id=1` - Get user profile
- `PUT /users.php?id=1` - Update profile
- `DELETE /users.php?id=1` - Delete user
- `POST /products.php` - Create product
- `PUT /products.php?id=1` - Update product
- `DELETE /products.php?id=1` - Delete product
- `GET /orders.php` - List orders
- `GET /orders.php?id=1` - Get order
- `POST /orders.php` - Create order
- `PUT /orders.php?id=1` - Update order
- `DELETE /orders.php?id=1` - Cancel order

## Next Steps

1. Test all endpoints with Postman or Insomnia
2. Change JWT_SECRET in docker-compose.yml
3. Add more products via API
4. Implement frontend integration
5. Deploy to production server

For full documentation, see: restapi/README.md

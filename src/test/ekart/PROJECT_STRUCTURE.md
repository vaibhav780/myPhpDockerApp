# eKart Project Structure

## Overview
This eKart application consists of two parts:
1. **Web Interface** - Session-based authentication for browser users
2. **REST API** - JWT token-based authentication for programmatic access

Both share the same MySQL database but use different authentication mechanisms.

---

## Directory Structure

```
ekart/
├── restapi/                    # REST API Files (All API endpoints here)
│   ├── config.php             # JWT configuration, database connection, helper functions
│   ├── auth.php               # POST /register, POST /login endpoints
│   ├── users.php              # GET/PUT/DELETE /users (profile management)
│   ├── products.php           # GET/POST/PUT/DELETE /products (catalog management)
│   ├── orders.php             # GET/POST/PUT/DELETE /orders (order processing)
│   ├── sample.php             # Sample API endpoint for testing
│   └── README.md              # Complete API documentation
│
├── Web Interface Files
│   ├── index.php              # Landing page
│   ├── register.php           # User registration
│   ├── products.php           # Product catalog with filters
│   ├── product_info.php       # Product detail page
│   ├── cart.php               # Shopping cart
│   ├── checkout.php           # Checkout process
│   ├── order_success.php      # Order confirmation
│   ├── user_profile.php       # User profile management
│   ├── search_suggestions.php # Autocomplete endpoint
│   ├── logout.php             # User logout
│   │
│   ├── admin_dashboard.php    # Admin product management
│   ├── admin_login.php        # Admin login
│   └── admin_logout.php       # Admin logout
│
├── Database Files
│   ├── db.php                 # Database connection for web
│   ├── db_connect.php         # Alternative DB connection
│   └── init.sql               # Database schema initialization
│
├── Docker Configuration
│   ├── Dockerfile             # PHP 8.2-apache container image
│   ├── docker-compose.yml     # Multi-container orchestration (web + db)
│   ├── .dockerignore          # Files to exclude from Docker build
│   └── .htaccess              # Apache rewrite rules & CORS headers
│
├── Documentation
│   ├── QUICKSTART.md          # Quick deployment guide
│   ├── PROJECT_STRUCTURE.md   # This file
│   ├── test-api.bat           # Windows API test script
│   └── test-api.sh            # Linux/Mac API test script
│
└── uploads/                   # Product image uploads directory

```

---

## REST API Endpoints

All REST API endpoints are located in the `/restapi/` folder:

### Base URL
```
http://localhost:8085/ekart/restapi
```

### Public Endpoints (No Authentication)
- `POST /auth.php` - Register new user
- `POST /auth.php?action=login` - Login and get JWT token
- `GET /products.php` - List all products
- `GET /products.php?id=X` - Get single product details

### Protected Endpoints (Require Bearer Token)

#### User Management
- `GET /users.php?id=X` - Get user profile
- `PUT /users.php?id=X` - Update user profile
- `DELETE /users.php?id=X` - Delete user account

#### Product Management (Admin Only)
- `POST /products.php` - Create new product
- `PUT /products.php?id=X` - Update product
- `DELETE /products.php?id=X` - Delete product

#### Order Management
- `GET /orders.php` - List user's orders
- `GET /orders.php?id=X` - Get single order details
- `POST /orders.php` - Create new order
- `PUT /orders.php?id=X` - Update order
- `DELETE /orders.php?id=X` - Cancel order

---

## Authentication

### Web Interface
- **Type:** Session-based
- **Storage:** PHP sessions
- **Pages:** All `*.php` files in root (except restapi/)
- **Access:** Browser users

### REST API
- **Type:** JWT Bearer tokens
- **Storage:** Client-side (localStorage, mobile app, etc.)
- **Endpoints:** All files in `/restapi/` folder
- **Access:** External applications, mobile apps, third-party integrations

**Token Format:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

---

## Database Schema

**Database Name:** `my_db`

### Tables
1. **users** - User accounts with profile information
   - Columns: id, username, email, password, phone, address, city, state, zipcode, gender, date_of_birth, interests, created_at

2. **products** - Product catalog
   - Columns: id, name, price, description, category, image, created_at, updated_at

3. **orders** - Customer orders
   - Columns: id, user_id, full_name, address, payment_method, status, total_amount, created_at

4. **order_items** - Order line items
   - Columns: id, order_id, product_id, quantity, price

5. **cart_items** - Shopping cart
   - Columns: id, user_id, product_id, quantity, added_at

---

## Deployment

### Development (Local)
```bash
cd c:\phpapps\src\test\ekart
docker-compose up -d --build
```

**Access Points:**
- Web: http://localhost:8085/ekart/
- API: http://localhost:8085/ekart/restapi/
- DB: localhost:3307

### Production Checklist
1. ✅ Change `JWT_SECRET` in docker-compose.yml
2. ✅ Update database credentials
3. ✅ Enable HTTPS
4. ✅ Set proper CORS origins (not `*`)
5. ✅ Implement rate limiting
6. ✅ Add API request logging
7. ✅ Set up database backups
8. ✅ Configure firewall rules

---

## Testing

### Quick Test (Windows)
```bash
.\test-api.bat
```

### Quick Test (Linux/Mac)
```bash
./test-api.sh
```

### Manual Testing
1. Register: `curl -X POST http://localhost:8085/ekart/restapi/auth.php -H "Content-Type: application/json" -d "{\"username\":\"test\",\"email\":\"test@example.com\",\"password\":\"test123\"}"`
2. Save the token from response
3. Use token: `curl http://localhost:8085/ekart/restapi/orders.php -H "Authorization: Bearer YOUR_TOKEN"`

---

## File Organization Benefits

### ✅ Clear Separation
- Web interface files in root
- API files in `/restapi/` folder
- Easy to locate and maintain

### ✅ Scalability
- Can deploy API separately if needed
- Different scaling strategies for web vs API
- Independent versioning

### ✅ Security
- Different authentication mechanisms
- API can have stricter rate limits
- Easier to implement API-specific middleware

### ✅ Documentation
- All API docs in one place
- Easier for external developers to integrate
- Clear API surface area

---

## Quick Reference

| Need | Location |
|------|----------|
| API Documentation | `/restapi/README.md` |
| Quick Start Guide | `/QUICKSTART.md` |
| Web Interface | Root `*.php` files |
| All API Endpoints | `/restapi/` folder |
| Database Schema | `/init.sql` |
| Docker Config | `/docker-compose.yml` |
| Test Scripts | `/test-api.bat` or `.sh` |

---

## Support

For detailed API documentation, see [restapi/README.md](restapi/README.md)

For deployment instructions, see [QUICKSTART.md](QUICKSTART.md)

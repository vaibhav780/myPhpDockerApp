@echo off
echo =========================================
echo eKart REST API Test Script (Windows)
echo =========================================
echo.

set BASE_URL=http://localhost:8085/ekart/restapi

echo 1. Testing Registration...
curl -X POST %BASE_URL%/auth.php -H "Content-Type: application/json" -d "{\"username\":\"apitest\",\"email\":\"apitest@example.com\",\"password\":\"test123\"}"
echo.
echo.

echo 2. Testing Login...
curl -X POST "%BASE_URL%/auth.php?action=login" -H "Content-Type: application/json" -d "{\"username\":\"apitest\",\"password\":\"test123\"}"
echo.
echo.

echo 3. Testing Get Products (Public)...
curl %BASE_URL%/products.php
echo.
echo.

echo SAVE THE TOKEN FROM LOGIN RESPONSE ABOVE!
echo.
set /p TOKEN="Enter your Bearer token: "

echo.
echo 4. Testing Create Product (Protected)...
curl -X POST %BASE_URL%/products.php -H "Content-Type: application/json" -H "Authorization: Bearer %TOKEN%" -d "{\"name\":\"Test Product\",\"price\":99.99,\"category\":\"Test Category\"}"
echo.
echo.

echo 5. Testing Create Order (Protected)...
curl -X POST %BASE_URL%/orders.php -H "Content-Type: application/json" -H "Authorization: Bearer %TOKEN%" -d "{\"full_name\":\"Test User\",\"address\":\"123 Test St\",\"items\":[{\"product_id\":1,\"quantity\":2}]}"
echo.
echo.

echo 6. Testing Get Orders (Protected)...
curl %BASE_URL%/orders.php -H "Authorization: Bearer %TOKEN%"
echo.
echo.

echo =========================================
echo All tests completed!
echo =========================================
pause

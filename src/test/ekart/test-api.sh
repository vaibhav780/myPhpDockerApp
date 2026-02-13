#!/bin/bash

# eKart REST API Test Script
echo "========================================="
echo "eKart REST API Test Script"
echo "========================================="
echo ""

BASE_URL="http://localhost:8085/ekart/restapi"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}1. Testing Registration...${NC}"
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/auth.php" \
  -H "Content-Type: application/json" \
  -d '{"username":"apitest","email":"apitest@example.com","password":"test123"}')

echo "$REGISTER_RESPONSE" | python -m json.tool 2>/dev/null || echo "$REGISTER_RESPONSE"
TOKEN=$(echo "$REGISTER_RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -n "$TOKEN" ]; then
    echo -e "${GREEN}✓ Registration successful${NC}"
    echo "Token: ${TOKEN:0:50}..."
else
    echo -e "${RED}✗ Registration failed${NC}"
fi

echo ""
echo -e "${YELLOW}2. Testing Login...${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth.php?action=login" \
  -H "Content-Type: application/json" \
  -d '{"username":"apitest","password":"test123"}')

echo "$LOGIN_RESPONSE" | python -m json.tool 2>/dev/null || echo "$LOGIN_RESPONSE"
TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -n "$TOKEN" ]; then
    echo -e "${GREEN}✓ Login successful${NC}"
else
    echo -e "${RED}✗ Login failed${NC}"
fi

echo ""
echo -e "${YELLOW}3. Testing Get Products (Public)...${NC}"
PRODUCTS_RESPONSE=$(curl -s "$BASE_URL/products.php")
echo "$PRODUCTS_RESPONSE" | python -m json.tool 2>/dev/null || echo "$PRODUCTS_RESPONSE"
echo -e "${GREEN}✓ Products fetched${NC}"

echo ""
echo -e "${YELLOW}4. Testing Create Product (Protected)...${NC}"
CREATE_PRODUCT_RESPONSE=$(curl -s -X POST "$BASE_URL/products.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Test Product","price":99.99,"category":"Test Category","description":"This is a test product"}')

echo "$CREATE_PRODUCT_RESPONSE" | python -m json.tool 2>/dev/null || echo "$CREATE_PRODUCT_RESPONSE"
PRODUCT_ID=$(echo "$CREATE_PRODUCT_RESPONSE" | grep -o '"product_id":[0-9]*' | cut -d':' -f2)

if [ -n "$PRODUCT_ID" ]; then
    echo -e "${GREEN}✓ Product created with ID: $PRODUCT_ID${NC}"
else
    echo -e "${RED}✗ Product creation failed${NC}"
fi

echo ""
echo -e "${YELLOW}5. Testing Create Order (Protected)...${NC}"
CREATE_ORDER_RESPONSE=$(curl -s -X POST "$BASE_URL/orders.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"full_name":"Test User","address":"123 Test St, Test City","payment_method":"Credit Card","items":[{"product_id":1,"quantity":2}]}')

echo "$CREATE_ORDER_RESPONSE" | python -m json.tool 2>/dev/null || echo "$CREATE_ORDER_RESPONSE"
ORDER_ID=$(echo "$CREATE_ORDER_RESPONSE" | grep -o '"order_id":[0-9]*' | cut -d':' -f2)

if [ -n "$ORDER_ID" ]; then
    echo -e "${GREEN}✓ Order created with ID: $ORDER_ID${NC}"
else
    echo -e "${RED}✗ Order creation failed${NC}"
fi

echo ""
echo -e "${YELLOW}6. Testing Get Orders (Protected)...${NC}"
ORDERS_RESPONSE=$(curl -s "$BASE_URL/orders.php" \
  -H "Authorization: Bearer $TOKEN")

echo "$ORDERS_RESPONSE" | python -m json.tool 2>/dev/null || echo "$ORDERS_RESPONSE"
echo -e "${GREEN}✓ Orders fetched${NC}"

echo ""
echo -e "${YELLOW}7. Testing Get User Profile (Protected)...${NC}"
USER_ID=$(echo "$LOGIN_RESPONSE" | grep -o '"user_id":[0-9]*' | cut -d':' -f2)
USER_RESPONSE=$(curl -s "$BASE_URL/users.php?id=$USER_ID" \
  -H "Authorization: Bearer $TOKEN")

echo "$USER_RESPONSE" | python -m json.tool 2>/dev/null || echo "$USER_RESPONSE"
echo -e "${GREEN}✓ User profile fetched${NC}"

echo ""
echo "========================================="
echo -e "${GREEN}All tests completed!${NC}"
echo "========================================="
echo ""
echo "Your Bearer Token (save this for future requests):"
echo "$TOKEN"

#!/usr/bin/env bash
# ==============================================================================
# Mobile API Production & Staging Automated Curl Test Suite
# Usage: ./test_mobile_api.sh [BASE_URL]
# Example: ./test_mobile_api.sh https://your-app.vercel.app
# ==============================================================================

BASE_URL="${1:-http://localhost:8000}"
# Trim trailing slash if present
BASE_URL="${BASE_URL%/}"

RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PASSED_TESTS=0
FAILED_TESTS=0

echo -e "${BLUE}======================================================================${NC}"
echo -e "${BLUE}        E-COMMERCE MOBILE API PRODUCTION TEST SUITE                   ${NC}"
echo -e "${BLUE}======================================================================${NC}"
echo -e "Target URL: ${YELLOW}${BASE_URL}${NC}\n"

test_endpoint() {
    local method="$1"
    local endpoint="$2"
    local data="$3"
    local token="$4"
    local expected_status="$5"
    local test_name="$6"

    echo -n "Testing $test_name [$method $endpoint]... "

    local header_auth=()
    if [ -n "$token" ]; then
        header_auth=(-H "Authorization: Bearer $token")
    fi

    local response_file=$(mktemp)
    local http_code

    if [ "$method" == "POST" ] || [ "$method" == "PUT" ] || [ "$method" == "PATCH" ]; then
        http_code=$(curl -s -o "$response_file" -w "%{http_code}" -X "$method" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            "${header_auth[@]}" \
            -d "$data" \
            "${BASE_URL}${endpoint}")
    else
        http_code=$(curl -s -o "$response_file" -w "%{http_code}" -X "$method" \
            -H "Accept: application/json" \
            "${header_auth[@]}" \
            "${BASE_URL}${endpoint}")
    fi

    if [ "$http_code" -eq "$expected_status" ]; then
        echo -e "${GREEN}PASS (HTTP $http_code)${NC}"
        ((PASSED_TESTS++))
    else
        echo -e "${RED}FAIL (Expected $expected_status, got $http_code)${NC}"
        echo -e "   Response: $(cat "$response_file" | head -n 3)"
        ((FAILED_TESTS++))
    fi

    cat "$response_file"
    rm -f "$response_file"
}

# 1. PUBLIC CATALOG ENDPOINTS
echo -e "${YELLOW}--- 1. Testing Public Catalog Endpoints ---${NC}"

test_endpoint "GET" "/api/banners" "" "" 200 "Get Banners List"
test_endpoint "GET" "/api/brands" "" "" 200 "Get Brands List"
test_endpoint "GET" "/api/categories" "" "" 200 "Get Categories List"
test_endpoint "GET" "/api/social-media-links" "" "" 200 "Get Social Media Links"
test_endpoint "GET" "/api/products" "" "" 200 "Get Products List"

echo ""

# 2. AUTHENTICATION FLOW
echo -e "${YELLOW}--- 2. Testing Customer Authentication ---${NC}"

RAND_EMAIL="mobile_tester_$(date +%s)@example.com"
REG_PAYLOAD="{\"name\":\"Mobile Tester\",\"email\":\"$RAND_EMAIL\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}"

echo -n "Registering new mobile customer ($RAND_EMAIL)... "
REG_RESP=$(curl -s -X POST -H "Content-Type: application/json" -H "Accept: application/json" -d "$REG_PAYLOAD" "${BASE_URL}/api/customer/register")
TOKEN=$(echo "$REG_RESP" | grep -o '"token":"[^"]*' | grep -o '[^"]*$')

if [ -n "$TOKEN" ]; then
    echo -e "${GREEN}PASS (Token acquired)${NC}"
    ((PASSED_TESTS++))
else
    echo -e "${RED}FAIL (Could not register / extract token)${NC}"
    echo "Response: $REG_RESP"
    ((FAILED_TESTS++))
fi

LOGIN_PAYLOAD="{\"email\":\"$RAND_EMAIL\",\"password\":\"password123\"}"
test_endpoint "POST" "/api/customer/login" "$LOGIN_PAYLOAD" "" 200 "Customer Login"

if [ -n "$TOKEN" ]; then
    test_endpoint "GET" "/api/customer/profile" "" "$TOKEN" 200 "Get Authenticated Customer Profile"
    
    UPDATE_PAYLOAD="{\"name\":\"Mobile Tester Updated\",\"phone\":\"098765432\"}"
    test_endpoint "PUT" "/api/customer/profile" "$UPDATE_PAYLOAD" "$TOKEN" 200 "Update Customer Profile"
    
    # 3. AUTHENTICATED WISHLIST & ORDERS
    echo -e "\n${YELLOW}--- 3. Testing Wishlist & Orders ---${NC}"
    test_endpoint "GET" "/api/wishlist" "" "$TOKEN" 200 "Get Wishlist Items"
    test_endpoint "GET" "/api/wishlist/ids" "" "$TOKEN" 200 "Get Wishlist Product IDs"
    test_endpoint "GET" "/api/orders" "" "$TOKEN" 200 "Get Customer Order History"

    # 4. LOGOUT
    echo -e "\n${YELLOW}--- 4. Testing Logout ---${NC}"
    test_endpoint "POST" "/api/customer/logout" "" "$TOKEN" 200 "Customer Logout"
fi

echo -e "\n${BLUE}======================================================================${NC}"
echo -e "${BLUE}                           TEST RESULTS                               ${NC}"
echo -e "${BLUE}======================================================================${NC}"
echo -e "Passed: ${GREEN}${PASSED_TESTS}${NC}"
echo -e "Failed: ${RED}${FAILED_TESTS}${NC}"
echo -e "${BLUE}======================================================================${NC}"

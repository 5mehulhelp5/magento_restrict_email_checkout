# API Protection Guide for MveRestrictCheckout Module

## Overview

The MveRestrictCheckout module now provides comprehensive protection against restricted emails and names for **both frontend and API requests**. This guide explains how API protection works and what endpoints are secured.

## Protected API Endpoints

### 1. Cart Creation APIs
- **POST** `/rest/V1/carts/mine` (Customer carts)
- **POST** `/rest/V1/carts` (Customer carts with ID)
- **POST** `/rest/V1/guest-carts` (Guest carts)
- **SOAP**: `cartCreate` methods
- **GraphQL**: Cart creation mutations

### 2. Order Placement APIs
- **POST** `/rest/V1/carts/{cartId}/payment-information`
- **POST** `/rest/V1/guest-carts/{cartId}/payment-information`
- **SOAP**: `salesOrderCreateV1` methods
- **GraphQL**: `placeOrder` mutations

## How API Protection Works

### 1. **API Detection**
The module automatically detects API requests by checking:
- Route name (`rest`, `soap`, `graphql`)
- URL path (`/rest/`, `/soap/`, `/graphql`)

### 2. **Request Validation**
When an API request is detected:
- Email and name restrictions are checked
- If restrictions apply, the request is blocked
- HTTP 403 Forbidden response is returned

### 3. **Error Response Format**
```json
{
  "message": "Guest cart creation is not allowed for this email address. Please register an account or use a different email address.",
  "code": 403
}
```

## Implementation Details

### 1. **API Cart Restriction Observer**
- **Event**: `checkout_cart_save_before`
- **Purpose**: Blocks cart creation for restricted data
- **Response**: HTTP 403 + custom error message

### 2. **API Order Restriction Observer**
- **Event**: `checkout_submit_all_after`
- **Purpose**: Blocks order placement for restricted data
- **Response**: HTTP 403 + custom error message

### 3. **API Exception Handler**
- **Purpose**: Converts `LocalizedException` to `WebapiException`
- **Result**: Proper HTTP 403 status codes for API requests
- **Fallback**: Original exception for frontend requests

## Configuration

### 1. **Module Settings**
- **Enable Module**: Must be enabled for API protection
- **Guest Checkout Restriction**: Controls guest cart/order blocking
- **Registered Checkout Restriction**: Controls registered user blocking
- **Customer Registration Restriction**: Controls account creation blocking

### 2. **Custom Messages**
- **Guest Checkout Message**: Displayed for guest restrictions
- **Registered Checkout Message**: Displayed for registered user restrictions
- **Registration Message**: Displayed for account creation restrictions

## Testing API Protection

### 1. **Test Cart Creation Blocking**
```bash
# Test guest cart creation with restricted email
curl -X POST "https://your-domain.com/rest/V1/guest-carts" \
  -H "Content-Type: application/json" \
  -d '{
    "cart": {
      "customer_email": "restricted@example.com",
      "customer_firstname": "John",
      "customer_lastname": "Doe"
    }
  }'
```

**Expected Response**: HTTP 403 with restriction message

### 2. **Test Order Placement Blocking**
```bash
# Test order placement with restricted email
curl -X POST "https://your-domain.com/rest/V1/guest-carts/{cartId}/payment-information" \
  -H "Content-Type: application/json" \
  -d '{
    "paymentMethod": {
      "method": "checkmo"
    },
    "billing_address": {
      "email": "restricted@example.com",
      "firstname": "John",
      "lastname": "Doe"
    }
  }'
```

**Expected Response**: HTTP 403 with restriction message

## Logging

### 1. **API Attempts Logged**
All blocked API attempts are logged to `var/log/mve_restrict_checkout.log` with:
- Request type (API vs frontend)
- Endpoint information
- Email and name details
- Reason for blocking

### 2. **Log Format Example**
```
[2024-01-15 14:30:25] [CRITICAL] MveRestrictCheckout API Cart Creation Blocked: Guest cart creation is not allowed for this email address. Please register an account or use a different email address. Context: {"email":"restricted@example.com","firstName":"John","lastName":"Doe","action":"cart_creation_blocked","reason":"restricted_email","request_type":"api_guest_cart"}
```

## Frontend vs API Protection

### 1. **Frontend Protection (Unchanged)**
- `CheckoutRestrictionObserver` → Frontend checkout
- `CartRestrictionObserver` → Frontend cart operations
- `CustomerRegistrationObserver` → Frontend registration

### 2. **API Protection (New)**
- `ApiCartRestrictionObserver` → API cart creation
- `ApiOrderRestrictionObserver` → API order placement
- `ApiExceptionHandler` → Proper HTTP responses

## Security Features

### 1. **Comprehensive Coverage**
- ✅ Frontend checkout and cart
- ✅ API cart creation and order placement
- ✅ Customer registration (frontend + API)
- ✅ Guest and registered user restrictions

### 2. **Proper HTTP Responses**
- HTTP 403 Forbidden for policy violations
- Custom error messages for user guidance
- Consistent error handling across all endpoints

### 3. **Detailed Logging**
- All blocked attempts are logged
- Context information for debugging
- Distinction between frontend and API attempts

## Troubleshooting

### 1. **API Protection Not Working**
- Check if module is enabled
- Verify observer registrations in `events.xml`
- Check Magento logs for errors
- Ensure proper event firing

### 2. **Wrong HTTP Status Codes**
- Verify `ApiExceptionHandler` is working
- Check if `WebapiException` is properly thrown
- Ensure proper exception conversion

### 3. **Missing Logs**
- Check logging configuration
- Verify log file permissions
- Check if observers are executing

## Summary

The MveRestrictCheckout module now provides **complete protection** against restricted emails and names for:

- **Frontend operations** (existing functionality)
- **API cart creation** (new protection)
- **API order placement** (new protection)
- **Customer registration** (existing + enhanced)

All API requests return proper HTTP 403 responses with custom error messages, ensuring consistent security across all access methods.

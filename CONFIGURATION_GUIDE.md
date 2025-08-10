# Configuration Guide - Marvelic MveRestrictCheckout Module

## Pre-Configured Settings

The module comes with the following default settings already configured:

### Blocked Email Domains
```
temp-mail.org
10minutemail.com
guerrillamail.com
```

### Blocked Email Addresses
```
test@example.com
fake@domain.com
admin@test.com
```

### Blocked First Name Patterns
```
test
fake
admin
user
```

### Blocked Last Name Patterns
```
test
fake
admin
user
```

### Custom Error Message
```
Sorry, registration is not allowed for this email address or name. Please use a valid email address and real name for registration.
```

## How to Access Configuration

1. **Log into your Magento admin panel**
2. **Navigate to**: Stores > Configuration > Marvelic > Checkout Restriction Settings
3. **Configure the settings** as needed

## Configuration Sections

### General Settings
- **Enable Module**: Turn the entire module on/off
- **Restrict Guest Checkout**: Block guest checkout for restricted data
- **Restrict Registered Customer Checkout**: Block registered customer checkout
- **Restrict Customer Registration**: Block customer registration
- **Enable Logging**: Enable/disable logging to var/log/mve_restrict_checkout.log

### Restricted Email Settings
- **Blocked Email Domains**: Add email domains to block (one per line)
  - Example: `temp-mail.org`, `10minutemail.com`, `guerrillamail.com`
- **Blocked Email Addresses**: Add specific email addresses to block (one per line)
  - Example: `test@example.com`, `fake@domain.com`, `admin@test.com`
- **Blocked First Name Patterns**: Add patterns to block in first names (one per line)
  - Example: `test`, `fake`, `admin`, `user`
- **Blocked Last Name Patterns**: Add patterns to block in last names (one per line)
  - Example: `test`, `fake`, `admin`, `user`



### Error Messages

This section allows you to customize all error messages displayed to customers and internal validation messages.

#### Guest Checkout Error Message
- **Field**: Guest Checkout Error Message
- **Type**: Textarea
- **Default**: "Guest checkout is not allowed for this email address or customer name."
- **Description**: Message shown when guest checkout is blocked
- **Usage**: Displayed to customers attempting guest checkout with restricted information

#### Registered Checkout Error Message
- **Field**: Registered Checkout Error Message
- **Type**: Textarea
- **Default**: "Checkout is not allowed for this email address or customer name."
- **Description**: Message shown when registered customer checkout is blocked
- **Usage**: Displayed to registered customers attempting checkout with restricted information

#### Customer Registration Error Message
- **Field**: Customer Registration Error Message
- **Type**: Textarea
- **Default**: "Customer registration is not allowed for this email address or customer name."
- **Description**: Message shown when customer registration is blocked
- **Usage**: Displayed to customers attempting to register with restricted information

#### Internal Email Restricted Message
- **Field**: Internal Email Restricted Message
- **Type**: Textarea
- **Default**: "Email address is restricted"
- **Description**: Internal validation message for email restrictions
- **Usage**: Used internally by the module's validation logic

#### Internal Name Restricted Message
- **Field**: Internal Name Restricted Message
- **Type**: Textarea
- **Default**: "Customer name is restricted"
- **Description**: Internal validation message for name restrictions
- **Usage**: Used internally by the module's validation logic

## Adding More Blocked Items

### To Add More Email Domains:
```
temp-mail.org
10minutemail.com
guerrillamail.com
mailinator.com
tempmail.com
throwawaymail.com
```

### To Add More Email Addresses:
```
test@example.com
fake@domain.com
admin@test.com
spam@test.com
demo@fake.com
```

### To Add More Name Patterns:
```
test
fake
admin
user
demo
spam
temporary
```

## Testing the Configuration

### Test Blocked Email Domains:
- Try registering with: `user@temp-mail.org`
- Try registering with: `test@10minutemail.com`

### Test Blocked Email Addresses:
- Try registering with: `test@example.com`
- Try registering with: `fake@domain.com`

### Test Blocked Names:
- Try registering with first name: `Test`
- Try registering with last name: `Fake`
- Try registering with first name: `Admin`

## Important Notes

1. **Case Insensitive**: Name patterns are case-insensitive
2. **One Per Line**: Each blocked item should be on a separate line
3. **No Commas**: Don't use commas, use line breaks instead
4. **Real-time Validation**: Changes take effect immediately after saving
5. **Scope Support**: Configure per website/store if needed

## Troubleshooting

### If restrictions aren't working:
1. Ensure the module is enabled
2. Check that specific restriction types are enabled
3. Clear cache: `php bin/magento cache:flush`
4. Verify the blocked data format (one per line)

### If you need to temporarily disable:
1. Set "Enable Module" to "No"
2. Or disable specific restriction types as needed

## Support

For additional help:
- Email: info@marvelic.com
- Check the main README.md for detailed documentation

## Overview

The Marvelic MveRestrictCheckout module provides comprehensive protection against unwanted customer registrations and checkout attempts. It works through both **frontend protection** (using Magento observers) and **API protection** (using Magento plugins) to ensure restrictions are enforced regardless of how customers interact with your store.

### Protection Coverage

- **Frontend Protection**: Blocks restricted users during normal website checkout and registration
- **API Protection**: Blocks restricted users when they attempt to create orders or register via Magento REST API calls
- **Cart Protection**: Prevents restricted users from adding products to cart
- **Configuration Management**: Centralized admin panel for all restriction settings

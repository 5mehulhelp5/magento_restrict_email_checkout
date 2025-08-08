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

### Restricted Email Settings
- **Blocked Email Domains**: Add email domains to block (one per line)
  - Example: `temp-mail.org`, `10minutemail.com`, `guerrillamail.com`
- **Blocked Email Addresses**: Add specific email addresses to block (one per line)
  - Example: `test@example.com`, `fake@domain.com`, `admin@test.com`
- **Blocked First Name Patterns**: Add patterns to block in first names (one per line)
  - Example: `test`, `fake`, `admin`, `user`
- **Blocked Last Name Patterns**: Add patterns to block in last names (one per line)
  - Example: `test`, `fake`, `admin`, `user`

### Address Restriction Settings
- **Check Delivery Address**: Apply restrictions to delivery address fields
- **Check Billing Address**: Apply restrictions to billing address fields
- **Blocked Address Email Domains**: Domains to block in address fields
- **Blocked Address Email Addresses**: Specific emails to block in address fields

### Error Messages
- **Guest Checkout Error Message**: Message for blocked guest checkout
- **Registered Checkout Error Message**: Message for blocked registered checkout
- **Registration Error Message**: Customize the error message shown when registration is blocked

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
- Email: support@marvelic.com
- Check the main README.md for detailed documentation

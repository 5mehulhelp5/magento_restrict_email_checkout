# Logging Test Guide - MveRestrictCheckout Module

## Overview

The MveRestrictCheckout module now includes a custom logger that writes to `var/log/mve_restrict_checkout.log` regardless of Magento's debug mode settings. This ensures that all module activities are logged when logging is enabled in the configuration.

## What Was Fixed

**Previous Issue:**
- The module was using Magento's standard logger (`Psr\Log\LoggerInterface`)
- Logs were written to standard Magento log files (`var/log/system.log`, `var/log/exception.log`)
- Logging was dependent on Magento's debug mode settings

**Current Solution:**
- Custom logger (`Marvelic\MveRestrictCheckout\Model\Logger`) implemented that implements PSR LoggerInterface
- Logs are written to dedicated file: `var/log/mve_restrict_checkout.log`
- Logging works independently of Magento's debug mode
- Logging is controlled only by the module's "Enable Logging" configuration setting
- Uses Magento's standard dependency injection system with PSR LoggerInterface
- **Clean, focused logging**: Only logs meaningful business actions (restrictions, errors) - no verbose debug information

## How to Test

### Method 1: Test Business Logic (Recommended)

1. **Ensure logging is enabled:**
   - Go to Admin > Stores > Configuration > Marvelic > Checkout Restriction Settings
   - Set "Enable Logging" to "Yes"
   - Save configuration

2. **Test actual restrictions:**
   - Try to checkout with a blocked email
   - Try to register with a blocked name
   - Try to add products to cart with restricted access

3. **Check the log file:**
   - Look for meaningful business action logs
   - No verbose debug information will be logged

### Method 2: Test Specific Functionality

1. **Test guest checkout restriction:**
   - Try to checkout with a blocked email (e.g., `test@temp-mail.org`)
   - Check the log file for restriction messages

2. **Test customer registration restriction:**
   - Try to register with a blocked name (e.g., "Test User")
   - Check the log file for restriction messages

3. **Test cart restrictions:**
   - Try to add products to cart with a blocked email
   - Check the log file for restriction messages

## Log File Location

The log file is created at:
```
var/log/mve_restrict_checkout.log
```

## Log Format

Each log entry follows this format:
```
[Timestamp] [LEVEL] Message Context: {JSON_CONTEXT}
```

Example:
```
[2024-01-15 14:30:25] [CRITICAL] MveRestrictCheckout Order Blocked: Guest checkout is not allowed for this email address. Please register an account or use a different email address.
[2024-01-15 14:30:25] [CRITICAL] MveRestrictCheckout Cart Restriction Error: Adding products to cart is not allowed for this email address. Please register an account or use a different email address.
[2024-01-15 14:30:25] [CRITICAL] MveRestrictCheckout Customer Registration Blocked: Customer registration is not allowed for this email address. Please use a different email address.
```

## Configuration

### Enable/Disable Logging

- **Admin Panel:** Stores > Configuration > Marvelic > Checkout Restriction Settings > General Settings > Enable Logging
- **Default:** Yes (enabled)
- **Scope:** Store/Website level

### What Gets Logged

When logging is enabled, the module logs:
- **Business Actions**: When restrictions are enforced (checkout blocked, cart blocked, registration blocked)
- **Error Conditions**: Critical errors and exceptions that prevent normal operation
- **No Verbose Logging**: Debug information, successful validations, and test messages are not logged

## Troubleshooting

### If No Log File is Created

1. **Check file permissions:**
   ```bash
   chmod 755 var/log/
   chmod 644 var/log/mve_restrict_checkout.log
   ```

2. **Verify module is enabled:**
   ```bash
   php bin/magento module:status Marvelic_MveRestrictCheckout
   ```

3. **Clear cache and recompile:**
   ```bash
   php bin/magento cache:flush
   php bin/magento setup:di:compile
   ```

4. **Check configuration:**
   - Ensure "Enable Logging" is set to "Yes"
   - Ensure "Enable Module" is set to "Yes"

### If Log File is Empty

1. **Visit a page on your site** - the test observer triggers on page loads
2. **Check Magento logs** for any errors in the custom logger
3. **Verify observer registration** in `etc/events.xml`

### If Logging Still Uses Standard Magento Logger

1. **Clear generated files:**
   ```bash
   rm -rf generated/
   php bin/magento setup:di:compile
   ```

2. **Verify DI configuration** in `etc/di.xml`, `etc/adminhtml/di.xml`, and `etc/frontend/di.xml`

## Performance Impact

- **Minimal impact** - logging only occurs when enabled
- **File I/O only** when writing to custom log file
- **No database queries** for logging operations
- **Configurable** - can be disabled in production if needed

## Security Considerations

- Log files contain sensitive information (emails, names)
- Ensure `var/log/` directory is not publicly accessible
- Consider log rotation for production environments
- Log files may contain customer data - handle according to privacy policies

## Support

If you continue to experience issues with logging:

1. Check this troubleshooting guide
2. Verify all files are in the correct locations
3. Check Magento system logs for errors
4. Contact support: support@marvelic.com

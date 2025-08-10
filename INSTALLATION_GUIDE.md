# Installation Guide for Marvelic MveRestrictCheckout Module

## Prerequisites

- Magento 2.4.6 (or compatible version)
- PHP 8.1, 8.2, or 8.3
- Composer (for dependency management)

## Installation Steps

### 1. Copy Module Files

Copy the module files to your Magento installation:

```bash
# Navigate to your Magento root directory
cd /path/to/your/magento

# Create the module directory structure
mkdir -p app/code/Marvelic/MveRestrictCheckout

# Copy all module files to the directory
cp -r /path/to/module/* app/code/Marvelic/MveRestrictCheckout/
```

**Important**: Ensure the module is in the EXACT location: `app/code/Marvelic/MveRestrictCheckout/`

### 2. Verify Module Structure

Ensure your module directory contains all necessary files:

```
app/code/Marvelic/MveRestrictCheckout/
├── composer.json
├── registration.php
├── etc/
│   ├── module.xml
│   ├── adminhtml/
│   │   ├── system.xml
│   │   ├── default.xml
│   │   ├── menu.xml
│   │   └── config.xml
│   ├── acl.xml
│   ├── events.xml
│   ├── frontend/
│   │   └── di.xml
│   └── webapi_rest/
│       └── di.xml
├── Model/
│   ├── Config.php
│   └── EmailValidator.php
├── Observer/
│   ├── CheckoutRestrictionObserver.php
│   ├── CartRestrictionObserver.php
│   └── CustomerRegistrationObserver.php
├── README.md
├── INSTALLATION_GUIDE.md
├── CONFIGURATION_GUIDE.md
└── LICENSE
```

### 3. Enable the Module

```bash
# Enable the module
php bin/magento module:enable Marvelic_MveRestrictCheckout

# Run setup upgrade
php bin/magento setup:upgrade

# Compile dependency injection
php bin/magento setup:di:compile

# Deploy static content (if in production mode)
php bin/magento setup:static-content:deploy -f

# Clear cache
php bin/magento cache:flush
```

### 4. Verify Installation

Run the verification script:

```bash
php verify_installation.php
```

This will check:
- Module installation status
- Configuration values
- Email validation functionality
- Name validation functionality

### 5. Configure the Module

1. Go to **Admin Panel** → **Stores** → **Configuration**
2. Navigate to **Marvelic** → **Checkout Restriction Settings**
3. Configure the following sections:
   - **General Settings**: Enable/disable restrictions and logging
   - **Restricted Email Settings**: Add blocked domains, emails, and names
   - **Error Messages**: Customize error messages

## Default Configuration

The module comes with pre-configured restrictions:

- **Blocked Domains**: temp-mail.org, 10minutemail.com, guerrillamail.com, etc.
- **Blocked Names**: test, fake, admin, user, demo, temp, temporary
- **Default Messages**: Customizable error messages for each restriction type

## Testing the Module

### Test Customer Registration Blocking

1. Try to register with a blocked email (e.g., test@temp-mail.org)
2. Try to register with a blocked name (e.g., "test user")
3. Verify that registration is blocked with custom error message

### Test Checkout Blocking

1. Try to checkout with a blocked email
2. Try to checkout with a blocked name
3. Verify that order creation is prevented
4. Verify custom error messages are displayed

### Test Cart Restrictions

1. Try to add products to cart with a blocked email
2. Verify that cart operations are blocked
3. Verify appropriate error messages

## Troubleshooting

### Common Issues

1. **"Class not found" Error**
   - Ensure module is in correct directory: `app/code/Marvelic/MveRestrictCheckout/`
   - Run `php bin/magento setup:di:compile`
   - Clear cache: `php bin/magento cache:flush`

2. **Module Not Appearing in Admin**
   - Check if module is enabled: `php bin/magento module:status Marvelic_MveRestrictCheckout`
   - Verify file permissions
   - Check admin ACL configuration

3. **Restrictions Not Working**
   - Verify module is enabled in configuration
   - Check if restrictions are enabled for specific types
   - Verify blocked domains/emails/names are configured
   - Check Magento logs for errors

### Log Files

Check these log files for debugging:

- `var/log/system.log` - General system errors
- `var/log/exception.log` - Exception details
- `var/log/debug.log` - Debug information (if enabled)

### Verification Commands

```bash
# Check module status
php bin/magento module:status Marvelic_MveRestrictCheckout

# Check module list
php bin/magento module:list | grep Marvelic

# Check configuration
php bin/magento config:show mve_restrict_checkout

# Clear generated files and recompile
rm -rf generated/
php bin/magento setup:di:compile
```

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Verify your Magento version compatibility
3. Check the module logs and Magento system logs
4. Contact support: support@marvelic.com

## Uninstallation

To remove the module:

```bash
# Disable the module
php bin/magento module:disable Marvelic_MveRestrictCheckout

# Remove from config
php bin/magento setup:upgrade

# Remove module files
rm -rf app/code/Marvelic/MveRestrictCheckout/

# Clear cache
php bin/magento cache:flush
```

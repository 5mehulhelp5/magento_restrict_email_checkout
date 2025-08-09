# Marvelic MveRestrictCheckout Module

A Magento 2.4.6-p11 module that provides comprehensive checkout restriction capabilities based on email domains, addresses, and customer names.

## Features

✅ **Customer Registration Protection** - Block customer registration for restricted emails and names  
✅ **Guest Checkout Protection** - Block guest checkout for restricted emails and addresses  
✅ **Registered Customer Checkout Protection** - Block registered customer checkout for restricted data  
✅ **Admin Configuration** - Full admin panel configuration for all restriction settings  
✅ **Address Field Validation** - Support for delivery and billing address restrictions  
✅ **Flexible Blocking Rules** - Block by domain, specific email, first name, or last name  

## Requirements

- Magento 2.4.6-p11
- PHP 8.1 or 8.2
- MySQL 8.0 or MariaDB 10.4+

## Installation

### Method 1: Composer Installation (Recommended)

```bash
composer require marvelic/mve-restrict-checkout
php bin/magento module:enable Marvelic_MveRestrictCheckout
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

### Method 2: Manual Installation

1. Download the module files
2. Place them in `app/code/Marvelic/MveRestrictCheckout/`
3. Run the following commands:

```bash
php bin/magento module:enable Marvelic_MveRestrictCheckout
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

## Configuration

### Admin Panel Configuration

Navigate to **Stores > Configuration > Marvelic > Checkout Restriction Settings**

#### General Settings
- **Enable Module**: Enable/disable the entire module
- **Restrict Guest Checkout**: Block guest checkout for restricted emails
- **Restrict Registered Customer Checkout**: Block registered customer checkout
- **Restrict Customer Registration**: Block customer registration

#### Restricted Email Settings
- **Blocked Email Domains**: Enter domains to block (one per line, e.g., `example.com`)
- **Blocked Email Addresses**: Enter specific email addresses to block
- **Blocked First Names**: Enter first names to block (case insensitive)
- **Blocked Last Names**: Enter last names to block (case insensitive)

#### Address Restriction Settings
- **Check Delivery Address**: Apply restrictions to delivery address fields
- **Check Billing Address**: Apply restrictions to billing address fields
- **Blocked Address Email Domains**: Domains to block in address fields
- **Blocked Address Email Addresses**: Specific emails to block in address fields

#### Error Messages
- **Guest Checkout Error Message**: Custom message for blocked guest checkout
- **Registered Checkout Error Message**: Custom message for blocked registered checkout
- **Registration Error Message**: Custom message for blocked registration

## Usage Examples

### Blocking Specific Domains

To block all emails from `spam.com`:

1. Go to **Stores > Configuration > Marvelic > Checkout Restriction Settings**
2. In **Blocked Email Domains**, enter:
   ```
   spam.com
   ```
3. Save configuration

### Blocking Specific Names

To block customers with first name "Test":

1. In **Blocked First Names**, enter:
   ```
   Test
   ```
2. Save configuration

### Blocking Address Fields

To block specific domains in delivery addresses:

1. Enable **Check Delivery Address**
2. In **Blocked Address Email Domains**, enter:
   ```
   fake.com
   ```
3. Save configuration

## Technical Details

### Module Structure

```
Marvelic_MveRestrictCheckout/
├── etc/
│   ├── adminhtml/
│   │   ├── system.xml
│   │   ├── default.xml
│   │   ├── menu.xml
│   │   ├── config.xml
│   │   ├── acl.xml
│   │   └── security.xml
│   ├── di.xml
│   └── module.xml
├── Model/
│   ├── Config.php
│   ├── EmailValidator.php
│   └── GuestPaymentInformationManagement.php
├── Plugin/
│   ├── AccountManagementPlugin.php
│   └── PaymentInformationManagementPlugin.php
├── registration.php
├── composer.json
├── README.md
├── INSTALLATION_GUIDE.md
├── CONFIGURATION_GUIDE.md
└── LICENSE
```

### Key Classes

- **Config**: Manages all module configuration settings
- **EmailValidator**: Validates emails, domains, and names against restrictions
- **GuestPaymentInformationManagement**: Handles guest checkout restrictions
- **AccountManagementPlugin**: Handles customer registration restrictions
- **PaymentInformationManagementPlugin**: Handles registered customer checkout restrictions

### Architecture

The module uses a combination of preferences and plugins:

- **Preferences**: `GuestPaymentInformationManagement` - Overrides guest checkout with email validation
- **Plugins**: `AccountManagementPlugin` and `PaymentInformationManagementPlugin` - Intercept customer registration and registered checkout

## Troubleshooting

### Common Issues

1. **Module not working after installation**
   - Clear cache: `php bin/magento cache:flush`
   - Recompile: `php bin/magento setup:di:compile`

2. **Configuration not saving**
   - Check admin permissions for "Checkout Restriction Settings"
   - Verify module is enabled: `php bin/magento module:status Marvelic_MveRestrictCheckout`

3. **Restrictions not applying**
   - Verify module is enabled in configuration
   - Check that specific restriction types are enabled
   - Ensure blocked data is properly formatted (one per line)

### Debug Mode

Enable debug logging by adding to `app/etc/env.php`:

```php
'system' => [
    'default' => [
        'dev' => [
            'debug' => [
                'debug_logging' => 1
            ]
        ]
    ]
]
```

## Support

For support and questions:

- Email: support@marvelic.com
- Issues: [GitHub Issues](https://github.com/marvelic/mve-restrict-checkout/issues)

## License

This module is licensed under the Open Software License v. 3.0 (OSL-3.0).

## Changelog

### Version 1.0.0
- Initial release
- Guest checkout protection
- Registered customer checkout protection
- Customer registration protection
- Admin configuration panel
- Address field validation support

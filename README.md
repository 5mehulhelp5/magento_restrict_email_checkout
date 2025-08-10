# Marvelic MveRestrictCheckout Module

A Magento 2.4.6-p11 module that provides comprehensive checkout restriction capabilities based on email domains, addresses, and customer names.

## Features

✅ **Customer Registration Protection** - Block customer registration for restricted emails and names  
✅ **Guest Checkout Protection** - Block guest checkout for restricted emails and names  
✅ **Registered Customer Checkout Protection** - Block registered customer checkout for restricted data  
✅ **API Protection**: Restrict order creation and customer registration via Magento REST API calls
- **Admin Configuration**: Comprehensive admin panel interface to configure all restriction settings
- **Configurable Logging**: Enable/disable logging to var/log/mve_restrict_checkout.log
- **Custom Error Messages**: Configurable error messages for each restriction type
- **Internal Validation Messages**: Configurable internal validation messages for email and name restrictions

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

The module provides comprehensive configuration options in the Magento Admin panel under **Stores > Configuration > Marvelic > Checkout Restriction**:

### General Settings
- **Enable Module**: Enable/disable the entire module
- **Restrict Guest Checkout**: Enable/disable guest checkout restrictions
- **Restrict Registered Checkout**: Enable/disable registered customer checkout restrictions
- **Restrict Customer Registration**: Enable/disable customer registration restrictions
- **Enable Logging**: Enable/disable logging to var/log/mve_restrict_checkout.log

### Restriction Lists
- **Blocked Email Domains**: List of email domains to block (one per line)
- **Blocked Email Addresses**: List of specific email addresses to block (one per line)
- **Blocked First Name Patterns**: List of first name patterns to block (one per line)
- **Blocked Last Name Patterns**: List of last name patterns to block (one per line)

### Error Messages
- **Guest Checkout Error Message**: Custom message for blocked guest checkout
- **Registered Checkout Error Message**: Custom message for blocked registered checkout
- **Customer Registration Error Message**: Custom message for blocked customer registration
- **Internal Email Restricted Message**: Internal validation message for email restrictions
- **Internal Name Restricted Message**: Internal validation message for name restrictions

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



## Technical Details

### Module Structure

```
Marvelic_MveRestrictCheckout/
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
├── Plugin/
│   └── Api/
│       ├── GuestCheckoutApiPlugin.php
│       ├── RegisteredCheckoutApiPlugin.php
│       └── CustomerRegistrationApiPlugin.php
├── README.md
├── INSTALLATION_GUIDE.md
├── CONFIGURATION_GUIDE.md
└── LICENSE
```

### Key Classes

- **`Config`**: Manages module configuration and provides methods to retrieve settings
- **`EmailValidator`**: Core validation logic for emails and names against blocked lists
- **`CheckoutRestrictionObserver`**: Handles checkout restrictions for both guest and registered customers
- **`CartRestrictionObserver`**: Handles restrictions when products are added to the cart
- **`CustomerRegistrationObserver`**: Handles customer registration restrictions
- **`GuestCheckoutApiPlugin`**: API protection for guest checkout via REST API
- **`RegisteredCheckoutApiPlugin`**: API protection for registered customer checkout via REST API
- **`CustomerRegistrationApiPlugin`**: API protection for customer registration via REST API

### Observer Architecture

The module uses a combination of **Observers** and **API Plugins** to provide comprehensive protection:

#### Frontend Protection (Observers)
- **`CheckoutRestrictionObserver`**: Intercepts `sales_order_place_before` event to block order creation
- **`CartRestrictionObserver`**: Intercepts `checkout_cart_product_add_after` event to block cart additions
- **`CustomerRegistrationObserver`**: Intercepts `customer_register_success` event to block registration

#### API Protection (Plugins)
- **`GuestCheckoutApiPlugin`**: Intercepts guest checkout API calls (`/rest/V1/guest-carts/{cartId}/payment-information`)
- **`RegisteredCheckoutApiPlugin`**: Intercepts registered customer checkout API calls (`/rest/V1/carts/{cartId}/payment-information`)
- **`CustomerRegistrationApiPlugin`**: Intercepts customer registration API calls (`/rest/V1/customers`)

This dual approach ensures protection regardless of whether orders are placed through the frontend or via API calls.

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


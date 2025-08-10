# This Module UNDER DEVELOPMENT. not ready for Production site. 
# Marvelic MveRestrictCheckout Module

A Magento 2.4.6-p11 module that provides comprehensive checkout restriction capabilities based on email domains, addresses, and customer names.

## Features

✅ **Customer Registration Protection** - Block customer registration for restricted emails and names  
✅ **Guest Checkout Protection** - Block guest checkout for restricted emails and names  
✅ **Registered Customer Checkout Protection** - Block registered customer checkout for restricted data  
✅ **API Protection** - Block cart creation and order placement via REST/SOAP/GraphQL APIs  
✅ **Admin Configuration** - Full admin panel configuration for all restriction settings  
✅ **Flexible Blocking Rules** - Block by domain, specific email, first name, or last name  
✅ **Configurable Logging** - Enable/disable logging to var/log/mve_restrict_checkout.log  

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
│   ├── CustomerRegistrationObserver.php
│   ├── ApiCartRestrictionObserver.php
│   └── ApiOrderRestrictionObserver.php
├── Model/
│   ├── Config.php
│   ├── EmailValidator.php
│   └── ApiExceptionHandler.php
├── README.md
├── INSTALLATION_GUIDE.md
├── CONFIGURATION_GUIDE.md
└── LICENSE
```

### Key Classes

- **Config**: Manages all module configuration settings
- **EmailValidator**: Validates emails, domains, and names against restrictions
- **ApiExceptionHandler**: Converts exceptions to proper HTTP responses for API requests

#### Frontend Observers
- **CheckoutRestrictionObserver**: Handles checkout restrictions for both guest and registered customers
- **CartRestrictionObserver**: Handles restrictions when products are added to cart
- **CustomerRegistrationObserver**: Handles customer registration restrictions

#### API Observers
- **ApiCartRestrictionObserver**: Blocks API cart creation for restricted data
- **ApiOrderRestrictionObserver**: Blocks API order placement for restricted data

### Observer Architecture

The module uses Magento 2 observers for all functionality:

#### Frontend Protection (Existing)
- **CheckoutRestrictionObserver**: Intercepts order placement with email and name validation
- **CartRestrictionObserver**: Intercepts cart operations with email validation
- **CustomerRegistrationObserver**: Intercepts customer registration with email and name validation

#### API Protection (New)
- **ApiCartRestrictionObserver**: Blocks API cart creation for restricted data
- **ApiOrderRestrictionObserver**: Blocks API order placement for restricted data
- **ApiExceptionHandler**: Converts exceptions to proper HTTP 403 responses for API requests

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



## Support

For support and questions:

- Email: support@marvelic.com
- Issues: [GitHub Issues](https://github.com/marvelic/mve-restrict-checkout/issues)

## License

This module is licensed under the Open Software License v. 3.0 (OSL-3.0).

## Changelog

### Version 1.1.0
- **API Protection**: Added comprehensive protection for REST/SOAP/GraphQL APIs
- **Cart Creation Blocking**: Blocks API cart creation for restricted emails/names
- **Order Placement Blocking**: Blocks API order placement for restricted data
- **HTTP 403 Responses**: Proper HTTP status codes for API violations
- **Enhanced Logging**: Separate logging for API vs frontend attempts

### Version 1.0.0
- Initial release
- Guest checkout protection
- Registered customer checkout protection
- Customer registration protection
- Admin configuration panel


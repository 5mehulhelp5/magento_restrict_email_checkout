# Quick Installation Guide

## Marvelic MveRestrictCheckout Module

### Prerequisites
- Magento 2.4.6-p11
- PHP 8.1 or 8.2
- Admin access to Magento

### Quick Install (Recommended)

1. **Copy module files** to your Magento installation:
   ```bash
   cp -r . /path/to/your/magento/app/code/Marvelic/MveRestrictCheckout/
   ```

2. **Run installation commands**:
   ```bash
   cd /path/to/your/magento
   php bin/magento module:enable Marvelic_MveRestrictCheckout
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy -f
   php bin/magento cache:flush
   ```

3. **Configure the module**:
   - Go to **Admin Panel > Stores > Configuration > Marvelic > Checkout Restriction Settings**
   - Enable the module
   - Configure your restriction rules
   - Save configuration

### Alternative: Use Installation Script

If you have the `install.sh` script:
```bash
chmod +x install.sh
./install.sh
```

### Verify Installation

Check if the module is enabled:
```bash
php bin/magento module:status Marvelic_MveRestrictCheckout
```

### Configuration Examples

#### Block Email Domains
```
spam.com
fake.com
test.com
```

#### Block Specific Names
```
Test
Fake
Spam
```

#### Block Specific Emails
```
test@example.com
fake@spam.com
```

### Support
- Email: support@marvelic.com
- Documentation: See README.md for detailed information

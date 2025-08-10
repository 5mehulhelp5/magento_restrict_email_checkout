# Installation Guide for Marvelic MveRestrictCheckout Module

## Step 1: Create Directory Structure in Magento

In your Magento installation, create the following directory structure:

```bash
mkdir -p app/code/Marvelic/MveRestrictCheckout
```

## Step 2: Copy Module Files

Copy ALL files from this module source to `app/code/Marvelic/MveRestrictCheckout/` in your Magento installation.

The final structure should look like this:
```
app/code/Marvelic/MveRestrictCheckout/
├── etc/
│   ├── adminhtml/
│   │   ├── default.xml
│   │   ├── menu.xml
│   │   ├── system.xml
│   │   ├── config.xml
│   │   └── acl.xml
│   ├── events.xml
│   └── module.xml
├── Model/
│   ├── Config.php
│   └── EmailValidator.php
├── Observer/
│   ├── TestObserver.php
│   ├── CheckoutRestrictionObserver.php
│   ├── CartRestrictionObserver.php
│   └── CustomerRegistrationObserver.php
├── registration.php
└── composer.json
```

## Step 3: Verify File Permissions

Make sure all files have proper permissions:
```bash
chmod -R 644 app/code/Marvelic/MveRestrictCheckout/
chmod 755 app/code/Marvelic/MveRestrictCheckout/
```

## Step 4: Install and Compile Module

Run these commands in your Magento root directory:

```bash
# Enable the module
php bin/magento module:enable Marvelic_MveRestrictCheckout

# Run setup upgrade
php bin/magento setup:upgrade

# Compile DI
php bin/magento setup:di:compile

# Flush cache
php bin/magento cache:flush

# Reindex
php bin/magento indexer:reindex
```

## Step 5: Verify Installation

1. Check if module is enabled:
   ```bash
   php bin/magento module:status Marvelic_MveRestrictCheckout
   ```

2. Check admin panel:
   - Go to Admin → Stores → Configuration → Marvelic → Checkout Restriction
   - You should see the module configuration

3. Check log file:
   - Visit any page on your Magento site
   - Check if `var/log/mve_restrict_checkout.log` is created
   - You should see: "Test Observer called - Module is working!"

## Troubleshooting

### If you get "Class not found" errors:

1. **Verify directory structure** - Make sure the module is in `app/code/Marvelic/MveRestrictCheckout/`
2. **Check file permissions** - All files should be readable
3. **Clear generated files** - Delete `generated/` directory and recompile
4. **Check module status** - Ensure the module is enabled

### If the log file is not created:

1. **Check observer registration** - Verify `etc/events.xml` exists
2. **Check module dependencies** - Ensure `Magento_Checkout` and `Magento_Customer` are enabled
3. **Check cache** - Clear all caches and recompile

### If checkout still doesn't work:

1. **Check configuration** - Ensure module is enabled in admin
2. **Check observer logs** - Look for debug messages in `var/log/debug.log`
3. **Test with simple email** - Try with a non-blocked email first

## Important Notes

- **Never copy files to `app/code/` while Magento is running**
- **Always run `setup:di:compile` after copying module files**
- **The module must be in the exact directory structure shown above**
- **File names and class names are case-sensitive**

<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration model for checkout restrictions
 */
class Config
{
    private const XML_PATH_ENABLED = 'mve_restrict_checkout/general/enabled';
    private const XML_PATH_RESTRICT_GUEST_CHECKOUT = 'mve_restrict_checkout/general/restrict_guest_checkout';
    private const XML_PATH_RESTRICT_REGISTERED_CHECKOUT = 'mve_restrict_checkout/general/restrict_registered_checkout';
    private const XML_PATH_RESTRICT_CUSTOMER_REGISTRATION = 'mve_restrict_checkout/general/restrict_customer_registration';
    
    private const XML_PATH_BLOCKED_DOMAINS = 'mve_restrict_checkout/restricted_emails/blocked_domains';
    private const XML_PATH_BLOCKED_EMAILS = 'mve_restrict_checkout/restricted_emails/blocked_emails';
    private const XML_PATH_BLOCKED_FIRST_NAMES = 'mve_restrict_checkout/restricted_emails/blocked_first_names';
    private const XML_PATH_BLOCKED_LAST_NAMES = 'mve_restrict_checkout/restricted_emails/blocked_last_names';
    
    private const XML_PATH_CHECK_DELIVERY_ADDRESS = 'mve_restrict_checkout/address_restrictions/check_delivery_address';
    private const XML_PATH_CHECK_BILLING_ADDRESS = 'mve_restrict_checkout/address_restrictions/check_billing_address';
    private const XML_PATH_BLOCKED_ADDRESS_DOMAINS = 'mve_restrict_checkout/address_restrictions/blocked_address_domains';
    private const XML_PATH_BLOCKED_ADDRESS_EMAILS = 'mve_restrict_checkout/address_restrictions/blocked_address_emails';
    
    private const XML_PATH_GUEST_CHECKOUT_MESSAGE = 'mve_restrict_checkout/messages/guest_checkout_message';
    private const XML_PATH_REGISTERED_CHECKOUT_MESSAGE = 'mve_restrict_checkout/messages/registered_checkout_message';
    private const XML_PATH_REGISTRATION_MESSAGE = 'mve_restrict_checkout/messages/registration_message';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if module is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isEnabled(?string $scopeCode = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Check if guest checkout restriction is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isGuestCheckoutRestricted(?string $scopeCode = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_RESTRICT_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Check if registered checkout restriction is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isRegisteredCheckoutRestricted(?string $scopeCode = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_RESTRICT_REGISTERED_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Check if customer registration restriction is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isCustomerRegistrationRestricted(?string $scopeCode = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_RESTRICT_CUSTOMER_REGISTRATION,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get blocked domains
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function getBlockedDomains(?string $scopeCode = null): array
    {
        $domains = $this->scopeConfig->getValue(
            self::XML_PATH_BLOCKED_DOMAINS,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $this->parseTextareaValue($domains);
    }

    /**
     * Get blocked emails
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function getBlockedEmails(?string $scopeCode = null): array
    {
        $emails = $this->scopeConfig->getValue(
            self::XML_PATH_BLOCKED_EMAILS,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $this->parseTextareaValue($emails);
    }

    /**
     * Get blocked first names
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function getBlockedFirstNames(?string $scopeCode = null): array
    {
        $names = $this->scopeConfig->getValue(
            self::XML_PATH_BLOCKED_FIRST_NAMES,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $this->parseTextareaValue($names);
    }

    /**
     * Get blocked last names
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function getBlockedLastNames(?string $scopeCode = null): array
    {
        $names = $this->scopeConfig->getValue(
            self::XML_PATH_BLOCKED_LAST_NAMES,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $this->parseTextareaValue($names);
    }

    /**
     * Check if delivery address checking is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isDeliveryAddressCheckEnabled(?string $scopeCode = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_CHECK_DELIVERY_ADDRESS,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Check if billing address checking is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isBillingAddressCheckEnabled(?string $scopeCode = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_CHECK_BILLING_ADDRESS,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get blocked address domains
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function getBlockedAddressDomains(?string $scopeCode = null): array
    {
        $domains = $this->scopeConfig->getValue(
            self::XML_PATH_BLOCKED_ADDRESS_DOMAINS,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $this->parseTextareaValue($domains);
    }

    /**
     * Get blocked address emails
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function getBlockedAddressEmails(?string $scopeCode = null): array
    {
        $emails = $this->scopeConfig->getValue(
            self::XML_PATH_BLOCKED_ADDRESS_EMAILS,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $this->parseTextareaValue($emails);
    }

    /**
     * Get guest checkout error message
     *
     * @param string|null $scopeCode
     * @return string
     */
    public function getGuestCheckoutMessage(?string $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_GUEST_CHECKOUT_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get registered checkout error message
     *
     * @param string|null $scopeCode
     * @return string
     */
    public function getRegisteredCheckoutMessage(?string $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_REGISTERED_CHECKOUT_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get registration error message
     *
     * @param string|null $scopeCode
     * @return string
     */
    public function getRegistrationMessage(?string $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_REGISTRATION_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Parse textarea value into array
     *
     * @param string|null $value
     * @return array
     */
    private function parseTextareaValue(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        $lines = explode("\n", $value);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines);

        return $lines;
    }
}

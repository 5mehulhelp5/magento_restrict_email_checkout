<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Configuration model for checkout restrictions
 */
class Config
{
    private const XML_PATH_ENABLED = 'mve_restrict_checkout/general/enabled';
    private const XML_PATH_RESTRICT_GUEST_CHECKOUT = 'mve_restrict_checkout/general/restrict_guest_checkout';
    private const XML_PATH_RESTRICT_REGISTERED_CHECKOUT = 'mve_restrict_checkout/general/restrict_registered_checkout';
    private const XML_PATH_RESTRICT_CUSTOMER_REGISTRATION = 'mve_restrict_checkout/general/restrict_customer_registration';
    private const XML_PATH_CUSTOMER_REGISTRATION_MESSAGE = 'mve_restrict_checkout/messages/customer_registration_message';
    private const XML_PATH_INTERNAL_EMAIL_RESTRICTED_MESSAGE = 'mve_restrict_checkout/messages/internal_email_restricted_message';
    private const XML_PATH_INTERNAL_NAME_RESTRICTED_MESSAGE = 'mve_restrict_checkout/messages/internal_name_restricted_message';
    private const XML_PATH_ENABLE_LOGGING = 'mve_restrict_checkout/general/enable_logging';
    
    private const XML_PATH_BLOCKED_DOMAINS = 'mve_restrict_checkout/restricted_emails/blocked_domains';
    private const XML_PATH_BLOCKED_EMAILS = 'mve_restrict_checkout/restricted_emails/blocked_emails';
    private const XML_PATH_BLOCKED_FIRST_NAMES = 'mve_restrict_checkout/restricted_emails/blocked_first_names';
    private const XML_PATH_BLOCKED_LAST_NAMES = 'mve_restrict_checkout/restricted_emails/blocked_last_names';
    
    private const XML_PATH_GUEST_CHECKOUT_MESSAGE = 'mve_restrict_checkout/messages/guest_checkout_message';
    private const XML_PATH_REGISTERED_CHECKOUT_MESSAGE = 'mve_restrict_checkout/messages/registered_checkout_message';
    private const XML_PATH_REGISTRATION_MESSAGE = 'mve_restrict_checkout/messages/registration_message';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Get raw configuration value for debugging
     *
     * @param string $path
     * @param string|null $scopeCode
     * @return mixed
     */
    public function getRawConfigValue(string $path, ?string $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Check if module is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isEnabled(?string $scopeCode = null): bool
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        

        
        return $value === '1' || $value === 1 || $value === true;
    }

    /**
     * Check if guest checkout restriction is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isGuestCheckoutRestricted(?string $scopeCode = null): bool
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_RESTRICT_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $value === '1' || $value === 1 || $value === true;
    }

    /**
     * Check if registered customer checkout restriction is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isRegisteredCheckoutRestricted(?string $scopeCode = null): bool
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_RESTRICT_REGISTERED_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $value === '1' || $value === 1 || $value === true;
    }

    /**
     * Check if customer registration restriction is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isCustomerRegistrationRestricted(?string $scopeCode = null): bool
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_RESTRICT_CUSTOMER_REGISTRATION,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $value === '1' || $value === 1 || $value === true;
    }

    /**
     * Check if logging is enabled
     *
     * @param string|null $scopeCode
     * @return bool
     */
    public function isLoggingEnabled(?string $scopeCode = null): bool
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_LOGGING,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        
        return $value === '1' || $value === 1 || $value === true;
    }

    /**
     * Get blocked email domains
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
     * Get blocked email addresses
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
     * Get customer registration error message
     *
     * @param string|null $scopeCode
     * @return string
     */
    public function getCustomerRegistrationMessage(?string $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_REGISTRATION_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get internal email restricted message
     *
     * @param string|null $scopeCode
     * @return string
     */
    public function getInternalEmailRestrictedMessage(?string $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_INTERNAL_EMAIL_RESTRICTED_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get internal name restricted message
     *
     * @param string|null $scopeCode
     * @return string
     */
    public function getInternalNameRestrictedMessage(?string $scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_INTERNAL_NAME_RESTRICTED_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Debug method to check configuration values
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function debugConfiguration(?string $scopeCode = null): array
    {
        return [
            'enabled' => $this->isEnabled($scopeCode),
            'restrict_guest_checkout' => $this->isGuestCheckoutRestricted($scopeCode),
            'restrict_registered_checkout' => $this->isRegisteredCheckoutRestricted($scopeCode),
            'restrict_customer_registration' => $this->isCustomerRegistrationRestricted($scopeCode),
            'blocked_domains' => $this->getBlockedDomains($scopeCode),
            'blocked_emails' => $this->getBlockedEmails($scopeCode),
            'blocked_first_names' => $this->getBlockedFirstNames($scopeCode),
            'blocked_last_names' => $this->getBlockedLastNames($scopeCode),
            'guest_checkout_message' => $this->getGuestCheckoutMessage($scopeCode),
            'registered_checkout_message' => $this->getRegisteredCheckoutMessage($scopeCode),
            'customer_registration_message' => $this->getCustomerRegistrationMessage($scopeCode),
            'internal_email_restricted_message' => $this->getInternalEmailRestrictedMessage($scopeCode),
            'internal_name_restricted_message' => $this->getInternalNameRestrictedMessage($scopeCode),
            'logging_enabled' => $this->isLoggingEnabled($scopeCode)
        ];
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
        $lines = array_filter($lines, function($line) {
            return !empty($line) && $line !== '';
        });

        return array_values($lines);
    }
}

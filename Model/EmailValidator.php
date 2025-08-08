<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

/**
 * Email validation service for checkout restrictions
 */
class EmailValidator
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Check if email is restricted
     *
     * @param string $email
     * @param string|null $scopeCode
     * @return bool
     */
    public function isEmailRestricted(string $email, ?string $scopeCode = null): bool
    {
        if (!$this->config->isEnabled($scopeCode)) {
            return false;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $email = strtolower(trim($email));
        
        // Check specific blocked emails
        $blockedEmails = $this->config->getBlockedEmails($scopeCode);
        if (in_array($email, $blockedEmails)) {
            return true;
        }

        // Check blocked domains
        $blockedDomains = $this->config->getBlockedDomains($scopeCode);
        $emailDomain = $this->extractDomain($email);
        
        if (in_array($emailDomain, $blockedDomains)) {
            return true;
        }

        return false;
    }

    /**
     * Check if name is restricted
     *
     * @param string $firstName
     * @param string $lastName
     * @param string|null $scopeCode
     * @return bool
     */
    public function isNameRestricted(string $firstName, string $lastName, ?string $scopeCode = null): bool
    {
        if (!$this->config->isEnabled($scopeCode)) {
            return false;
        }

        // Validate input parameters
        if (empty($firstName) || empty($lastName)) {
            return false;
        }

        // Sanitize input
        $firstName = strtolower(trim(strip_tags($firstName)));
        $lastName = strtolower(trim(strip_tags($lastName)));

        // Check blocked first names
        $blockedFirstNames = array_map('strtolower', $this->config->getBlockedFirstNames($scopeCode));
        if (in_array($firstName, $blockedFirstNames)) {
            return true;
        }

        // Check blocked last names
        $blockedLastNames = array_map('strtolower', $this->config->getBlockedLastNames($scopeCode));
        if (in_array($lastName, $blockedLastNames)) {
            return true;
        }

        return false;
    }

    /**
     * Check if address email is restricted
     *
     * @param string $email
     * @param string|null $scopeCode
     * @return bool
     */
    public function isAddressEmailRestricted(string $email, ?string $scopeCode = null): bool
    {
        if (!$this->config->isEnabled($scopeCode)) {
            return false;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $email = strtolower(trim($email));
        
        // Check specific blocked address emails
        $blockedAddressEmails = $this->config->getBlockedAddressEmails($scopeCode);
        if (in_array($email, $blockedAddressEmails)) {
            return true;
        }

        // Check blocked address domains
        $blockedAddressDomains = $this->config->getBlockedAddressDomains($scopeCode);
        $emailDomain = $this->extractDomain($email);
        
        if (in_array($emailDomain, $blockedAddressDomains)) {
            return true;
        }

        return false;
    }

    /**
     * Extract domain from email address
     *
     * @param string $email
     * @return string
     */
    private function extractDomain(string $email): string
    {
        $parts = explode('@', $email);
        return count($parts) === 2 ? strtolower(trim($parts[1])) : '';
    }

    /**
     * Validate customer data for restrictions
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string|null $scopeCode
     * @return array
     */
    public function validateCustomerData(string $email, string $firstName, string $lastName, ?string $scopeCode = null): array
    {
        $errors = [];

        if ($this->isEmailRestricted($email, $scopeCode)) {
            $errors[] = 'Email address is restricted';
        }

        if ($this->isNameRestricted($firstName, $lastName, $scopeCode)) {
            $errors[] = 'Customer name is restricted';
        }

        return $errors;
    }
}

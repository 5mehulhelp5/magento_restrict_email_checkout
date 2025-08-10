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
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $email = strtolower(trim($email));
        
        // Check specific blocked emails
        $blockedEmails = $this->config->getBlockedEmails($scopeCode);
        if (!empty($blockedEmails) && in_array($email, $blockedEmails)) {
            return true;
        }

        // Check blocked domains
        $blockedDomains = $this->config->getBlockedDomains($scopeCode);
        if (!empty($blockedDomains)) {
            $parts = explode('@', $email);
            $emailDomain = count($parts) === 2 ? strtolower(trim($parts[1])) : '';
            if (in_array($emailDomain, $blockedDomains)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if first name is restricted
     *
     * @param string $firstName
     * @param string|null $scopeCode
     * @return bool
     */
    public function isFirstNameRestricted(string $firstName, ?string $scopeCode = null): bool
    {
        // Validate input parameter
        if (empty($firstName)) {
            return false;
        }

        // Sanitize input
        $firstName = strtolower(trim(strip_tags($firstName)));

        // Check blocked first names
        $blockedFirstNames = $this->config->getBlockedFirstNames($scopeCode);
        if (!empty($blockedFirstNames)) {
            $blockedFirstNames = array_map('strtolower', $blockedFirstNames);
            if (in_array($firstName, $blockedFirstNames)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if last name is restricted
     *
     * @param string $lastName
     * @param string|null $scopeCode
     * @return bool
     */
    public function isLastNameRestricted(string $lastName, ?string $scopeCode = null): bool
    {
        // Validate input parameter
        if (empty($lastName)) {
            return false;
        }

        // Sanitize input
        $lastName = strtolower(trim(strip_tags($lastName)));

        // Check blocked last names
        $blockedLastNames = $this->config->getBlockedLastNames($scopeCode);
        if (!empty($blockedLastNames)) {
            $blockedLastNames = array_map('strtolower', $blockedLastNames);
            if (in_array($lastName, $blockedLastNames)) {
                return true;
            }
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
        // Validate input parameters
        if (empty($firstName) || empty($lastName)) {
            return false;
        }

        // Sanitize input
        $firstName = strtolower(trim(strip_tags($firstName)));
        $lastName = strtolower(trim(strip_tags($lastName)));

        // Check blocked first names
        $blockedFirstNames = $this->config->getBlockedFirstNames($scopeCode);
        if (!empty($blockedFirstNames)) {
            $blockedFirstNames = array_map('strtolower', $blockedFirstNames);
            if (in_array($firstName, $blockedFirstNames)) {
                return true;
            }
        }

        // Check blocked last names
        $blockedLastNames = $this->config->getBlockedLastNames($scopeCode);
        if (!empty($blockedLastNames)) {
            $blockedLastNames = array_map('strtolower', $blockedLastNames);
            if (in_array($lastName, $blockedLastNames)) {
                return true;
            }
        }

        return false;
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
            $errors[] = $this->config->getInternalEmailRestrictedMessage($scopeCode);
        }

        if ($this->isNameRestricted($firstName, $lastName, $scopeCode)) {
            $errors[] = $this->config->getInternalNameRestrictedMessage($scopeCode);
        }

        return $errors;
    }
}

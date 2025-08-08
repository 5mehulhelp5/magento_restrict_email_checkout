<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\Data\CustomerInterface;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;

/**
 * Plugin for customer account management
 */
class AccountManagementPlugin
{
    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param EmailValidator $emailValidator
     * @param Config $config
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
    }

    /**
     * Before plugin for createAccount
     *
     * @param AccountManagement $subject
     * @param CustomerInterface $customer
     * @param string|null $password
     * @param string $redirectUrl
     * @return array
     * @throws LocalizedException
     */
    public function beforeCreateAccount(
        AccountManagement $subject,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        $this->validateCustomerRegistration($customer);
        return [$customer, $password, $redirectUrl];
    }

    /**
     * Before plugin for createAccountWithPasswordHash
     *
     * @param AccountManagement $subject
     * @param CustomerInterface $customer
     * @param string $hash
     * @param string $redirectUrl
     * @return array
     * @throws LocalizedException
     */
    public function beforeCreateAccountWithPasswordHash(
        AccountManagement $subject,
        CustomerInterface $customer,
        $hash,
        $redirectUrl = ''
    ) {
        $this->validateCustomerRegistration($customer);
        return [$customer, $hash, $redirectUrl];
    }

    /**
     * Validate customer registration restrictions
     *
     * @param CustomerInterface $customer
     * @throws LocalizedException
     */
    private function validateCustomerRegistration(CustomerInterface $customer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->config->isCustomerRegistrationRestricted()) {
            return;
        }

        // Validate email
        if ($this->emailValidator->isEmailRestricted($customer->getEmail())) {
            throw new LocalizedException(__($this->config->getRegistrationMessage()));
        }

        // Validate name
        if ($this->emailValidator->isNameRestricted($customer->getFirstname(), $customer->getLastname())) {
            throw new LocalizedException(__($this->config->getRegistrationMessage()));
        }
    }
}

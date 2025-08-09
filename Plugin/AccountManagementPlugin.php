<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Phrase;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;

/**
 * Plugin for customer account management with registration restrictions
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
     * Before create account plugin
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

        $email = $customer->getEmail();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();

        // Validate email
        if ($email && $this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getRegistrationMessage();
            throw new LocalizedException(new Phrase($message));
        }

        // Validate name
        if ($firstName && $lastName && $this->emailValidator->isNameRestricted($firstName, $lastName)) {
            $message = $this->config->getRegistrationMessage();
            throw new LocalizedException(new Phrase($message));
        }
    }
}

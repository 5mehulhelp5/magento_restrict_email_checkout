<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin\Api;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * API Plugin for Customer Registration Protection
 * Intercepts customer registration API calls and validates restrictions
 */
class CustomerRegistrationApiPlugin
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Before plugin for customer registration API
     *
     * @param AccountManagementInterface $subject
     * @param CustomerInterface $customer
     * @param string|null $password
     * @param string $redirectUrl
     * @return array
     * @throws LocalizedException
     */
    public function beforeCreateAccount(
        AccountManagementInterface $subject,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        try {
            // Early return if module is disabled
            if (!$this->config->isEnabled()) {
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->debug("MveRestrictCheckout API: Module disabled, allowing customer registration");
                }
                return [$customer, $password, $redirectUrl];
            }

            // Early return if customer registration restrictions are disabled
            if (!$this->config->isCustomerRegistrationRestricted()) {
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->debug("MveRestrictCheckout API: Customer registration restrictions disabled, allowing registration");
                }
                return [$customer, $password, $redirectUrl];
            }

            $email = $customer->getEmail();
            $firstName = $customer->getFirstname();
            $lastName = $customer->getLastname();

            if ($this->config->isLoggingEnabled()) {
                $this->logger->debug("MveRestrictCheckout API: Validating customer registration - Email: {$email}");
            }

            // Validate email and name restrictions
            $errors = $this->emailValidator->validateCustomerData($email, $firstName, $lastName);

            if (!empty($errors)) {
                $errorMessage = $this->config->getCustomerRegistrationMessage();
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->critical("MveRestrictCheckout API: Customer registration blocked - Email: {$email}, Errors: " . implode(', ', $errors));
                }
                throw new LocalizedException(__($errorMessage));
            }

            if ($this->config->isLoggingEnabled()) {
                $this->logger->debug("MveRestrictCheckout API: Customer registration validation passed - Email: {$email}");
            }

        } catch (LocalizedException $e) {
            // Re-throw LocalizedException to maintain API error response
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors but don't block registration
            if ($this->config->isLoggingEnabled()) {
                $this->logger->error("MveRestrictCheckout API: Unexpected error during customer registration validation: " . $e->getMessage());
            }
        }

        return [$customer, $password, $redirectUrl];
    }
}

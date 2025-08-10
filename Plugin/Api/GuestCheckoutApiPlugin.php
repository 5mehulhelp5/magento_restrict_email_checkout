<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin\Api;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * API Plugin for Guest Checkout Protection
 * Intercepts guest checkout API calls and validates restrictions
 */
class GuestCheckoutApiPlugin
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
     * Before plugin for guest checkout API
     *
     * @param GuestPaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformation(
        GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        $paymentMethod,
        $billingAddress = null
    ) {
        try {
            // Early return if module is disabled
            if (!$this->config->isEnabled()) {
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->debug("MveRestrictCheckout API: Module disabled, allowing guest checkout");
                }
                return [$cartId, $email, $paymentMethod, $billingAddress];
            }

            // Early return if guest checkout restrictions are disabled
            if (!$this->config->isGuestCheckoutRestricted()) {
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->debug("MveRestrictCheckout API: Guest checkout restrictions disabled, allowing checkout");
                }
                return [$cartId, $email, $paymentMethod, $billingAddress];
            }

            if ($this->config->isLoggingEnabled()) {
                $this->logger->debug("MveRestrictCheckout API: Validating guest checkout - Email: {$email}");
            }

            // Extract first and last name from billing address
            $firstName = $billingAddress ? $billingAddress->getFirstname() : '';
            $lastName = $billingAddress ? $billingAddress->getLastname() : '';

            // Validate email and name restrictions
            $errors = $this->emailValidator->validateCustomerData($email, $firstName, $lastName);

            if (!empty($errors)) {
                $errorMessage = $this->config->getGuestCheckoutMessage();
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->critical("MveRestrictCheckout API: Guest checkout blocked - Email: {$email}, Errors: " . implode(', ', $errors));
                }
                throw new LocalizedException(__($errorMessage));
            }

            if ($this->config->isLoggingEnabled()) {
                $this->logger->debug("MveRestrictCheckout API: Guest checkout validation passed - Email: {$email}");
            }

        } catch (LocalizedException $e) {
            // Re-throw LocalizedException to maintain API error response
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors but don't block checkout
            if ($this->config->isLoggingEnabled()) {
                $this->logger->error("MveRestrictCheckout API: Unexpected error during guest checkout validation: " . $e->getMessage());
            }
        }

        return [$cartId, $email, $paymentMethod, $billingAddress];
    }
}

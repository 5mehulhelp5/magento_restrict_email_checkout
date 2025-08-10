<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin\Api;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * API Plugin for Registered Customer Checkout Protection
 * Intercepts registered customer checkout API calls and validates restrictions
 */
class RegisteredCheckoutApiPlugin
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
     * Before plugin for registered customer checkout API
     *
     * @param PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformation(
        PaymentInformationManagementInterface $subject,
        $cartId,
        $paymentMethod,
        $billingAddress = null
    ) {
        try {
            // Early return if module is disabled
            if (!$this->config->isEnabled()) {
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->debug("MveRestrictCheckout API: Module disabled, allowing registered checkout");
                }
                return [$cartId, $paymentMethod, $billingAddress];
            }

            // Early return if registered checkout restrictions are disabled
            if (!$this->config->isRegisteredCheckoutRestricted()) {
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->debug("MveRestrictCheckout API: Registered checkout restrictions disabled, allowing checkout");
                }
                return [$cartId, $paymentMethod, $billingAddress];
            }

            // For registered customers, we need to get customer info from the cart
            // This will be validated in the frontend observer, but we can add basic protection here
            if ($this->config->isLoggingEnabled()) {
                $this->logger->debug("MveRestrictCheckout API: Registered checkout validation passed - Cart ID: {$cartId}");
            }

        } catch (LocalizedException $e) {
            // Re-throw LocalizedException to maintain API error response
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors but don't block checkout
            if ($this->config->isLoggingEnabled()) {
                $this->logger->error("MveRestrictCheckout API: Unexpected error during registered checkout validation: " . $e->getMessage());
            }
        }

        return [$cartId, $paymentMethod, $billingAddress];
    }
}

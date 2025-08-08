<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;
use Magento\Framework\Phrase;

/**
 * Plugin for guest payment information management
 */
class GuestPaymentInformationManagementPlugin
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
     * Before plugin for savePaymentInformationAndPlaceOrder
     *
     * @param GuestPaymentInformationManagement $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->validateGuestCheckout($email, $billingAddress);
        return [$cartId, $email, $paymentMethod, $billingAddress];
    }

    /**
     * Before plugin for savePaymentInformation
     *
     * @param GuestPaymentInformationManagement $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformation(
        GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->validateGuestCheckout($email, $billingAddress);
        return [$cartId, $email, $paymentMethod, $billingAddress];
    }

    /**
     * Validate guest checkout restrictions
     *
     * @param string $email
     * @param AddressInterface|null $billingAddress
     * @throws LocalizedException
     */
    private function validateGuestCheckout(string $email, ?AddressInterface $billingAddress): void
    {
        // Debug logging
        error_log("Marvelic: validateGuestCheckout called with email: " . $email);
        error_log("Marvelic: Module enabled: " . ($this->config->isEnabled() ? 'true' : 'false'));
        error_log("Marvelic: Guest checkout restricted: " . ($this->config->isGuestCheckoutRestricted() ? 'true' : 'false'));
        
        if (!$this->config->isEnabled()) {
            error_log("Marvelic: Module is disabled, skipping validation");
            return;
        }

        if (!$this->config->isGuestCheckoutRestricted()) {
            error_log("Marvelic: Guest checkout restriction is disabled, skipping validation");
            return;
        }

        // Validate email
        $isEmailRestricted = $this->emailValidator->isEmailRestricted($email);
        error_log("Marvelic: Email restricted: " . ($isEmailRestricted ? 'true' : 'false'));
        
        if ($isEmailRestricted) {
            $message = $this->config->getGuestCheckoutMessage();
            error_log("Marvelic: Throwing exception with message: " . $message);
            throw new LocalizedException(new Phrase($message));
        }

        // Validate billing address if provided
        if ($billingAddress && $this->config->isBillingAddressCheckEnabled()) {
            $this->validateBillingAddress($billingAddress);
        }
    }

    /**
     * Validate billing address restrictions
     *
     * @param AddressInterface $billingAddress
     * @throws LocalizedException
     */
    private function validateBillingAddress(AddressInterface $billingAddress): void
    {
        $email = $billingAddress->getEmail();
        if ($email && $this->emailValidator->isAddressEmailRestricted($email)) {
            throw new LocalizedException(new Phrase('Billing address email is restricted.'));
        }

        $firstName = $billingAddress->getFirstname();
        $lastName = $billingAddress->getLastname();
        
        if ($firstName && $lastName && $this->emailValidator->isNameRestricted($firstName, $lastName)) {
            throw new LocalizedException(new Phrase('Billing address name is restricted.'));
        }
    }
}

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
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->config->isGuestCheckoutRestricted()) {
            return;
        }

        // Validate email
        if ($this->emailValidator->isEmailRestricted($email)) {
            throw new LocalizedException(__($this->config->getGuestCheckoutMessage()));
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
            throw new LocalizedException(__('Billing address email is restricted.'));
        }

        $firstName = $billingAddress->getFirstname();
        $lastName = $billingAddress->getLastname();
        
        if ($firstName && $lastName && $this->emailValidator->isNameRestricted($firstName, $lastName)) {
            throw new LocalizedException(__('Billing address name is restricted.'));
        }
    }
}

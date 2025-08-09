<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Phrase;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;

/**
 * Plugin for guest payment information management with checkout restrictions
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
     * Before save payment information and place order plugin
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
     * Validate guest checkout restrictions
     *
     * @param string $email
     * @param AddressInterface|null $billingAddress
     * @throws LocalizedException
     */
    private function validateGuestCheckout(string $email, ?AddressInterface $billingAddress): void
    {
        // Check if module is enabled
        if (!$this->config->isEnabled()) {
            return;
        }

        // Check if guest checkout restriction is enabled
        if (!$this->config->isGuestCheckoutRestricted()) {
            return;
        }

        // Validate email
        if ($this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getGuestCheckoutMessage();
            if (empty($message)) {
                $message = 'Sorry, guest checkout is not allowed for this email address. Please register an account or use a different email address.';
            }
            
            // Use a more specific exception type
            throw new CouldNotSaveException(
                new Phrase($message)
            );
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

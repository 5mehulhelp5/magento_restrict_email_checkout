<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin;

use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Phrase;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Plugin for payment information management with registered customer checkout restrictions
 */
class PaymentInformationManagementPlugin
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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config,
        CartRepositoryInterface $cartRepository
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Before save payment information and place order plugin
     *
     * @param PaymentInformationManagement $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagement $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->validateRegisteredCheckout($cartId, $billingAddress);
        return [$cartId, $paymentMethod, $billingAddress];
    }

    /**
     * Validate registered customer checkout restrictions
     *
     * @param int $cartId
     * @param AddressInterface|null $billingAddress
     * @throws LocalizedException
     */
    private function validateRegisteredCheckout(int $cartId, ?AddressInterface $billingAddress): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->config->isRegisteredCheckoutRestricted()) {
            return;
        }

        // Get customer email from cart
        try {
            $cart = $this->cartRepository->get($cartId);
            $customerEmail = $cart->getCustomerEmail();
            
            if ($customerEmail && $this->emailValidator->isEmailRestricted($customerEmail)) {
                $message = $this->config->getRegisteredCheckoutMessage();
                throw new LocalizedException(new Phrase($message));
            }
        } catch (\Exception $e) {
            // If cart not found, continue with validation
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

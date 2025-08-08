<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Plugin;

use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;

/**
 * Plugin for registered customer payment information management
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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config,
        CustomerRepositoryInterface $customerRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Before plugin for savePaymentInformationAndPlaceOrder
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
     * Before plugin for savePaymentInformation
     *
     * @param PaymentInformationManagement $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformation(
        PaymentInformationManagement $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->validateRegisteredCheckout($cartId, $billingAddress);
        return [$cartId, $paymentMethod, $billingAddress];
    }

    /**
     * Validate registered checkout restrictions
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

        try {
            $cart = $this->cartRepository->get($cartId);
            $customerId = $cart->getCustomerId();
            
            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);
                
                // Validate customer email
                if ($this->emailValidator->isEmailRestricted($customer->getEmail())) {
                    throw new LocalizedException(__($this->config->getRegisteredCheckoutMessage()));
                }

                // Validate customer name
                if ($this->emailValidator->isNameRestricted($customer->getFirstname(), $customer->getLastname())) {
                    throw new LocalizedException(__($this->config->getRegisteredCheckoutMessage()));
                }
            }

            // Validate billing address if provided
            if ($billingAddress && $this->config->isBillingAddressCheckEnabled()) {
                $this->validateBillingAddress($billingAddress);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Cart or customer not found, skip validation
            return;
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

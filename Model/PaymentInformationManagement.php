<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Checkout\Model\PaymentInformationManagement as OriginalPaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Phrase;

/**
 * Payment information management with checkout restrictions for registered customers
 */
class PaymentInformationManagement extends OriginalPaymentInformationManagement
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
     * @param \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory
     * @param EmailValidator $emailValidator
     * @param Config $config
     */
    public function __construct(
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory,
        EmailValidator $emailValidator,
        Config $config
    ) {
        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentInformationManagement,
            $cartRepository,
            $searchCriteriaBuilder,
            $filterBuilder,
            $cartTotalsRepository,
            $logger,
            $cartFactory
        );
        $this->emailValidator = $emailValidator;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->validateRegisteredCheckout($cartId, $billingAddress);
        return parent::savePaymentInformationAndPlaceOrder($cartId, $paymentMethod, $billingAddress);
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
        $cart = $this->cartRepository->get($cartId);
        $customerEmail = $cart->getCustomerEmail();

        if ($customerEmail && $this->emailValidator->isEmailRestricted($customerEmail)) {
            $message = $this->config->getRegisteredCheckoutMessage();
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

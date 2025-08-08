<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Checkout\Model\GuestPaymentInformationManagement as OriginalGuestPaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Guest payment information management with checkout restrictions
 */
class GuestPaymentInformationManagement extends OriginalGuestPaymentInformationManagement
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
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $guestPaymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface $guestCartManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Api\CartExtensionFactory $cartExtensionFactory
     * @param \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory
     * @param EmailValidator $emailValidator
     * @param Config $config
     */
    public function __construct(
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $guestPaymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $guestCartManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Api\CartExtensionFactory $cartExtensionFactory,
        \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory,
        EmailValidator $emailValidator,
        Config $config
    ) {
        parent::__construct(
            $billingAddressManagement,
            $guestPaymentMethodManagement,
            $guestCartManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentInformationManagement,
            $cartRepository,
            $searchCriteriaBuilder,
            $filterBuilder,
            $cartTotalsRepository,
            $logger,
            $cartExtensionFactory,
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
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->validateGuestCheckout($email, $billingAddress);
        return parent::savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMethod, $billingAddress);
    }

    /**
     * @inheritDoc
     */
    public function savePaymentInformation(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->validateGuestCheckout($email, $billingAddress);
        return parent::savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
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

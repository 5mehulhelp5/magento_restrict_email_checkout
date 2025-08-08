<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Checkout\Model\PaymentInformationManagement as OriginalPaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Api\CartExtensionFactory $cartExtensionFactory
     * @param \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Api\CartExtensionFactory $cartExtensionFactory,
        \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory,
        EmailValidator $emailValidator,
        Config $config,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
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
        $this->customerRepository = $customerRepository;
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
     * @inheritDoc
     */
    public function savePaymentInformation(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->validateRegisteredCheckout($cartId, $billingAddress);
        return parent::savePaymentInformation($cartId, $paymentMethod, $billingAddress);
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

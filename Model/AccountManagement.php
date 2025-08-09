<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Customer\Model\AccountManagement as OriginalAccountManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Phrase;

/**
 * Account management with customer registration restrictions
 */
class AccountManagement extends OriginalAccountManagement
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
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Customer\Model\Metadata\Validator $validator
     * @param \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\CustomerMetadataInterface $customerMetadataService
     * @param \Magento\Customer\Model\Config\Share $configShare
     * @param \Magento\PasswordHash\Encryptor $encryptor
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryApi
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Directory\Model\AllowedCountries $allowedCountriesReader
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Customer\Api\AccountConfirmationInterface $accountConfirmation
     * @param \Magento\Customer\Model\ResourceModel\Visitor $visitorResource
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilderApi
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilderApi
     * @param \Magento\Framework\Api\Search\SortOrderBuilder $sortOrderBuilderApi
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilderSearch
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilderSearch
     * @param \Magento\Framework\Api\Search\SortOrderBuilder $sortOrderBuilderSearch
     * @param EmailValidator $emailValidator
     * @param Config $config
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Math\Random $mathRandom,
        \Magento\Customer\Model\Metadata\Validator $validator,
        \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory $validationResultsDataFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadataService,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\PasswordHash\Encryptor $encryptor,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryApi,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Directory\Model\AllowedCountries $allowedCountriesReader,
        \Magento\Framework\Session\Generic $session,
        \Magento\Customer\Api\AccountConfirmationInterface $accountConfirmation,
        \Magento\Customer\Model\ResourceModel\Visitor $visitorResource,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilderApi,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilderApi,
        \Magento\Framework\Api\Search\SortOrderBuilder $sortOrderBuilderApi,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilderSearch,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilderSearch,
        \Magento\Framework\Api\Search\SortOrderBuilder $sortOrderBuilderSearch,
        EmailValidator $emailValidator,
        Config $config
    ) {
        parent::__construct(
            $customerFactory,
            $eventManager,
            $storeManager,
            $mathRandom,
            $validator,
            $validationResultsDataFactory,
            $addressRepository,
            $customerMetadataService,
            $configShare,
            $encryptor,
            $customerRepository,
            $scopeConfig,
            $transportBuilder,
            $encryptorInterface,
            $customerRepositoryApi,
            $dateTime,
            $customerRegistry,
            $dataProcessor,
            $registry,
            $groupRepository,
            $storeManagerInterface,
            $customerDataFactory,
            $customerModel,
            $extensibleDataObjectConverter,
            $addressDataFactory,
            $allowedCountriesReader,
            $session,
            $accountConfirmation,
            $visitorResource,
            $searchCriteriaBuilder,
            $filterBuilder,
            $sortOrderBuilder,
            $filterGroupBuilder,
            $searchCriteriaBuilderApi,
            $filterGroupBuilderApi,
            $sortOrderBuilderApi,
            $searchCriteriaBuilderSearch,
            $filterGroupBuilderSearch,
            $sortOrderBuilderSearch
        );
        $this->emailValidator = $emailValidator;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function createAccount(CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        $this->validateCustomerRegistration($customer);
        return parent::createAccount($customer, $password, $redirectUrl);
    }

    /**
     * Validate customer registration restrictions
     *
     * @param CustomerInterface $customer
     * @throws LocalizedException
     */
    private function validateCustomerRegistration(CustomerInterface $customer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->config->isCustomerRegistrationRestricted()) {
            return;
        }

        $email = $customer->getEmail();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();

        // Validate email
        if ($email && $this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getRegistrationMessage();
            throw new LocalizedException(new Phrase($message));
        }

        // Validate name
        if ($firstName && $lastName && $this->emailValidator->isNameRestricted($firstName, $lastName)) {
            $message = $this->config->getRegistrationMessage();
            throw new LocalizedException(new Phrase($message));
        }
    }
}

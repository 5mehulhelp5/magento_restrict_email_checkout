<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Customer\Model\AccountManagement as OriginalAccountManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Customer account management with registration restrictions
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
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Customer\Model\Metadata\Validator $validator
     * @param \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\CustomerMetadataInterface $customerMetadataService
     * @param \Magento\Customer\Model\Config\Share $configShare
     * @param \Magento\PasswordHash\HasherInterface $passwordHasher
     * @param \Magento\Framework\Validator\DataObject $objectValidator
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param EmailValidator $emailValidator
     * @param Config $config
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Model\Metadata\Validator $validator,
        \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory $validationResultsDataFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadataService,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\PasswordHash\HasherInterface $passwordHasher,
        \Magento\Framework\Validator\DataObject $objectValidator,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
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
            $passwordHasher,
            $objectValidator,
            $extensibleDataObjectConverter,
            $customerDataFactory,
            $groupRepository,
            $searchCriteriaBuilder,
            $filterBuilder,
            $addressDataFactory,
            $regionDataFactory,
            $dataObjectProcessor,
            $registry,
            $accountManagement,
            $encryptor,
            $dateTime,
            $customerRegistry,
            $stringHelper,
            $customerModel,
            $objectFactory,
            $dataObjectHelper,
            $extensionAttributesJoinProcessor,
            $filterGroupBuilder,
            $collectionProcessor
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
     * @inheritDoc
     */
    public function createAccountWithPasswordHash(CustomerInterface $customer, $hash, $redirectUrl = '')
    {
        $this->validateCustomerRegistration($customer);
        return parent::createAccountWithPasswordHash($customer, $hash, $redirectUrl);
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

        // Validate email
        if ($this->emailValidator->isEmailRestricted($customer->getEmail())) {
            throw new LocalizedException(__($this->config->getRegistrationMessage()));
        }

        // Validate name
        if ($this->emailValidator->isNameRestricted($customer->getFirstname(), $customer->getLastname())) {
            throw new LocalizedException(__($this->config->getRegistrationMessage()));
        }
    }
}

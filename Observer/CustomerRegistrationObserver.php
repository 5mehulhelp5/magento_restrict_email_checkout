<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;
use Magento\Framework\Phrase;

/**
 * Observer for customer registration restrictions
 */
class CustomerRegistrationObserver implements ObserverInterface
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
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config,
        ManagerInterface $messageManager
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->config->isCustomerRegistrationRestricted()) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        if (!$customer) {
            return;
        }

        $email = $customer->getEmail();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();

        if ($this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getRegistrationMessage();
            $this->messageManager->addErrorMessage(new Phrase($message));
            throw new LocalizedException(new Phrase($message));
        }

        if ($firstName && $lastName && $this->emailValidator->isNameRestricted($firstName, $lastName)) {
            $message = $this->config->getRegistrationMessage();
            $this->messageManager->addErrorMessage(new Phrase($message));
            throw new LocalizedException(new Phrase($message));
        }
    }
}

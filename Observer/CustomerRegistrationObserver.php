<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Marvelic\MveRestrictCheckout\Model\EmailValidator;
use Marvelic\MveRestrictCheckout\Model\Config;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config,
        LoggerInterface $logger,
        ManagerInterface $messageManager
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute observer - prevents customer creation if restrictions apply
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        // Only check if module is enabled and customer registration restriction is enabled
        if (!$this->config->isEnabled() || !$this->config->isCustomerRegistrationRestricted()) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        if (!$customer) {
            return;
        }

        // Only check restrictions for new customers (no ID yet)
        if ($customer->getId()) {
            return;
        }

        $email = $customer->getEmail();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();

        // Check email restrictions
        if ($this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getRegistrationMessage();
            if (empty($message)) {
                $message = 'Customer registration is not allowed for this email address. Please use a different email address.';
            }

            // Log the blocked attempt
            if ($this->config->isLoggingEnabled()) {
                $this->logger->critical("MveRestrictCheckout Customer Registration Blocked: Email address is restricted", [
                    'email' => $email,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'action' => 'registration_prevented',
                    'reason' => 'restricted_email'
                ]);
            }

            // Show error message to user
            $this->messageManager->addErrorMessage($message);

            // Throw exception to prevent save
            throw new LocalizedException(new Phrase($message));
        }

        // Check name restrictions
        if ($this->emailValidator->isNameRestricted($firstName, $lastName)) {
            $message = $this->config->getRegistrationMessage();
            if (empty($message)) {
                $message = 'Customer registration is not allowed for this customer name. Please use a different name.';
            }

            // Log the blocked attempt
            if ($this->config->isLoggingEnabled()) {
                $this->logger->critical("MveRestrictCheckout Customer Registration Blocked: Customer name is restricted", [
                    'email' => $email,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'action' => 'registration_prevented',
                    'reason' => 'restricted_name'
                ]);
            }

            // Show error message to user
            $this->messageManager->addErrorMessage($message);

            // Throw exception to prevent save
            throw new LocalizedException(new Phrase($message));
        }
    }
}

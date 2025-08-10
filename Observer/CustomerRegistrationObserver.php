<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
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
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Execute observer - this will prevent customer registration
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        try {
            if (!$this->config->isEnabled() || !$this->config->isCustomerRegistrationRestricted()) {
                return;
            }

            $customer = $observer->getEvent()->getCustomer();
            if (!$customer) {
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
                throw new LocalizedException(new Phrase($message));
            }

            // Check name restrictions
            if ($this->emailValidator->isNameRestricted($firstName, $lastName)) {
                $message = $this->config->getRegistrationMessage();
                if (empty($message)) {
                    $message = 'Customer registration is not allowed for this customer name. Please use a different name.';
                }
                throw new LocalizedException(new Phrase($message));
            }



        } catch (\Exception $e) {
            if ($this->config->isLoggingEnabled()) {
                $this->logger->critical("MveRestrictCheckout Customer Registration Blocked: " . $e->getMessage());
            }
            
            // Re-throw to prevent customer creation
            throw $e;
        }
    }
}

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
 * Observer for checkout restrictions - blocks order creation
 */
class CheckoutRestrictionObserver implements ObserverInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Execute observer - this will prevent order creation
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        try {
            if (!$this->config->isEnabled()) {
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->debug("MveRestrictCheckout: Module disabled, allowing order");
                }
                return;
            }

            $order = $observer->getEvent()->getOrder();
            if (!$order) {
                if ($this->config->isLoggingEnabled()) {
                    $this->logger->debug("MveRestrictCheckout: No order found, allowing order");
                }
                return;
            }

            $email = $order->getCustomerEmail();
            $isGuest = $order->getCustomerId() === null;
            $firstName = $order->getCustomerFirstname();
            $lastName = $order->getCustomerLastname();
            
            if ($this->config->isLoggingEnabled()) {
                $this->logger->debug("MveRestrictCheckout: Validating order - Email: {$email}, Guest: " . ($isGuest ? 'Yes' : 'No'));
            }

            // Check guest checkout restrictions
            if ($isGuest && $this->config->isGuestCheckoutRestricted()) {
                $this->validateGuestCheckout($email, $firstName, $lastName);
            }

            // Check registered checkout restrictions
            if (!$isGuest && $this->config->isRegisteredCheckoutRestricted()) {
                $this->validateRegisteredCheckout($email, $firstName, $lastName);
            }

            if ($this->config->isLoggingEnabled()) {
                $this->logger->debug("MveRestrictCheckout: Order validation passed");
            }
            
        } catch (\Exception $e) {
            if ($this->config->isLoggingEnabled()) {
                $this->logger->critical("MveRestrictCheckout Order Blocked: " . $e->getMessage());
            }
            
            // Add error message to session
            $this->messageManager->addErrorMessage($e->getMessage());
            
            // Re-throw to prevent order creation
            throw $e;
        }
    }

    /**
     * Validate guest checkout restrictions
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @throws LocalizedException
     */
    private function validateGuestCheckout(string $email, string $firstName, string $lastName): void
    {
        // Check email restrictions
        if ($this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getGuestCheckoutMessage();
            if (empty($message)) {
                $message = 'Guest checkout is not allowed for this email address. Please register an account or use a different email address.';
            }
            throw new LocalizedException(new Phrase($message));
        }

        // Check name restrictions
        if ($this->emailValidator->isNameRestricted($firstName, $lastName)) {
            $message = $this->config->getGuestCheckoutMessage();
            if (empty($message)) {
                $message = 'Guest checkout is not allowed for this customer name. Please register an account or use a different name.';
            }
            throw new LocalizedException(new Phrase($message));
        }
    }

    /**
     * Validate registered checkout restrictions
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @throws LocalizedException
     */
    private function validateRegisteredCheckout(string $email, string $firstName, string $lastName): void
    {
        // Check email restrictions
        if ($this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getRegisteredCheckoutMessage();
            if (empty($message)) {
                $message = 'Checkout is not allowed for this email address. Please use a different email address.';
            }
            throw new LocalizedException(new Phrase($message));
        }

        // Check name restrictions
        if ($this->emailValidator->isNameRestricted($firstName, $lastName)) {
            $message = $this->config->getRegisteredCheckoutMessage();
            if (empty($message)) {
                $message = 'Checkout is not allowed for this customer name. Please use a different name.';
            }
            throw new LocalizedException(new Phrase($message));
        }
    }


}

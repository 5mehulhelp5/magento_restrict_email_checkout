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
 * Observer for cart restrictions
 */
class CartRestrictionObserver implements ObserverInterface
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
     * Execute observer - this will prevent adding products to cart for restricted emails
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        try {
            if (!$this->config->isEnabled()) {
                return;
            }

            $cart = $observer->getEvent()->getCart();
            if (!$cart) {
                return;
            }

            $quote = $cart->getQuote();
            if (!$quote) {
                return;
            }

            $email = $quote->getCustomerEmail();
            if (!$email) {
                return;
            }

            $isGuest = $quote->getCustomerId() === null;

            // Check guest cart restrictions
            if ($isGuest && $this->config->isGuestCheckoutRestricted()) {
                if ($this->emailValidator->isEmailRestricted($email)) {
                    $message = $this->config->getGuestCheckoutMessage();
                    if (empty($message)) {
                        $message = 'Adding products to cart is not allowed for this email address. Please register an account or use a different email address.';
                    }
                    throw new LocalizedException(new Phrase($message));
                }
            }

            // Check registered cart restrictions
            if (!$isGuest && $this->config->isRegisteredCheckoutRestricted()) {
                if ($this->emailValidator->isEmailRestricted($email)) {
                    $message = $this->config->getRegisteredCheckoutMessage();
                    if (empty($message)) {
                        $message = 'Adding products to cart is not allowed for this email address. Please use a different email address.';
                    }
                    throw new LocalizedException(new Phrase($message));
                }
            }

        } catch (\Exception $e) {
            if ($this->config->isLoggingEnabled()) {
                $this->logger->critical("MveRestrictCheckout Cart Restriction Error: " . $e->getMessage());
            }
            throw $e;
        }
    }
}

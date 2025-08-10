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
use Marvelic\MveRestrictCheckout\Model\ApiExceptionHandler;

/**
 * Observer for API order placement restrictions
 */
class ApiOrderRestrictionObserver implements ObserverInterface
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
     * @var ApiExceptionHandler
     */
    private $apiExceptionHandler;

    /**
     * @param EmailValidator $emailValidator
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param ApiExceptionHandler $apiExceptionHandler
     */
    public function __construct(
        EmailValidator $emailValidator,
        Config $config,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        ApiExceptionHandler $apiExceptionHandler
    ) {
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->apiExceptionHandler = $apiExceptionHandler;
    }

    /**
     * Execute observer - blocks API order placement if restrictions apply
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        // Only check if module is enabled
        if (!$this->config->isEnabled()) {
            return;
        }

        // Only process API requests
        if (!$this->isApiRequest()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            return;
        }

        $email = $order->getCustomerEmail();
        $isGuest = $order->getCustomerId() === null;
        $firstName = $order->getCustomerFirstname();
        $lastName = $order->getCustomerLastname();

        // Check guest order restrictions
        if ($isGuest && $this->config->isGuestCheckoutRestricted()) {
            $this->validateGuestOrder($email, $firstName, $lastName, $order);
        }

        // Check registered order restrictions
        if (!$isGuest && $this->config->isRegisteredCheckoutRestricted()) {
            $this->validateRegisteredOrder($email, $firstName, $lastName, $order);
        }
    }

    /**
     * Check if current request is an API request
     *
     * @return bool
     */
    private function isApiRequest(): bool
    {
        return $this->apiExceptionHandler->isApiRequest();
    }

    /**
     * Validate guest order placement
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param mixed $order
     * @return void
     * @throws LocalizedException
     */
    private function validateGuestOrder(string $email, string $firstName, string $lastName, $order): void
    {
        if ($this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getGuestCheckoutMessage();
            if (empty($message)) {
                $message = 'Guest checkout is not allowed for this email address. Please register an account or use a different email address.';
            }

            $this->logAndThrowApiException($message, [
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'action' => 'order_placement_blocked',
                'reason' => 'restricted_email',
                'request_type' => 'api_guest_order',
                'orderId' => $order->getIncrementId() ?? 'unknown',
                'customerId' => 'guest'
            ]);
        }

        if ($this->emailValidator->isNameRestricted($firstName, $lastName)) {
            $message = $this->config->getGuestCheckoutMessage();
            if (empty($message)) {
                $message = 'Guest checkout is not allowed for this customer name. Please register an account or use a different name.';
            }

            $this->logAndThrowApiException($message, [
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'action' => 'order_placement_blocked',
                'reason' => 'restricted_name',
                'request_type' => 'api_guest_order',
                'orderId' => $order->getIncrementId() ?? 'unknown',
                'customerId' => 'guest'
            ]);
        }
    }

    /**
     * Validate registered order placement
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param mixed $order
     * @return void
     * @throws LocalizedException
     */
    private function validateRegisteredOrder(string $email, string $firstName, string $lastName, $order): void
    {
        if ($this->emailValidator->isEmailRestricted($email)) {
            $message = $this->config->getRegisteredCheckoutMessage();
            if (empty($message)) {
                $message = 'Order placement is not allowed for this email address. Please use a different email address.';
            }

            $this->logAndThrowApiException($message, [
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'action' => 'order_placement_blocked',
                'reason' => 'restricted_email',
                'request_type' => 'api_registered_order',
                'orderId' => $order->getIncrementId() ?? 'unknown',
                'customerId' => $order->getCustomerId() ?? 'unknown'
            ]);
        }

        if ($this->emailValidator->isNameRestricted($firstName, $lastName)) {
            $message = $this->config->getRegisteredCheckoutMessage();
            if (empty($message)) {
                $message = 'Order placement is not allowed for this customer name. Please use a different name.';
            }

            $this->logAndThrowApiException($message, [
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'action' => 'order_placement_blocked',
                'reason' => 'restricted_name',
                'request_type' => 'api_registered_order',
                'orderId' => $order->getIncrementId() ?? 'unknown',
                'customerId' => $order->getCustomerId() ?? 'unknown'
            ]);
        }
    }

    /**
     * Log the blocked attempt and throw API exception
     *
     * @param string $message
     * @param array $context
     * @return void
     * @throws LocalizedException
     */
    private function logAndThrowApiException(string $message, array $context): void
    {
        // Log the blocked attempt
        if ($this->config->isLoggingEnabled()) {
            $this->logger->critical("MveRestrictCheckout API Order Placement Blocked: " . $message, $context);
        }

        // Create LocalizedException and let ApiExceptionHandler convert it to proper HTTP response
        $exception = new LocalizedException(new Phrase($message));
        $this->apiExceptionHandler->handleApiException($exception);
    }
}

<?php
/**
 * Copyright © Marvelic. All rights reserved.
 */

namespace Marvelic\MveRestrictCheckout\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Handles API exceptions and converts them to proper HTTP responses
 */
class ApiExceptionHandler
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Check if current request is an API request
     *
     * @return bool
     */
    public function isApiRequest(): bool
    {
        $route = $this->request->getRouteName();
        
        // Check for REST API, SOAP API, or GraphQL
        return $route === 'rest' || 
               $route === 'soap' || 
               $route === 'graphql' ||
               strpos($this->request->getPathInfo(), '/rest/') === 0 ||
               strpos($this->request->getPathInfo(), '/soap/') === 0 ||
               strpos($this->request->getPathInfo(), '/graphql') === 0;
    }

    /**
     * Convert LocalizedException to WebapiException for API requests
     *
     * @param LocalizedException $exception
     * @return WebapiException
     */
    public function convertToWebapiException(LocalizedException $exception): WebapiException
    {
        return new WebapiException(
            $exception->getMessage(),
            0,
            WebapiException::HTTP_FORBIDDEN
        );
    }

    /**
     * Handle exception for API requests
     *
     * @param LocalizedException $exception
     * @return void
     * @throws WebapiException
     */
    public function handleApiException(LocalizedException $exception): void
    {
        if ($this->isApiRequest()) {
            // Log the API exception
            $this->logger->warning("MveRestrictCheckout API Exception: " . $exception->getMessage(), [
                'exception' => $exception->getMessage(),
                'request_type' => 'api_request',
                'endpoint' => $this->request->getPathInfo()
            ]);

            // Convert to WebapiException for proper HTTP 403 response
            throw $this->convertToWebapiException($exception);
        }

        // For non-API requests, re-throw the original exception
        throw $exception;
    }
}

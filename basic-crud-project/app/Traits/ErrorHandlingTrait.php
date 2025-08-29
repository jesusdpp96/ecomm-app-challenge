<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\ErrorHandler;
use App\Libraries\ResponseFormatter;

/**
 * Trait for consistent error handling across controllers
 */
trait ErrorHandlingTrait
{
    /**
     * Universal exception handler - handles any throwable with appropriate response
     *
     * @param \Throwable $exception
     * @param string $defaultMessage
     * @return ResponseInterface
     */
    protected function handleExceptionResponse(\Throwable $exception, string $defaultMessage = 'Operation failed'): ResponseInterface
    {
        $errors = ErrorHandler::handleException($exception);
        $statusCode = ErrorHandler::getHttpStatusFromException($exception);
        
        $response = ResponseFormatter::error($errors, $defaultMessage, $statusCode);
        
        return $this->response->setStatusCode($statusCode)->setJSON($response);
    }

    /**
     * Execute operation with automatic exception handling
     * Eliminates need for try-catch blocks in controllers
     *
     * @param callable $operation
     * @param string $defaultErrorMessage
     * @return ResponseInterface|mixed
     */
    protected function executeWithErrorHandling(callable $operation, string $defaultErrorMessage = 'Operation failed')
    {
        try {
            return $operation();
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e, $defaultErrorMessage);
        }
    }
}

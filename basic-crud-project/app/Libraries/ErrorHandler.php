<?php

namespace App\Libraries;

use Throwable;
use CodeIgniter\Validation\Validation;
use CodeIgniter\HTTP\ResponseInterface;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductStorageException;
use App\Exceptions\ProductValidationException;

/**
 * Centralized error handling following CodeIgniter 4 best practices
 */
class ErrorHandler
{
    /**
     * Handle validation errors with CodeIgniter format compatibility
     *
     * @param array $errors
     * @param bool $useCodeIgniterFormat
     * @return array
     */
    public static function handleValidationErrors(array $errors, bool $useCodeIgniterFormat = false): array
    {
        if ($useCodeIgniterFormat) {
            return $errors; // Return as-is for CodeIgniter compatibility
        }

        $formattedErrors = [];
        
        foreach ($errors as $field => $message) {
            $formattedErrors[] = [
                'field' => $field,
                'message' => is_array($message) ? implode(', ', $message) : $message,
                'type' => 'validation'
            ];
        }

        return $formattedErrors;
    }

    /**
     * Handle CodeIgniter validation errors
     *
     * @param Validation $validation
     * @param bool $useCodeIgniterFormat
     * @return array
     */
    public static function handleCodeIgniterValidation(Validation $validation, bool $useCodeIgniterFormat = false): array
    {
        $errors = $validation->getErrors();
        return self::handleValidationErrors($errors, $useCodeIgniterFormat);
    }

    /**
     * Handle not found exceptions
     *
     * @param string $entity
     * @param int|string $id
     * @return array
     */
    public static function handleNotFoundException(string $entity, $id): array
    {
        return [
            [
                'field' => 'id',
                'message' => ucfirst($entity) . " with ID {$id} not found 222",
                'type' => 'not_found'
            ]
        ];
    }

    /**
     * Handle storage errors
     *
     * @param Throwable $e
     * @return array
     */
    public static function handleStorageError(Throwable $e): array
    {
        self::logError($e, ['context' => 'storage']);
        
        return [
            [
                'field' => 'storage',
                'message' => self::shouldExposeError($e) ? $e->getMessage() : 'Storage operation failed',
                'type' => 'storage_error'
            ]
        ];
    }

    /**
     * Handle generic errors
     *
     * @param Throwable $e
     * @return array
     */
    public static function handleGenericError(Throwable $e): array
    {
        self::logError($e, ['context' => 'generic']);
        
        $message = self::shouldExposeError($e) 
            ? $e->getMessage() 
            : 'An unexpected error occurred';

        return [
            [
                'field' => 'system',
                'message' => $message,
                'type' => 'system_error'
            ]
        ];
    }

    /**
     * Handle exceptions with proper HTTP status codes and routing
     *
     * @param Throwable $e
     * @return array
     */
    public static function handleException(Throwable $e): array
    {
        $exceptionClass = get_class($e);
        
        // Handle custom exceptions
        switch ($exceptionClass) {
            case ProductNotFoundException::class:
                /** @var ProductNotFoundException $e */
                return self::handleNotFoundException('product', $e->getProductId());
            
            case ProductValidationException::class:
                /** @var ProductValidationException $e */
                return self::handleValidationErrors($e->getValidationErrors());
            
            case ProductStorageException::class:
                return self::handleStorageError($e);
            
            case 'CodeIgniter\\Exceptions\\PageNotFoundException':
                return self::handleNotFoundException('page', 'unknown');
            
            case 'Respect\\Validation\\Exceptions\\ValidationException':
            case 'InvalidArgumentException':
            case 'DomainException':
                return self::handleValidationErrors(['validation' => $e->getMessage()]);
            
            default:
                return self::handleGenericError($e);
        }
    }

    /**
     * Get appropriate HTTP status code from exception type
     *
     * @param Throwable $e
     * @return int
     */
    public static function getHttpStatusFromException(Throwable $e): int
    {
        $exceptionMap = [
            ProductNotFoundException::class => 404,
            ProductValidationException::class => 400,
            ProductStorageException::class => 500,
            'CodeIgniter\\Exceptions\\PageNotFoundException' => 404,
            'InvalidArgumentException' => 400,
            'DomainException' => 400,
            'Respect\\Validation\\Exceptions\\ValidationException' => 400,
        ];

        $exceptionClass = get_class($e);
        
        return $exceptionMap[$exceptionClass] ?? 500;
    }

    /**
     * Log error with context
     *
     * @param Throwable $e
     * @param array $context
     * @return void
     */
    private static function logError(Throwable $e, array $context = []): void
    {
        $logContext = array_merge($context, [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        log_message('error', 'ErrorHandler: ' . $e->getMessage(), $logContext);
    }

    /**
     * Determine if error details should be exposed to client
     *
     * @param Throwable $e
     * @return bool
     */
    private static function shouldExposeError(Throwable $e): bool
    {
        // In production, only expose validation and business logic errors
        $exposableExceptions = [
            'Respect\Validation\Exceptions\ValidationException',
            'InvalidArgumentException',
            'DomainException'
        ];

        $exceptionClass = get_class($e);
        
        // Always expose in development
        if (ENVIRONMENT === 'development') {
            return true;
        }

        return in_array($exceptionClass, $exposableExceptions);
    }
}

<?php

namespace App\Libraries;

use Throwable;

/**
 * Centralized error handling for the application
 */
class ErrorHandler
{
    /**
     * Handle validation errors
     *
     * @param array $errors
     * @return array
     */
    public static function handleValidationErrors(array $errors): array
    {
        $formattedErrors = [];
        
        foreach ($errors as $field => $message) {
            $formattedErrors[] = [
                'field' => $field,
                'message' => $message,
                'type' => 'validation'
            ];
        }

        return $formattedErrors;
    }

    /**
     * Handle not found exceptions
     *
     * @param string $entity
     * @param int $id
     * @return array
     */
    public static function handleNotFoundException(string $entity, int $id): array
    {
        return [
            [
                'field' => 'id',
                'message' => ucfirst($entity) . " with ID {$id} not found",
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
                'message' => 'Storage operation failed',
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

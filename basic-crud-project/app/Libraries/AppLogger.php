<?php

namespace App\Libraries;

/**
 * Application-specific logger wrapper
 */
class AppLogger
{
    private string $context;

    public function __construct(string $context = 'Application')
    {
        $this->context = $context;
    }

    /**
     * Log an operation
     *
     * @param string $action
     * @param int|null $productId
     * @param array $data
     * @return void
     */
    public function logOperation(string $action, ?int $productId = null, array $data = []): void
    {
        $logData = [
            'context' => $this->context,
            'action' => $action,
            'product_id' => $productId,
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('c'),
            'data' => $data
        ];

        log_message('info', "Operation: {$action}", $logData);
    }

    /**
     * Log an error
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logError(string $message, array $context = []): void
    {
        $logData = array_merge([
            'context' => $this->context,
            'timestamp' => date('c')
        ], $context);

        log_message('error', $message, $logData);
    }

    /**
     * Log a warning
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logWarning(string $message, array $context = []): void
    {
        $logData = array_merge([
            'context' => $this->context,
            'timestamp' => date('c')
        ], $context);

        log_message('warning', $message, $logData);
    }

    /**
     * Log debug information
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logDebug(string $message, array $context = []): void
    {
        if (ENVIRONMENT === 'development') {
            $logData = array_merge([
                'context' => $this->context,
                'timestamp' => date('c')
            ], $context);

            log_message('debug', $message, $logData);
        }
    }
}

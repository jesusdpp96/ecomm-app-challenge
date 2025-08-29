<?php

namespace App\Traits;

use CodeIgniter\HTTP\RequestInterface;

/**
 * Trait for consistent CRUD operation logging using CodeIgniter 4 native logging
 */
trait CrudLoggingTrait
{
  /**
   * Log a CRUD operation with structured context
   *
   * @param string $operation The CRUD operation (create, read, update, delete, list, search)
   * @param string $entity The entity being operated on (e.g., 'product')
   * @param int|null $entityId The ID of the entity (if applicable)
   * @param array $data Additional data to log
   * @return void
   */
  protected function logCrudOperation(string $operation, string $entity, ?int $entityId = null, array $data = []): void
  {

    $username = session()->get('username');
    $role = session()->get('role');
    $context = [
      'operation' => $operation,
      'entity' => $entity,
      'entity_id' => $entityId,
      'class' => static::class,
      'user_ip' => $this->getUserIp(),
      'user_agent' => $this->getUserAgent(),
      'username' => $username,
      'role' => $role,
      'data' => $data
    ];

    $message = 'CRUD Operation: ' . $operation . ' on ' . $entity . ($entityId ? ' (ID: ' . $entityId . ')' : '');
    $logData = [
      'message' => $message,
      'context' => $context
    ];
    
    log_message('info', json_encode($logData));
  }

  /**
   * Log an error with structured context
   *
   * @param string $message The error message
   * @param array $context Additional context data
   * @return void
   */
  protected function logError(string $message, array $context = []): void
  {
    $context['class'] = static::class;
    $context['user_ip'] = $this->getUserIp();

    $logData = [
      'message' => $message,
      'context' => $context
    ];

    log_message('error', json_encode($logData));
  }

  /**
   * Log a warning with structured context
   *
   * @param string $message The warning message
   * @param array $context Additional context data
   * @return void
   */
  protected function logWarning(string $message, array $context = []): void
  {
    $context['class'] = static::class;
    $context['user_ip'] = $this->getUserIp();
    
    $logData = [
      'message' => $message,
      'context' => $context
    ];

    log_message('warning', json_encode($logData));
  }

  /**
   * Log debug information (only in development environment)
   *
   * @param string $message The debug message
   * @param array $context Additional context data
   * @return void
   */
  protected function logDebug(string $message, array $context = []): void
  {
    if (ENVIRONMENT === 'development') {
      $context['class'] = static::class;

      log_message('debug', $message, $context);
    }
  }

  /**
   * Get user IP address safely
   *
   * @return string
   */
  private function getUserIp(): string
  {
    if (property_exists($this, 'request') && $this->request instanceof RequestInterface) {
      return $this->request->getIPAddress();
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  }

  /**
   * Get user agent safely
   *
   * @return string
   */
  private function getUserAgent(): string
  {
    if (property_exists($this, 'request') && $this->request instanceof RequestInterface) {
      return $this->request->getHeaderLine('User-Agent') ?: 'unknown';
    }

    return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
  }
}

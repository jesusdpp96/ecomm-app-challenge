<?php

namespace App\Exceptions;

/**
 * Custom exception class for storage-related errors
 */
class StorageException extends \Exception
{
    public const FILE_NOT_FOUND = 1001;
    public const PERMISSION_DENIED = 1002;
    public const LOCK_FAILED = 1003;
    public const INVALID_JSON = 1004;
    public const BACKUP_FAILED = 1005;

    /**
     * Create a new StorageException instance
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get a user-friendly error message based on the error code
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return match ($this->code) {
            self::FILE_NOT_FOUND => 'The requested file could not be found.',
            self::PERMISSION_DENIED => 'Permission denied to access the file.',
            self::LOCK_FAILED => 'Failed to acquire file lock.',
            self::INVALID_JSON => 'Invalid JSON data structure.',
            self::BACKUP_FAILED => 'Failed to create backup file.',
            default => 'An unknown storage error occurred.'
        };
    }
}

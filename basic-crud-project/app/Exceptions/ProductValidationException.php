<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when product validation fails
 */
class ProductValidationException extends Exception
{
    private array $validationErrors;

    public function __construct(array $validationErrors, string $message = null, int $code = 400, Exception $previous = null)
    {
        $this->validationErrors = $validationErrors;
        $message = $message ?: "Product validation failed";
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the validation errors
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}

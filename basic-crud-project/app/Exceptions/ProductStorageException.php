<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when product storage operations fail
 */
class ProductStorageException extends Exception
{
    private string $operation;
    private $productId;

    public function __construct(string $operation, $productId = null, string $message = null, int $code = 500, Exception $previous = null)
    {
        $this->operation = $operation;
        $this->productId = $productId;
        $message = $message ?: "Product {$operation} operation failed" . ($productId ? " for ID {$productId}" : "");
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the operation that failed
     *
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * Get the product ID involved in the operation
     *
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }
}

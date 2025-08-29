<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a product is not found
 */
class ProductNotFoundException extends Exception
{
    private $productId;

    public function __construct($productId, string $message = null, int $code = 404, Exception $previous = null)
    {
        $this->productId = $productId;
        $message = $message ?: "Product with ID {$productId} not found";
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the product ID that was not found
     *
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }
}

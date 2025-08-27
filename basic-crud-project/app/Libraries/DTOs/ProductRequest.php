<?php

namespace App\Libraries\DTOs;

use App\Entities\Product;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Product request DTO for handling input data
 */
class ProductRequest
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?float $price,
        public readonly array $metadata = []
    ) {}

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'price' => $this->price,
            'metadata' => $this->metadata
        ];
    }

    /**
     * Validate the request data using Product entity
     *
     * @return array
     */
    public function validate(): array
    {
        try {
            // Use Product entity validation by creating a temporary instance
            new Product(
                null,
                $this->title ?? '',
                $this->price ?? 0.0
            );
            
            return []; // No errors
        } catch (ValidationException $e) {
            return ['validation' => $e->getMessage()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create from array data
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? null,
            isset($data['price']) ? (float)$data['price'] : null,
            $data['metadata'] ?? []
        );
    }

    /**
     * Check if request has valid data
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    /**
     * Get sanitized data for Product creation
     *
     * @return array
     */
    public function getSanitizedData(): array
    {
        return [
            'title' => $this->sanitizeString($this->title ?? ''),
            'price' => $this->sanitizePrice($this->price ?? 0.0)
        ];
    }

    /**
     * Sanitize string input
     *
     * @param string $input
     * @return string
     */
    private function sanitizeString(string $input): string
    {
        $input = trim($input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $input;
    }

    /**
     * Sanitize price input
     *
     * @param float $price
     * @return float
     */
    private function sanitizePrice(float $price): float
    {
        return round(max(0, $price), 2);
    }
}

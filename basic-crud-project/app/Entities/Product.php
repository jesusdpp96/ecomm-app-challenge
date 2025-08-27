<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use DateTime;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Immutable Product entity with business rules validation using Respect/Validation
 */
class Product extends Entity
{
    protected $id;
    protected $title;
    protected $price;
    protected $created_at;

    protected $dates = ['created_at'];
    
    protected $casts = [
        'id' => 'integer',
        'price' => 'float'
    ];

    /**
     * Business rules for Product validation
     */
    private static function getValidationRules(): array
    {
        return [
            'id' => v::optional(v::intVal()->positive()),
            'title' => v::stringType()
                        ->notEmpty()
                        ->length(1, 255)
                        ->setName('Title'),
            'price' => v::numericVal()
                       ->positive()
                       ->max(999999.99)
                       ->setName('Price'),
            'created_at' => v::optional(v::dateTime())
        ];
    }

    /**
     * Constructor - requires all basic data
     *
     * @param int|null $id
     * @param string $title
     * @param float|string|int $price
     * @param DateTime|string|null $created_at
     */
    public function __construct($id, $title, $price, $created_at = null)
    {
        parent::__construct();
        
        // Set created_at to current time if not provided
        if ($created_at === null) {
            $created_at = new DateTime();
        } elseif (is_string($created_at)) {
            $created_at = new DateTime($created_at);
        }

        // Sanitize and set attributes
        $this->attributes = [
            'id' => $id === null ? null : (int)$id,
            'title' => $this->sanitizeString((string)$title),
            'price' => $this->sanitizeNumeric($price),
            'created_at' => $created_at
        ];

        // Validate on creation
        $this->validateEntity();
    }

    /**
     * Create new instance from array data
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['title'] ?? '',
            $data['price'] ?? 0.0,
            $data['created_at'] ?? null
        );
    }

    /**
     * Validate the entire entity using business rules
     *
     * @throws ValidationException
     */
    private function validateEntity(): void
    {
        $rules = self::getValidationRules();
        
        foreach ($rules as $field => $validator) {
            $validator->assert($this->attributes[$field] ?? null);
        }
    }

    /**
     * Check if entity is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        try {
            $this->validateEntity();
            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        $errors = [];
        $rules = self::getValidationRules();
        
        foreach ($rules as $field => $validator) {
            try {
                $validator->assert($this->attributes[$field] ?? null);
            } catch (ValidationException $e) {
                $errors[$field] = $e->getMessage();
            }
        }
        
        return $errors;
    }

    /**
     * Immutable setter for title - returns new instance
     *
     * @param string $title
     * @return static
     */
    public function withTitle(string $title): self
    {
        return new self(
            $this->__get('id'),
            $title,
            $this->__get('price'),
            $this->__get('created_at')
        );
    }

    /**
     * Immutable setter for price - returns new instance
     *
     * @param float $price
     * @return static
     */
    public function withPrice(float $price): self
    {
        return new self(
            $this->__get('id'),
            $this->__get('title'),
            $price,
            $this->__get('created_at')
        );
    }

    /**
     * Immutable setter for id - returns new instance
     *
     * @param int|null $id
     * @return static
     */
    public function withId(?int $id): self
    {
        return new self(
            $id,
            $this->__get('title'),
            $this->__get('price'),
            $this->__get('created_at')
        );
    }

    /**
     * Immutable setter for created_at - returns new instance
     *
     * @param DateTime|string $date
     * @return static
     */
    public function withCreatedAt($date): self
    {
        return new self(
            $this->__get('id'),
            $this->__get('title'),
            $this->__get('price'),
            $date
        );
    }

    /**
     * Convert entity to array with proper formatting
     *
     * @param bool $onlyChanged
     * @param bool $cast
     * @param bool $recursive
     * @return array
     */
    public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array
    {
        $array = parent::toArray($onlyChanged, $cast, $recursive);

        if (isset($array['created_at']) && (is_object($array['created_at']) && method_exists($array['created_at'], 'format'))) {
            $array['created_at'] = $array['created_at']->format('c');
        }

        return $array;
    }

    /**
     * Get formatted price with currency symbol
     *
     * @param string $currency
     * @return string
     */
    public function getFormattedPrice(string $currency = '$'): string
    {
        return $currency . number_format($this->__get('price'), 2);
    }

    /**
     * Get formatted creation date
     *
     * @param string $format
     * @return string
     */
    public function getFormattedDate(string $format = 'Y-m-d H:i:s'): string
    {
        $createdAt = $this->__get('created_at');
        if ($createdAt instanceof DateTime || (is_object($createdAt) && method_exists($createdAt, 'format'))) {
            return $createdAt->format($format);
        }

        return '';
    }


    /**
     * Sanitize string input to prevent security issues
     *
     * @param string $input
     * @return string
     */
    private function sanitizeString(string $input): string
    {
        // Remove null bytes and control characters
        $input = str_replace(["\0", "\x0B"], '', $input);
        
        // Strip HTML and PHP tags
        $input = strip_tags($input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return trim($input);
    }

    /**
     * Sanitize numeric input
     *
     * @param mixed $input
     * @return float
     */
    private function sanitizeNumeric($input): float
    {
        // Remove any non-numeric characters except decimal point and minus sign
        $cleaned = preg_replace('/[^0-9.-]/', '', (string)$input);
        
        // Ensure it's a valid number, default to 0.0 if not
        return is_numeric($cleaned) ? (float)$cleaned : 0.0;
    }

    /**
     * Magic getter to maintain CodeIgniter 4 property access
     * Allows syntax like: $product->title
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        // Return raw attributes to avoid CodeIgniter casting
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        
        return parent::__get($key);
    }

    /**
     * Magic isset to maintain CodeIgniter 4 property checking
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return parent::__isset($key);
    }

    /**
     * Prevent direct property setting to maintain immutability
     *
     * @param string $key
     * @param mixed $value
     * @throws \RuntimeException
     */
    public function __set(string $key, $value = null): void
    {
        throw new \RuntimeException(
            "Product entity is immutable. Use with{$key}() method to create a new instance."
        );
    }

    /**
     * Create a copy of the product with updated attributes
     *
     * @param array $attributes
     * @return static
     */
    public function withAttributes(array $attributes): self
    {
        return new self(
            $attributes['id'] ?? $this->__get('id'),
            $attributes['title'] ?? $this->__get('title'),
            $attributes['price'] ?? $this->__get('price'),
            $attributes['created_at'] ?? $this->__get('created_at')
        );
    }
}
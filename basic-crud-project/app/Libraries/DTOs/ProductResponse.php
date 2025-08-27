<?php

namespace App\Libraries\DTOs;

use App\Entities\Product;

/**
 * Product response DTO for formatting output data
 */
class ProductResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly float $price,
        public readonly string $created_at,
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
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'created_at' => $this->created_at,
            'formatted_price' => '$' . number_format($this->price, 2),
            'metadata' => $this->metadata
        ];
    }

    /**
     * Create from Product entity
     *
     * @param Product $product
     * @return self
     */
    public static function fromModel(Product $product): self
    {
        return new self(
            $product->id,
            $product->title,
            $product->price,
            $product->getFormattedDate('c'),
            []
        );
    }

    /**
     * Create from array data
     *
     * @param array $product
     * @return self
     */
    public static function fromArray(array $product): self
    {
        return new self(
            $product['id'],
            $product['title'],
            $product['price'],
            $product['created_at'],
            $product['metadata'] ?? []
        );
    }

    /**
     * Create collection from Product entities
     *
     * @param Product[] $products
     * @return array
     */
    public static function fromCollection(array $products): array
    {
        return array_map(function(Product $product) {
            return self::fromModel($product)->toArray();
        }, $products);
    }
}

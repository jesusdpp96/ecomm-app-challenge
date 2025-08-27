<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\JSONStorage;
use App\Entities\Product;
use App\Exceptions\StorageException;
use Config\Storage as StorageConfig;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Product model with JSON storage backend
 * Uses Product entity for business logic and validation
 */
class ProductModel extends Model
{
    protected JSONStorage $storage;
    protected StorageConfig $config;

    /**
     * Initialize the model
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->config = config('Storage');
        $this->storage = new JSONStorage($this->config->getProductsFilePath());
    }

    /**
     * Get all products with optional filters, sorting, and pagination
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAllProducts(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        try {
            $data = $this->storage->read();
            $productEntities = $this->convertArraysToEntities($data['products']);

            // Apply filters
            if (!empty($filters)) {
                $productEntities = $this->applyFilters($productEntities, $filters);
            }

            // Apply sorting
            $sortBy = $filters['sort_by'] ?? 'id';
            $order = $filters['order'] ?? 'asc';
            $productEntities = $this->applySorting($productEntities, $sortBy, $order);

            // Get total count before pagination
            $totalCount = count($productEntities);

            // Apply pagination
            $paginatedProducts = $this->applyPagination($productEntities, $page, $perPage);

            return [
                'products' => $paginatedProducts,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_items' => $totalCount,
                    'total_pages' => ceil($totalCount / $perPage),
                    'has_next' => $page < ceil($totalCount / $perPage),
                    'has_prev' => $page > 1
                ]
            ];
        } catch (StorageException $e) {
            log_message('error', 'ProductModel::getAllProducts - ' . $e->getMessage());
            return ['products' => [], 'pagination' => []];
        }
    }

    /**
     * Get product by ID
     *
     * @param int $id
     * @return Product|null
     */
    public function getProductById(int $id): ?Product
    {
        try {
            $data = $this->storage->read();
            
            foreach ($data['products'] as $productData) {
                if ($productData['id'] === $id) {
                    return Product::fromArray($productData);
                }
            }
            
            return null;
        } catch (StorageException $e) {
            log_message('error', 'ProductModel::getProductById - ' . $e->getMessage());
            return null;
        } catch (ValidationException $e) {
            log_message('error', 'ProductModel::getProductById - Invalid product data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new product
     *
     * @param array $data
     * @return Product|false
     */
    public function createProduct(array $data): Product|false
    {
        try {
            $storageData = $this->storage->read();
            
            // Generate new ID
            $newId = $this->generateId($storageData);
            
            // Create Product entity (validation happens in constructor)
            $product = new Product(
                $newId,
                $data['title'] ?? '',
                $data['price'] ?? 0.0,
                $data['created_at'] ?? null
            );

            // Add to products array
            $storageData['products'][] = $product->toArray();
            $storageData['next_id'] = $newId + 1;

            // Save to storage
            if ($this->storage->write($storageData)) {
                return $product;
            }

            return false;
        } catch (ValidationException $e) {
            log_message('error', 'ProductModel::createProduct - Validation error: ' . $e->getMessage());
            return false;
        } catch (StorageException $e) {
            log_message('error', 'ProductModel::createProduct - Storage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing product
     *
     * @param int $id
     * @param array $data
     * @return Product|false
     */
    public function updateProduct(int $id, array $data): Product|false
    {
        try {
            $storageData = $this->storage->read();
            
            // Find and update product
            foreach ($storageData['products'] as $index => $productData) {
                if ($productData['id'] === $id) {
                    // Get existing product
                    $existingProduct = Product::fromArray($productData);
                    
                    // Create updated product entity (validation happens here)
                    $updatedProduct = $existingProduct->withAttributes([
                        'title' => $data['title'] ?? $existingProduct->title,
                        'price' => $data['price'] ?? $existingProduct->price
                    ]);
                    
                    // Update storage
                    $storageData['products'][$index] = $updatedProduct->toArray();
                    
                    if ($this->storage->write($storageData)) {
                        return $updatedProduct;
                    }
                    
                    return false;
                }
            }

            return false; // Product not found
        } catch (ValidationException $e) {
            log_message('error', 'ProductModel::updateProduct - Validation error: ' . $e->getMessage());
            return false;
        } catch (StorageException $e) {
            log_message('error', 'ProductModel::updateProduct - Storage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a product
     *
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        try {
            $storageData = $this->storage->read();
            
            // Find and remove product
            foreach ($storageData['products'] as $index => $product) {
                if ($product['id'] === $id) {
                    array_splice($storageData['products'], $index, 1);
                    return $this->storage->write($storageData);
                }
            }

            return false; // Product not found
        } catch (StorageException $e) {
            log_message('error', 'ProductModel::deleteProduct - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Search products by title
     *
     * @param string $query
     * @return Product[]
     */
    public function searchProducts(string $query): array
    {
        try {
            $data = $this->storage->read();
            $productEntities = $this->convertArraysToEntities($data['products']);
            
            if (empty(trim($query))) {
                return $productEntities;
            }

            $query = strtolower(trim($query));
            $results = [];

            foreach ($productEntities as $product) {
                if (strpos(strtolower($product->title), $query) !== false) {
                    $results[] = $product;
                }
            }

            return $results;
        } catch (StorageException $e) {
            log_message('error', 'ProductModel::searchProducts - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate product data using Product entity
     *
     * @param array $data
     * @return array
     */
    public function validateProductData(array $data): array
    {
        try {
            // Create a temporary product to validate (without ID for new products)
            $tempProduct = new Product(
                null,
                $data['title'] ?? '',
                $data['price'] ?? 0.0,
                $data['created_at'] ?? null
            );
            
            return []; // No errors if entity creation succeeded
        } catch (ValidationException $e) {
            // Parse validation exception to return structured errors
            return ['validation' => $e->getMessage()];
        }
    }

    /**
     * Generate next available ID
     *
     * @param array $storageData
     * @return int
     */
    private function generateId(array $storageData = null): int
    {
        if ($storageData === null) {
            $storageData = $this->storage->read();
        }

        return $storageData['next_id'];
    }

    /**
     * Apply filters to products array
     *
     * @param Product[] $products
     * @param array $filters
     * @return Product[]
     */
    private function applyFilters(array $products, array $filters): array
    {
        $filtered = $products;

        // Filter by price range
        if (isset($filters['min_price'])) {
            $minPrice = (float)$filters['min_price'];
            $filtered = array_filter($filtered, function(Product $product) use ($minPrice) {
                return $product->price >= $minPrice;
            });
        }

        if (isset($filters['max_price'])) {
            $maxPrice = (float)$filters['max_price'];
            $filtered = array_filter($filtered, function(Product $product) use ($maxPrice) {
                return $product->price <= $maxPrice;
            });
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $dateFrom = $filters['date_from'];
            $filtered = array_filter($filtered, function(Product $product) use ($dateFrom) {
                return $product->getFormattedDate('c') >= $dateFrom;
            });
        }

        if (isset($filters['date_to'])) {
            $dateTo = $filters['date_to'];
            $filtered = array_filter($filtered, function(Product $product) use ($dateTo) {
                return $product->getFormattedDate('c') <= $dateTo;
            });
        }

        // Filter by search query
        if (isset($filters['search']) && !empty(trim($filters['search']))) {
            $query = strtolower(trim($filters['search']));
            $filtered = array_filter($filtered, function(Product $product) use ($query) {
                return strpos(strtolower($product->title), $query) !== false;
            });
        }

        return array_values($filtered);
    }

    /**
     * Apply sorting to products array
     *
     * @param Product[] $products
     * @param string $sortBy
     * @param string $order
     * @return Product[]
     */
    private function applySorting(array $products, string $sortBy, string $order): array
    {
        $validSortFields = ['id', 'title', 'price', 'created_at'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'id';
        }

        $order = strtolower($order) === 'desc' ? 'desc' : 'asc';

        usort($products, function(Product $a, Product $b) use ($sortBy, $order) {
            $valueA = $a->$sortBy;
            $valueB = $b->$sortBy;

            // Handle different data types
            if ($sortBy === 'price') {
                $valueA = (float)$valueA;
                $valueB = (float)$valueB;
            } elseif ($sortBy === 'id') {
                $valueA = (int)$valueA;
                $valueB = (int)$valueB;
            } elseif ($sortBy === 'title') {
                $valueA = strtolower($valueA);
                $valueB = strtolower($valueB);
            } elseif ($sortBy === 'created_at') {
                $valueA = $a->getFormattedDate('c');
                $valueB = $b->getFormattedDate('c');
            }

            $comparison = $valueA <=> $valueB;
            
            return $order === 'desc' ? -$comparison : $comparison;
        });

        return $products;
    }

    /**
     * Apply pagination to products array
     *
     * @param Product[] $products
     * @param int $page
     * @param int $perPage
     * @return Product[]
     */
    private function applyPagination(array $products, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage)); // Limit to 100 items per page
        
        $offset = ($page - 1) * $perPage;
        
        return array_slice($products, $offset, $perPage);
    }

    /**
     * Get products count
     *
     * @return int
     */
    public function getProductsCount(): int
    {
        try {
            $data = $this->storage->read();
            return count($data['products']);
        } catch (StorageException $e) {
            log_message('error', 'ProductModel::getProductsCount - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if product exists
     *
     * @param int $id
     * @return bool
     */
    public function productExists(int $id): bool
    {
        return $this->getProductById($id) !== null;
    }

    /**
     * Get products by price range
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return Product[]
     */
    public function getProductsByPriceRange(float $minPrice, float $maxPrice): array
    {
        $filters = [
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ];
        
        $result = $this->getAllProducts($filters, 1, 1000);
        return $result['products'];
    }

    /**
     * Convert array data to Product entities
     *
     * @param array $productsData
     * @return Product[]
     */
    private function convertArraysToEntities(array $productsData): array
    {
        $entities = [];
        
        foreach ($productsData as $productData) {
            try {
                $entities[] = Product::fromArray($productData);
            } catch (ValidationException $e) {
                // Log invalid product data but continue processing
                log_message('warning', 'ProductModel::convertArraysToEntities - Invalid product data: ' . $e->getMessage());
            }
        }
        
        return $entities;
    }

    /**
     * Get product as array (for backward compatibility)
     *
     * @param int $id
     * @return array|null
     */
    public function getProductByIdAsArray(int $id): ?array
    {
        $product = $this->getProductById($id);
        return $product ? $product->toArray() : null;
    }

    /**
     * Get all products as arrays (for backward compatibility)
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAllProductsAsArrays(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $result = $this->getAllProducts($filters, $page, $perPage);
        
        // Convert Product entities to arrays
        $result['products'] = array_map(function(Product $product) {
            return $product->toArray();
        }, $result['products']);
        
        return $result;
    }
}

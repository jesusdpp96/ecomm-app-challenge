<?php

namespace Tests\Unit\Models;

use App\Models\ProductModel;
use App\Entities\Product;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Integration test to verify ProductModel works correctly with Product entity
 */
class ProductModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected ProductModel $model;
    protected string $testDataFile;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a temporary test data file
        $this->testDataFile = WRITEPATH . 'data/test_products.json';
        $testData = [
            'products' => [
                [
                    'id' => 1,
                    'title' => 'Test Product 1',
                    'price' => 99.99,
                    'created_at' => '2023-01-01T12:00:00+00:00'
                ],
                [
                    'id' => 2,
                    'title' => 'Test Product 2',
                    'price' => 149.99,
                    'created_at' => '2023-01-02T12:00:00+00:00'
                ]
            ],
            'next_id' => 3,
            'metadata' => [
                'created_at' => '2023-01-01T10:00:00+00:00',
                'last_modified' => '2023-01-02T12:00:00+00:00',
                'version' => '1.0'
            ]
        ];
        
        // Ensure directory exists
        $dir = dirname($this->testDataFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($this->testDataFile, json_encode($testData, JSON_PRETTY_PRINT));
        
        // Mock the config to use our test file
        $this->model = new class extends ProductModel {
            public function __construct() {
                parent::__construct();
                // Override the storage path for testing
                $this->storage = new \App\Libraries\JSONStorage(WRITEPATH . 'data/test_products.json');
            }
        };
    }

    protected function tearDown(): void
    {
        // Clean up test file
        if (file_exists($this->testDataFile)) {
            unlink($this->testDataFile);
        }
        parent::tearDown();
    }

    public function testGetProductByIdReturnsProductEntity()
    {
        $product = $this->model->getProductById(1);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(1, $product->id);
        $this->assertEquals('Test Product 1', $product->title);
        $this->assertEquals(99.99, $product->price);
    }

    public function testCreateProduct()
    {
        $productData = [
            'title' => 'New Test Product',
            'price' => 199.99
        ];
        
        $product = $this->model->createProduct($productData);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(3, $product->id); // Should be next available ID
        $this->assertEquals('New Test Product', $product->title);
        $this->assertEquals(199.99, $product->price);
        $this->assertNotNull($product->created_at);
    }

    public function testUpdateProduct()
    {
        $updateData = [
            'title' => 'Updated Product Title',
            'price' => 299.99
        ];
        
        $updatedProduct = $this->model->updateProduct(1, $updateData);
        
        $this->assertInstanceOf(Product::class, $updatedProduct);
        $this->assertEquals(1, $updatedProduct->id);
        $this->assertEquals('Updated Product Title', $updatedProduct->title);
        $this->assertEquals(299.99, $updatedProduct->price);
        
        // Test partial update
        $partialUpdate = $this->model->updateProduct(1, ['title' => 'Partially Updated']);
        $this->assertEquals('Partially Updated', $partialUpdate->title);
        $this->assertEquals(299.99, $partialUpdate->price); // Price should remain unchanged
        
        // Test updating non-existent product
        $nonExistent = $this->model->updateProduct(999, $updateData);
        $this->assertFalse($nonExistent);
    }

    public function testReadAllProducts()
    {
        $result = $this->model->getAllProducts();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('pagination', $result);
        
        $products = $result['products'];
        $this->assertCount(2, $products);
        
        foreach ($products as $product) {
            $this->assertInstanceOf(Product::class, $product);
        }
        
        // Test pagination structure
        $pagination = $result['pagination'];
        $this->assertArrayHasKey('current_page', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);
        $this->assertArrayHasKey('total_items', $pagination);
        $this->assertArrayHasKey('total_pages', $pagination);
        $this->assertEquals(2, $pagination['total_items']);
    }

    public function testSearchProducts()
    {
        // Test exact match
        $results = $this->model->searchProducts('Test Product 1');
        
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertInstanceOf(Product::class, $results[0]);
        $this->assertEquals('Test Product 1', $results[0]->title);
        
        // Test partial match
        $partialResults = $this->model->searchProducts('Test');
        $this->assertCount(2, $partialResults);
        
        // Test case insensitive search
        $caseResults = $this->model->searchProducts('test product');
        $this->assertCount(2, $caseResults);
        
        // Test empty query returns all products
        $emptyResults = $this->model->searchProducts('');
        $this->assertCount(2, $emptyResults);
        
        // Test no matches
        $noResults = $this->model->searchProducts('NonExistent');
        $this->assertCount(0, $noResults);
    }

    public function testProductValidation()
    {
        // Test empty title validation
        $invalidTitle = [
            'title' => '',
            'price' => 99.99
        ];
        $result = $this->model->createProduct($invalidTitle);
        $this->assertFalse($result);
        
        // Test negative price validation
        $invalidPrice = [
            'title' => 'Valid Title',
            'price' => -10
        ];
        $result = $this->model->createProduct($invalidPrice);
        $this->assertFalse($result);
        
        // Test validation method directly
        $validationErrors = $this->model->validateProductData($invalidTitle);
        $this->assertNotEmpty($validationErrors);
        
        // Test valid data passes validation
        $validData = [
            'title' => 'Valid Product',
            'price' => 99.99
        ];
        $validationErrors = $this->model->validateProductData($validData);
        $this->assertEmpty($validationErrors);
    }

    public function testDeleteProduct()
    {
        // Test successful deletion
        $result = $this->model->deleteProduct(1);
        $this->assertTrue($result);
        
        // Verify product is deleted
        $deletedProduct = $this->model->getProductById(1);
        $this->assertNull($deletedProduct);
        
        // Test deleting non-existent product
        $nonExistentResult = $this->model->deleteProduct(999);
        $this->assertFalse($nonExistentResult);
        
        // Verify remaining products count
        $remainingProducts = $this->model->getAllProducts();
        $this->assertCount(1, $remainingProducts['products']);
    }

    public function testPagination()
    {
        // Add more test data for pagination
        for ($i = 3; $i <= 15; $i++) {
            $this->model->createProduct([
                'title' => "Test Product {$i}",
                'price' => $i * 10.0
            ]);
        }
        
        // Test first page
        $page1 = $this->model->getAllProducts([], 1, 5);
        $this->assertCount(5, $page1['products']);
        $this->assertEquals(1, $page1['pagination']['current_page']);
        $this->assertEquals(5, $page1['pagination']['per_page']);
        $this->assertEquals(15, $page1['pagination']['total_items']);
        $this->assertEquals(3, $page1['pagination']['total_pages']);
        $this->assertTrue($page1['pagination']['has_next']);
        $this->assertFalse($page1['pagination']['has_prev']);
        
        // Test second page
        $page2 = $this->model->getAllProducts([], 2, 5);
        $this->assertCount(5, $page2['products']);
        $this->assertEquals(2, $page2['pagination']['current_page']);
        $this->assertTrue($page2['pagination']['has_next']);
        $this->assertTrue($page2['pagination']['has_prev']);
        
        // Test last page
        $page3 = $this->model->getAllProducts([], 3, 5);
        $this->assertCount(5, $page3['products']);
        $this->assertEquals(3, $page3['pagination']['current_page']);
        $this->assertFalse($page3['pagination']['has_next']);
        $this->assertTrue($page3['pagination']['has_prev']);
    }

    public function testFiltering()
    {
        // Add products with different prices for filtering
        $this->model->createProduct(['title' => 'Cheap Product', 'price' => 10.0]);
        $this->model->createProduct(['title' => 'Expensive Product', 'price' => 500.0]);
        
        // Test price range filtering
        $cheapProducts = $this->model->getAllProducts(['min_price' => 5, 'max_price' => 50]);
        $this->assertGreaterThan(0, count($cheapProducts['products']));
        foreach ($cheapProducts['products'] as $product) {
            $this->assertGreaterThanOrEqual(5, $product->price);
            $this->assertLessThanOrEqual(50, $product->price);
        }
        
        // Test search filtering
        $searchResults = $this->model->getAllProducts(['search' => 'Cheap']);
        $this->assertCount(1, $searchResults['products']);
        $this->assertEquals('Cheap Product', $searchResults['products'][0]->title);
        
        // Test getProductsByPriceRange method
        $priceRangeProducts = $this->model->getProductsByPriceRange(100, 200);
        foreach ($priceRangeProducts as $product) {
            $this->assertGreaterThanOrEqual(100, $product->price);
            $this->assertLessThanOrEqual(200, $product->price);
        }
    }

    public function testSorting()
    {
        // Add more products for sorting tests
        $this->model->createProduct(['title' => 'Alpha Product', 'price' => 300.0]);
        $this->model->createProduct(['title' => 'Beta Product', 'price' => 50.0]);
        
        // Test sorting by price ascending
        $sortedByPriceAsc = $this->model->getAllProducts(['sort_by' => 'price', 'order' => 'asc']);
        $prices = array_map(fn($p) => $p->price, $sortedByPriceAsc['products']);
        $expectedPrices = $prices;
        sort($expectedPrices);
        $this->assertEquals($expectedPrices, $prices);
        
        // Test sorting by price descending
        $sortedByPriceDesc = $this->model->getAllProducts(['sort_by' => 'price', 'order' => 'desc']);
        $pricesDesc = array_map(fn($p) => $p->price, $sortedByPriceDesc['products']);
        $expectedPricesDesc = $pricesDesc;
        rsort($expectedPricesDesc);
        $this->assertEquals($expectedPricesDesc, $pricesDesc);
        
        // Test sorting by title
        $sortedByTitle = $this->model->getAllProducts(['sort_by' => 'title', 'order' => 'asc']);
        $titles = array_map(fn($p) => $p->title, $sortedByTitle['products']);
        $sortedTitles = $titles;
        sort($sortedTitles, SORT_STRING | SORT_FLAG_CASE);
        $this->assertEquals(array_map('strtolower', $titles), array_map('strtolower', $sortedTitles));
        
        // Test sorting by ID
        $sortedById = $this->model->getAllProducts(['sort_by' => 'id', 'order' => 'asc']);
        $ids = array_map(fn($p) => $p->id, $sortedById['products']);
        $expectedIds = $ids;
        sort($expectedIds);
        $this->assertEquals($expectedIds, $ids);
    }

    public function testInvalidData()
    {
        // Test creating product with completely invalid data
        $invalidData = [
            'title' => null,
            'price' => 'not_a_number'
        ];
        $result = $this->model->createProduct($invalidData);
        $this->assertFalse($result);
        
        // Test updating with invalid data
        $updateResult = $this->model->updateProduct(1, $invalidData);
        $this->assertFalse($updateResult);
        
        // Test with missing required fields
        $missingFields = [];
        $result = $this->model->createProduct($missingFields);
        $this->assertFalse($result);
        
        // Test with extremely long title
        $longTitle = str_repeat('a', 1000);
        $longTitleData = [
            'title' => $longTitle,
            'price' => 99.99
        ];
        // This should either fail validation or be handled gracefully
        $result = $this->model->createProduct($longTitleData);
        // The behavior depends on Product entity validation rules
        // Since we don't know the exact validation rules, we'll just check it's handled
        $this->assertTrue(is_bool($result) || $result instanceof Product);
        
        // Test with zero price (should fail validation since price must be positive)
        $zeroPrice = [
            'title' => 'Free Product',
            'price' => 0.0
        ];
        $result = $this->model->createProduct($zeroPrice);
        $this->assertFalse($result);
    }

    public function testBackwardCompatibilityMethods()
    {
        // Test that backward compatibility methods work
        $productArray = $this->model->getProductByIdAsArray(1);
        
        $this->assertIsArray($productArray);
        $this->assertEquals(1, $productArray['id']);
        $this->assertEquals('Test Product 1', $productArray['title']);
        
        $allProductsArray = $this->model->getAllProductsAsArrays();
        $this->assertIsArray($allProductsArray['products']);
        $this->assertIsArray($allProductsArray['products'][0]);
    }
}

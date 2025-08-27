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

    public function testCreateProductReturnsProductEntity()
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
    }

    public function testUpdateProductReturnsUpdatedProductEntity()
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
    }

    public function testGetAllProductsReturnsProductEntities()
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
    }

    public function testSearchProductsReturnsProductEntities()
    {
        $results = $this->model->searchProducts('Test Product 1');
        
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertInstanceOf(Product::class, $results[0]);
        $this->assertEquals('Test Product 1', $results[0]->title);
    }

    public function testValidationIsHandledByProductEntity()
    {
        // Test that validation errors are properly handled
        $invalidData = [
            'title' => '', // Empty title should fail
            'price' => -10 // Negative price should fail
        ];
        
        $result = $this->model->createProduct($invalidData);
        
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

<?php

namespace Tests\Unit\Libraries;

use PHPUnit\Framework\TestCase;
use App\Libraries\JSONStorage;
use App\Exceptions\StorageException;

/**
 * Comprehensive tests for JSONStorage library
 */
class JSONStorageTest extends TestCase
{
    private string $testFilePath;
    private JSONStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testFilePath = WRITEPATH . 'tests/test_products.json';
        
        // Clean up any existing test file
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
        
        // Clean up lock file
        $lockFile = $this->testFilePath . '.lock';
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
        
        $this->storage = new JSONStorage($this->testFilePath);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
        
        $lockFile = $this->testFilePath . '.lock';
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
        
        
        parent::tearDown();
    }

    public function testReadFromEmptyFile(): void
    {
        // File should be automatically initialized
        $this->assertTrue($this->storage->exists());
        
        $data = $this->storage->read();
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('products', $data);
        $this->assertArrayHasKey('next_id', $data);
        $this->assertArrayHasKey('metadata', $data);
        $this->assertEquals([], $data['products']);
        $this->assertEquals(1, $data['next_id']);
    }

    public function testWriteToFile(): void
    {
        $testData = [
            'products' => [
                [
                    'id' => 1,
                    'title' => 'Test Product',
                    'price' => 99.99,
                    'created_at' => '2024-01-15T10:30:00+00:00'
                ]
            ],
            'next_id' => 2,
            'metadata' => [
                'created_at' => '2024-01-15T10:00:00+00:00',
                'last_modified' => '2024-01-15T10:30:00+00:00',
                'version' => '1.0'
            ]
        ];

        $result = $this->storage->write($testData);
        $this->assertTrue($result);

        $readData = $this->storage->read();
        $this->assertEquals($testData['products'], $readData['products']);
        $this->assertEquals($testData['next_id'], $readData['next_id']);
    }

    public function testFileLocking(): void
    {
        $this->assertTrue($this->storage->lock());
        $this->assertTrue($this->storage->unlock());
    }


    public function testConcurrentAccess(): void
    {
        // Simulate concurrent access by creating multiple storage instances
        $storage1 = new JSONStorage($this->testFilePath);
        $storage2 = new JSONStorage($this->testFilePath);

        $testData1 = [
            'products' => [
                [
                    'id' => 1,
                    'title' => 'Product 1',
                    'price' => 99.99,
                    'created_at' => '2024-01-15T10:30:00+00:00'
                ]
            ],
            'next_id' => 2,
            'metadata' => [
                'created_at' => '2024-01-15T10:00:00+00:00',
                'last_modified' => '2024-01-15T10:30:00+00:00',
                'version' => '1.0'
            ]
        ];

        $result1 = $storage1->write($testData1);
        $this->assertTrue($result1);

        $readData = $storage2->read();
        $this->assertEquals($testData1['products'], $readData['products']);
    }

    public function testInvalidJSONHandling(): void
    {
        // Write invalid JSON directly to file
        file_put_contents($this->testFilePath, '{"invalid": json}');

        $this->expectException(StorageException::class);
        $this->expectExceptionCode(StorageException::INVALID_JSON);
        
        $this->storage->read();
    }

    public function testPermissionErrors(): void
    {
        // Create a file with no read permissions
        file_put_contents($this->testFilePath, '{}');
        chmod($this->testFilePath, 0000);

        try {
            $result = $this->storage->read();
            
            // If we reach here, the file was readable despite 0000 permissions (elevated context)
            // In this case, the empty JSON '{}' should fail structure validation
            $this->fail('Expected StorageException was not thrown. File was readable with result: ' . json_encode($result));
            
        } catch (StorageException $e) {
            // In elevated permission contexts (root, containers), the file may still be readable
            // but fail validation, resulting in INVALID_JSON instead of PERMISSION_DENIED
            $acceptableErrorCodes = [
                StorageException::PERMISSION_DENIED, // Expected in normal user context
                StorageException::INVALID_JSON       // May occur in elevated permission contexts
            ];
            
            $this->assertContains(
                $e->getCode(), 
                $acceptableErrorCodes,
                "Expected error code " . StorageException::PERMISSION_DENIED . " (PERMISSION_DENIED) " .
                "or " . StorageException::INVALID_JSON . " (INVALID_JSON in elevated contexts), " .
                "but got: " . $e->getCode() . " - " . $e->getMessage()
            );
        } finally {
            // Restore permissions for cleanup
            chmod($this->testFilePath, 0644);
        }
    }

    public function testFileCorruption(): void
    {
        // Write corrupted data structure
        $corruptedData = [
            'products' => 'not_an_array',
            'next_id' => 'not_a_number',
            'metadata' => []
        ];

        $this->expectException(StorageException::class);
        $this->expectExceptionCode(StorageException::INVALID_JSON);
        
        $this->storage->write($corruptedData);
    }

    public function testValidateStructure(): void
    {
        $validData = [
            'products' => [
                [
                    'id' => 1,
                    'title' => 'Test Product',
                    'price' => 99.99,
                    'created_at' => '2024-01-15T10:30:00+00:00'
                ]
            ],
            'next_id' => 2,
            'metadata' => [
                'created_at' => '2024-01-15T10:00:00+00:00',
                'last_modified' => '2024-01-15T10:30:00+00:00',
                'version' => '1.0'
            ]
        ];

        $this->assertTrue($this->storage->validateStructure($validData));

        // Test invalid structure
        $invalidData = [
            'products' => [],
            'next_id' => 1
            // Missing metadata
        ];

        $this->assertFalse($this->storage->validateStructure($invalidData));
    }

    public function testGetFileSize(): void
    {
        $initialSize = $this->storage->getFileSize();
        $this->assertGreaterThan(0, $initialSize);

        $testData = [
            'products' => [
                [
                    'id' => 1,
                    'title' => 'Test Product with a very long title to increase file size',
                    'price' => 99.99,
                    'created_at' => '2024-01-15T10:30:00+00:00'
                ]
            ],
            'next_id' => 2,
            'metadata' => [
                'created_at' => '2024-01-15T10:00:00+00:00',
                'last_modified' => '2024-01-15T10:30:00+00:00',
                'version' => '1.0'
            ]
        ];

        $this->storage->write($testData);
        $newSize = $this->storage->getFileSize();
        $this->assertGreaterThan($initialSize, $newSize);
    }

    public function testExists(): void
    {
        $this->assertTrue($this->storage->exists());
        
        // Test with non-existent file
        $nonExistentStorage = new JSONStorage('/tmp/non_existent_file.json');
        $this->assertTrue($nonExistentStorage->exists()); // Should be created automatically
    }
}

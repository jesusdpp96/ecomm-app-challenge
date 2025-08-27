<?php

namespace App\Libraries;

use App\Exceptions\StorageException;
use Config\Storage as StorageConfig;

/**
 * JSON file storage library with atomic operations and concurrency control
 */
class JSONStorage
{
    private string $filePath;
    private $lockHandle;
    private $config;

    /**
     * Initialize JSONStorage with file path
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        
        // Use default config if CodeIgniter config is not available
        if (function_exists('config')) {
            $this->config = config('Storage');
        } else {
            // Create a simple config object for standalone usage
            $this->config = new class {
                public bool $enableLocking = true;
                public int $lockTimeout = 30;
            };
        }
        
        $this->initializeFile();
    }

    /**
     * Read data from JSON file
     *
     * @return array
     * @throws StorageException
     */
    public function read(): array
    {
        if (!$this->exists()) {
            throw new StorageException("File not found: {$this->filePath}", StorageException::FILE_NOT_FOUND);
        }

        if (!is_readable($this->filePath)) {
            throw new StorageException("Permission denied reading file: {$this->filePath}", StorageException::PERMISSION_DENIED);
        }

        $content = file_get_contents($this->filePath);
        if ($content === false) {
            throw new StorageException("Failed to read file: {$this->filePath}", StorageException::FILE_NOT_FOUND);
        }

        if (empty($content)) {
            return $this->getDefaultStructure();
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new StorageException("Invalid JSON in file: " . json_last_error_msg(), StorageException::INVALID_JSON);
        }

        if (!$this->validateStructure($data)) {
            throw new StorageException("Invalid JSON structure", StorageException::INVALID_JSON);
        }

        return $data;
    }

    /**
     * Write data to JSON file with atomic operation
     *
     * @param array $data
     * @return bool
     * @throws StorageException
     */
    public function write(array $data): bool
    {
        if (!$this->validateStructure($data)) {
            throw new StorageException("Invalid data structure", StorageException::INVALID_JSON);
        }

        // Acquire lock if enabled
        if ($this->config->enableLocking) {
            if (!$this->lock()) {
                throw new StorageException("Failed to acquire file lock", StorageException::LOCK_FAILED);
            }
        }

        try {
            // Update metadata
            $data['metadata']['last_modified'] = date('c');
            
            $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($jsonData === false) {
                throw new StorageException("Failed to encode JSON: " . json_last_error_msg(), StorageException::INVALID_JSON);
            }

            // Write to temporary file first for atomic operation
            $tempFile = $this->filePath . '.tmp';
            $result = file_put_contents($tempFile, $jsonData, LOCK_EX);
            
            if ($result === false) {
                throw new StorageException("Failed to write to temporary file", StorageException::PERMISSION_DENIED);
            }

            // Atomic rename
            if (!rename($tempFile, $this->filePath)) {
                unlink($tempFile);
                throw new StorageException("Failed to rename temporary file", StorageException::PERMISSION_DENIED);
            }

            return true;
        } finally {
            // Always release lock
            if ($this->config->enableLocking) {
                $this->unlock();
            }
        }
    }

    /**
     * Acquire file lock
     *
     * @return bool
     */
    public function lock(): bool
    {
        if (!$this->config->enableLocking) {
            return true;
        }

        $this->lockHandle = fopen($this->filePath . '.lock', 'w');
        if (!$this->lockHandle) {
            return false;
        }

        $startTime = time();
        while (!flock($this->lockHandle, LOCK_EX | LOCK_NB)) {
            if (time() - $startTime >= $this->config->lockTimeout) {
                fclose($this->lockHandle);
                $this->lockHandle = null;
                return false;
            }
            usleep(100000); // 100ms
        }

        return true;
    }

    /**
     * Release file lock
     *
     * @return bool
     */
    public function unlock(): bool
    {
        if (!$this->lockHandle) {
            return true;
        }

        $result = flock($this->lockHandle, LOCK_UN);
        fclose($this->lockHandle);
        $this->lockHandle = null;

        // Clean up lock file
        $lockFile = $this->filePath . '.lock';
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        return $result;
    }


    /**
     * Validate JSON data structure
     *
     * @param array $data
     * @return bool
     */
    public function validateStructure(array $data): bool
    {
        // Check required top-level keys
        $requiredKeys = ['products', 'next_id', 'metadata'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }

        // Validate products array
        if (!is_array($data['products'])) {
            return false;
        }

        // Validate next_id
        if (!is_int($data['next_id']) || $data['next_id'] < 1) {
            return false;
        }

        // Validate metadata
        if (!is_array($data['metadata'])) {
            return false;
        }

        $requiredMetadata = ['created_at', 'last_modified', 'version'];
        foreach ($requiredMetadata as $key) {
            if (!array_key_exists($key, $data['metadata'])) {
                return false;
            }
        }

        // Validate each product
        foreach ($data['products'] as $product) {
            if (!$this->validateProduct($product)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get file size in bytes
     *
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->exists() ? filesize($this->filePath) : 0;
    }

    /**
     * Check if file exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->filePath);
    }

    /**
     * Initialize file if it doesn't exist
     *
     * @return void
     * @throws StorageException
     */
    private function initializeFile(): void
    {
        if ($this->exists()) {
            return;
        }

        $directory = dirname($this->filePath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new StorageException("Failed to create directory: {$directory}", StorageException::PERMISSION_DENIED);
            }
        }

        $defaultData = $this->getDefaultStructure();
        $jsonData = json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($this->filePath, $jsonData) === false) {
            throw new StorageException("Failed to initialize file: {$this->filePath}", StorageException::PERMISSION_DENIED);
        }
    }


    /**
     * Get default JSON structure
     *
     * @return array
     */
    private function getDefaultStructure(): array
    {
        return [
            'products' => [],
            'next_id' => 1,
            'metadata' => [
                'created_at' => date('c'),
                'last_modified' => date('c'),
                'version' => '1.0'
            ]
        ];
    }

    /**
     * Validate individual product structure
     *
     * @param mixed $product
     * @return bool
     */
    private function validateProduct($product): bool
    {
        if (!is_array($product)) {
            return false;
        }

        $requiredFields = ['id', 'title', 'price', 'created_at'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $product)) {
                return false;
            }
        }

        // Validate field types
        if (!is_int($product['id']) || $product['id'] < 1) {
            return false;
        }

        if (!is_string($product['title']) || empty(trim($product['title']))) {
            return false;
        }

        if (!is_numeric($product['price']) || $product['price'] < 0) {
            return false;
        }

        if (!is_string($product['created_at'])) {
            return false;
        }

        return true;
    }

}

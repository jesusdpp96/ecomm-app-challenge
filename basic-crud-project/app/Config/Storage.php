<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Storage configuration class
 */
class Storage extends BaseConfig
{
    /**
     * Path where data files are stored
     */
    public string $dataPath = WRITEPATH . 'data/';

    /**
     * Products JSON file name
     */
    public string $productsFile = 'products.json';

    /**
     * Enable automatic backup before write operations
     */
    public bool $enableBackup = true;

    /**
     * Maximum number of backup files to keep
     */
    public int $maxBackups = 10;

    /**
     * Enable file locking for concurrent access protection
     */
    public bool $enableLocking = true;

    /**
     * Lock timeout in seconds
     */
    public int $lockTimeout = 30;

    /**
     * Get the full path to the products file
     */
    public function getProductsFilePath(): string
    {
        return $this->dataPath . $this->productsFile;
    }

    /**
     * Get the backup directory path
     */
    public function getBackupPath(): string
    {
        return $this->dataPath . 'backups/';
    }
}

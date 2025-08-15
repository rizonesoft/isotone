<?php
/**
 * Isotone Automation Cache Manager
 * 
 * Manages file checksums and incremental scanning for performance
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Storage;

use RedBeanPHP\R;

class CacheManager
{
    private string $cacheDir;
    private array $memoryCache = [];
    private bool $initialized = false;
    
    public function __construct()
    {
        $this->cacheDir = dirname(dirname(__DIR__)) . '/cache';
    }
    
    /**
     * Initialize the cache manager
     */
    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }
        
        // Ensure cache directory exists
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        // Load memory cache
        $this->loadMemoryCache();
        
        $this->initialized = true;
    }
    
    /**
     * Get files modified since last run for a specific task
     */
    public function getModifiedFiles(string $task): array
    {
        $lastRun = $this->getLastRunTime($task);
        $modifiedFiles = [];
        
        // Get directories to scan based on task
        $directories = $this->getTaskDirectories($task);
        
        foreach ($directories as $dir) {
            $files = $this->scanDirectory($dir, $lastRun);
            $modifiedFiles = array_merge($modifiedFiles, $files);
        }
        
        return $modifiedFiles;
    }
    
    /**
     * Update cache after task completion
     */
    public function updateCache(string $task): void
    {
        // Store last run time
        $this->setLastRunTime($task, time());
        
        // Update file checksums for modified files
        $directories = $this->getTaskDirectories($task);
        
        foreach ($directories as $dir) {
            $this->updateDirectoryChecksums($dir);
        }
        
        // Save memory cache to disk
        $this->saveMemoryCache();
    }
    
    /**
     * Check if a file has been modified
     */
    public function isFileModified(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $currentChecksum = $this->calculateChecksum($filePath);
        $cachedChecksum = $this->getChecksum($filePath);
        
        return $currentChecksum !== $cachedChecksum;
    }
    
    /**
     * Get checksum for a file
     */
    public function getChecksum(string $filePath): ?string
    {
        // Check memory cache first
        if (isset($this->memoryCache['checksums'][$filePath])) {
            return $this->memoryCache['checksums'][$filePath];
        }
        
        // Check database if connected
        if (R::testConnection()) {
            $cache = R::findOne('automationcache', 'file_path = ?', [$filePath]);
            
            if ($cache) {
                // Store in memory cache
                $this->memoryCache['checksums'][$filePath] = $cache->checksum;
                return $cache->checksum;
            }
        }
        
        return null;
    }
    
    /**
     * Store checksum for a file
     */
    public function setChecksum(string $filePath, string $checksum): void
    {
        // Update memory cache
        $this->memoryCache['checksums'][$filePath] = $checksum;
        
        // Update database if connected
        if (R::testConnection()) {
            $cache = R::findOne('automationcache', 'file_path = ?', [$filePath]);
            
            if (!$cache) {
                $cache = R::dispense('automationcache');
                $cache->file_path = $filePath;
            }
            
            $cache->checksum = $checksum;
            $cache->updated_at = date('Y-m-d H:i:s');
            
            R::store($cache);
        }
    }
    
    /**
     * Clear cache for specific files or all
     */
    public function clearCache(array $files = []): void
    {
        if (empty($files)) {
            // Clear all cache
            R::wipe('automationcache');
            $this->memoryCache = [];
            
            // Clear cache directory
            $this->clearCacheDirectory();
        } else {
            // Clear specific files
            foreach ($files as $file) {
                unset($this->memoryCache['checksums'][$file]);
                R::exec('DELETE FROM automationcache WHERE file_path = ?', [$file]);
            }
        }
        
        $this->saveMemoryCache();
    }
    
    /**
     * Get cache statistics
     */
    public function getStatistics(): array
    {
        $stats = [
            'total_files_cached' => 0,
            'memory_cache_size' => count($this->memoryCache['checksums'] ?? []),
            'disk_cache_size' => $this->getDirectorySize($this->cacheDir),
            'oldest_cache' => null,
            'newest_cache' => null
        ];
        
        // Get database stats if connected
        if (R::testConnection()) {
            $stats['total_files_cached'] = R::count('automationcache');
            $stats['oldest_cache'] = R::getCell('SELECT MIN(updated_at) FROM automationcache');
            $stats['newest_cache'] = R::getCell('SELECT MAX(updated_at) FROM automationcache');
        }
        
        return $stats;
    }
    
    /**
     * Get last run time for a task
     */
    private function getLastRunTime(string $task): int
    {
        $key = "last_run_$task";
        
        if (isset($this->memoryCache['runs'][$key])) {
            return $this->memoryCache['runs'][$key];
        }
        
        // Check database if connected
        if (R::testConnection()) {
            $state = R::findOne('automationstate', 'state_key = ?', [$key]);
            
            if ($state) {
                $time = (int)$state->state_value;
                $this->memoryCache['runs'][$key] = $time;
                return $time;
            }
        }
        
        return 0;
    }
    
    /**
     * Set last run time for a task
     */
    private function setLastRunTime(string $task, int $time): void
    {
        $key = "last_run_$task";
        
        $this->memoryCache['runs'][$key] = $time;
        
        // Store in database if connected
        if (R::testConnection()) {
            $state = R::findOne('automationstate', 'state_key = ?', [$key]);
            
            if (!$state) {
                $state = R::dispense('automationstate');
                $state->state_key = $key;
            }
            
            $state->state_value = (string)$time;
            $state->updated_at = date('Y-m-d H:i:s');
            
            R::store($state);
        }
    }
    
    /**
     * Get directories to scan for a task
     */
    private function getTaskDirectories(string $task): array
    {
        $rootPath = dirname(dirname(dirname(__DIR__)));
        
        $taskDirs = [
            'check:docs' => ['docs', 'README.md', 'CLAUDE.md', 'composer.json'],
            'update:docs' => ['app', 'docs', 'config'],
            'generate:hooks' => ['app', 'iso-admin', 'iso-content/themes', 'iso-content/plugins'],
            'sync:ide' => ['docs', 'CLAUDE.md'],
            'sync:user-docs' => ['docs', 'user-docs']
        ];
        
        $dirs = $taskDirs[$task] ?? ['app', 'docs'];
        
        return array_map(function($dir) use ($rootPath) {
            return $rootPath . '/' . $dir;
        }, $dirs);
    }
    
    /**
     * Scan directory for modified files
     */
    private function scanDirectory(string $dir, int $since): array
    {
        if (!file_exists($dir)) {
            return [];
        }
        
        $modifiedFiles = [];
        
        if (is_file($dir)) {
            if (filemtime($dir) > $since) {
                $modifiedFiles[] = $dir;
            }
        } else {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getMTime() > $since) {
                    $modifiedFiles[] = $file->getPathname();
                }
            }
        }
        
        return $modifiedFiles;
    }
    
    /**
     * Update checksums for all files in a directory
     */
    private function updateDirectoryChecksums(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }
        
        if (is_file($dir)) {
            $checksum = $this->calculateChecksum($dir);
            $this->setChecksum($dir, $checksum);
        } else {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filePath = $file->getPathname();
                    $checksum = $this->calculateChecksum($filePath);
                    $this->setChecksum($filePath, $checksum);
                }
            }
        }
    }
    
    /**
     * Calculate checksum for a file
     */
    private function calculateChecksum(string $filePath): string
    {
        // Use file size and modification time for quick checksum
        // This is faster than MD5/SHA1 for large codebases
        $stat = stat($filePath);
        return sprintf('%d_%d', $stat['size'], $stat['mtime']);
    }
    
    /**
     * Load memory cache from disk
     */
    private function loadMemoryCache(): void
    {
        $cacheFile = $this->cacheDir . '/memory.cache';
        
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            $this->memoryCache = unserialize($data) ?: [];
        }
    }
    
    /**
     * Save memory cache to disk
     */
    private function saveMemoryCache(): void
    {
        $cacheFile = $this->cacheDir . '/memory.cache';
        file_put_contents($cacheFile, serialize($this->memoryCache));
    }
    
    /**
     * Clear cache directory
     */
    private function clearCacheDirectory(): void
    {
        $files = glob($this->cacheDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * Get directory size
     */
    private function getDirectorySize(string $dir): int
    {
        $size = 0;
        
        if (is_dir($dir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return $size;
    }
}
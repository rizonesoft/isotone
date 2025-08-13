<?php
/**
 * Isotone CMS - Database Migration System
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */

declare(strict_types=1);

namespace Isotone\Core;

use RedBeanPHP\R;
use Exception;

class Migration
{
    /**
     * Migration history table
     */
    private const MIGRATION_TABLE = 'isotone_migrations';
    
    /**
     * Available migrations
     */
    private static array $migrations = [
        '1.0.0' => [
            'description' => 'Initial database schema',
            'up' => 'createInitialSchema',
            'down' => 'dropInitialSchema'
        ]
    ];
    
    /**
     * Initialize migration system
     */
    public static function initialize(): void
    {
        // Create migrations table if it doesn't exist
        if (!R::inspect(self::MIGRATION_TABLE)) {
            $migration = R::dispense(self::MIGRATION_TABLE);
            $migration->version = '0.0.0';
            $migration->batch = 0;
            $migration->executed_at = date('Y-m-d H:i:s');
            R::store($migration);
        }
    }
    
    /**
     * Run pending migrations
     */
    public static function migrate(): array
    {
        self::initialize();
        
        $results = [];
        $currentVersion = self::getCurrentVersion();
        $batch = self::getNextBatch();
        
        foreach (self::$migrations as $version => $migration) {
            if (version_compare($version, $currentVersion, '>')) {
                try {
                    // Execute migration
                    $method = $migration['up'];
                    if (method_exists(self::class, $method)) {
                        self::$method();
                    }
                    
                    // Record migration
                    $record = R::dispense(self::MIGRATION_TABLE);
                    $record->version = $version;
                    $record->description = $migration['description'];
                    $record->batch = $batch;
                    $record->executed_at = date('Y-m-d H:i:s');
                    R::store($record);
                    
                    $results[] = [
                        'version' => $version,
                        'status' => 'success',
                        'message' => $migration['description']
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'version' => $version,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                    break; // Stop on error
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Rollback last batch of migrations
     */
    public static function rollback(): array
    {
        $results = [];
        $lastBatch = self::getLastBatch();
        
        if ($lastBatch === 0) {
            return [['status' => 'info', 'message' => 'Nothing to rollback']];
        }
        
        $migrations = R::find(self::MIGRATION_TABLE, 'batch = ? ORDER BY version DESC', [$lastBatch]);
        
        foreach ($migrations as $migration) {
            $version = $migration->version;
            if (isset(self::$migrations[$version])) {
                try {
                    $method = self::$migrations[$version]['down'];
                    if (method_exists(self::class, $method)) {
                        self::$method();
                    }
                    
                    R::trash($migration);
                    
                    $results[] = [
                        'version' => $version,
                        'status' => 'success',
                        'message' => 'Rolled back: ' . self::$migrations[$version]['description']
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'version' => $version,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                    break;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Get current database version
     */
    public static function getCurrentVersion(): string
    {
        $latest = R::findOne(self::MIGRATION_TABLE, 'ORDER BY version DESC LIMIT 1');
        return $latest ? $latest->version : '0.0.0';
    }
    
    /**
     * Get next batch number
     */
    private static function getNextBatch(): int
    {
        $latest = R::findOne(self::MIGRATION_TABLE, 'ORDER BY batch DESC LIMIT 1');
        return $latest ? $latest->batch + 1 : 1;
    }
    
    /**
     * Get last batch number
     */
    private static function getLastBatch(): int
    {
        $latest = R::findOne(self::MIGRATION_TABLE, 'ORDER BY batch DESC LIMIT 1');
        return $latest ? $latest->batch : 0;
    }
    
    /**
     * Check if database needs migration
     */
    public static function needsMigration(): bool
    {
        $currentVersion = self::getCurrentVersion();
        $latestVersion = Version::SCHEMA_VERSION;
        return version_compare($latestVersion, $currentVersion, '>');
    }
    
    /**
     * Get migration status
     */
    public static function getStatus(): array
    {
        self::initialize();
        
        $executed = [];
        $pending = [];
        
        $records = R::findAll(self::MIGRATION_TABLE);
        foreach ($records as $record) {
            $executed[$record->version] = [
                'batch' => $record->batch,
                'executed_at' => $record->executed_at
            ];
        }
        
        foreach (self::$migrations as $version => $migration) {
            if (!isset($executed[$version]) || $version === '0.0.0') {
                $pending[] = [
                    'version' => $version,
                    'description' => $migration['description']
                ];
            }
        }
        
        return [
            'current_version' => self::getCurrentVersion(),
            'target_version' => Version::SCHEMA_VERSION,
            'executed' => $executed,
            'pending' => $pending,
            'needs_migration' => self::needsMigration()
        ];
    }
    
    // Migration methods
    
    /**
     * Create initial schema
     */
    private static function createInitialSchema(): void
    {
        // Settings table
        $setting = R::dispense('isotone_settings');
        $setting->key = 'site_title';
        $setting->value = 'Isotone CMS';
        $setting->type = 'string';
        R::store($setting);
        
        // Users table structure (RedBean will create it)
        $user = R::dispense('isotone_users');
        $user->username = 'admin';
        $user->email = 'admin@example.com';
        $user->password = password_hash('admin', PASSWORD_DEFAULT);
        $user->role = 'administrator';
        $user->created_at = date('Y-m-d H:i:s');
        R::store($user);
        R::trash($user); // Remove sample user
        
        // Content table structure
        $content = R::dispense('isotone_content');
        $content->title = 'Sample';
        $content->slug = 'sample';
        $content->content = '';
        $content->type = 'page';
        $content->status = 'draft';
        $content->created_at = date('Y-m-d H:i:s');
        R::store($content);
        R::trash($content); // Remove sample
    }
    
    /**
     * Drop initial schema
     */
    private static function dropInitialSchema(): void
    {
        // RedBean doesn't have a direct way to drop tables
        // This would need to be implemented based on your needs
        R::nuke(); // Nuclear option - drops all tables
    }
}
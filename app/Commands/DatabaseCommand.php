<?php
/**
 * Isotone CMS - Database CLI Commands
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */

declare(strict_types=1);

namespace Isotone\Commands;

use Isotone\Services\DatabaseService;

class DatabaseCommand
{
    /**
     * Test database connection
     */
    public static function test(): void
    {
        echo "\n";
        echo "🔍 Testing Database Connection\n";
        echo str_repeat('=', 40) . "\n\n";
        
        $status = DatabaseService::getStatus();
        
        if ($status['connected']) {
            echo "✅ Database Connected Successfully!\n\n";
            echo "Connection Details:\n";
            echo "  Host:     " . $status['host'] . "\n";
            echo "  Database: " . $status['database'] . "\n";
            echo "  Username: " . $status['username'] . "\n";
            echo "  Tables:   " . $status['tables'] . "\n";
            
            if (!empty($status['table_list'])) {
                echo "  List:     " . implode(', ', $status['table_list']) . "\n";
            }
            
            if (isset($status['version'])) {
                echo "  Version:  " . $status['version'] . "\n";
            }
        } else {
            echo "❌ Database Connection Failed\n\n";
            echo "Error: " . ($status['error'] ?? 'Unknown error') . "\n\n";
            echo "Configuration:\n";
            echo "  Host:     " . $status['host'] . "\n";
            echo "  Database: " . $status['database'] . "\n";
            echo "  Username: " . $status['username'] . "\n\n";
            echo "Please check:\n";
            echo "  • Database server is running\n";
            echo "  • Database '{$status['database']}' exists\n";
            echo "  • Username/password are correct\n";
            echo "  • PHP PDO MySQL extension is installed\n";
        }
        
        echo "\n";
    }
    
    /**
     * Initialize database schema
     */
    public static function initialize(): void
    {
        echo "\n";
        echo "🔧 Initializing Database Schema\n";
        echo str_repeat('=', 40) . "\n\n";
        
        $result = DatabaseService::initializeSchema();
        
        if ($result['success']) {
            echo "✅ Database schema initialized successfully!\n\n";
            echo "Tables Created:\n";
            foreach ($result['tables_created'] as $table) {
                echo "  • $table\n";
            }
            echo "\nActions Performed:\n";
            foreach ($result['results'] as $action) {
                echo "  • $action\n";
            }
            echo "\n";
            echo "🔑 Default admin user created:\n";
            echo "  Username: admin\n";
            echo "  Password: admin123\n";
            echo "  (Please change this password after first login)\n";
        } else {
            echo "❌ Schema initialization failed\n\n";
            echo "Error: " . $result['error'] . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Show database status
     */
    public static function status(): void
    {
        echo "\n";
        echo "📊 Database Status\n";
        echo str_repeat('=', 40) . "\n\n";
        
        $status = DatabaseService::getStatus();
        
        $statusIcon = $status['connected'] ? '🟢' : '🔴';
        $statusText = $status['connected'] ? 'Connected' : 'Disconnected';
        
        echo "Status: $statusIcon $statusText\n\n";
        
        if ($status['connected']) {
            echo "Configuration:\n";
            echo "  Host:     " . $status['host'] . "\n";
            echo "  Database: " . $status['database'] . "\n";
            echo "  Username: " . $status['username'] . "\n";
            echo "  Tables:   " . $status['tables'] . " tables\n";
            
            if (isset($status['version'])) {
                echo "  Version:  " . $status['version'] . "\n";
            }
            
            if (!empty($status['table_list'])) {
                echo "\nTables:\n";
                foreach ($status['table_list'] as $table) {
                    echo "  • $table\n";
                }
            }
        } else {
            echo "Error: " . ($status['error'] ?? 'Connection failed') . "\n";
            echo "\nConfiguration:\n";
            echo "  Host:     " . $status['host'] . "\n";
            echo "  Database: " . $status['database'] . "\n";
            echo "  Username: " . $status['username'] . "\n";
        }
        
        echo "\n";
    }
}
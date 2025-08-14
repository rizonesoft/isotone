<?php
/**
 * Isotone - Database Service
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */

declare(strict_types=1);

namespace Isotone\Services;

use RedBeanPHP\R;
use Exception;

class DatabaseService
{
    /**
     * Connection status
     */
    private static bool $connected = false;
    
    /**
     * Initialize database connection
     */
    public static function initialize(): bool
    {
        try {
            if (self::$connected) {
                return true;
            }
            
            // Check if running in WSL
            $is_wsl = file_exists('/proc/version') && (
                stripos(file_get_contents('/proc/version'), 'microsoft') !== false ||
                stripos(file_get_contents('/proc/version'), 'WSL') !== false
            );
            
            // Get database configuration from config.php
            $configHost = defined('DB_HOST') ? DB_HOST : 'localhost';
            $port = defined('DB_PORT') ? DB_PORT : 3306;
            $database = defined('DB_NAME') ? DB_NAME : '';
            $username = defined('DB_USER') ? DB_USER : '';
            $password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
            
            // Determine the actual host based on environment
            if ($is_wsl && php_sapi_name() === 'cli') {
                // In WSL CLI, try to use Windows host IP
                if ($configHost === 'localhost' || $configHost === '127.0.0.1') {
                    // Try to get Windows host IP from resolv.conf
                    if (file_exists('/etc/resolv.conf')) {
                        $resolv = file_get_contents('/etc/resolv.conf');
                        if (preg_match('/nameserver\s+(\d+\.\d+\.\d+\.\d+)/', $resolv, $matches)) {
                            $host = $matches[1];
                        } else {
                            // Fallback to common WSL2 host IPs - try multiple
                            $fallbackHosts = ['172.19.240.1', '172.17.0.1', '172.26.0.1'];
                            foreach ($fallbackHosts as $testHost) {
                                $testDsn = "mysql:host={$testHost};port={$port};charset=utf8mb4";
                                try {
                                    $testPdo = new \PDO($testDsn, $username, $password);
                                    $host = $testHost;
                                    break;
                                } catch (\PDOException $e) {
                                    continue;
                                }
                            }
                            if (!isset($host)) {
                                $host = '172.19.240.1'; // Default fallback
                            }
                        }
                    } else {
                        $host = '172.19.240.1';
                    }
                } else {
                    $host = $configHost;
                }
            } else {
                // Web context or non-WSL: use localhost as 127.0.0.1 for TCP connection
                $host = ($configHost === 'localhost') ? '127.0.0.1' : $configHost;
            }
            
            if (empty($database)) {
                throw new Exception('Database name not configured');
            }
            
            // Build DSN
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            
            // Setup RedBeanPHP
            R::setup($dsn, $username, $password);
            
            // Test connection
            R::testConnection();
            
            // Additional test - try to execute a simple query
            R::exec('SELECT 1');
            
            // Configure RedBeanPHP
            R::freeze(false); // Allow schema changes during development
            R::ext('xdispense', function($type) {
                return R::getRedBean()->dispense($type);
            });
            
            self::$connected = true;
            return true;
            
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if database is connected
     */
    public static function isConnected(): bool
    {
        try {
            if (!self::$connected) {
                return false;
            }
            
            // Test with a simple query
            R::exec('SELECT 1');
            return true;
            
        } catch (Exception $e) {
            self::$connected = false;
            return false;
        }
    }
    
    /**
     * Get connection status with details
     */
    public static function getStatus(): array
    {
        try {
            $configHost = env('DB_HOST', 'localhost');
            $database = env('DB_DATABASE', '');
            $username = env('DB_USERNAME', '');
            
            if (!self::initialize()) {
                return [
                    'connected' => false,
                    'error' => 'Connection failed',
                    'host' => $configHost,
                    'database' => $database,
                    'username' => $username
                ];
            }
            
            // Get the actual host used for connection
            $is_wsl = file_exists('/proc/version') && (
                stripos(file_get_contents('/proc/version'), 'microsoft') !== false ||
                stripos(file_get_contents('/proc/version'), 'WSL') !== false
            );
            
            if ($is_wsl && php_sapi_name() === 'cli') {
                if ($configHost === 'localhost' || $configHost === '127.0.0.1') {
                    if (file_exists('/etc/resolv.conf')) {
                        $resolv = file_get_contents('/etc/resolv.conf');
                        if (preg_match('/nameserver\s+(\d+\.\d+\.\d+\.\d+)/', $resolv, $matches)) {
                            $host = $matches[1] . ' (WSL)';
                        } else {
                            $host = '172.19.240.1 (WSL)';
                        }
                    } else {
                        $host = '172.19.240.1 (WSL)';
                    }
                } else {
                    $host = $configHost;
                }
            } else {
                $host = ($configHost === 'localhost') ? '127.0.0.1' : $configHost;
            }
            
            // Get database info
            $tables = R::inspect();
            
            // Get MySQL version
            $version = 'Unknown';
            try {
                $result = R::getRow('SELECT VERSION() as version');
                $version = $result['version'] ?? 'Unknown';
            } catch (Exception $e) {
                // Ignore version error
            }
            
            return [
                'connected' => true,
                'host' => $host,
                'database' => $database,
                'username' => $username,
                'tables' => count($tables),
                'table_list' => array_keys($tables),
                'version' => $version
            ];
            
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', ''),
                'username' => env('DB_USERNAME', '')
            ];
        }
    }
    
    /**
     * Initialize basic schema
     */
    public static function initializeSchema(): array
    {
        try {
            if (!self::initialize()) {
                throw new Exception('Database not connected');
            }
            
            $results = [];
            
            // Create settings table (RedBean prefers simple names without underscores)
            $setting = R::dispense('isotonesetting');
            $setting->key = 'site_title';
            $setting->value = 'Isotone';
            $setting->type = 'string';
            $setting->created_at = date('Y-m-d H:i:s');
            R::store($setting);
            $results[] = 'Settings table created';
            
            // Create users table structure
            $user = R::dispense('isotoneuser');
            $user->username = 'admin';
            $user->email = 'admin@example.com';
            $user->password = password_hash('admin123', PASSWORD_DEFAULT);
            $user->role = 'administrator';
            $user->status = 'active';
            $user->created_at = date('Y-m-d H:i:s');
            $user->updated_at = date('Y-m-d H:i:s');
            R::store($user);
            $results[] = 'Users table created with admin user';
            
            // Create content table structure
            $content = R::dispense('isotonecontent');
            $content->title = 'Welcome to Isotone';
            $content->slug = 'welcome';
            $content->content = 'Your lightweight CMS is ready to use!';
            $content->type = 'page';
            $content->status = 'published';
            $content->author_id = $user->id;
            $content->created_at = date('Y-m-d H:i:s');
            $content->updated_at = date('Y-m-d H:i:s');
            R::store($content);
            $results[] = 'Content table created with welcome page';
            
            return [
                'success' => true,
                'results' => $results,
                'tables_created' => ['isotonesetting', 'isotoneuser', 'isotonecontent']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Close database connection
     */
    public static function close(): void
    {
        if (self::$connected) {
            R::close();
            self::$connected = false;
        }
    }
}
<?php
/**
 * Database Connection Test Script
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";
echo "<pre>";

// Test 1: Check if .env exists
$envPath = dirname(__DIR__) . '/.env';
echo "1. Checking .env file:\n";
if (file_exists($envPath)) {
    echo "   ✓ .env file exists\n";
    
    // Load .env
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
    
    echo "   DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
    echo "   DB_PORT: " . ($_ENV['DB_PORT'] ?? 'not set') . "\n";
    echo "   DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'not set') . "\n";
    echo "   DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'not set') . "\n";
    echo "   DB_PASSWORD: " . (empty($_ENV['DB_PASSWORD']) ? '(empty)' : '(set)') . "\n";
} else {
    echo "   ✗ .env file NOT found!\n";
}

echo "\n2. Testing PDO connection:\n";
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'isotone_db';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    // Try different host options
    $hosts = [
        $host,
        '127.0.0.1',
        'localhost'
    ];
    
    $connected = false;
    foreach ($hosts as $testHost) {
        echo "   Trying host: $testHost ... ";
        try {
            $dsn = "mysql:host=$testHost;port=$port;dbname=$database;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✓ SUCCESS!\n";
            $connected = true;
            
            // Get MySQL version
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            echo "   MySQL Version: $version\n";
            
            // List tables
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            echo "   Tables in database: " . count($tables) . "\n";
            if (count($tables) > 0) {
                foreach ($tables as $table) {
                    echo "     - $table\n";
                }
            }
            break;
        } catch (PDOException $e) {
            echo "✗ Failed: " . $e->getMessage() . "\n";
        }
    }
    
    if (!$connected) {
        echo "\n   ✗ Could not connect to database with any host option.\n";
        echo "\n   Possible solutions:\n";
        echo "   1. Make sure XAMPP MySQL is running\n";
        echo "   2. Create database 'isotone_db' in phpMyAdmin\n";
        echo "   3. Check username/password in .env file\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n3. PHP Configuration:\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✓ Loaded' : '✗ Not loaded') . "\n";

echo "</pre>";
?>

<style>
body {
    font-family: monospace;
    background: #0A0E27;
    color: #00D9FF;
    padding: 20px;
}
pre {
    background: rgba(0,0,0,0.3);
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #00D9FF;
}
</style>
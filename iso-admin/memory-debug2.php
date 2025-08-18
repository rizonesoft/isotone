<?php
/**
 * Memory Usage Debug Script v2
 * More detailed tracking
 */

// Track memory at the very start
$memory_points = [];
$memory_points['1_start'] = round(memory_get_usage() / 1024 / 1024, 2);

// Load config
require_once dirname(__DIR__) . '/config.php';
$memory_points['2_after_config'] = round(memory_get_usage() / 1024 / 1024, 2);

// Check if autoloader is already loaded
$autoloader_loaded = class_exists('Composer\Autoload\ClassLoader', false);
$memory_points['3_autoloader_check'] = round(memory_get_usage() / 1024 / 1024, 2) . ' (loaded: ' . ($autoloader_loaded ? 'yes' : 'no') . ')';

// Load class-user.php
require_once dirname(__DIR__) . '/iso-includes/class-user.php';
$memory_points['4_after_class_user'] = round(memory_get_usage() / 1024 / 1024, 2);

// Start session
session_start();
$memory_points['5_after_session'] = round(memory_get_usage() / 1024 / 1024, 2);

// Create user object
$user = new IsotoneUser();
$memory_points['6_after_user_object'] = round(memory_get_usage() / 1024 / 1024, 2);

// Load database.php
require_once dirname(__DIR__) . '/iso-includes/database.php';
$memory_points['7_after_database_php'] = round(memory_get_usage() / 1024 / 1024, 2);

// Connect to database
isotone_db_connect();
$memory_points['8_after_db_connect'] = round(memory_get_usage() / 1024 / 1024, 2);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Memory Debug v2</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        table { border-collapse: collapse; margin: 20px 0; }
        td, th { padding: 10px; border: 1px solid #0f0; text-align: left; }
        th { background: #0a0a0a; }
        .high { color: #f00; font-weight: bold; }
        .medium { color: #ff0; }
        .low { color: #0f0; }
    </style>
</head>
<body>
    <h1>Memory Usage Debug v2</h1>
    
    <table>
        <tr>
            <th>Step</th>
            <th>Memory</th>
            <th>Increase</th>
        </tr>
        <?php 
        $prev = 0;
        foreach ($memory_points as $point => $usage): 
            $current = is_string($usage) ? floatval($usage) : $usage;
            $diff = $prev > 0 ? $current - $prev : 0;
            $class = $diff > 10 ? 'high' : ($diff > 1 ? 'medium' : 'low');
        ?>
        <tr>
            <td><?php echo $point; ?></td>
            <td><?php echo $usage; ?> MB</td>
            <td class="<?php echo $class; ?>">
                <?php echo $prev > 0 ? '+' . round($diff, 2) . ' MB' : '-'; ?>
            </td>
        </tr>
        <?php 
            $prev = $current;
        endforeach; 
        ?>
    </table>
    
    <h2>Composer Autoloader Analysis</h2>
    <?php
    $autoload_file = dirname(__DIR__) . '/vendor/autoload.php';
    if (file_exists($autoload_file)) {
        $size = filesize($autoload_file) / 1024;
        echo "<p>autoload.php size: " . round($size, 2) . " KB</p>";
    }
    
    $static_file = dirname(__DIR__) . '/vendor/composer/autoload_static.php';
    if (file_exists($static_file)) {
        $size = filesize($static_file) / 1024;
        echo "<p>autoload_static.php size: " . round($size, 2) . " KB</p>";
        
        // Count how many classes are registered
        $content = file_get_contents($static_file);
        $classmap_count = substr_count($content, "'=>");
        echo "<p>Classes in classmap: ~" . $classmap_count . "</p>";
    }
    ?>
    
    <h2>Loaded Extensions</h2>
    <?php
    $extensions = get_loaded_extensions();
    echo "<p>" . count($extensions) . " PHP extensions loaded</p>";
    
    // Check for opcache
    if (in_array('Zend OPcache', $extensions)) {
        echo "<p style='color: #0f0;'>✓ OPcache is enabled</p>";
        $opcache_status = opcache_get_status();
        if ($opcache_status) {
            echo "<p>OPcache memory usage: " . round($opcache_status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB</p>";
        }
    } else {
        echo "<p style='color: #f00;'>✗ OPcache is NOT enabled (enabling it would improve performance)</p>";
    }
    ?>
    
</body>
</html>
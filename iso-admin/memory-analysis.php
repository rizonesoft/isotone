<?php
/**
 * Memory Analysis - Find what we can optimize
 */

// Minimal setup to measure baseline
$checkpoints = [];
$checkpoints['1_bare_minimum'] = memory_get_usage();

// Start session
session_start();
$checkpoints['2_session_started'] = memory_get_usage();

// Load only config
require_once dirname(__DIR__) . '/config.php';
$checkpoints['3_config_loaded'] = memory_get_usage();

// Check what happens with just RedBeanPHP
if (!class_exists('RedBeanPHP\R')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
$checkpoints['4_autoloader'] = memory_get_usage();

use RedBeanPHP\R;

// Load database helper
require_once dirname(__DIR__) . '/iso-includes/database.php';
$checkpoints['5_database_helper'] = memory_get_usage();

// Connect to database
isotone_db_connect();
$checkpoints['6_db_connected'] = memory_get_usage();

// Load user class
require_once dirname(__DIR__) . '/iso-includes/class-user.php';
$checkpoints['7_user_class'] = memory_get_usage();

// Calculate sizes and find optimization opportunities
$prev = 0;
$breakdown = [];
$total_used = 0;

foreach ($checkpoints as $name => $bytes) {
    $mb = round($bytes / 1024 / 1024, 3);
    $increase = $prev > 0 ? round(($bytes - $prev) / 1024 / 1024, 3) : 0;
    $breakdown[] = [
        'checkpoint' => $name,
        'total_mb' => $mb,
        'increase_mb' => $increase,
        'bytes' => $bytes
    ];
    $prev = $bytes;
    $total_used = $mb;
}

// Get loaded files analysis
$files = get_included_files();
$vendor_files = array_filter($files, function($f) { return strpos($f, '/vendor/') !== false; });
$symfony_files = array_filter($vendor_files, function($f) { return strpos($f, '/symfony/') !== false; });

// Analyze vendor packages
$vendor_analysis = [];
$vendor_dirs = [];
foreach ($vendor_files as $file) {
    if (preg_match('#/vendor/([^/]+)/([^/]+)/#', $file, $matches)) {
        $package = $matches[1] . '/' . $matches[2];
        if (!isset($vendor_dirs[$package])) {
            $vendor_dirs[$package] = ['count' => 0, 'size' => 0];
        }
        $vendor_dirs[$package]['count']++;
        if (file_exists($file)) {
            $vendor_dirs[$package]['size'] += filesize($file);
        }
    }
}

// Sort by size
uasort($vendor_dirs, function($a, $b) { return $b['size'] - $a['size']; });
?>
<!DOCTYPE html>
<html>
<head>
    <title>Memory Analysis</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #0a0a0a; color: #00ff00; padding: 20px; }
        h1, h2 { color: #00ffff; text-shadow: 0 0 10px #00ffff; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #00ff00; }
        th { background: #003300; color: #00ff00; }
        .high { color: #ff0000; font-weight: bold; }
        .medium { color: #ffff00; }
        .low { color: #00ff00; }
        .suggestion { background: #001100; padding: 10px; margin: 10px 0; border-left: 3px solid #00ffff; }
    </style>
</head>
<body>
    <h1>🎯 Memory Analysis - Target: Reduce by 1MB</h1>
    
    <h2>Current Memory Breakdown</h2>
    <table>
        <tr>
            <th>Checkpoint</th>
            <th>Total (MB)</th>
            <th>Increase</th>
            <th>% of Total</th>
        </tr>
        <?php foreach ($breakdown as $point): 
            $percent = round(($point['increase_mb'] / $total_used) * 100, 1);
            $class = $point['increase_mb'] > 1 ? 'high' : ($point['increase_mb'] > 0.5 ? 'medium' : 'low');
        ?>
        <tr>
            <td><?= $point['checkpoint'] ?></td>
            <td><?= $point['total_mb'] ?> MB</td>
            <td class="<?= $class ?>"><?= $point['increase_mb'] > 0 ? '+' . $point['increase_mb'] : '-' ?> MB</td>
            <td><?= $percent ?>%</td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Vendor Package Analysis</h2>
    <table>
        <tr>
            <th>Package</th>
            <th>Files</th>
            <th>Size (KB)</th>
            <th>Size (MB)</th>
        </tr>
        <?php foreach (array_slice($vendor_dirs, 0, 10, true) as $package => $info): ?>
        <tr>
            <td><?= $package ?></td>
            <td><?= $info['count'] ?></td>
            <td><?= round($info['size'] / 1024, 2) ?></td>
            <td class="<?= ($info['size'] / 1024 / 1024) > 0.5 ? 'high' : 'low' ?>"><?= round($info['size'] / 1024 / 1024, 3) ?> MB</td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>📊 Statistics</h2>
    <ul>
        <li>Total files loaded: <?= count($files) ?></li>
        <li>Vendor files: <?= count($vendor_files) ?></li>
        <li>Symfony files: <?= count($symfony_files) ?></li>
        <li>Peak memory: <?= round(memory_get_peak_usage() / 1024 / 1024, 2) ?> MB</li>
        <li>Current memory: <?= round(memory_get_usage() / 1024 / 1024, 2) ?> MB</li>
    </ul>
    
    <h2>💡 Optimization Suggestions to Save 1MB</h2>
    
    <?php
    $suggestions = [];
    
    // Check if we're loading unnecessary Symfony components
    if (count($symfony_files) > 10) {
        $symfony_size = 0;
        foreach ($symfony_files as $file) {
            if (file_exists($file)) $symfony_size += filesize($file);
        }
        $symfony_mb = round($symfony_size / 1024 / 1024, 2);
        $suggestions[] = "Remove unused Symfony components ({$symfony_mb} MB currently loaded)";
    }
    
    // Check autoloader optimization
    $autoloader_size = 0;
    if (isset($vendor_dirs['composer/composer'])) {
        $autoloader_size = round($vendor_dirs['composer/composer']['size'] / 1024 / 1024, 2);
        if ($autoloader_size > 0.3) {
            $suggestions[] = "Optimize Composer autoloader: Run 'composer dump-autoload -o' (currently {$autoloader_size} MB)";
        }
    }
    
    // Check for development packages
    if (file_exists(dirname(__DIR__) . '/composer.json')) {
        $composer = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'), true);
        if (isset($composer['require-dev']) && !empty($composer['require-dev'])) {
            $suggestions[] = "Remove dev dependencies in production: Run 'composer install --no-dev'";
        }
    }
    
    // RedBeanPHP optimization
    if (isset($vendor_dirs['gabordemooij/redbean'])) {
        $rb_size = round($vendor_dirs['gabordemooij/redbean']['size'] / 1024 / 1024, 2);
        $suggestions[] = "Consider using R::freeze(true) in production to reduce RedBeanPHP overhead ({$rb_size} MB)";
    }
    
    // Session optimization
    $session_size = strlen(serialize($_SESSION));
    if ($session_size > 1024) {
        $session_mb = round($session_size / 1024 / 1024, 3);
        $suggestions[] = "Clean up session data (currently {$session_mb} MB)";
    }
    
    foreach ($suggestions as $suggestion):
    ?>
    <div class="suggestion">
        ✓ <?= $suggestion ?>
    </div>
    <?php endforeach; ?>
    
    <h2>🎯 Quick Wins</h2>
    <div class="suggestion">
        <strong>Immediate actions to save ~1MB:</strong><br>
        1. Run: <code>composer dump-autoload --optimize --no-dev</code><br>
        2. Add to config.php: <code>define('ISOTONE_PRODUCTION', true);</code><br>
        3. In database.php, add: <code>if (ISOTONE_PRODUCTION) R::freeze(true);</code><br>
        4. Remove debug files: memory-debug.php, session-debug.php, etc.<br>
        5. Clear unused session variables regularly
    </div>
    
</body>
</html>
<?php
/**
 * Memory Usage Debug Script
 * Tracks memory usage at different points during page load
 */

// Track memory at the very start
$memory_points = [];
$memory_points['start'] = round(memory_get_usage() / 1024 / 1024, 2);

// Include auth.php and track memory
require_once 'auth.php';
$memory_points['after_auth'] = round(memory_get_usage() / 1024 / 1024, 2);

// Load RedBeanPHP
use RedBeanPHP\R;
require_once dirname(__DIR__) . '/iso-includes/database.php';
$memory_points['after_includes'] = round(memory_get_usage() / 1024 / 1024, 2);

// Connect to database
isotone_db_connect();
$memory_points['after_db_connect'] = round(memory_get_usage() / 1024 / 1024, 2);

// Do a simple query
$count = (int)R::getCell('SELECT COUNT(*) FROM post');
$memory_points['after_query'] = round(memory_get_usage() / 1024 / 1024, 2);

// Calculate differences
$diffs = [];
$prev = 0;
foreach ($memory_points as $point => $usage) {
    if ($prev > 0) {
        $diff = $usage - $prev;
        $diffs[$point] = '+' . round($diff, 2) . ' MB';
    }
    $prev = $usage;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Memory Debug</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        table { border-collapse: collapse; margin: 20px 0; }
        td, th { padding: 10px; border: 1px solid #0f0; text-align: left; }
        th { background: #0a0a0a; }
        .diff { color: #ff0; }
    </style>
</head>
<body>
    <h1>Memory Usage Debug</h1>
    
    <table>
        <tr>
            <th>Checkpoint</th>
            <th>Memory (MB)</th>
            <th>Increase</th>
        </tr>
        <?php foreach ($memory_points as $point => $usage): ?>
        <tr>
            <td><?php echo $point; ?></td>
            <td><?php echo $usage; ?> MB</td>
            <td class="diff"><?php echo $diffs[$point] ?? '-'; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Loaded Files Count: <?php echo count(get_included_files()); ?></h2>
    
    <h2>Top 10 Largest Included Files:</h2>
    <table>
        <tr>
            <th>File</th>
            <th>Size (KB)</th>
        </tr>
        <?php 
        $files = get_included_files();
        $file_sizes = [];
        foreach ($files as $file) {
            if (file_exists($file)) {
                $file_sizes[$file] = filesize($file) / 1024;
            }
        }
        arsort($file_sizes);
        $top_files = array_slice($file_sizes, 0, 10, true);
        foreach ($top_files as $file => $size): 
        ?>
        <tr>
            <td><?php echo basename($file); ?></td>
            <td><?php echo round($size, 2); ?> KB</td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Vendor Packages Loaded:</h2>
    <?php
    $vendor_files = array_filter($files, function($f) { return strpos($f, '/vendor/') !== false; });
    echo '<p>' . count($vendor_files) . ' vendor files loaded</p>';
    ?>
    
</body>
</html>
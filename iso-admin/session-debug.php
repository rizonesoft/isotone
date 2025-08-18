<?php
/**
 * Session Debug Script
 * Check what's stored in the session
 */

// Start session
session_start();

// Get session data
$session_data = $_SESSION;

// Calculate sizes
function getSize($var) {
    $serialized = serialize($var);
    return strlen($serialized);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Debug</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        table { border-collapse: collapse; margin: 20px 0; width: 100%; }
        td, th { padding: 10px; border: 1px solid #0f0; text-align: left; }
        th { background: #0a0a0a; }
        .large { color: #f00; font-weight: bold; }
        .medium { color: #ff0; }
        pre { background: #0a0a0a; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Session Debug</h1>
    
    <h2>Session Variables</h2>
    <table>
        <tr>
            <th>Key</th>
            <th>Type</th>
            <th>Size (bytes)</th>
            <th>Size (MB)</th>
        </tr>
        <?php foreach ($session_data as $key => $value): 
            $size = getSize($value);
            $size_mb = round($size / 1024 / 1024, 4);
            $class = $size_mb > 1 ? 'large' : ($size_mb > 0.1 ? 'medium' : '');
        ?>
        <tr>
            <td><?php echo htmlspecialchars($key); ?></td>
            <td><?php echo gettype($value); ?></td>
            <td><?php echo number_format($size); ?></td>
            <td class="<?php echo $class; ?>"><?php echo $size_mb; ?> MB</td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Total Session Size</h2>
    <?php
    $total_size = getSize($_SESSION);
    $total_mb = round($total_size / 1024 / 1024, 2);
    ?>
    <p>Total serialized size: <?php echo number_format($total_size); ?> bytes (<?php echo $total_mb; ?> MB)</p>
    
    <h2>Session Save Path</h2>
    <p><?php echo session_save_path() ?: sys_get_temp_dir(); ?></p>
    
    <h2>Session ID</h2>
    <p><?php echo session_id(); ?></p>
    
    <h2>Session Details</h2>
    <?php foreach ($session_data as $key => $value): ?>
        <h3><?php echo htmlspecialchars($key); ?></h3>
        <pre><?php 
            if (is_array($value) || is_object($value)) {
                // Limit output for large arrays/objects
                $output = print_r($value, true);
                if (strlen($output) > 1000) {
                    echo substr($output, 0, 1000) . "\n... (truncated)";
                } else {
                    echo $output;
                }
            } else {
                echo htmlspecialchars((string)$value);
            }
        ?></pre>
    <?php endforeach; ?>
    
    <h2>Clear Session</h2>
    <form method="post" action="">
        <button type="submit" name="clear_session" style="background: #f00; color: #fff; padding: 10px;">
            CLEAR ALL SESSION DATA
        </button>
    </form>
    
    <?php
    if (isset($_POST['clear_session'])) {
        session_destroy();
        echo "<p style='color: #0f0; font-weight: bold;'>Session cleared! Refresh the page.</p>";
    }
    ?>
    
</body>
</html>
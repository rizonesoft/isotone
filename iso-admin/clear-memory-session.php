<?php
/**
 * Clear the memory_data from session
 */

session_start();

// Check if memory_data exists
if (isset($_SESSION['memory_data'])) {
    $size_before = strlen(serialize($_SESSION['memory_data'])) / 1024 / 1024;
    unset($_SESSION['memory_data']);
    echo "Cleared memory_data from session (was using " . round($size_before, 2) . " MB)<br>";
} else {
    echo "No memory_data found in session<br>";
}

// Show current session size
$total_size = strlen(serialize($_SESSION)) / 1024 / 1024;
echo "Current session size: " . round($total_size, 4) . " MB<br>";

echo "<br><a href='dashboard-new.php'>Go to Dashboard</a>";
?>
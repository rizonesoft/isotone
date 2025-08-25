<?php
// Test file for error page
$_GET['code'] = 404;
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        // Test with default settings - no performance override
    </script>
</head>
<body>
    <?php require_once __DIR__ . '/server/error.php'; ?>
</body>
</html>
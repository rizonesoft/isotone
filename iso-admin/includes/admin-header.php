<?php
/**
 * Admin Header Include
 * Common head section for all admin pages
 * 
 * @package Isotone
 */
?>
    <!-- Tailwind CSS -->
    <?php if (file_exists(__DIR__ . '/../css/tailwind.css')): ?>
        <link rel="stylesheet" href="/isotone/iso-admin/css/tailwind.css">
    <?php else: ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="/isotone/iso-includes/js/tailwind-config.js"></script>
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" href="/isotone/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/isotone/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/isotone/favicon-16x16.png">
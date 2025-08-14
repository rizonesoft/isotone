<?php
/**
 * Authentication Check for Admin Pages
 * Include this at the top of every admin page
 * 
 * @package Isotone
 */

session_start();

// Load configuration and user class
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/iso-includes/class-user.php';

// Check if user is logged in
if (!isset($_SESSION['isotone_admin_logged_in']) || $_SESSION['isotone_admin_logged_in'] !== true) {
    // Not logged in, redirect to login page
    $current_page = $_SERVER['REQUEST_URI'];
    header('Location: /isotone/iso-admin/login.php?redirect=' . urlencode($current_page));
    exit;
}

// Check session timeout (2 hours)
if (isset($_SESSION['isotone_admin_last_activity']) && (time() - $_SESSION['isotone_admin_last_activity'] > 7200)) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: /isotone/iso-admin/login.php?expired=1');
    exit;
}

// Update last activity time
$_SESSION['isotone_admin_last_activity'] = time();

// Make current user data available
$current_user = $_SESSION['isotone_admin_user'] ?? 'Unknown';
$current_user_id = $_SESSION['isotone_admin_user_id'] ?? 0;
$current_user_data = $_SESSION['isotone_admin_user_data'] ?? [];

// Check user role for permission
function requireRole($role) {
    global $current_user_id;
    
    if (!$current_user_id) {
        header('Location: /isotone/iso-admin/login.php');
        exit;
    }
    
    $userObj = new IsotoneUser();
    if (!$userObj->hasRole($current_user_id, $role)) {
        // User doesn't have required role
        header('HTTP/1.0 403 Forbidden');
        die('You do not have permission to access this page.');
    }
}
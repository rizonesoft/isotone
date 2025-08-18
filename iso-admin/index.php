<?php
/**
 * Admin Dashboard
 * 
 * @package Isotone
 */

// Check authentication
require_once 'auth.php';

// Redirect to dashboard
header('Location: /isotone/iso-admin/dashboard.php');
exit;
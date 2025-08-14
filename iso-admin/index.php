<?php
/**
 * Admin Dashboard
 * 
 * @package Isotone
 */

// Check authentication
require_once 'auth.php';

// Redirect to new dashboard
header('Location: /isotone/iso-admin/dashboard-new.php');
exit;
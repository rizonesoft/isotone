<?php
/**
 * Security Helper Class
 * 
 * Provides security utilities for Isotone
 * 
 * @package Isotone
 */

class IsotoneSecurity {
    
    /**
     * Generate session fingerprint
     * Combines IP address and User Agent for session validation
     */
    public static function generateFingerprint() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'no-user-agent';
        
        // For localhost/development, use a simplified fingerprint
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || strpos($ip, '192.168.') === 0 || strpos($ip, '172.') === 0) {
            // Development environment - use only user agent for fingerprint
            $fingerprint_base = 'dev|' . $ua;
        } else {
            // Production - use partial IP (first 3 octets) to handle dynamic IPs
            $ip_parts = explode('.', $ip);
            if (count($ip_parts) >= 3) {
                $partial_ip = $ip_parts[0] . '.' . $ip_parts[1] . '.' . $ip_parts[2];
            } else {
                $partial_ip = $ip;
            }
            $fingerprint_base = $partial_ip . '|' . $ua;
        }
        
        // Check if SECURE_AUTH_KEY is properly set
        if (!defined('SECURE_AUTH_KEY') || 
            SECURE_AUTH_KEY === '' || 
            SECURE_AUTH_KEY === 'put your unique phrase here' ||
            strpos(SECURE_AUTH_KEY, 'your unique phrase') !== false) {
            
            // Log warning about insecure configuration
            error_log('WARNING: SECURE_AUTH_KEY is not properly configured in config.php. Using fallback key.');
            
            // Use a fallback key based on host and installation path
            $key = 'isotone_fallback_' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '_' . dirname(__DIR__);
        } else {
            $key = SECURE_AUTH_KEY;
        }
        
        return hash('sha256', $fingerprint_base . '|' . $key);
    }
    
    /**
     * Validate session fingerprint
     */
    public static function validateFingerprint() {
        // If no fingerprint exists, this is a new session - allow it
        if (!isset($_SESSION['fingerprint'])) {
            return true;
        }
        
        // For logged in users, validate the fingerprint
        $current_fingerprint = self::generateFingerprint();
        $stored_fingerprint = $_SESSION['fingerprint'];
        
        // If fingerprints don't match, log for debugging
        if ($stored_fingerprint !== $current_fingerprint) {
            error_log('Fingerprint mismatch - Stored: ' . $stored_fingerprint . ', Current: ' . $current_fingerprint);
        }
        
        return $stored_fingerprint === $current_fingerprint;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        if (empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token field HTML
     */
    public static function csrfField() {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Check for brute force attempts (delegates to LoginSecurity class)
     */
    public static function checkBruteForce($identifier = null) {
        // Load database connection if not loaded
        if (!class_exists('LoginSecurity')) {
            require_once dirname(__DIR__) . '/iso-includes/database.php';
            require_once dirname(__DIR__) . '/iso-includes/class-login-security.php';
            isotone_db_connect();
        }
        
        $ip = $identifier ?: $_SERVER['REMOTE_ADDR'];
        return LoginSecurity::checkBruteForce($ip);
    }
    
    /**
     * Record login attempt (delegates to LoginSecurity class)
     */
    public static function recordLoginAttempt($username = null, $success = false) {
        // Load database connection if not loaded
        if (!class_exists('LoginSecurity')) {
            require_once dirname(__DIR__) . '/iso-includes/database.php';
            require_once dirname(__DIR__) . '/iso-includes/class-login-security.php';
            isotone_db_connect();
        }
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        return LoginSecurity::recordAttempt($ip, $username, $success, $user_agent);
    }
    
    /**
     * Sanitize output (XSS protection)
     */
    public static function escape($string, $encoding = 'UTF-8') {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, $encoding);
    }
    
    /**
     * Sanitize HTML output (allows some tags)
     */
    public static function escapeHtml($html) {
        $allowed_tags = '<p><br><strong><em><u><a><ul><ol><li><blockquote><code><pre>';
        return strip_tags($html, $allowed_tags);
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Hash password (using PHP's password_hash)
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Secure session configuration
     */
    public static function secureSession() {
        // Set secure session parameters
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Use secure cookies if HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        
        // Set session name
        session_name('ISOTONE_SESS');
        
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            // Regenerate every 30 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowed_types = [], $max_size = 5242880) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload failed'];
        }
        
        // Check file size (default 5MB)
        if ($file['size'] > $max_size) {
            return ['valid' => false, 'error' => 'File too large'];
        }
        
        // Check MIME type if specified
        if (!empty($allowed_types)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime, $allowed_types)) {
                return ['valid' => false, 'error' => 'Invalid file type'];
            }
        }
        
        // Check for PHP in filename
        $filename = basename($file['name']);
        if (preg_match('/\.ph(p[3457]?|t|tml)$/i', $filename)) {
            return ['valid' => false, 'error' => 'Invalid file extension'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($event, $details = []) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['isotone_admin_user_id'] ?? null,
            'details' => $details
        ];
        
        // Log to file (create security log if doesn't exist)
        $log_file = dirname(__DIR__) . '/iso-content/logs/security.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
}

// Convenience functions
function iso_escape($string) {
    return IsotoneSecurity::escape($string);
}

function iso_csrf_field() {
    return IsotoneSecurity::csrfField();
}

function iso_verify_csrf($token = null) {
    $token = $token ?: ($_POST['csrf_token'] ?? '');
    return IsotoneSecurity::validateCSRFToken($token);
}
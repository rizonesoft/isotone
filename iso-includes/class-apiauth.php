<?php
/**
 * API Authentication Class
 * Handles API key authentication for Isotone admin APIs
 * 
 * @package Isotone
 * @since 0.3.3
 */

class IsotoneAPIAuth {
    
    /**
     * Check if request has valid API key
     * 
     * @return array|false User data if authenticated, false otherwise
     */
    public static function authenticate() {
        // Get API key from request headers
        $api_key = self::getApiKeyFromRequest();
        
        if (!$api_key) {
            return false;
        }
        
        // Validate format (iso_live_sk_ or iso_test_sk_ prefix)
        if (!preg_match('/^iso_(live|test)_sk_[a-zA-Z0-9]{32,}$/', $api_key)) {
            self::logAuthAttempt($api_key, false, 'Invalid format');
            return false;
        }
        
        // Look up key in database
        $key_data = self::validateApiKey($api_key);
        
        if (!$key_data) {
            self::logAuthAttempt($api_key, false, 'Key not found');
            return false;
        }
        
        // Check if key is active
        if (!$key_data['is_active']) {
            self::logAuthAttempt($api_key, false, 'Key inactive');
            return false;
        }
        
        // Check expiration
        if ($key_data['expires_at'] && strtotime($key_data['expires_at']) < time()) {
            self::logAuthAttempt($api_key, false, 'Key expired');
            return false;
        }
        
        // Check IP whitelist if configured
        if (!empty($key_data['ip_whitelist']) && !self::checkIpWhitelist($key_data['ip_whitelist'])) {
            self::logAuthAttempt($api_key, false, 'IP not whitelisted');
            return false;
        }
        
        // Check rate limiting
        if (!self::checkRateLimit($key_data['id'])) {
            self::logAuthAttempt($api_key, false, 'Rate limit exceeded');
            return false;
        }
        
        // Update last used timestamp
        self::updateLastUsed($key_data['id']);
        
        // Log successful authentication
        self::logAuthAttempt($api_key, true);
        
        // Return user data with permissions
        return [
            'user_id' => $key_data['user_id'],
            'user' => $key_data['user_name'],
            'api_key_id' => $key_data['id'],
            'api_key_name' => $key_data['name'],
            'permissions' => json_decode($key_data['permissions'], true) ?? [],
            'auth_type' => 'api_key'
        ];
    }
    
    /**
     * Fallback for getallheaders() when not available (e.g., CLI)
     * 
     * @return array
     */
    private static function getAllHeaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                // Convert HTTP_X_API_KEY to X-Api-Key
                $header_name = str_replace('_', '-', substr($name, 5));
                $header_name = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $header_name))));
                $headers[$header_name] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * Get API key from request headers
     * 
     * @return string|null
     */
    private static function getApiKeyFromRequest() {
        // Check X-API-Key header
        $headers = function_exists('getallheaders') ? getallheaders() : self::getAllHeaders();
        // Check various case variations
        if (isset($headers['X-API-Key'])) {
            return $headers['X-API-Key'];
        }
        if (isset($headers['X-Api-Key'])) {
            return $headers['X-Api-Key'];
        }
        if (isset($headers['x-api-key'])) {
            return $headers['x-api-key'];
        }
        
        // Check Authorization Bearer header
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        if (isset($headers['authorization'])) {
            if (preg_match('/Bearer\s+(.+)$/i', $headers['authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        // Check query parameter as fallback (not recommended for production)
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }
        
        return null;
    }
    
    /**
     * Validate API key against database
     * 
     * @param string $api_key
     * @return array|false
     */
    private static function validateApiKey($api_key) {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        // Ensure database connection
        if (!RedBeanPHP\R::testConnection()) {
            require_once dirname(__DIR__) . '/iso-includes/database.php';
            isotone_db_connect();
        }
        
        // Find all active API keys
        $keys = RedBeanPHP\R::find('apikey', 'is_active = 1');
        
        foreach ($keys as $key) {
            // Check if the provided key matches the hash
            if (password_verify($api_key, $key->key_hash)) {
                // Load associated user (using 'user' table)
                $user = RedBeanPHP\R::load('user', $key->user_id);
                if (!$user || !$user->id) {
                    continue;
                }
                
                return [
                    'id' => $key->id,
                    'user_id' => $key->user_id,
                    'user_name' => $user->username ?? 'admin',
                    'name' => $key->name,
                    'permissions' => $key->permissions,
                    'expires_at' => $key->expires_at,
                    'is_active' => $key->is_active,
                    'ip_whitelist' => $key->ip_whitelist
                ];
            }
        }
        
        return false;
    }
    
    /**
     * Check if current IP is in whitelist
     * 
     * @param string $whitelist JSON array of allowed IPs
     * @return bool
     */
    private static function checkIpWhitelist($whitelist) {
        $allowed_ips = json_decode($whitelist, true);
        if (!is_array($allowed_ips) || empty($allowed_ips)) {
            return true; // No whitelist means all IPs allowed
        }
        
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Check for proxy headers
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $client_ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        
        return in_array($client_ip, $allowed_ips);
    }
    
    /**
     * Check rate limiting for API key
     * 
     * @param int $key_id
     * @return bool True if within limits
     */
    private static function checkRateLimit($key_id) {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        // Default rate limit: 1000 requests per hour
        $limit = 1000;
        $window = 3600; // 1 hour in seconds
        
        // Count recent requests
        $since = date('Y-m-d H:i:s', time() - $window);
        $count = RedBeanPHP\R::count('apiratelimit', 
            'api_key_id = ? AND created_at > ?', 
            [$key_id, $since]
        );
        
        if ($count >= $limit) {
            return false;
        }
        
        // Record this request
        $record = RedBeanPHP\R::dispense('apiratelimit');
        $record->api_key_id = $key_id;
        $record->created_at = date('Y-m-d H:i:s');
        $record->endpoint = $_SERVER['REQUEST_URI'] ?? '';
        $record->method = $_SERVER['REQUEST_METHOD'] ?? '';
        $record->ip = $_SERVER['REMOTE_ADDR'] ?? '';
        RedBeanPHP\R::store($record);
        
        // Clean old records (older than 24 hours)
        $old = date('Y-m-d H:i:s', time() - 86400);
        RedBeanPHP\R::exec('DELETE FROM apiratelimit WHERE created_at < ?', [$old]);
        
        return true;
    }
    
    /**
     * Update last used timestamp
     * 
     * @param int $key_id
     */
    private static function updateLastUsed($key_id) {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        $key = RedBeanPHP\R::load('apikey', $key_id);
        if ($key && $key->id) {
            $key->last_used = date('Y-m-d H:i:s');
            $key->usage_count = ($key->usage_count ?? 0) + 1;
            RedBeanPHP\R::store($key);
        }
    }
    
    /**
     * Log authentication attempt
     * 
     * @param string $api_key
     * @param bool $success
     * @param string $reason
     */
    private static function logAuthAttempt($api_key, $success, $reason = '') {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        $log = RedBeanPHP\R::dispense('apiauthlog');
        $log->api_key_prefix = substr($api_key, 0, 20) . '...'; // Store only prefix for security
        $log->success = $success;
        $log->reason = $reason;
        $log->ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $log->endpoint = $_SERVER['REQUEST_URI'] ?? '';
        $log->created_at = date('Y-m-d H:i:s');
        RedBeanPHP\R::store($log);
    }
    
    /**
     * Generate a new API key
     * 
     * @param string $prefix 'live' or 'test'
     * @return string
     */
    public static function generateApiKey($prefix = 'live') {
        $prefix = in_array($prefix, ['live', 'test']) ? $prefix : 'live';
        $random = bin2hex(random_bytes(24)); // 48 character random string
        return "iso_{$prefix}_sk_{$random}";
    }
    
    /**
     * Check if user has permission
     * 
     * @param array $user_data User data from authenticate()
     * @param string $permission Permission to check (e.g., 'todos.write')
     * @return bool
     */
    public static function hasPermission($user_data, $permission) {
        if (!isset($user_data['permissions'])) {
            return false;
        }
        
        $permissions = $user_data['permissions'];
        
        // Check for wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }
        
        // Check specific permission
        if (in_array($permission, $permissions)) {
            return true;
        }
        
        // Check for partial wildcard (e.g., 'todos.*')
        $parts = explode('.', $permission);
        if (count($parts) > 1) {
            $wildcard = $parts[0] . '.*';
            if (in_array($wildcard, $permissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get rate limit headers for response
     * 
     * @param int $key_id
     * @return array
     */
    public static function getRateLimitHeaders($key_id) {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        $limit = 1000;
        $window = 3600;
        $since = date('Y-m-d H:i:s', time() - $window);
        
        $count = RedBeanPHP\R::count('apiratelimit', 
            'api_key_id = ? AND created_at > ?', 
            [$key_id, $since]
        );
        
        $remaining = max(0, $limit - $count);
        $reset = time() + $window;
        
        return [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $reset
        ];
    }
}

// Helper functions for backward compatibility
function iso_api_authenticate() {
    return IsotoneAPIAuth::authenticate();
}

function iso_api_has_permission($user_data, $permission) {
    return IsotoneAPIAuth::hasPermission($user_data, $permission);
}

function iso_api_generate_key($prefix = 'live') {
    return IsotoneAPIAuth::generateApiKey($prefix);
}
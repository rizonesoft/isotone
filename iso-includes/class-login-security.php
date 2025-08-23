<?php
/**
 * Login Security Class - Using RedBeanPHP Beans
 * Secure, plugin-developer friendly implementation
 * 
 * @package Isotone
 * @since 1.0.0
 */

// Load RedBeanPHP if not already loaded
if (!class_exists('RedBeanPHP\\R')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

use RedBeanPHP\R;

class LoginSecurity {
    
    /**
     * Initialize bean validation and security hooks
     */
    public static function initializeHooks() {
        // Add validation for loginattempt beans
        R::ext('dispense', function($type) {
            $bean = R::getRedBean()->dispense($type);
            
            // Add creation timestamp for certain beans
            if (in_array($type, ['loginattempt', 'lockout', 'iplist', 'usernamelist'])) {
                $bean->createdAt = date('Y-m-d H:i:s');
            }
            
            return $bean;
        });
    }
    
    /**
     * Get security setting from database
     * 
     * @param string $name Setting name
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    public static function getSetting($name, $default = null) {
        try {
            $setting = R::findOne('protection', 'setting_name = ?', [$name]);
            
            if ($setting) {
                // Cast to appropriate type based on setting_type
                switch ($setting->setting_type) {
                    case 'integer':
                        return (int) $setting->setting_value;
                    case 'boolean':
                        return (bool) $setting->setting_value;
                    case 'float':
                        return (float) $setting->setting_value;
                    default:
                        return $setting->setting_value;
                }
            }
        } catch (Exception $e) {
            error_log('Failed to get security setting: ' . $e->getMessage());
        }
        
        return $default;
    }
    
    /**
     * Update or create security setting
     * 
     * @param string $name Setting name
     * @param mixed $value Setting value
     * @param string $type Data type (string, integer, boolean, float)
     * @return bool Success status
     */
    public static function updateSetting($name, $value, $type = 'string') {
        try {
            $setting = R::findOne('protection', 'setting_name = ?', [$name]);
            
            if (!$setting) {
                $setting = R::dispense('protection');
                $setting->setting_name = $name;
                $setting->setting_type = $type;
            }
            
            $setting->setting_value = (string) $value; // Store as string, cast on retrieval
            $setting->updated_at = date('Y-m-d H:i:s');
            
            R::store($setting);
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to update security setting: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if IP is in denylist
     * 
     * @param string $ip IP address to check
     * @return bool True if denylisted
     */
    public static function isIPDenylisted($ip) {
        if (!self::getSetting('enable_ip_denylist', true)) {
            return false;
        }
        
        try {
            $denied = R::findOne('iplist', 
                'ip_address = ? AND list_type = ? AND active = ?', 
                [$ip, 'denylist', 1]
            );
            
            return !empty($denied);
        } catch (Exception $e) {
            error_log('Failed to check IP denylist: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if IP is in safelist
     * 
     * @param string $ip IP address to check
     * @return bool True if safelisted
     */
    public static function isIPSafelisted($ip) {
        if (!self::getSetting('enable_ip_safelist', false)) {
            return false;
        }
        
        try {
            $safe = R::findOne('iplist', 
                'ip_address = ? AND list_type = ? AND active = ?', 
                [$ip, 'safelist', 1]
            );
            
            return !empty($safe);
        } catch (Exception $e) {
            error_log('Failed to check IP safelist: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if username is denylisted
     * 
     * @param string $username Username to check
     * @return bool True if denylisted
     */
    public static function isUsernameDenylisted($username) {
        if (!self::getSetting('enable_username_denylist', true)) {
            return false;
        }
        
        try {
            $denied = R::findOne('usernamelist', 
                'username = ? AND list_type = ? AND active = ?', 
                [$username, 'denylist', 1]
            );
            
            return !empty($denied);
        } catch (Exception $e) {
            error_log('Failed to check username denylist: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if username is safelisted
     * 
     * @param string $username Username to check
     * @return bool True if safelisted
     */
    public static function isUsernameSafelisted($username) {
        if (!self::getSetting('enable_username_safelist', false)) {
            return false;
        }
        
        try {
            $safe = R::findOne('usernamelist', 
                'username = ? AND list_type = ? AND active = ?', 
                [$username, 'safelist', 1]
            );
            
            return !empty($safe);
        } catch (Exception $e) {
            error_log('Failed to check username safelist: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record login attempt in database
     * 
     * @param string $ip IP address
     * @param string|null $username Username if provided
     * @param bool $success Whether login was successful
     * @param string|null $user_agent User agent string
     * @return bool Success status
     */
    public static function recordAttempt($ip, $username = null, $success = false, $user_agent = null) {
        try {
            // Create new login attempt bean
            $attempt = R::dispense('loginattempt');
            $attempt->ip_address = $ip;
            $attempt->username = $username;
            $attempt->attempt_time = date('Y-m-d H:i:s');
            $attempt->success = $success ? 1 : 0;
            $attempt->user_agent = $user_agent ?: $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            R::store($attempt);
            
            // Clean old attempts (older than 30 days)
            $oldDate = date('Y-m-d H:i:s', strtotime('-30 days'));
            $oldAttempts = R::find('loginattempt', 'attempt_time < ?', [$oldDate]);
            R::trashAll($oldAttempts);
            
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to record login attempt: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if IP or username is currently locked out
     * 
     * @param string $ip IP address
     * @param string|null $username Username
     * @return array Lockout status and details
     */
    public static function isLockedOut($ip, $username = null) {
        try {
            $now = date('Y-m-d H:i:s');
            
            // Check IP lockout
            $ipLockout = R::findOne('lockout', 
                'ipAddress = ? AND active = ? AND unlockTime > ?', 
                [$ip, 1, $now]
            );
            
            if ($ipLockout) {
                return [
                    'locked' => true,
                    'unlock_time' => $ipLockout->unlockTime,
                    'reason' => $ipLockout->reason
                ];
            }
            
            // Check username lockout if provided
            if ($username) {
                $userLockout = R::findOne('lockout', 
                    'username = ? AND active = ? AND unlockTime > ?', 
                    [$username, 1, $now]
                );
                
                if ($userLockout) {
                    return [
                        'locked' => true,
                        'unlock_time' => $userLockout->unlockTime,
                        'reason' => $userLockout->reason
                    ];
                }
            }
            
            return ['locked' => false];
            
        } catch (Exception $e) {
            error_log('Failed to check lockout: ' . $e->getMessage());
            return ['locked' => false];
        }
    }
    
    /**
     * Create a lockout
     * 
     * @param string $ip IP address
     * @param string|null $username Username
     * @param int|null $duration Lockout duration in seconds
     * @param string|null $reason Lockout reason
     * @return bool Success status
     */
    public static function createLockout($ip, $username = null, $duration = null, $reason = null) {
        try {
            $duration = $duration ?: self::getSetting('lockout_duration', 900);
            $reason = $reason ?: 'Too many failed login attempts';
            
            // Create lockout bean
            $lockout = R::dispense('lockout');
            $lockout->ipAddress = $ip;
            $lockout->username = $username;
            $lockout->lockoutTime = date('Y-m-d H:i:s');
            $lockout->unlockTime = date('Y-m-d H:i:s', time() + $duration);
            $lockout->reason = $reason;
            $lockout->active = 1;
            
            R::store($lockout);
            
            // Notify admin if enabled
            if (self::getSetting('notify_admin_lockout', false)) {
                self::notifyAdminLockout($ip, $username, $reason);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to create lockout: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent failed attempts count
     * 
     * @param string $ip IP address
     * @param string|null $username Username
     * @return int Number of recent failed attempts
     */
    public static function getRecentFailedAttempts($ip, $username = null) {
        try {
            $reset_time = self::getSetting('reset_time', 900);
            $since = date('Y-m-d H:i:s', time() - $reset_time);
            
            // Count IP-based attempts
            $ipCount = R::count('loginattempt', 
                'ip_address = ? AND success = 0 AND attempt_time > ?', 
                [$ip, $since]
            );
            
            // Count username-based attempts if provided
            $userCount = 0;
            if ($username) {
                $userCount = R::count('loginattempt', 
                    'username = ? AND success = 0 AND attempt_time > ?', 
                    [$username, $since]
                );
            }
            
            // Return the higher count
            return max($ipCount, $userCount);
            
        } catch (Exception $e) {
            error_log('Failed to get failed attempts: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Check for brute force and handle accordingly
     * 
     * @param string $ip IP address
     * @param string|null $username Username
     * @return array Status and details
     */
    public static function checkBruteForce($ip, $username = null) {
        // Check if already locked out
        $lockoutStatus = self::isLockedOut($ip, $username);
        if ($lockoutStatus['locked']) {
            $wait_time = strtotime($lockoutStatus['unlock_time']) - time();
            return [
                'blocked' => true,
                'wait_time' => $wait_time,
                'message' => self::getSetting('lockout_message', 
                    'Too many failed login attempts. Please try again in ' . ceil($wait_time / 60) . ' minutes.')
            ];
        }
        
        // Check if IP is denylisted
        if (self::isIPDenylisted($ip)) {
            return [
                'blocked' => true,
                'wait_time' => 3600,
                'message' => 'Access denied from your IP address.'
            ];
        }
        
        // Check if username is denylisted
        if ($username && self::isUsernameDenylisted($username)) {
            return [
                'blocked' => true,
                'wait_time' => 3600,
                'message' => 'This username is not allowed to login.'
            ];
        }
        
        // If safelisted, skip rate limiting
        if (self::isIPSafelisted($ip) || ($username && self::isUsernameSafelisted($username))) {
            return ['blocked' => false];
        }
        
        // Check failed attempts
        $failed_attempts = self::getRecentFailedAttempts($ip, $username);
        $max_attempts = self::getSetting('max_login_attempts', 5);
        
        if ($failed_attempts >= $max_attempts) {
            // Create lockout
            self::createLockout($ip, $username);
            
            $lockout_duration = self::getSetting('lockout_duration', 900);
            return [
                'blocked' => true,
                'wait_time' => $lockout_duration,
                'message' => self::getSetting('lockout_message', 
                    'Too many failed login attempts. Please try again in ' . ceil($lockout_duration / 60) . ' minutes.')
            ];
        }
        
        return [
            'blocked' => false,
            'remaining_attempts' => $max_attempts - $failed_attempts
        ];
    }
    
    /**
     * Get remaining attempts for display
     * 
     * @param string $ip IP address
     * @param string|null $username Username
     * @return int|null Remaining attempts or null if disabled
     */
    public static function getRemainingAttempts($ip, $username = null) {
        if (!self::getSetting('show_remaining_attempts', true)) {
            return null;
        }
        
        $failed_attempts = self::getRecentFailedAttempts($ip, $username);
        $max_attempts = self::getSetting('max_login_attempts', 5);
        
        return $max_attempts - $failed_attempts;
    }
    
    /**
     * Clear lockout (manual unlock)
     * 
     * @param string|null $ip IP address
     * @param string|null $username Username
     * @param int|null $id Lockout ID
     * @return bool Success status
     */
    public static function clearLockout($ip = null, $username = null, $id = null) {
        try {
            if ($id) {
                $lockout = R::load('lockout', $id);
                if ($lockout->id) {
                    $lockout->active = 0;
                    R::store($lockout);
                }
            } elseif ($ip) {
                $lockouts = R::find('lockout', 'ipAddress = ? AND active = 1', [$ip]);
                foreach ($lockouts as $lockout) {
                    $lockout->active = 0;
                    R::store($lockout);
                }
            } elseif ($username) {
                $lockouts = R::find('lockout', 'username = ? AND active = 1', [$username]);
                foreach ($lockouts as $lockout) {
                    $lockout->active = 0;
                    R::store($lockout);
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to clear lockout: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add IP to list (safelist or denylist)
     * 
     * @param string $ip IP address
     * @param string $list_type 'safelist' or 'denylist'
     * @param string|null $reason Reason for listing
     * @param string|null $added_by Who added the entry
     * @return bool Success status
     */
    public static function addIPToList($ip, $list_type, $reason = null, $added_by = null) {
        try {
            // Check if already exists
            $existing = R::findOne('iplist', 
                'ip_address = ? AND list_type = ?', 
                [$ip, $list_type]
            );
            
            if ($existing) {
                // Update existing entry
                $existing->active = 1;
                $existing->reason = $reason;
                $existing->added_by = $added_by;
                $existing->added_date = date('Y-m-d H:i:s');
                R::store($existing);
            } else {
                // Create new entry
                $iplist = R::dispense('iplist');
                $iplist->ip_address = $ip;
                $iplist->list_type = $list_type;
                $iplist->reason = $reason;
                $iplist->added_by = $added_by;
                $iplist->added_date = date('Y-m-d H:i:s');
                $iplist->active = 1;
                R::store($iplist);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to add IP to list: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add username to list (safelist or denylist)
     * 
     * @param string $username Username
     * @param string $list_type 'safelist' or 'denylist'
     * @param string|null $reason Reason for listing
     * @param string|null $added_by Who added the entry
     * @return bool Success status
     */
    public static function addUsernameToList($username, $list_type, $reason = null, $added_by = null) {
        try {
            error_log('addUsernameToList called with: username=' . $username . ', list_type=' . $list_type . ', reason=' . $reason . ', added_by=' . $added_by);
            
            // Ensure database is connected
            if (!R::testConnection()) {
                error_log('Database connection lost, attempting to reconnect');
                require_once dirname(__DIR__) . '/iso-includes/database.php';
                isotone_db_connect();
            }
            
            // Check if already exists - using snake_case for database columns
            $existing = R::findOne('usernamelist', 
                'username = ? AND list_type = ?', 
                [$username, $list_type]
            );
            
            error_log('Existing entry found: ' . ($existing ? 'yes (id=' . $existing->id . ')' : 'no'));
            
            if ($existing) {
                // Update existing entry
                $existing->active = 1;
                $existing->reason = $reason;
                $existing->added_by = $added_by;
                $existing->added_date = date('Y-m-d H:i:s');
                $id = R::store($existing);
                error_log('Updated existing entry with id: ' . $id);
            } else {
                // Create new entry
                $userlist = R::dispense('usernamelist');
                $userlist->username = $username;
                $userlist->list_type = $list_type;
                $userlist->reason = $reason;
                $userlist->added_by = $added_by;
                $userlist->added_date = date('Y-m-d H:i:s');
                $userlist->active = 1;
                $id = R::store($userlist);
                error_log('Created new entry with id: ' . $id);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to add username to list - Exception: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Get denied attempts log
     * 
     * @param int $limit Number of records to retrieve
     * @param int $offset Starting offset
     * @return array Failed login attempts
     */
    public static function getDeniedAttemptsLog($limit = 100, $offset = 0) {
        try {
            $attempts = R::find('loginattempt', 
                'success = 0 ORDER BY attempt_time DESC LIMIT ?,?', 
                [$offset, $limit]
            );
            
            // Convert beans to array for backward compatibility
            $result = [];
            foreach ($attempts as $attempt) {
                $result[] = $attempt->export();
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Failed to get denied attempts log: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active lockouts
     * 
     * @return array Active lockout records
     */
    public static function getActiveLockouts() {
        try {
            $now = date('Y-m-d H:i:s');
            $lockouts = R::find('lockout', 
                'active = 1 AND unlockTime > ? ORDER BY lockoutTime DESC', 
                [$now]
            );
            
            // Convert beans to array for backward compatibility
            $result = [];
            foreach ($lockouts as $lockout) {
                $result[] = $lockout->export();
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Failed to get active lockouts: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Notify admin about lockout (placeholder for email functionality)
     * 
     * @param string $ip IP address
     * @param string|null $username Username
     * @param string $reason Lockout reason
     */
    private static function notifyAdminLockout($ip, $username, $reason) {
        $admin_email = self::getSetting('admin_email', '');
        if (empty($admin_email)) {
            return;
        }
        
        // TODO: Implement email notification using Isotone's mail system
        error_log("Lockout notification: IP=$ip, Username=$username, Reason=$reason");
    }
    
    /**
     * Plugin Developer Helper Methods
     * These methods make it easy for plugin developers to work with login security
     */
    
    /**
     * Check if an IP can attempt login
     * 
     * @param string $ip IP address
     * @return bool True if login attempt is allowed
     */
    public static function canAttemptLogin($ip) {
        $status = self::checkBruteForce($ip);
        return !$status['blocked'];
    }
    
    /**
     * Record a custom security event (for plugins)
     * 
     * @param string $event_type Type of security event
     * @param array $data Event data
     * @return bool Success status
     */
    public static function recordSecurityEvent($event_type, array $data = []) {
        try {
            $event = R::dispense('securityevent');
            $event->eventType = $event_type;
            $event->eventData = json_encode($data);
            $event->ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $event->eventTime = date('Y-m-d H:i:s');
            
            R::store($event);
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to record security event: ' . $e->getMessage());
            return false;
        }
    }
}

// Initialize hooks when class is loaded
LoginSecurity::initializeHooks();
?>
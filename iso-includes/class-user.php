<?php
/**
 * User Management Class using RedBeanPHP
 * 
 * @package Isotone
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config.php';

use RedBeanPHP\R;

class IsotoneUser {
    
    public function __construct() {
        // Setup RedBeanPHP if not already connected
        if (!R::testConnection()) {
            R::setup('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
        }
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username Username or email
     * @param string $password Plain text password
     * @return array|false User data or false on failure
     */
    public function authenticate($username, $password) {
        try {
            // Find user by username or email
            $user = R::findOne('users', 
                '(username = ? OR email = ?) AND status = ?', 
                [$username, $username, 'active']
            );
            
            if ($user && password_verify($password, $user->password)) {
                // Update last login
                $user->last_login = date('Y-m-d H:i:s');
                R::store($user);
                
                // Convert to array and remove password
                $userData = $user->export();
                unset($userData['password']);
                return $userData;
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Authentication error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return int|false User ID or false on failure
     */
    public function create($data) {
        try {
            $user = R::dispense('users');
            
            // Set user properties
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
            $user->display_name = $data['display_name'] ?? $data['username'];
            $user->role = $data['role'] ?? 'subscriber';
            $user->status = $data['status'] ?? 'active';
            $user->created_at = date('Y-m-d H:i:s');
            $user->bio = $data['bio'] ?? null;
            
            $id = R::store($user);
            return $id;
        } catch (Exception $e) {
            error_log('User creation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user data
     * 
     * @param int $id User ID
     * @param array $data Data to update
     * @return bool Success
     */
    public function update($id, $data) {
        try {
            $user = R::load('users', $id);
            
            if (!$user->id) {
                return false;
            }
            
            // Update allowed fields
            if (isset($data['username'])) $user->username = $data['username'];
            if (isset($data['email'])) $user->email = $data['email'];
            if (isset($data['password'])) {
                $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            if (isset($data['display_name'])) $user->display_name = $data['display_name'];
            if (isset($data['role'])) $user->role = $data['role'];
            if (isset($data['status'])) $user->status = $data['status'];
            if (isset($data['bio'])) $user->bio = $data['bio'];
            
            $user->updated_at = date('Y-m-d H:i:s');
            
            R::store($user);
            return true;
        } catch (Exception $e) {
            error_log('User update error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false
     */
    public function getById($id) {
        try {
            $user = R::load('users', $id);
            
            if (!$user->id) {
                return false;
            }
            
            $userData = $user->export();
            unset($userData['password']);
            return $userData;
        } catch (Exception $e) {
            error_log('Get user error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return array|false User data or false
     */
    public function getByUsername($username) {
        try {
            $user = R::findOne('users', 'username = ?', [$username]);
            
            if (!$user) {
                return false;
            }
            
            $userData = $user->export();
            unset($userData['password']);
            return $userData;
        } catch (Exception $e) {
            error_log('Get user error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users
     * 
     * @param array $filters Optional filters
     * @return array Users
     */
    public function getAll($filters = []) {
        try {
            $where = [];
            $bindings = [];
            
            if (!empty($filters['role'])) {
                $where[] = 'role = ?';
                $bindings[] = $filters['role'];
            }
            
            if (!empty($filters['status'])) {
                $where[] = 'status = ?';
                $bindings[] = $filters['status'];
            }
            
            $whereClause = !empty($where) ? implode(' AND ', $where) : '1';
            
            $users = R::findAll('user', $whereClause . ' ORDER BY created_at DESC', $bindings);
            
            $result = [];
            foreach ($users as $user) {
                $userData = $user->export();
                unset($userData['password']);
                $result[] = $userData;
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Get users error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete user
     * 
     * @param int $id User ID
     * @return bool Success
     */
    public function delete($id) {
        try {
            $user = R::load('users', $id);
            
            if (!$user->id) {
                return false;
            }
            
            R::trash($user);
            return true;
        } catch (Exception $e) {
            error_log('Delete user error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if username exists
     * 
     * @param string $username Username
     * @return bool
     */
    public function usernameExists($username) {
        try {
            $count = R::count('user', 'username = ?', [$username]);
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email
     * @return bool
     */
    public function emailExists($email) {
        try {
            $count = R::count('user', 'email = ?', [$email]);
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Verify user has role
     * 
     * @param int $userId User ID
     * @param string $role Required role
     * @return bool
     */
    public function hasRole($userId, $role) {
        try {
            $user = R::load('users', $userId);
            
            if (!$user->id) {
                return false;
            }
            
            // Role hierarchy
            $roles = [
                'subscriber' => 1,
                'contributor' => 2,
                'author' => 3,
                'editor' => 4,
                'admin' => 5,
                'superadmin' => 6  // Changed from super_admin to match install script
            ];
            
            $userLevel = $roles[$user->role] ?? 0;
            $requiredLevel = $roles[$role] ?? 0;
            
            return $userLevel >= $requiredLevel;
        } catch (Exception $e) {
            return false;
        }
    }
}
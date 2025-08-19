<?php
/**
 * Secure Login Page with Rate Limiting
 * 
 * @package Isotone
 */

// Load security class
require_once dirname(__DIR__) . '/iso-includes/class-security.php';

// Start secure session
IsotoneeSecurity::secureSession();

// Check for brute force
$brute_check = IsotoneeSecurity::checkBruteForce();
if ($brute_check['blocked']) {
    $error_message = $brute_check['message'];
    $blocked = true;
} else {
    $blocked = false;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
    // Verify CSRF token
    if (!iso_verify_csrf()) {
        $error_message = 'Security token invalid. Please refresh and try again.';
    } else {
        // Get credentials
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Load config and user class
        require_once dirname(__DIR__) . '/config.php';
        require_once dirname(__DIR__) . '/iso-includes/class-user.php';
        require_once dirname(__DIR__) . '/iso-includes/database.php';
        
        // Connect to database
        isotone_db_connect();
        
        // Attempt login
        $user = new IsotoneUser();
        $login_result = $user->login($username, $password);
        
        if ($login_result['success']) {
            // Record successful login
            IsotoneeSecurity::recordLoginAttempt($username, true);
            IsotoneeSecurity::logSecurityEvent('login_success', [
                'user' => $username
            ]);
            
            // Regenerate session ID on login
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['isotone_admin_logged_in'] = true;
            $_SESSION['isotone_admin_user'] = $username;
            $_SESSION['isotone_admin_user_id'] = $login_result['user_id'];
            $_SESSION['isotone_admin_user_data'] = $login_result['user_data'] ?? [];
            $_SESSION['isotone_admin_last_activity'] = time();
            $_SESSION['fingerprint'] = IsotoneeSecurity::generateFingerprint();
            
            // Handle remember me (secure cookie)
            if ($remember) {
                $token = IsotoneeSecurity::generateToken();
                $cookie_data = $username . '|' . $token;
                $cookie_hash = hash_hmac('sha256', $cookie_data, SECURE_AUTH_KEY);
                
                // Set secure cookie for 30 days
                setcookie(
                    'isotone_remember',
                    base64_encode($cookie_data . '|' . $cookie_hash),
                    time() + (30 * 24 * 3600),
                    '/isotone/',
                    '',
                    isset($_SERVER['HTTPS']),
                    true
                );
                
                // Store token hash in database for validation
                // TODO: Add remember_tokens table
            }
            
            // Redirect to dashboard or requested page
            $redirect = $_GET['redirect'] ?? '/isotone/iso-admin/dashboard.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            // Record failed login
            IsotoneeSecurity::recordLoginAttempt($username, false);
            IsotoneeSecurity::logSecurityEvent('login_failed', [
                'user' => $username,
                'reason' => $login_result['error'] ?? 'Invalid credentials'
            ]);
            
            $error_message = 'Invalid username or password.';
        }
    }
}

// Check for logout
if (isset($_GET['logout'])) {
    IsotoneeSecurity::logSecurityEvent('logout', [
        'user' => $_SESSION['isotone_admin_user'] ?? 'unknown'
    ]);
    
    session_unset();
    session_destroy();
    
    // Clear remember cookie
    setcookie('isotone_remember', '', time() - 3600, '/isotone/');
    
    $success_message = 'You have been logged out successfully.';
}

// Check for session expiry
if (isset($_GET['expired'])) {
    $error_message = 'Your session has expired. Please login again.';
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Isotone Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#00D9FF',
                        secondary: '#00FF88'
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gradient-to-br from-gray-900 via-blue-900 to-gray-900">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo/Title -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">Isotone</h1>
                <p class="text-gray-300">Admin Panel</p>
            </div>
            
            <!-- Login Form -->
            <div class="bg-white/10 backdrop-blur-lg rounded-lg shadow-2xl p-8">
                <?php if (isset($error_message)): ?>
                <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($success_message)): ?>
                <div class="bg-green-500/20 border border-green-500 text-green-200 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!$blocked): ?>
                <form method="POST" action="" class="space-y-6">
                    <?php echo iso_csrf_field(); ?>
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-200">
                            Username or Email
                        </label>
                        <input 
                            type="text" 
                            name="username" 
                            id="username"
                            required
                            autocomplete="username"
                            class="mt-1 block w-full px-3 py-2 bg-white/10 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Enter your username"
                        >
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-200">
                            Password
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            required
                            autocomplete="current-password"
                            class="mt-1 block w-full px-3 py-2 bg-white/10 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Enter your password"
                        >
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember"
                                class="h-4 w-4 text-primary focus:ring-primary border-gray-600 rounded bg-white/10"
                            >
                            <label for="remember" class="ml-2 block text-sm text-gray-200">
                                Remember me
                            </label>
                        </div>
                        
                        <a href="forgot-password.php" class="text-sm text-primary hover:text-secondary">
                            Forgot password?
                        </a>
                    </div>
                    
                    <button 
                        type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-900 bg-gradient-to-r from-primary to-secondary hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        Sign in
                    </button>
                </form>
                <?php else: ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p class="text-red-300 mb-4"><?php echo htmlspecialchars($error_message); ?></p>
                    <p class="text-gray-400 text-sm">Please wait before trying again.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> Isotone. All rights reserved.
                </p>
            </div>
        </div>
    </div>
    
    <?php if ($blocked): ?>
    <script>
        // Auto-refresh page after block expires
        setTimeout(function() {
            window.location.reload();
        }, <?php echo ($brute_check['wait_time'] ?? 60) * 1000; ?>);
    </script>
    <?php endif; ?>
</body>
</html>
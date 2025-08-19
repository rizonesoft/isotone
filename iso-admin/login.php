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

// Ensure CSRF token exists for the login form
// This must happen BEFORE any logout/session destruction
if (empty($_SESSION['csrf_token'])) {
    IsotoneeSecurity::generateCSRFToken();
}

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
        $userData = $user->authenticate($username, $password);
        
        if ($userData) {
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
            $_SESSION['isotone_admin_user_id'] = $userData['id'];
            $_SESSION['isotone_admin_user_data'] = $userData;
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
                'reason' => 'Invalid credentials'
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
    
    // Preserve rate limiting data before destroying session
    $preserved_data = [];
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'login_attempts_') === 0) {
            $preserved_data[$key] = $value;
        }
    }
    
    // Clear session
    session_unset();
    session_destroy();
    
    // Clear remember cookie
    setcookie('isotone_remember', '', time() - 3600, '/isotone/');
    
    // Restart session for the login form
    IsotoneeSecurity::secureSession();
    
    // Restore rate limiting data
    foreach ($preserved_data as $key => $value) {
        $_SESSION[$key] = $value;
    }
    
    // Generate new CSRF token for the login form
    IsotoneeSecurity::generateCSRFToken();
    
    $success_message = 'You have been logged out successfully.';
}

// Check for session expiry
if (isset($_GET['expired'])) {
    $error_message = 'Your session has expired. Please login again.';
    // Ensure we have a CSRF token for the form
    if (empty($_SESSION['csrf_token'])) {
        IsotoneeSecurity::generateCSRFToken();
    }
}

// Check for session invalid error
if (isset($_GET['error']) && $_GET['error'] === 'session_invalid') {
    $error_message = 'Your session was invalid. Please login again.';
    // Ensure we have a CSRF token for the form
    if (empty($_SESSION['csrf_token'])) {
        IsotoneeSecurity::generateCSRFToken();
    }
}

// Final check: Always ensure CSRF token exists before displaying the form
if (empty($_SESSION['csrf_token'])) {
    IsotoneeSecurity::generateCSRFToken();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Isotone Admin</title>
    
    <!-- Import Inter font like the theme -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Use exact same variables as theme */
        :root {
            --primary: #0A0E27;
            --primary-dark: #060815;
            --accent: #00D9FF;
            --accent-green: #00FF88;
            --danger: #FF3366;
            
            --text-primary: #FFFFFF;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-muted: rgba(255, 255, 255, 0.5);
            
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 0.75rem;
            --space-lg: 1rem;
            --space-xl: 1.5rem;
            --space-2xl: 2rem;
            --space-3xl: 3rem;
            
            --radius-sm: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            
            --font-weight-normal: 400;
            --font-weight-medium: 500;
            --font-weight-semibold: 600;
            --font-weight-bold: 700;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            letter-spacing: 0.01em;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.02em;
        }
        
        /* Gradient overlay like the theme */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(0, 217, 255, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(0, 255, 136, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        
        /* Login Container */
        .login-wrapper {
            width: 100%;
            max-width: 450px;
            padding: var(--space-xl);
        }
        
        /* Logo - matching theme header logo */
        .logo-section {
            text-align: center;
            margin-bottom: var(--space-3xl);
        }
        
        .logo {
            display: inline-flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-md);
        }
        
        .logo img {
            width: 40px;
            height: 40px;
            filter: drop-shadow(0 0 10px rgba(0, 217, 255, 0.6));
        }
        
        .logo span {
            font-size: 1.5rem;
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            letter-spacing: 0.02em;
        }
        
        /* Glass card - subtle hover effect */
        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: var(--space-2xl);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .login-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 217, 255, 0.15);
            border-color: rgba(0, 217, 255, 0.3);
        }
        
        /* Form styling */
        .form-group {
            margin-bottom: var(--space-xl);
        }
        
        label {
            display: block;
            margin-bottom: var(--space-sm);
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: var(--font-weight-medium);
            letter-spacing: 0.03em;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: var(--space-md) var(--space-lg);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            letter-spacing: 0.02em;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }
        
        input::-webkit-input-placeholder {
            color: var(--text-muted);
            font-weight: 300 !important;
            opacity: 0.7;
            letter-spacing: 0.02em;
        }
        
        input::-moz-placeholder {
            color: var(--text-muted);
            font-weight: 300 !important;
            opacity: 0.7;
            letter-spacing: 0.02em;
        }
        
        input:-ms-input-placeholder {
            color: var(--text-muted);
            font-weight: 300 !important;
            opacity: 0.7;
            letter-spacing: 0.02em;
        }
        
        input:-moz-placeholder {
            color: var(--text-muted);
            font-weight: 300 !important;
            opacity: 0.7;
            letter-spacing: 0.02em;
        }
        
        input::placeholder {
            color: var(--text-muted);
            font-weight: 300 !important;
            opacity: 0.7;
            letter-spacing: 0.02em;
        }
        
        /* Checkbox and remember me */
        .remember-section {
            margin-bottom: var(--space-xl);
        }
        
        .remember-label {
            display: flex;
            align-items: flex-start;
            gap: var(--space-sm);
            color: var(--text-secondary);
            font-size: 0.875rem;
            cursor: pointer;
        }
        
        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
        }
        
        .forgot-link {
            color: var(--accent);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s ease;
        }
        
        .forgot-link:hover {
            color: var(--accent-green);
        }
        
        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: var(--space-md) var(--space-xl);
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-green) 100%);
            border: none;
            border-radius: var(--radius-sm);
            color: var(--primary);
            font-size: 1rem;
            font-weight: var(--font-weight-semibold);
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.03em;
        }
        
        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .submit-btn:hover::before {
            left: 100%;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.3);
        }
        
        /* Alert messages */
        .alert {
            padding: var(--space-md) var(--space-lg);
            border-radius: var(--radius-sm);
            margin-bottom: var(--space-xl);
            font-size: 0.875rem;
        }
        
        .alert-error {
            background: rgba(255, 51, 102, 0.1);
            border: 1px solid rgba(255, 51, 102, 0.3);
            color: #ff6b9d;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: var(--accent-green);
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: var(--space-2xl);
            color: var(--text-muted);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Logo section -->
        <div class="logo-section">
            <div class="logo">
                <!-- Using favicon as logo like theme does -->
                <img src="/isotone/favicon.png" alt="Isotone">
                <span>Isotone</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">Admin Portal</p>
        </div>
        
        <!-- Login card with glass morphism -->
        <div class="login-card">
            <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>
                
            <?php if (!$blocked): ?>
            <form method="POST" action="" id="loginForm">
                <?php echo iso_csrf_field(); ?>
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input 
                        type="text" 
                        name="username" 
                        id="username"
                        required
                        autocomplete="username"
                        placeholder="Enter your username"
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                </div>
                
                <div class="remember-section">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me for 30 days<br>
                        <a href="forgot-password.php" class="forgot-link" style="font-size: 0.813rem; margin-top: 4px; display: inline-block;">Forgot password?</a></span>
                    </label>
                </div>
                
                <button type="submit" class="submit-btn">
                    Sign in to Dashboard
                </button>
            </form>
            <?php else: ?>
            <div style="text-align: center; padding: var(--space-2xl) 0;">
                <div style="width: 64px; height: 64px; margin: 0 auto var(--space-lg);">
                    <svg style="width: 100%; height: 100%; color: var(--danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <p style="color: var(--danger); margin-bottom: var(--space-md); font-weight: var(--font-weight-semibold);">
                    <?php echo htmlspecialchars($error_message); ?>
                </p>
                <p style="color: var(--text-muted); font-size: var(--text-sm);">
                    The page will refresh automatically when the lockout expires.
                </p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Isotone. All rights reserved.</p>
        </div>
    </div>
    
    <script>
        // Form submission handling with loading state
        const form = document.getElementById('loginForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                // Add a subtle loading effect to the button
                const button = form.querySelector('.submit-btn');
                button.style.opacity = '0.7';
                button.style.pointerEvents = 'none';
                button.textContent = 'Signing in...';
            });
        }
        
        <?php if ($blocked): ?>
        // Auto-refresh page after block expires
        setTimeout(function() {
            window.location.reload();
        }, <?php echo ($brute_check['wait_time'] ?? 60) * 1000; ?>);
        <?php endif; ?>
    </script>
</body>
</html>
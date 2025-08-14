<?php
/**
 * Admin Login Page
 * 
 * @package Isotone
 */

session_start();

// Load configuration and user class
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/iso-includes/class-user.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['isotone_admin_logged_in']) && $_SESSION['isotone_admin_logged_in'] === true) {
    header('Location: /isotone/iso-admin/');
    exit;
}

// Handle login form submission
$error = '';
$success = '';

// Check for logout message
if (isset($_GET['logout'])) {
    $success = 'You have been successfully logged out.';
}

// Check for session expired message
if (isset($_GET['expired'])) {
    $error = 'Your session has expired. Please log in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Authenticate user
        $userObj = new IsotoneUser();
        $user = $userObj->authenticate($username, $password);
        
        if ($user) {
            // Check if user has admin privileges
            if ($userObj->hasRole($user['id'], 'editor')) {
                // Set session variables
                $_SESSION['isotone_admin_logged_in'] = true;
                $_SESSION['isotone_admin_user'] = $user['username'];
                $_SESSION['isotone_admin_user_id'] = $user['id'];
                $_SESSION['isotone_admin_user_data'] = $user;
                $_SESSION['isotone_admin_last_activity'] = time();
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('isotone_remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                    // TODO: Store token in database for validation
                }
                
                // Redirect to dashboard or return URL
                $redirect = $_GET['redirect'] ?? '/isotone/iso-admin/';
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'You do not have permission to access the admin area.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Isotone</title>
    
    <!-- Isotone Custom CSS -->
    <link rel="stylesheet" href="/isotone/iso-includes/css/isotone.css">
    
    <!-- Favicon -->
    <link rel="icon" href="/isotone/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/isotone/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/isotone/favicon-16x16.png">
    
</head>
<body class="iso-app">
    <div class="iso-container iso-container-sm">
        <div class="iso-header" style="justify-content: center; margin-bottom: 2rem; gap: 0.5rem;">
            <!-- Isotone Logo -->
            <div class="iso-header-logo">
                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="isotone-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#00D9FF;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#00FF88;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <g fill="url(#isotone-gradient)">
                        <path clip-rule="evenodd" d="m12.7712 5.04198c-1.2919-.14261-2.598.07714-3.77211.63464-1.17413.55751-2.16993 1.43077-2.87596 2.52206-.70603 1.09128-1.09442 2.35752-1.12171 3.65702s.30761 2.5809.96721 3.7008c.28027.4759.12169 1.0889-.3542 1.3692-.47588.2803-1.08887.1217-1.36914-.3542-.84797-1.4398-1.27851-3.0872-1.24343-4.7578s.5344-3.29848 1.44206-4.70142c.90767-1.40295 2.18787-2.52561 3.69731-3.24233 1.50945-.71673 3.18857-.99923 4.84947-.8159.5489.0606.9448.55473.8842 1.10369-.0606.54895-.5547.94483-1.1037.88424zm5.6143 2.03237c.4759-.28027 1.0889-.12169 1.3691.35419.848 1.43982 1.2785 3.08726 1.2435 4.75776-.0351 1.6706-.5344 3.2985-1.4421 4.7015-.9077 1.4029-2.1879 2.5256-3.6973 3.2423-1.5095.7167-3.1886.9992-4.8495.8159-.5489-.0606-.9448-.5548-.8842-1.1037.0606-.549.5547-.9448 1.1037-.8842 1.2919.1426 2.598-.0772 3.7721-.6347 1.1742-.5575 2.17-1.4308 2.876-2.522.706-1.0913 1.0944-2.3576 1.1217-3.657.0273-1.2995-.3076-2.58095-.9672-3.70091-.2803-.47588-.1217-1.08887.3542-1.36914z" fill-rule="evenodd"></path>
                        <path d="m16.2428 7.75723c.781.78105 2.0473.78105 2.8284 0 .781-.78105.781-2.04738 0-2.82843-.7811-.78104-2.0474-.78104-2.8284 0-.7811.78105-.7811 2.04738 0 2.82843z"></path>
                        <path clip-rule="evenodd" d="m18.3641 5.63591c-.3905-.39052-1.0237-.39052-1.4142 0-.3905.39053-.3905 1.02369 0 1.41421.3905.39053 1.0237.39053 1.4142 0 .3905-.39052.3905-1.02368 0-1.41421zm-2.8284-1.41421c1.1715-1.17158 3.071-1.17158 4.2426 0 1.1716 1.17157 1.1716 3.07106 0 4.24264-1.1716 1.17157-3.0711 1.17157-4.2426 0-1.1716-1.17157-1.1716-3.07107 0-4.24264z" fill-rule="evenodd"></path>
                        <path d="m4.9288 19.0712c.78105.781 2.04738.781 2.82843 0 .78105-.7811.78105-2.0474 0-2.8284-.78105-.7811-2.04738-.7811-2.82843 0-.78104.781-.78104 2.0473 0 2.8284z"></path>
                        <path clip-rule="evenodd" d="m7.05012 16.9499c-.39052-.3905-1.02368-.3905-1.41421 0-.39052.3905-.39052 1.0237 0 1.4142.39053.3905 1.02369.3905 1.41421 0 .39053-.3905.39053-1.0237 0-1.4142zm-2.82842-1.4142c1.17157-1.1716 3.07106-1.1716 4.24264 0 1.17157 1.1715 1.17157 3.071 0 4.2426s-3.07107 1.1716-4.24264 0c-1.17158-1.1716-1.17158-3.0711 0-4.2426z" fill-rule="evenodd"></path>
                        <path d="m10.5858 13.4142c.781.7811 2.0474.7811 2.8284 0 .7811-.781.7811-2.0474 0-2.8284-.781-.78106-2.0474-.78106-2.8284 0-.78106.781-.78106 2.0474 0 2.8284z"></path>
                        <path clip-rule="evenodd" d="m12.7071 11.2929c-.3905-.3905-1.0237-.3905-1.4142 0s-.3905 1.0237 0 1.4142 1.0237.3905 1.4142 0 .3905-1.0237 0-1.4142zm-2.82842-1.41422c1.17162-1.17157 3.07102-1.17157 4.24262 0 1.1716 1.17162 1.1716 3.07102 0 4.24262s-3.071 1.1716-4.24262 0c-1.17157-1.1716-1.17157-3.071 0-4.24262z" fill-rule="evenodd"></path>
                    </g>
                </svg>
            </div>
            <h1 class="iso-title iso-title-lg">Isotone</h1>
        </div>
        
        <div class="iso-content">
            <p class="iso-subtitle">Sign in to your admin panel</p>
        </div>
        
        <form method="POST">
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-lg">
                <label for="username" class="iso-label">Username or Email</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="iso-input" 
                    placeholder="Enter your username"
                    required
                    autofocus
                >
            </div>
            
            <div class="mb-lg">
                <label for="password" class="iso-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="iso-input" 
                    placeholder="Enter your password"
                    required
                >
            </div>
            
            <div class="mb-lg" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" id="remember" name="remember" style="width: 18px; height: 18px;">
                <label for="remember" style="color: var(--text-secondary); font-size: 0.875rem;">Remember me</label>
            </div>
            
            <button type="submit" class="iso-btn iso-btn-primary" style="width: 100%;">
                Sign In
            </button>
        </form>
        
        <div class="iso-links">
            <a href="/isotone/">← Back to Site</a>
            <a href="#">Forgot Password?</a>
        </div>
    </div>
</body>
</html>
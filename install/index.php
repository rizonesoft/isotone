<?php
/**
 * Isotone CMS - Installation Wizard
 * Sets up initial Super Admin account and database
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ISOTONE_ROOT', dirname(__DIR__));
require_once ISOTONE_ROOT . '/vendor/autoload.php';

use Isotone\Services\DatabaseService;
use RedBeanPHP\R;

// Helper function for env vars
if (!function_exists('env')) {
    function env($key, $default = null) {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }
}

// Check if already installed
$installFile = ISOTONE_ROOT . '/.isotone-installed';
$isInstalled = file_exists($installFile);

// Load environment if exists
if (file_exists(ISOTONE_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ISOTONE_ROOT);
    $dotenv->safeLoad();
}

// Handle form submission
$message = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isInstalled) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'test_connection') {
        // Test database connection
        header('Content-Type: application/json');
        try {
            if (DatabaseService::initialize()) {
                echo json_encode(['success' => true, 'message' => 'Database connected successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not connect to database. Check your settings.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'install') {
        // Validate inputs
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            try {
                // Initialize database
                if (!DatabaseService::initialize()) {
                    throw new Exception('Could not connect to database');
                }
                
                // Check if admin already exists
                $existingAdmin = R::findOne('isotoneuser', 'role = ?', ['superadmin']);
                
                if (!$existingAdmin) {
                    // Create super admin user
                    $admin = R::dispense('isotoneuser');
                    $admin->username = $username;
                    $admin->email = $email;
                    $admin->password = password_hash($password, PASSWORD_DEFAULT);
                    $admin->role = 'superadmin';
                    $admin->status = 'active';
                    $admin->created_at = date('Y-m-d H:i:s');
                    $admin->updated_at = date('Y-m-d H:i:s');
                    $adminId = R::store($admin);
                    
                    // Create initial settings
                    $settings = [
                        'site_title' => 'Isotone CMS',
                        'site_description' => 'Lightweight. Powerful. Everywhere.',
                        'admin_email' => $email,
                        'timezone' => 'UTC',
                        'date_format' => 'Y-m-d',
                        'time_format' => 'H:i:s'
                    ];
                    
                    foreach ($settings as $key => $value) {
                        $setting = R::findOne('isotonesetting', 'key = ?', [$key]);
                        if (!$setting) {
                            $setting = R::dispense('isotonesetting');
                            $setting->key = $key;
                        }
                        $setting->value = $value;
                        $setting->type = 'string';
                        $setting->updated_at = date('Y-m-d H:i:s');
                        R::store($setting);
                    }
                    
                    // Create installation marker
                    file_put_contents($installFile, json_encode([
                        'installed_at' => date('Y-m-d H:i:s'),
                        'version' => '0.1.2-alpha',
                        'admin_user' => $username
                    ]));
                    
                    $success = true;
                    $message = 'Installation complete! You can now log in with your credentials.';
                    
                    // Clear session
                    session_destroy();
                    
                } else {
                    $error = 'Super admin already exists. Delete .isotone-installed to reinstall.';
                }
                
            } catch (Exception $e) {
                $error = 'Installation failed: ' . $e->getMessage();
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

// Get database status
$dbConnected = false;
$dbInfo = '';
try {
    // Try to initialize connection first
    $dbConnected = DatabaseService::initialize();
    if ($dbConnected) {
        $status = DatabaseService::getStatus();
        $dbInfo = $status['database'] ?? 'Unknown';
    } else {
        // Fallback: try simple PDO connection
        $host = $_ENV['DB_HOST'] ?? env('DB_HOST', 'localhost');
        $port = $_ENV['DB_PORT'] ?? env('DB_PORT', '3306');
        $database = $_ENV['DB_DATABASE'] ?? env('DB_DATABASE', 'isotone_db');
        $username = $_ENV['DB_USERNAME'] ?? env('DB_USERNAME', 'root');
        $password = $_ENV['DB_PASSWORD'] ?? env('DB_PASSWORD', '');
        
        try {
            $dsn = "mysql:host=127.0.0.1;port=$port;dbname=$database;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password);
            $dbConnected = true;
            $dbInfo = $database;
        } catch (PDOException $e) {
            $dbInfo = 'Not connected';
        }
    }
} catch (Exception $e) {
    $dbInfo = 'Not connected';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isotone CMS - Installation</title>
    <link rel="icon" type="image/png" sizes="512x512" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <link rel="manifest" href="../site.webmanifest">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
        
        :root {
            --primary: #0A0E27;
            --accent: #00D9FF;
            --accent-green: #00FF88;
            --text-primary: #E2E8F0;
            --text-secondary: #94A3B8;
            --border: rgba(71, 85, 105, 0.3);
            --success: #00FF88;
            --warning: #FFB800;
            --danger: #FF453A;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0A0E27 0%, #0F1433 50%, #0A0E27 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
            letter-spacing: 0.01em;
        }
        
        /* Subtle static gradient overlay */
        body::before {
            content: '';
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: 
                radial-gradient(ellipse at top left, rgba(0, 217, 255, 0.08) 0%, transparent 40%),
                radial-gradient(ellipse at bottom right, rgba(0, 255, 136, 0.08) 0%, transparent 40%);
            pointer-events: none;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            position: relative;
            border: 1px solid var(--border);
            box-shadow: 
                0 0 0 1px rgba(0, 217, 255, 0.1),
                0 10px 40px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        h1 {
            font-size: 3rem;
            font-weight: 900;
            margin: 0;
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
            background: linear-gradient(135deg, #FFFFFF 0%, #00D9FF 50%, #00FF88 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 4s ease-in-out infinite;
            background-size: 200% 200%;
        }
        
        @keyframes shimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .subtitle {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
            letter-spacing: 0.02em;
        }
        
        input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }
        
        input::placeholder {
            color: var(--text-secondary);
            opacity: 0.5;
        }
        
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .btn {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--accent), var(--accent-green));
            color: var(--primary);
            border: none;
            text-decoration: none;
            border-radius: 12px;
            margin-top: 1rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 
                0 4px 20px rgba(0, 217, 255, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before:not(:disabled) {
            left: 100%;
        }
        
        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 
                0 6px 30px rgba(0, 217, 255, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        
        .btn.has-arrow::after {
            content: '→';
            font-size: 1.2rem;
            margin-left: 0.5rem;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: rgba(0, 0, 0, 0.3);
            color: var(--text-primary);
            border: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        
        .status {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .status.success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: var(--success);
        }
        
        .status.error {
            background: rgba(255, 69, 58, 0.1);
            border: 1px solid rgba(255, 69, 58, 0.3);
            color: var(--danger);
        }
        
        .status.info {
            background: rgba(0, 217, 255, 0.1);
            border: 1px solid rgba(0, 217, 255, 0.3);
            color: var(--accent);
        }
        
        .db-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge.connected {
            background: rgba(0, 255, 136, 0.1);
            color: var(--success);
            border: 1px solid rgba(0, 255, 136, 0.3);
        }
        
        .badge.disconnected {
            background: rgba(255, 69, 58, 0.1);
            color: var(--danger);
            border: 1px solid rgba(255, 69, 58, 0.3);
        }
        
        .installed {
            text-align: center;
            padding: 3rem;
        }
        
        .installed h2 {
            color: var(--success);
            margin-bottom: 1rem;
        }
        
        .links {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
            text-align: center;
        }
        
        .links a {
            color: var(--accent);
            text-decoration: none;
            margin: 0 1rem;
            transition: color 0.3s ease;
        }
        
        .links a:hover {
            color: var(--accent-green);
        }
        
        /* Static grid decoration */
        .grid-bg {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background-image: 
                linear-gradient(rgba(0, 217, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 217, 255, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
            opacity: 0.5;
        }
        
        /* Logo styling */
        .logo-icon {
            width: 60px;
            height: 60px;
            filter: drop-shadow(0 0 20px rgba(0, 217, 255, 0.5));
            animation: pulse 2s ease-in-out infinite;
            margin: 0 auto 1.5rem;
            display: block;
        }
        
        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                filter: drop-shadow(0 0 20px rgba(0, 217, 255, 0.5));
            }
            50% { 
                transform: scale(1.05);
                filter: drop-shadow(0 0 30px rgba(0, 255, 136, 0.6));
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
                min-height: 100vh;
                overflow-y: scroll;
                -webkit-overflow-scrolling: touch;
            }
            
            .container {
                padding: 2rem 1.5rem;
                max-width: 95%;
                margin: 1rem auto;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .logo-icon {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="grid-bg"></div>
    <div class="container">
        <?php if ($isInstalled): ?>
            <div class="installed">
                <img src="../assets/logo.svg" alt="Isotone" class="logo-icon">
                <h1>Already Installed</h1>
                <p class="subtitle">Isotone CMS is already installed on this system.</p>
                <div class="status info">
                    To reinstall, delete the .isotone-installed file in the root directory.
                </div>
                <div class="links">
                    <a href="../">Home</a>
                    <a href="../admin">Admin Panel</a>
                </div>
            </div>
        <?php elseif ($success): ?>
            <div class="installed">
                <img src="../assets/logo.svg" alt="Isotone" class="logo-icon">
                <h1>Installation Complete!</h1>
                <p class="subtitle">Your Isotone CMS is ready to use.</p>
                <div class="status success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <a href="../admin" class="btn has-arrow">Go to Admin Panel</a>
                <div class="links">
                    <a href="../">View Site</a>
                </div>
            </div>
        <?php else: ?>
            <img src="../assets/images/logo.svg" alt="Isotone" class="logo-icon">
            <h1>Install Isotone</h1>
            <p class="subtitle">Set up your Super Admin account</p>
            
            <div class="db-status">
                <span>Database: <?php echo htmlspecialchars($dbInfo ?: 'Not configured'); ?></span>
                <span class="badge <?php echo $dbConnected ? 'connected' : 'disconnected'; ?>">
                    <?php echo $dbConnected ? 'Connected' : 'Disconnected'; ?>
                </span>
            </div>
            
            <?php if ($error): ?>
                <div class="status error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!$dbConnected): ?>
                <div class="status error">
                    Database connection required. Please configure your .env file first.
                </div>
                <button type="button" class="btn btn-secondary" onclick="testConnection()">
                    Test Database Connection
                </button>
            <?php endif; ?>
            
            <form method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="action" value="install">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Choose a username"
                        minlength="3"
                        required
                        <?php echo !$dbConnected ? 'disabled' : ''; ?>
                    >
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="admin@example.com"
                        required
                        <?php echo !$dbConnected ? 'disabled' : ''; ?>
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Choose a strong password"
                        minlength="8"
                        required
                        <?php echo !$dbConnected ? 'disabled' : ''; ?>
                    >
                    <div class="password-requirements">
                        Minimum 8 characters, recommended to use letters, numbers, and symbols
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Re-enter your password"
                        minlength="8"
                        required
                        <?php echo !$dbConnected ? 'disabled' : ''; ?>
                    >
                </div>
                
                <button type="submit" class="btn" <?php echo !$dbConnected ? 'disabled' : ''; ?>>
                    Install Isotone CMS
                </button>
            </form>
            
            <div class="links">
                <a href="../">Back to Home</a>
                <a href="https://github.com/rizonesoft/isotone" target="_blank">Documentation</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                alert('Password must be at least 8 characters!');
                return false;
            }
            
            return confirm('Ready to install Isotone CMS with these credentials?');
        }
        
        function testConnection() {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_connection'
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                alert('Error testing connection: ' + error);
            });
        }
    </script>
</body>
</html>
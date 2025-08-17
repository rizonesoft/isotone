<?php
/**
 * Isotone - Installation Wizard
 * Sets up initial Super Admin account and database
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ISOTONE_ROOT', dirname(__DIR__));
require_once ISOTONE_ROOT . '/vendor/autoload.php';

use Isotone\Services\DatabaseService;
use RedBeanPHP\R;

// Check if already installed
$installFile = ISOTONE_ROOT . '/.isotone-installed';
$isInstalled = file_exists($installFile);

// Load configuration if exists
if (file_exists(ISOTONE_ROOT . '/config.php')) {
    require_once ISOTONE_ROOT . '/config.php';
}

// Get system information for already installed page
use Isotone\Core\Version;
if ($isInstalled) {
    
    // Version information
    $isotonerVersion = Version::format();
    $versionStage = Version::getStage();
    $versionBadgeClass = match($versionStage) {
        'alpha' => 'iso-badge-info',
        'beta' => 'iso-badge-warning',
        'rc' => 'iso-badge-info',
        default => 'iso-badge-success'
    };
    
    // Database information
    $dbStatus = DatabaseService::getStatus();
    $dbConnected = $dbStatus['connected'];
    $dbBadgeClass = $dbConnected ? 'iso-badge-success' : 'iso-badge-danger';
    $dbStatusText = $dbConnected ? 'Connected' : 'Disconnected';
    
    // PHP Version
    $phpVersion = PHP_VERSION;
    $environment = ucfirst(defined('ENVIRONMENT') ? ENVIRONMENT : 'development');
    
    // Composer status
    $composerInstalled = file_exists(ISOTONE_ROOT . '/vendor/autoload.php');
    $composerStatus = $composerInstalled ? 'Installed' : 'Not installed';
    $composerBadgeClass = $composerInstalled ? 'iso-badge-success' : 'iso-badge-warning';
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
                $existingAdmin = R::findOne('users', 'role = ?', ['superadmin']);
                
                if (!$existingAdmin) {
                    // Create super admin user
                    $admin = R::dispense('users');
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
                        'site_title' => 'Isotone',
                        'site_description' => 'Lightweight. Powerful. Everywhere.',
                        'admin_email' => $email,
                        'timezone' => 'UTC',
                        'date_format' => 'Y-m-d',
                        'time_format' => 'H:i:s'
                    ];
                    
                    foreach ($settings as $key => $value) {
                        $setting = R::findOne('settings', 'setting_key = ?', [$key]);
                        if (!$setting) {
                            $setting = R::dispense('settings');
                            $setting->setting_key = $key;
                        }
                        $setting->setting_value = $value;
                        $setting->setting_type = 'string';
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
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $port = defined('DB_PORT') ? DB_PORT : 3306;
        $database = defined('DB_NAME') ? DB_NAME : 'isotone_db';
        $username = defined('DB_USER') ? DB_USER : 'root';
        $password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
        
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
    <title>Isotone - Installation</title>
    <link rel="icon" type="image/png" sizes="512x512" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <link rel="manifest" href="../site.webmanifest">
    <!-- Global Isotone CSS -->
    <link rel="stylesheet" href="../iso-includes/css/isotone.css">
    <style>
        /* Page-specific overrides only */
        
        /* Success page specific */
        .success-title {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .installed {
            text-align: center;
            padding: 3rem;
        }
        
        .installed .iso-status {
            margin: 2rem 0;
        }
        
        .installed .iso-btn {
            margin-top: 2rem;
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        /* Smaller heading for already installed */
        .installed-heading {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-top: 3rem;
            margin-bottom: 1rem;
            letter-spacing: 0.02em;
        }
        
        /* Password requirements hint */
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
    </style>
</head>
<body class="iso-app iso-background">
    <div class="grid-bg"></div>
    <div class="iso-container iso-container-md iso-animate-fadeInUp">
        <?php if ($isInstalled): ?>
            <div class="iso-brand-corner">
                <img src="../iso-includes/assets/logo.svg" alt="Isotone" class="logo">
                <span class="iso-brand-text">Isotone</span>
            </div>
            <div class="installed">
                <h2 class="installed-heading">Already Installed</h2>
                <p class="iso-subtitle">Isotone is already installed on this system.</p>
                
                <!-- System Status Grid -->
                <div class="iso-status-grid">
                    <div class="iso-status-item">
                        <span>Version</span>
                        <span class="iso-badge <?php echo $versionBadgeClass; ?>"><?php echo $isotonerVersion; ?></span>
                    </div>
                    <div class="iso-status-item">
                        <span>PHP Version</span>
                        <span class="iso-badge iso-badge-success"><?php echo $phpVersion; ?></span>
                    </div>
                    <div class="iso-status-item">
                        <span>Environment</span>
                        <span class="iso-badge iso-badge-warning"><?php echo $environment; ?></span>
                    </div>
                    <div class="iso-status-item">
                        <span>Composer</span>
                        <span class="iso-badge <?php echo $composerBadgeClass; ?>"><?php echo $composerStatus; ?></span>
                    </div>
                    <div class="iso-status-item">
                        <span>Database</span>
                        <span class="iso-badge <?php echo $dbBadgeClass; ?>"><?php echo $dbStatusText; ?></span>
                    </div>
                    <?php if ($dbConnected): ?>
                    <div class="iso-status-item">
                        <span>DB Name</span>
                        <span class="iso-badge iso-badge-info"><?php echo $dbStatus['database']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="iso-status iso-status-info">
                    To reinstall, delete the .isotone-installed file in the root directory and refresh this page.
                </div>
                <div class="iso-links">
                    <a href="../">Home</a>
                    <a href="../iso-admin">Admin Panel</a>
                </div>
            </div>
        <?php elseif ($success): ?>
            <div class="installed">
                <h1 class="iso-title-md success-title">Installation Complete!</h1>
                <p class="iso-subtitle">Your Isotone is ready to use.</p>
                <div class="iso-status iso-status-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <a href="../iso-admin" class="iso-btn iso-btn-arrow">Go to Admin Panel</a>
                <div class="iso-links">
                    <a href="../">View Site</a>
                </div>
            </div>
        <?php else: ?>
            <div class="iso-header">
                <img src="../iso-includes/assets/logo.svg" alt="Isotone" class="iso-header-logo">
                <h1 class="iso-title">Install Isotone</h1>
            </div>
            <p class="iso-subtitle">Set up your Super Admin account</p>
            
            <div class="iso-db-status">
                <span>Database: <?php echo htmlspecialchars($dbInfo ?: 'Not configured'); ?></span>
                <span class="iso-badge <?php echo $dbConnected ? 'iso-badge-success' : 'iso-badge-danger'; ?>">
                    <?php echo $dbConnected ? 'Connected' : 'Disconnected'; ?>
                </span>
            </div>
            
            <?php if ($error): ?>
                <div class="iso-status iso-status-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!$dbConnected): ?>
                <div class="iso-status iso-status-error">
                    Database connection required. Please configure your .env file first.
                </div>
                <button type="button" class="iso-btn iso-btn-secondary" onclick="testConnection()">
                    Test Database Connection
                </button>
            <?php endif; ?>
            
            <form method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="action" value="install">
                
                <div class="iso-form-group">
                    <label for="username" class="iso-label">Username</label>
                    <input class="iso-input" 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Choose a username"
                        minlength="3"
                        required
                        <?php echo !$dbConnected ? 'disabled' : ''; ?>
                    >
                </div>
                
                <div class="iso-form-group">
                    <label for="email" class="iso-label">Email Address</label>
                    <input class="iso-input" 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="admin@example.com"
                        required
                        <?php echo !$dbConnected ? 'disabled' : ''; ?>
                    >
                </div>
                
                <div class="iso-form-group">
                    <label for="password" class="iso-label">Password</label>
                    <input class="iso-input" 
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
                
                <div class="iso-form-group">
                    <label for="confirm_password" class="iso-label">Confirm Password</label>
                    <input class="iso-input" 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Re-enter your password"
                        minlength="8"
                        required
                        <?php echo !$dbConnected ? 'disabled' : ''; ?>
                    >
                </div>
                
                <button type="submit" class="iso-btn" <?php echo !$dbConnected ? 'disabled' : ''; ?>>
                    Install Isotone
                </button>
            </form>
            
            <div class="iso-links">
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
            
            return confirm('Ready to install Isotone with these credentials?');
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
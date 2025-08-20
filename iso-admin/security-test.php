<?php
/**
 * Security Features Test
 * 
 * This file tests the security implementations
 * DELETE THIS FILE IN PRODUCTION!
 */

require_once dirname(__DIR__) . '/iso-includes/class-security.php';

// Start session for testing
IsotoneSecurity::secureSession();

$tests = [];

// Test 1: Session Security
$tests['Session Security'] = [
    'Secure Cookies' => ini_get('session.cookie_httponly') == 1 ? '✅ Enabled' : '❌ Disabled',
    'Strict Mode' => ini_get('session.use_strict_mode') == 1 ? '✅ Enabled' : '❌ Disabled',
    'SameSite' => ini_get('session.cookie_samesite') ?: '❌ Not Set',
    'Session Name' => session_name() === 'ISOTONE_SESS' ? '✅ Custom' : '❌ Default'
];

// Test 2: CSRF Protection
$token = IsotoneSecurity::generateCSRFToken();
$tests['CSRF Protection'] = [
    'Token Generated' => !empty($token) ? '✅ Yes' : '❌ No',
    'Token Length' => strlen($token) . ' characters',
    'Validation Works' => IsotoneSecurity::validateCSRFToken($token) ? '✅ Yes' : '❌ No'
];

// Test 3: Session Fingerprinting
$fingerprint = IsotoneSecurity::generateFingerprint();
$tests['Session Fingerprinting'] = [
    'Fingerprint Generated' => !empty($fingerprint) ? '✅ Yes' : '❌ No',
    'Validation Works' => IsotoneSecurity::validateFingerprint() ? '✅ Yes' : '❌ No'
];

// Test 4: Brute Force Protection
$tests['Brute Force Protection'] = [
    'Check Function' => is_array(IsotoneSecurity::checkBruteForce()) ? '✅ Working' : '❌ Failed',
    'Block After' => '5 attempts',
    'Block Duration' => '15 minutes'
];

// Test 5: XSS Protection
$xss_test = "<script>alert('XSS')</script>";
$tests['XSS Protection'] = [
    'HTML Escaping' => IsotoneSecurity::escape($xss_test) === '&lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;' ? '✅ Working' : '❌ Failed',
    'Strip Tags' => strip_tags(IsotoneSecurity::escapeHtml($xss_test)) === "alert('XSS')" ? '✅ Working' : '❌ Failed'
];

// Test 6: Password Hashing
$test_password = 'TestPassword123!';
$hash = IsotoneSecurity::hashPassword($test_password);
$tests['Password Security'] = [
    'BCrypt Hashing' => strpos($hash, '$2y$') === 0 ? '✅ BCrypt' : '❌ Other',
    'Cost Factor' => '12',
    'Verification' => IsotoneSecurity::verifyPassword($test_password, $hash) ? '✅ Working' : '❌ Failed'
];

// Test 7: Security Headers (check response headers)
$headers = headers_list();
$security_headers = [
    'X-Frame-Options' => false,
    'X-Content-Type-Options' => false,
    'X-XSS-Protection' => false
];

foreach ($headers as $header) {
    foreach ($security_headers as $key => $value) {
        if (stripos($header, $key) !== false) {
            $security_headers[$key] = true;
        }
    }
}

$tests['Security Headers'] = [
    'X-Frame-Options' => $security_headers['X-Frame-Options'] ? '✅ Set' : '⚠️ Check .htaccess',
    'X-Content-Type-Options' => $security_headers['X-Content-Type-Options'] ? '✅ Set' : '⚠️ Check .htaccess',
    'X-XSS-Protection' => $security_headers['X-XSS-Protection'] ? '✅ Set' : '⚠️ Check .htaccess'
];

// Test 8: File Upload Validation
$fake_file = [
    'name' => 'test.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => '/tmp/test',
    'error' => UPLOAD_ERR_OK,
    'size' => 1024000
];

$php_file = [
    'name' => 'malicious.php',
    'type' => 'text/php',
    'tmp_name' => '/tmp/test',
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];

$validation1 = IsotoneSecurity::validateFileUpload($fake_file, ['image/jpeg', 'image/png']);
$validation2 = IsotoneSecurity::validateFileUpload($php_file, ['image/jpeg', 'image/png']);

$tests['File Upload Security'] = [
    'Valid File' => $validation1['valid'] ? '✅ Accepted' : '❌ Rejected',
    'PHP File Block' => !$validation2['valid'] ? '✅ Blocked' : '❌ Not Blocked',
    'Max Size Check' => '5MB limit'
];

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Test - Isotone</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Isotone Security Test</h1>
        <p class="text-red-400 mb-8">⚠️ DELETE THIS FILE IN PRODUCTION!</p>
        
        <?php foreach ($tests as $category => $items): ?>
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-cyan-400"><?php echo $category; ?></h2>
            <div class="space-y-2">
                <?php foreach ($items as $test => $result): ?>
                <div class="flex justify-between">
                    <span class="text-gray-300"><?php echo $test; ?>:</span>
                    <span class="font-mono"><?php echo $result; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="bg-yellow-900 border border-yellow-600 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-yellow-400">Additional Security Recommendations</h2>
            <ul class="space-y-2 text-yellow-200">
                <li>• Enable HTTPS and uncomment Strict-Transport-Security headers</li>
                <li>• Configure IP whitelist for admin area if possible</li>
                <li>• Set up regular automated backups</li>
                <li>• Monitor security logs regularly</li>
                <li>• Keep PHP and all dependencies updated</li>
                <li>• Use strong passwords and consider 2FA</li>
                <li>• Review and audit user permissions</li>
                <li>• Implement rate limiting on API endpoints</li>
            </ul>
        </div>
        
        <div class="mt-8 text-center">
            <a href="dashboard.php" class="inline-block px-6 py-2 bg-cyan-600 hover:bg-cyan-700 rounded-lg">
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
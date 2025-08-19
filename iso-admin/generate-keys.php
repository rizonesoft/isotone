<?php
/**
 * Generate Security Keys
 * 
 * This script generates secure keys for your config.php file
 * Run this once and update your config.php with the generated keys
 */

// Generate random secure keys
function generateKey($length = 64) {
    return base64_encode(random_bytes($length));
}

$keys = [
    'AUTH_KEY' => generateKey(),
    'SECURE_AUTH_KEY' => generateKey(),
    'LOGGED_IN_KEY' => generateKey(),
    'NONCE_KEY' => generateKey(),
    'AUTH_SALT' => generateKey(),
    'SECURE_AUTH_SALT' => generateKey(),
    'LOGGED_IN_SALT' => generateKey(),
    'NONCE_SALT' => generateKey(),
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Security Keys - Isotone</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Security Key Generator</h1>
        
        <div class="bg-yellow-900 border border-yellow-600 rounded-lg p-4 mb-6">
            <p class="text-yellow-200">
                <strong>⚠️ Important:</strong> Copy these keys and update your <code>config.php</code> file. 
                These keys will be different each time you refresh this page.
            </p>
        </div>
        
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-cyan-400">Generated Keys</h2>
            <p class="text-gray-400 mb-4">Copy and paste these lines into your config.php file:</p>
            
            <pre class="bg-gray-900 p-4 rounded overflow-x-auto text-sm"><code><?php
foreach ($keys as $name => $value) {
    echo "define('" . $name . "', '" . $value . "');\n";
}
?></code></pre>
            
            <button onclick="copyToClipboard()" class="mt-4 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 rounded">
                Copy to Clipboard
            </button>
        </div>
        
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-cyan-400">How to Update config.php</h2>
            <ol class="list-decimal list-inside space-y-2 text-gray-300">
                <li>Open <code>/isotone/config.php</code> in your text editor</li>
                <li>Find the section labeled "SECURITY SETTINGS" (around line 57)</li>
                <li>Replace the existing define statements with the ones generated above</li>
                <li>Save the file</li>
                <li>Delete this file (<code>generate-keys.php</code>) for security</li>
            </ol>
        </div>
        
        <div class="mt-8 text-center">
            <a href="dashboard.php" class="inline-block px-6 py-2 bg-gray-600 hover:bg-gray-700 rounded-lg">
                Back to Dashboard
            </a>
        </div>
    </div>
    
    <script>
        function copyToClipboard() {
            const codeBlock = document.querySelector('pre code');
            const text = codeBlock.textContent;
            
            navigator.clipboard.writeText(text).then(function() {
                alert('Keys copied to clipboard!');
            }, function(err) {
                alert('Could not copy text. Please select and copy manually.');
            });
        }
    </script>
</body>
</html>
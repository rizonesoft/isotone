#!/usr/bin/env php
<?php
/**
 * Automated Hooks Documentation Generator
 * 
 * Scans the codebase for hook implementations and generates documentation
 * 
 * @package Isotone\Scripts
 */

require_once __DIR__ . '/../vendor/autoload.php';

class HooksDocumentationGenerator
{
    private $hooks = [
        'actions' => [],
        'filters' => [],
        'deprecated' => []
    ];
    
    private $hookDetails = [];
    private $scanStats = [];
    
    /**
     * Directories to scan for hooks
     */
    private $scanDirectories = [
        'app',
        'iso-admin',
        'iso-content/themes',
        'iso-content/plugins'
    ];
    
    /**
     * Run the documentation generator
     */
    public function run()
    {
        echo "🔍 Scanning for hooks implementation...\n";
        
        $this->scanCodebase();
        $this->analyzeHooks();
        $this->generateHooksMarkdown();
        $this->generateApiReference();
        $this->updateHooksStatus();
        
        echo "✅ Hooks documentation generated successfully!\n";
        $this->printStats();
    }
    
    /**
     * Scan the codebase for hook usage
     */
    private function scanCodebase()
    {
        $rootDir = dirname(__DIR__);
        
        foreach ($this->scanDirectories as $dir) {
            $fullPath = $rootDir . '/' . $dir;
            if (is_dir($fullPath)) {
                $this->scanDirectory($fullPath);
            }
        }
    }
    
    /**
     * Scan a directory recursively
     */
    private function scanDirectory($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->scanFile($file->getPathname());
            }
        }
    }
    
    /**
     * Scan a PHP file for hooks
     */
    private function scanFile($filepath)
    {
        $content = file_get_contents($filepath);
        $relativePath = str_replace(dirname(__DIR__) . '/', '', $filepath);
        
        // Track stats
        if (!isset($this->scanStats['files_scanned'])) {
            $this->scanStats['files_scanned'] = 0;
        }
        $this->scanStats['files_scanned']++;
        
        // Find do_action calls
        if (preg_match_all(
            '/do_action\s*\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*([^)]+))?\)/s',
            $content,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            foreach ($matches[1] as $index => $match) {
                $hook = $match[0];
                $line = $this->getLineNumber($content, $match[1]);
                $context = $this->getContext($content, $match[1]);
                $args = isset($matches[2][$index]) ? $this->parseArgs($matches[2][$index][0]) : [];
                
                if (!isset($this->hooks['actions'][$hook])) {
                    $this->hooks['actions'][$hook] = [];
                }
                
                $this->hooks['actions'][$hook][] = [
                    'file' => $relativePath,
                    'line' => $line,
                    'context' => $context,
                    'args' => $args
                ];
                
                // Store detailed info
                $this->hookDetails[$hook] = [
                    'type' => 'action',
                    'description' => $this->extractDescription($content, $match[1]),
                    'since' => $this->extractSince($content, $match[1]),
                    'args' => $args
                ];
            }
        }
        
        // Find apply_filters calls
        if (preg_match_all(
            '/apply_filters\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([^,)]+)(?:,\s*([^)]+))?\)/s',
            $content,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            foreach ($matches[1] as $index => $match) {
                $hook = $match[0];
                $line = $this->getLineNumber($content, $match[1]);
                $context = $this->getContext($content, $match[1]);
                $value = trim($matches[2][$index][0]);
                $args = isset($matches[3][$index]) ? $this->parseArgs($matches[3][$index][0]) : [];
                
                if (!isset($this->hooks['filters'][$hook])) {
                    $this->hooks['filters'][$hook] = [];
                }
                
                $this->hooks['filters'][$hook][] = [
                    'file' => $relativePath,
                    'line' => $line,
                    'context' => $context,
                    'value' => $value,
                    'args' => $args
                ];
                
                // Store detailed info
                $this->hookDetails[$hook] = [
                    'type' => 'filter',
                    'description' => $this->extractDescription($content, $match[1]),
                    'since' => $this->extractSince($content, $match[1]),
                    'value' => $value,
                    'args' => $args
                ];
            }
        }
        
        // Find deprecated hooks
        if (preg_match_all(
            '/(?:do_action|apply_filters)_deprecated\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            $content,
            $matches
        )) {
            foreach ($matches[1] as $hook) {
                $this->hooks['deprecated'][$hook] = $relativePath;
            }
        }
    }
    
    /**
     * Get line number from offset
     */
    private function getLineNumber($content, $offset)
    {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }
    
    /**
     * Get context around hook
     */
    private function getContext($content, $offset, $contextLines = 2)
    {
        $lines = explode("\n", $content);
        $lineNum = $this->getLineNumber($content, $offset) - 1;
        
        $start = max(0, $lineNum - $contextLines);
        $end = min(count($lines) - 1, $lineNum + $contextLines);
        
        $context = array_slice($lines, $start, $end - $start + 1);
        return implode("\n", $context);
    }
    
    /**
     * Parse hook arguments
     */
    private function parseArgs($argsString)
    {
        $args = [];
        $argsString = trim($argsString);
        
        if (empty($argsString)) {
            return $args;
        }
        
        // Simple argument parsing (can be enhanced)
        $parts = preg_split('/,(?![^(]*\))/', $argsString);
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^\$(\w+)/', $part, $match)) {
                $args[] = $match[1];
            } else {
                $args[] = $part;
            }
        }
        
        return $args;
    }
    
    /**
     * Extract description from PHPDoc
     */
    private function extractDescription($content, $offset)
    {
        $lines = explode("\n", $content);
        $lineNum = $this->getLineNumber($content, $offset) - 1;
        
        // Look for PHPDoc comment above
        for ($i = $lineNum - 1; $i >= max(0, $lineNum - 10); $i--) {
            if (preg_match('/\*\s+(.+)/', $lines[$i], $match)) {
                $desc = trim($match[1]);
                if (!preg_match('/^@/', $desc) && $desc !== '*') {
                    return $desc;
                }
            }
        }
        
        return '';
    }
    
    /**
     * Extract @since tag from PHPDoc
     */
    private function extractSince($content, $offset)
    {
        $lines = explode("\n", $content);
        $lineNum = $this->getLineNumber($content, $offset) - 1;
        
        // Look for @since tag
        for ($i = $lineNum - 1; $i >= max(0, $lineNum - 10); $i--) {
            if (preg_match('/@since\s+([\d.]+)/', $lines[$i], $match)) {
                return $match[1];
            }
        }
        
        return '1.0.0';
    }
    
    /**
     * Analyze hooks for patterns and categories
     */
    private function analyzeHooks()
    {
        $this->scanStats['total_actions'] = count($this->hooks['actions']);
        $this->scanStats['total_filters'] = count($this->hooks['filters']);
        $this->scanStats['total_deprecated'] = count($this->hooks['deprecated']);
        
        // Categorize hooks
        $this->categorizeHooks();
    }
    
    /**
     * Categorize hooks by their prefix
     */
    private function categorizeHooks()
    {
        $categories = [
            'iso_' => 'Isotone Core',
            'admin_' => 'Admin',
            'init' => 'Initialization',
            'save_' => 'Data Saving',
            'delete_' => 'Data Deletion',
            'pre_' => 'Pre-processing',
            'post_' => 'Post-processing',
            'the_' => 'Content Display',
            'get_' => 'Data Retrieval',
            'rest_' => 'REST API',
            'ajax_' => 'AJAX'
        ];
        
        $this->hookCategories = [];
        
        foreach (array_merge(array_keys($this->hooks['actions']), array_keys($this->hooks['filters'])) as $hook) {
            $category = 'Other';
            foreach ($categories as $prefix => $cat) {
                if (strpos($hook, $prefix) === 0) {
                    $category = $cat;
                    break;
                }
            }
            
            if (!isset($this->hookCategories[$category])) {
                $this->hookCategories[$category] = [];
            }
            $this->hookCategories[$category][] = $hook;
        }
    }
    
    /**
     * Generate HOOKS.md with actual implementation status
     */
    private function generateHooksMarkdown()
    {
        $rootDir = dirname(__DIR__);
        $hooksFile = $rootDir . '/HOOKS.md';
        
        // Read existing HOOKS.md to preserve structure
        $existingContent = file_get_contents($hooksFile);
        
        // Update implementation status
        $updatedContent = $this->updateImplementationStatus($existingContent);
        
        // Update statistics
        $updatedContent = $this->updateStatistics($updatedContent);
        
        // Add discovered hooks section
        $updatedContent = $this->addDiscoveredHooks($updatedContent);
        
        file_put_contents($hooksFile, $updatedContent);
        echo "📝 Updated HOOKS.md\n";
    }
    
    /**
     * Update implementation status in HOOKS.md
     */
    private function updateImplementationStatus($content)
    {
        // Update status for each implemented hook
        foreach ($this->hooks['actions'] as $hook => $locations) {
            $pattern = '/\| `?' . preg_quote($hook, '/') . '`?\s*\|[^|]+\|[^|]+\|[^❌✅🚧📅🔄]+\|/';
            $replacement = function($matches) use ($hook) {
                $parts = explode('|', $matches[0]);
                $parts[4] = ' ✅ ';
                return implode('|', $parts);
            };
            $content = preg_replace_callback($pattern, $replacement, $content);
        }
        
        foreach ($this->hooks['filters'] as $hook => $locations) {
            $pattern = '/\| `?' . preg_quote($hook, '/') . '`?\s*\|[^|]+\|[^|]+\|[^❌✅🚧📅🔄]+\|/';
            $replacement = function($matches) use ($hook) {
                $parts = explode('|', $matches[0]);
                $parts[4] = ' ✅ ';
                return implode('|', $parts);
            };
            $content = preg_replace_callback($pattern, $replacement, $content);
        }
        
        return $content;
    }
    
    /**
     * Update statistics in HOOKS.md
     */
    private function updateStatistics($content)
    {
        $total = $this->scanStats['total_actions'] + $this->scanStats['total_filters'];
        $percentage = round(($total / 100) * 100); // Assuming 100 planned hooks
        
        $content = preg_replace(
            '/\*\*Last Updated\*\*: .+/',
            '**Last Updated**: ' . date('Y-m-d H:i:s'),
            $content
        );
        
        $content = preg_replace(
            '/\*\*Total Hooks Planned\*\*: \d+/',
            '**Total Hooks Planned**: 100+',
            $content
        );
        
        $content = preg_replace(
            '/\*\*Hooks Implemented\*\*: \d+/',
            '**Hooks Implemented**: ' . $total,
            $content
        );
        
        $content = preg_replace(
            '/\*\*Implementation Progress\*\*: \d+%/',
            '**Implementation Progress**: ' . $percentage . '%',
            $content
        );
        
        return $content;
    }
    
    /**
     * Add discovered hooks section
     */
    private function addDiscoveredHooks($content)
    {
        $section = "\n## 🔍 Discovered Hooks (Auto-Generated)\n\n";
        $section .= "These hooks were found in the codebase:\n\n";
        
        // Actions
        if (!empty($this->hooks['actions'])) {
            $section .= "### Actions Found\n\n";
            $section .= "| Hook | Locations | Description |\n";
            $section .= "|------|-----------|-------------|\n";
            
            foreach ($this->hooks['actions'] as $hook => $locations) {
                $files = array_unique(array_column($locations, 'file'));
                $fileList = implode(', ', array_map(function($f) {
                    return '`' . basename($f) . '`';
                }, array_slice($files, 0, 3)));
                
                $desc = $this->hookDetails[$hook]['description'] ?? '';
                $section .= "| `$hook` | $fileList | $desc |\n";
            }
        }
        
        // Filters
        if (!empty($this->hooks['filters'])) {
            $section .= "\n### Filters Found\n\n";
            $section .= "| Hook | Locations | Description |\n";
            $section .= "|------|-----------|-------------|\n";
            
            foreach ($this->hooks['filters'] as $hook => $locations) {
                $files = array_unique(array_column($locations, 'file'));
                $fileList = implode(', ', array_map(function($f) {
                    return '`' . basename($f) . '`';
                }, array_slice($files, 0, 3)));
                
                $desc = $this->hookDetails[$hook]['description'] ?? '';
                $section .= "| `$hook` | $fileList | $desc |\n";
            }
        }
        
        // Remove old auto-generated section if exists
        $pattern = '/## 🔍 Discovered Hooks \(Auto-Generated\).*/s';
        $content = preg_replace($pattern, '', $content);
        
        // Add new section before the end
        $content = str_replace(
            "**Last Updated**:",
            $section . "\n---\n\n**Last Updated**:",
            $content
        );
        
        return $content;
    }
    
    /**
     * Generate API Reference for user documentation
     */
    private function generateApiReference()
    {
        $rootDir = dirname(__DIR__);
        $apiRefFile = $rootDir . '/user-docs/development/api-reference.md';
        
        // Create directory if it doesn't exist
        $dir = dirname($apiRefFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $content = $this->generateApiReferenceContent();
        file_put_contents($apiRefFile, $content);
        echo "📝 Generated user-docs/development/api-reference.md\n";
    }
    
    /**
     * Generate API Reference content
     */
    private function generateApiReferenceContent()
    {
        $content = "# Isotone API Reference\n\n";
        $content .= "> **Auto-generated:** " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "## Hooks & Filters\n\n";
        $content .= "Isotone uses a WordPress-compatible hooks system with the `iso_` prefix for WordPress-equivalent hooks.\n\n";
        
        // Table of Contents
        $content .= "### Quick Navigation\n\n";
        foreach ($this->hookCategories as $category => $hooks) {
            $anchor = strtolower(str_replace(' ', '-', $category));
            $content .= "- [$category](#$anchor) (" . count($hooks) . " hooks)\n";
        }
        
        $content .= "\n---\n\n";
        
        // Detailed sections by category
        foreach ($this->hookCategories as $category => $hooks) {
            $content .= "### $category\n\n";
            
            foreach ($hooks as $hook) {
                $type = isset($this->hooks['actions'][$hook]) ? 'action' : 'filter';
                $details = $this->hookDetails[$hook] ?? [];
                
                $content .= "#### `$hook`\n\n";
                $content .= "**Type:** " . ucfirst($type) . "\n";
                
                if (!empty($details['description'])) {
                    $content .= "**Description:** " . $details['description'] . "\n";
                }
                
                if (!empty($details['since'])) {
                    $content .= "**Since:** " . $details['since'] . "\n";
                }
                
                // Usage example
                $content .= "\n**Usage:**\n```php\n";
                if ($type === 'action') {
                    $args = !empty($details['args']) ? ', $' . implode(', $', $details['args']) : '';
                    $content .= "add_action('$hook', function($args) {\n";
                    $content .= "    // Your code here\n";
                    $content .= "});\n";
                } else {
                    $value = $details['value'] ?? '$value';
                    $args = !empty($details['args']) ? ', $' . implode(', $', $details['args']) : '';
                    $content .= "add_filter('$hook', function($value$args) {\n";
                    $content .= "    // Modify and return \$value\n";
                    $content .= "    return \$value;\n";
                    $content .= "});\n";
                }
                $content .= "```\n\n";
                
                // File locations
                if ($type === 'action' && isset($this->hooks['actions'][$hook])) {
                    $locations = $this->hooks['actions'][$hook];
                } elseif ($type === 'filter' && isset($this->hooks['filters'][$hook])) {
                    $locations = $this->hooks['filters'][$hook];
                } else {
                    $locations = [];
                }
                
                if (!empty($locations)) {
                    $content .= "**Found in:**\n";
                    foreach (array_slice($locations, 0, 3) as $loc) {
                        $content .= "- `{$loc['file']}:{$loc['line']}`\n";
                    }
                    if (count($locations) > 3) {
                        $content .= "- _...and " . (count($locations) - 3) . " more locations_\n";
                    }
                }
                
                $content .= "\n---\n\n";
            }
        }
        
        // Add helper functions section
        $content .= $this->generateHelperFunctionsSection();
        
        return $content;
    }
    
    /**
     * Generate helper functions documentation
     */
    private function generateHelperFunctionsSection()
    {
        $content = "## Helper Functions\n\n";
        $content .= "### Hook Management\n\n";
        
        $functions = [
            'add_action' => [
                'desc' => 'Hooks a function to a specific action',
                'params' => ['$tag', '$callback', '$priority = 10', '$accepted_args = 1'],
                'return' => 'true'
            ],
            'do_action' => [
                'desc' => 'Execute functions hooked on a specific action',
                'params' => ['$tag', '...$args'],
                'return' => 'void'
            ],
            'add_filter' => [
                'desc' => 'Hook a function to a specific filter',
                'params' => ['$tag', '$callback', '$priority = 10', '$accepted_args = 1'],
                'return' => 'true'
            ],
            'apply_filters' => [
                'desc' => 'Apply filters to a value',
                'params' => ['$tag', '$value', '...$args'],
                'return' => 'mixed'
            ],
            'remove_action' => [
                'desc' => 'Remove a function from a specified action hook',
                'params' => ['$tag', '$callback', '$priority = 10'],
                'return' => 'bool'
            ],
            'remove_filter' => [
                'desc' => 'Remove a function from a specified filter hook',
                'params' => ['$tag', '$callback', '$priority = 10'],
                'return' => 'bool'
            ],
            'has_action' => [
                'desc' => 'Check if any action has been registered for a hook',
                'params' => ['$tag', '$callback = false'],
                'return' => 'bool|int'
            ],
            'has_filter' => [
                'desc' => 'Check if any filter has been registered for a hook',
                'params' => ['$tag', '$callback = false'],
                'return' => 'bool|int'
            ],
            'current_action' => [
                'desc' => 'Retrieve the name of the current action',
                'params' => [],
                'return' => 'string|false'
            ],
            'current_filter' => [
                'desc' => 'Retrieve the name of the current filter',
                'params' => [],
                'return' => 'string|false'
            ],
            'did_action' => [
                'desc' => 'Retrieve the number of times an action has fired',
                'params' => ['$tag'],
                'return' => 'int'
            ],
            'did_filter' => [
                'desc' => 'Retrieve the number of times a filter has been applied',
                'params' => ['$tag'],
                'return' => 'int'
            ]
        ];
        
        foreach ($functions as $func => $info) {
            $content .= "#### `$func()`\n\n";
            $content .= $info['desc'] . "\n\n";
            $content .= "**Parameters:**\n";
            foreach ($info['params'] as $param) {
                $content .= "- `$param`\n";
            }
            if (empty($info['params'])) {
                $content .= "- None\n";
            }
            $content .= "\n**Returns:** `{$info['return']}`\n\n";
            $content .= "---\n\n";
        }
        
        // Isotone-specific functions
        $content .= "### Isotone-Specific Functions\n\n";
        
        $isoFunctions = [
            'iso_head' => 'Output content in the <head> section',
            'iso_footer' => 'Output content before </body>',
            'iso_body_open' => 'Output content after <body>',
            'iso_enqueue_script' => 'Enqueue a JavaScript file',
            'iso_enqueue_style' => 'Enqueue a CSS stylesheet',
            'iso_localize_script' => 'Localize a script with data',
            'iso_create_nonce' => 'Create a security nonce',
            'iso_verify_nonce' => 'Verify a security nonce',
            'iso_nonce_field' => 'Output nonce field for forms',
            'iso_send_json_success' => 'Send JSON success response',
            'iso_send_json_error' => 'Send JSON error response',
            'iso_die' => 'Kill execution and display message'
        ];
        
        foreach ($isoFunctions as $func => $desc) {
            $content .= "#### `$func()`\n\n";
            $content .= "$desc\n\n";
            $content .= "---\n\n";
        }
        
        return $content;
    }
    
    /**
     * Update hooks status in database or config
     */
    private function updateHooksStatus()
    {
        $statusFile = dirname(__DIR__) . '/storage/hooks-status.json';
        
        $status = [
            'last_scan' => date('Y-m-d H:i:s'),
            'stats' => $this->scanStats,
            'categories' => array_map('count', $this->hookCategories),
            'implemented_hooks' => array_merge(
                array_keys($this->hooks['actions']),
                array_keys($this->hooks['filters'])
            )
        ];
        
        file_put_contents($statusFile, json_encode($status, JSON_PRETTY_PRINT));
        echo "📊 Updated hooks status\n";
    }
    
    /**
     * Print statistics
     */
    private function printStats()
    {
        echo "\n📊 Statistics:\n";
        echo "  Files scanned: {$this->scanStats['files_scanned']}\n";
        echo "  Actions found: {$this->scanStats['total_actions']}\n";
        echo "  Filters found: {$this->scanStats['total_filters']}\n";
        echo "  Deprecated hooks: {$this->scanStats['total_deprecated']}\n";
        echo "\n📁 Categories:\n";
        foreach ($this->hookCategories as $category => $hooks) {
            echo "  $category: " . count($hooks) . " hooks\n";
        }
    }
}

// Run the generator
$generator = new HooksDocumentationGenerator();
$generator->run();
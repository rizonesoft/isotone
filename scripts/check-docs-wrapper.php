<?php
/**
 * Documentation Checker - Wrapper for Automation Module
 * 
 * This is a backward compatibility wrapper that calls the new
 * Isotone Automation Module. The original check-docs.php is preserved
 * but this wrapper is now used by composer scripts.
 * 
 * Run: php scripts/check-docs-wrapper.php
 */

// Get command line arguments
$quiet = in_array('--quiet', $argv);

// Call the new automation module
$command = 'php ' . dirname(__DIR__) . '/iso-automation/cli.php check:docs';

if ($quiet) {
    $command .= ' --quiet';
}

$output = [];
$returnCode = 0;

// Execute the command
passthru($command, $returnCode);

// Exit with the same code
exit($returnCode);
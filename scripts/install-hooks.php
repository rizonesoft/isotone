<?php
/**
 * Install Git hooks for documentation maintenance
 * Run: php scripts/install-hooks.php
 */

echo "Installing Git hooks for documentation maintenance...\n";

$preCommitHook = <<<'BASH'
#!/bin/bash
# Isotone CMS Pre-commit Hook
# Ensures documentation is in sync before committing

echo "🔍 Running pre-commit checks..."

# Check documentation integrity
php scripts/check-docs.php
if [ $? -ne 0 ]; then
    echo "❌ Documentation check failed!"
    echo "Fix the errors above before committing."
    exit 1
fi

echo "✅ Documentation check passed!"
BASH;

$hookPath = dirname(__DIR__) . '/.git/hooks/pre-commit';

if (file_put_contents($hookPath, $preCommitHook)) {
    chmod($hookPath, 0755);
    echo "✅ Pre-commit hook installed successfully!\n";
    echo "The hook will run 'composer docs:check' before each commit.\n";
} else {
    echo "❌ Failed to install pre-commit hook.\n";
    echo "Please manually create .git/hooks/pre-commit\n";
}
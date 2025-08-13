<?php
/**
 * Install Git hooks for documentation maintenance
 * Run: php scripts/install-hooks.php
 */

echo "Installing Git hooks for documentation maintenance...\n";

$preCommitHook = <<<'BASH'
#!/bin/bash
# Isotone CMS Pre-commit Hook
# Smart documentation validation (only updates when needed)

echo "🔍 Running pre-commit checks..."

# Only check documentation integrity (don't auto-update)
echo "🔍 Validating documentation..."
php scripts/check-docs.php --quiet
if [ $? -ne 0 ]; then
    echo "❌ Documentation validation failed!"
    echo "Run 'composer docs:update' to fix, then commit again."
    exit 1
fi

echo "✅ Documentation validation passed!"
BASH;

$prePushHook = <<<'BASH'
#!/bin/bash
# Isotone CMS Pre-push Hook  
# Only triggers on significant changes

echo "🚀 Running pre-push validation..."

# Check if this is a significant push (version change, new features, etc.)
SIGNIFICANT=false

# Check for version changes
if git diff --name-only origin/main..HEAD | grep -q "config/version.json"; then
    echo "📝 Version change detected, updating documentation..."
    SIGNIFICANT=true
    
    # Update docs and changelog for version changes
    composer docs:update --quiet
    php -r "require 'vendor/autoload.php'; use Isotone\Commands\ChangelogCommand; ChangelogCommand::save();"
    
    # Commit doc updates if any
    if [ -n "$(git diff --name-only docs/ CHANGELOG.md)" ]; then
        git add docs/ CHANGELOG.md
        git commit -m "docs: Auto-update for version change"
    fi
fi

# Always do final validation
echo "🔍 Final documentation validation..."
composer docs:check --quiet
if [ $? -ne 0 ]; then
    echo "❌ Documentation validation failed!"
    exit 1
fi

if [ "$SIGNIFICANT" = true ]; then
    echo "✅ Documentation updated for significant changes!"
else
    echo "✅ Documentation validation passed!"
fi
BASH;

// Install pre-commit hook
$preCommitPath = dirname(__DIR__) . '/.git/hooks/pre-commit';
if (file_put_contents($preCommitPath, $preCommitHook)) {
    chmod($preCommitPath, 0755);
    echo "✅ Pre-commit hook installed successfully!\n";
} else {
    echo "❌ Failed to install pre-commit hook.\n";
}

// Install pre-push hook
$prePushPath = dirname(__DIR__) . '/.git/hooks/pre-push';
if (file_put_contents($prePushPath, $prePushHook)) {
    chmod($prePushPath, 0755);
    echo "✅ Pre-push hook installed successfully!\n";
} else {
    echo "❌ Failed to install pre-push hook.\n";
}

echo "\n📚 Git hooks installed:\n";
echo "  • Pre-commit: Validates documentation integrity (prevents broken docs)\n";
echo "  • Pre-push: Smart updates only on significant changes (version bumps, etc.)\n";
echo "\nDocumentation will update intelligently, not on every commit!\n";
# Scripts Directory

This directory contains scripts that are **run automatically** by IDEs, git hooks, CI/CD pipelines, and AI assistants.

## Who Runs These Scripts?
- Claude Code and other AI assistants
- IDEs (PhpStorm, VSCode, etc.)
- Git hooks (pre-commit, pre-push)
- CI/CD pipelines
- Cron jobs
- Build systems

## What Belongs Here
- Code formatters and linters
- Documentation generators
- Build and compilation scripts
- Automated test runners
- Git hook scripts
- IDE helper generators
- Cache management utilities
- Asset processors

## Security Note
This directory is protected from web access via .htaccess. Scripts can only be run via CLI.

## Important
This directory is for automated scripts only. User-facing scripts should be implemented as CLI commands via the `isotone` command.

## Example Usage
These scripts are typically called automatically:

```bash
# Called by git pre-commit hook
php scripts/pre-commit.php

# Called by IDE for formatting
php scripts/format-code.php

# Called by CI/CD
php scripts/run-tests.php
```
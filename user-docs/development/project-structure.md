---
title: Project Structure Guide
description: Complete overview of Isotone's directory structure, file organization, and component architecture
tags: [project-structure, directories, files, organization, architecture]
category: development
priority: 88
last_updated: 2025-08-25
---

# Isotone Project Structure

*Updated on 2025-08-25 - Reflects separation of development tools from production code*

## Project Structure

```
isotone/
├── CLAUDE.md
├── README.md
├── composer              # Wrapper script for composer in tools/
├── config.sample.php
├── docs/                 # Generated HTML documentation
│   ├── api-reference/
│   ├── assets/
│   │   ├── css/
│   │   ├── images/
│   │   ├── js/
│   ├── automation/
│   ├── configuration/
│   ├── development/
│   ├── getting-started/
│   ├── icons/
│   ├── toni/
│   └── troubleshooting/
├── error/                # HTTP error pages
├── install/              # Installation wizard
│   ├── README.md
│   └── assets/
├── iso-admin/            # Admin panel
│   ├── api/
│   ├── css/
│   ├── includes/
│   ├── js/
│   └── lumina/          # Lumina UI templates
├── iso-api/              # REST API endpoints
│   └── admin/
├── iso-content/          # User content
│   ├── README.md
│   ├── cache/
│   ├── logs/
│   ├── plugins/
│   │   └── hello-isotone/
│   ├── themes/
│   │   ├── isotone/
│   │   ├── isotone-default/
│   │   ├── neutron/
│   │   └── quantum/
│   └── uploads/
├── iso-core/             # Core system
│   ├── Commands/
│   ├── Config/
│   ├── Core/
│   ├── Services/
│   └── runtime/         # Production dependencies (formerly vendor/)
│       ├── autoload.php
│       ├── gabordemooij/    # RedBeanPHP
│       ├── intervention/    # Image processing
│       ├── league/          # CommonMark
│       ├── monolog/         # Logging
│       ├── nesbot/          # Carbon dates
│       └── symfony/         # YAML parser
├── iso-development/      # Development tools (excluded from production)
│   ├── README.md
│   ├── admin/           # Development admin pages
│   ├── cache/
│   ├── cli.php          # Automation CLI
│   ├── lumina/          # Lumina UI source (LESS)
│   ├── src/             # Automation source
│   │   ├── Analyzers/
│   │   ├── Commands/
│   │   ├── Core/
│   │   ├── Documentation/
│   │   ├── Generators/
│   │   └── Rules/
│   ├── storage/
│   ├── tailwind/        # Tailwind build tools
│   │   ├── README.md
│   │   ├── package.json
│   │   └── src/
│   ├── tests/           # Test files
│   └── tools/           # Development dependencies
│       ├── composer     # Composer executable
│       ├── composer.json
│       ├── composer.lock
│       └── phpstan.neon # PHPStan config
├── iso-includes/        # Shared includes
│   ├── assets/
│   ├── css/
│   ├── js/
│   ├── lumina/          # Lumina UI compiled CSS
│   └── scripts/
├── server/              # Server configurations
├── user-docs/
│   ├── .kb/
│   ├── .vitepress/
│   │   └── dist/
│   ├── README.md
│   ├── api/
│   ├── api-reference/
│   ├── automation/
│   ├── configuration/
│   ├── development/
│   ├── getting-started/
│   ├── icons/
│   ├── package.json
│   ├── reference/
│   ├── toni/
│   └── troubleshooting/
```

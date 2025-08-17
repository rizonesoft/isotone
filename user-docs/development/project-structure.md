# Isotone Project Structure

*Updated on 2025-08-17 - Reflects Service-Oriented Architecture*

## Project Structure

```
.claude/
  settings.local.json
.github/
  copilot-instructions.md
.windsurf/
  rules/
    development-guide.md
CHANGELOG.md
CLAUDE.md
CONTRIBUTING.md
NOTES.md
README.md
iso-core/
  Commands/              # CLI command handlers
    ChangelogCommand.php
    DatabaseCommand.php
    HooksCommand.php
    VersionCommand.php
  Core/                  # Core CMS functionality
    Application.php
    Hook.php
    Migration.php
    SystemHooks.php
    ThemeAPI.php
    Version.php
  Services/              # Business logic services (Service-Oriented Architecture)
    ContentService.php
    DatabaseService.php
    ThemeService.php
    ToniService.php      # AI Assistant service
  helpers.php            # Global helper functions
  hooks.php              # WordPress-style hooks system
  theme-functions.php    # Theme support functions
composer.json
config/
  theme.php
  version.json
config.php               # Database configuration (not in git)
config.sample.php        # Configuration template
index.php                # Main entry point
install/
  README.md
  index.php
  test-db.php
iso-admin/               # Admin interface (Service-Oriented, not MVC)
  api/                   # API endpoints
    toni.php            # AI Assistant API
  auth.php              # Authentication handler
  automation.php        # Automation dashboard
  css/                  # Admin styles
    admin.css
  dashboard-new.php     # Modern dashboard
  documentation.php     # Documentation viewer
  hooks-explorer.php    # Hooks system explorer
  includes/            # Shared components
    admin-auth.php
    admin-layout.php   # Main layout template
  login.php
  logout.php
  settings.php         # Settings with tabs (General, APIs, Advanced)
  users.php
iso-automation/
  README.md
  cache/
  cli.php
  config/
  src/
    Analyzers/
    Core/
    Dashboard/
    Generators/
    Rules/
    Storage/
iso-content/
  README.md
  plugins/
    hello-isotone/
  themes/
    isotone-default/
    neutron/
  uploads/
iso-includes/
  assets/
  class-user.php
  css/
  js/
  landing-page.php
  scripts/
iso-runtime/
  README.md
  cache/
  logs/
  temp/
storage/
  hook-stats.json
  hooks-implementation.json
  hooks-status.json
user-docs/
  .vitepress/
  README.md
  api/
    api-reference.md
    theme-api.md
  configuration/
    config-guide.md
    database.md
  developers/
    commands.md
    project-structure.md
    routes.md
    themes.md
  development/
    api-reference.md
    getting-started.md
  getting-started/
    installation.md
  guides/
  index.md
  installation/
    development-setup.md
    tech-stack.md
  package.json
```

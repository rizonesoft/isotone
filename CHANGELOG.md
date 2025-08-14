# Changelog

All notable changes to Isotone will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.4-alpha] - Genesis - 2025-08-14

### Added
- Modern admin interface with collapsible sidebar and submenus
- Top admin bar with quick actions and search (Cmd/Ctrl+K)
- Responsive dashboard with widgets (stats, analytics, quick draft)
- System health monitoring widget
- User management with CRUD operations
- Breadcrumb navigation and toast notifications
- Dark/light mode toggle UI (ready for implementation)
- Mobile responsive design with Alpine.js interactivity
- Chart.js integration for analytics visualization
- Heroicons SVG icon system throughout admin
- localStorage persistence for UI preferences
- Authentication system with role-based permissions
- RedBeanPHP ORM integration (no migrations needed)
- WordPress-like plugin system hooks

## [0.1.3-alpha] - Genesis - 2025-08-14

### Added
- Dual documentation system (docs/ for LLMs, user-docs/ for humans)
- Automated user-docs sync with composer docs:sync
- Renamed docs to ALL CAPS convention (DEVELOPMENT-SETUP.md, etc.)
- Integrated IDE rules sync in docs:update workflow
- Complete documentation automation with composer docs:all
- Moved internal docs (GITHUB-SETUP.md, DOCS-STRUCTURE.md) to docs/
- Created sync-user-docs.php for automated documentation syncing
- Enhanced update-docs.php with user-docs and IDE sync integration

### ⚠ BREAKING CHANGES
- Documentation files renamed to ALL CAPS (development-setup.md → DEVELOPMENT-SETUP.md)
- GITHUB-SETUP.md and DOCS-STRUCTURE.md moved from root to docs/

## [0.1.2-alpha] - Genesis - 2025-08-13

### Added
- Intelligent documentation automation system
- Smart git hooks for documentation validation
- AI satisfaction detection for auto-commit workflow
- Optimized documentation update triggers
- Enhanced CLI version management
- Improved version badge text contrast (white text)

## [0.1.0-alpha] - Genesis - 2025-01-13

### Added
- Core foundation
- Modern UI with dark theme
- Basic routing system
- Environment configuration
- SVG logo and branding
- Intelligent versioning system with semantic versioning
- Version API endpoints
- CLI version management commands
- Automated changelog generation


# Changelog

All notable changes to Isotone will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.7-alpha] - Genesis - 2025-08-16

### Added
- Active menu state persistence in admin sidebar
- Visual indicators for active menu items (cyan borders, dots, bold text)
- Dynamic documentation UI that generates sections from folder structure
- IDE rule synchronization with Windsurf support (12,000 char limit handling)
- Improved documentation validation and auto-update commands

### Changed
- Unified all documentation in `/user-docs` folder (removed `/docs`)
- Migrated all script-based functionality to automation module
- Updated all Composer commands to use automation CLI
- Redesigned `docs:update` command to work with new user-docs structure
- Improved URL comparison logic for menu active states

### Removed
- Entire `/scripts` directory (functionality moved to automation)
- Database-backed documentation system (now file-based only)
- Obsolete commands: `docs:sync`, `docs:all`
- Empty `/setup` directory

### Fixed
- Dashboard menu item incorrectly staying active on all admin pages
- Documentation Analyzer return type (now returns boolean)
- Path references updated for new user-docs structure
- Admin area TODO added for dark/light mode styling improvements

## [0.1.6-alpha] - Genesis - 2025-08-15

### Added
- Comprehensive dark/light mode support across all admin pages
- Dark mode variant classes for Tailwind components
- Light mode optimized logo and title with better contrast
- Fixed layout jumping issues when switching themes
- Hooks system refactoring with improved documentation
- Native Theme API with WordPress-compatible functions
- Theme service for managing and activating themes
- Content service for posts and pages management
- Automated hooks documentation generation
- Plugin and theme developer guides
- Hook naming conventions documentation
- VitePress-powered user documentation site

## [0.1.5-alpha] - Genesis - 2025-08-14

### Added
- Complete redesign of landing page with professional layout
- Rebranded from 'Isotone CMS' to just 'Isotone'
- Fixed layout issues with body.iso-landing class separation
- Proper section spacing with 100px padding and 60px spacers
- Clean hero section with centered content and key stats
- Replaced emoji icons with proper SVG Heroicons
- Removed glass-on-glass container issues for better readability
- Tech stack badges showcasing modern PHP components
- Development progress bars showing project status
- Quick start guide with numbered steps
- Responsive stat grid without wrapping issues
- Alternating section backgrounds for visual rhythm
- Improved typography and visual hierarchy
- RedBeanPHP documentation integration via Context7

### ⚠ BREAKING CHANGES
- Landing page now uses body.iso-landing instead of body.iso-app class

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

## [0.1.2] - Genesis - 2025-08-13

### Added
- Intelligent documentation automation system
- Smart git hooks for documentation validation
- AI satisfaction detection for auto-commit workflow
- Optimized documentation update triggers
- Enhanced CLI version management

## [0.1.2-alpha] - Genesis - 2025-08-13

## [0.1.1] - Genesis - 2025-08-13

### Added
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


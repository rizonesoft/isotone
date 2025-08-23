# Changelog

All notable changes to Isotone will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.1-alpha] - Genesis - 2025-08-23

### Added
- **Icon API System** - Complete icon management with lazy loading support
  - Created `/iso-api/icons.php` RESTful endpoint
  - Added `iso_icon()`, `iso_get_icon()`, `iso_icon_url()` helper functions
  - Implemented icon preloading with `iso_preload_icons()`
  - Added icon helper components (`iso_icon_button()`, `iso_icon_link()`)
  - **Improved non-lazy loading support** - Icons now work correctly with inline SVG
  - **Added fallback icon** - Shows question mark icon when requested icon not found
  - **Better URL generation** - Improved path detection for various server environments
- **Icon Libraries** - Three Heroicons v2 variants
  - IconLibrary (outline, 24x24, stroke-based)
  - IconLibrarySolid (solid, 24x24, fill-based)  
  - IconLibraryMicro (micro, 16x16, fill-based)
- **JavaScript Icon Support** - Global icon objects for frontend
  - `IsotoneIcons`, `IsotoneIconsSolid`, `IsotoneIconsMicro` 
- **Customizer API** - WordPress-compatible theme customization
  - CustomizerPanel, CustomizerSection, CustomizerControl classes
  - Live preview with postMessage support
  - Icon picker control with search functionality
- **Icon Gallery Generator** - Automated icon documentation
  - Better error messaging when Tailwind CSS files are missing
  - Improved path resolution for Tailwind v4 @source directives
- **Admin UI Enhancements**
  - Glassmorphism effects on all content cards with bottom glow
  - Enhanced card headers with icons, badges, and action buttons
  - Professional info cards with subtle hover effects
  - Comprehensive CSS component library (admin-components.css)

### Fixed
- Fixed Tailwind CSS v4.1 build system with correct @source paths
- Fixed Tailwind CSS file size issue (was 4.6 KB, now properly 50.6 KB minified)
- Fixed admin dashboard styling not loading due to incorrect build paths
- Fixed Icon API inline SVG generation with proper error handling
- Fixed icon library loading with class and method existence checks

### Changed
- Updated Tailwind CSS from v4.0.0-beta.8 to stable v4.1.12
- Removed Tailwind CDN fallback for better performance and security
- Improved admin-layout.php Tailwind loading logic with clear error messages
- Optimized page size from 330.5 KB to ~210 KB (36% reduction)
- Replaced all hardcoded SVG icons with Icon API calls throughout admin
- Moved all inline styles to external CSS files (no inline styles allowed)

## [0.3.0-alpha] - Genesis - 2025-08-20

### Added
- Documentation asset management system implementation
- Moved documentation assets to iso-automation/src/Documentation/assets/
- Updated DocsBuilderCommand to copy assets instead of generating them
- Collapsible navigation with tree view lines and +/- indicators
- Professional theme toggle without excessive effects
- Compact sidebar spacing for handling many documents
- Removed hover background effects from navigation sections
- Proper centering of +/- symbols in collapse buttons
- Fixed vertical line alignment in tree view navigation
- JavaScript navigation with localStorage persistence
- Multiple CSS design evolution backups preserved
- Updated Tailwind CSS documentation to reflect v4.1.12
- LoginSecurity class for enhanced login attempt tracking
- Documentation search functionality preparation
- Improved typography and letter spacing in documentation

## [0.2.1-alpha] - Genesis - 2025-08-19

### Added
- Comprehensive security system implementation
- Session fingerprinting with IP and User-Agent validation
- CSRF token protection for all forms
- Brute force protection with rate limiting
- XSS protection helpers (iso_esc_html, iso_esc_attr, etc.)
- Secure session configuration with httponly cookies
- Authentication keys system (SECURE_AUTH_KEY, NONCE_KEY, etc.)
- Security keys validation and fallback handling
- Development environment detection for simplified fingerprinting
- Enhanced login page with Isotone theme styling
- Glass morphism design with Inter font
- Improved letter spacing across all UI elements
- Safe .htaccess security rules without breaking functionality
- Apache 2.2 and 2.4 compatibility
- Optional IP restriction and password protection templates
- Security test page for validating configuration
- Automatic security warnings in debug mode
- Fixed session validation order in auth.php
- Fixed config.php loading order for authentication keys

### ⚠ BREAKING CHANGES
- auth.php now requires config.php to be loaded first
- Session fingerprint validation is now enforced
- CSRF tokens are required for all POST requests

## [0.2.0-alpha] - Genesis - 2025-08-18

### Added
- Complete documentation system overhaul
- Removed auto-generated documentation commands (docs:check, docs:update)
- Static category organization for user documentation
- Dynamic markdown file discovery within categories
- Improved documentation viewer with collapsible sidebar
- Icons for documentation categories
- Comprehensive installation.md guide
- Added rule_management rule for better rule maintenance
- Added documentation_accuracy rule for fact-checking
- Kept useful docs:hooks command for generating hooks documentation
- Documentation now grows organically based on actual needs

### ⚠ BREAKING CHANGES
- Removed composer docs:check command
- Removed composer docs:update command
- Deleted DocumentationManager.php
- Documentation no longer auto-generates template files

## [0.1.7-alpha] - Genesis - 2025-08-16

### Added
- Tailwind CSS v4 integration with build system
- Automated Tailwind installation and configuration
- CSS build commands (build, watch, minify)
- Custom tailwind.config.js with dark mode support
- Toni AI Assistant integration with GPT-5 support
- AI-powered development assistant interface
- Enhanced automation dashboard with real-time output
- Documentation system improvements
- Hooks Explorer interactive documentation viewer
- API reference auto-generation from code

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


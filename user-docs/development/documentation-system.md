# Documentation System

## Overview

The Isotone documentation system provides a structured, hierarchical approach to organizing and displaying documentation. It uses a static main category structure with dynamically discovered markdown files as subitems.

## Category Structure

The documentation is organized into logical main categories:

### 1. Getting Started
Quick start guides and installation instructions for new users.

### 2. Core Concepts
Fundamental concepts and architecture documentation that explain how Isotone works at its core.

### 3. Configuration
System configuration guides, settings documentation, and environment setup.

### 4. Development
Development guides, best practices, coding standards, and workflow documentation.

### 5. API Reference
Complete API documentation including endpoints, methods, and response formats.

### 6. Themes & Plugins
Documentation for extending Isotone with custom themes and plugins.

### 7. Automation
Documentation for the Isotone automation system, rules engine, and workflow automation.

### 8. Toni AI Assistant
Guides for using the AI-powered development assistant integrated into Isotone.

### 9. Troubleshooting
Common issues, solutions, and debugging guides.

### 10. Reference
Technical specifications, glossary, and detailed reference materials.

## How It Works

### Static Categories, Dynamic Content

The system uses a hybrid approach:
- **Main categories** are statically defined with proper ordering and descriptions
- **Document files** within categories are dynamically discovered from the filesystem
- **Automatic mapping** places existing folders into appropriate logical categories

### File Organization

Documentation files are stored in `/user-docs/` with the following structure:
```
user-docs/
├── getting-started/
│   ├── installation.md
│   └── tech-stack.md
├── configuration/
│   ├── config-guide.md
│   └── database.md
├── development/
│   ├── architecture.md
│   ├── commands.md
│   └── project-structure.md
└── [other categories]/
```

### Folder Mapping

Existing folders are automatically mapped to logical categories:
- `getting-started` → Getting Started
- `configuration` → Configuration
- `development` → Development
- `api-reference` → API Reference
- `automation` → Automation
- `toni` → Toni AI Assistant
- `themes`, `plugins` → Themes & Plugins
- Unmapped folders → Reference (catch-all)

## Features

### Navigation

1. **Collapsible Categories**: Click category headers to expand/collapse document lists
2. **Document Count**: Shows number of documents in each category
3. **Auto-expansion**: Current category automatically expands on page load
4. **Source Grouping**: When documents from multiple folders appear in one category, they're grouped by source

### Document Display

1. **Markdown Rendering**: Full markdown support with syntax highlighting
2. **Copy Code Buttons**: One-click code copying for all code blocks
3. **Navigation Buttons**: Previous/Next buttons for sequential reading
4. **Breadcrumb Trail**: Shows current location in documentation hierarchy

### Visual Indicators

- Each category has a unique icon for quick visual identification
- Active documents are highlighted in the navigation
- Categories show descriptions to explain their purpose
- Empty categories show "No documents yet" placeholder

## Adding Documentation

### Creating New Documents

1. Create a markdown file in the appropriate folder under `/user-docs/`
2. Use descriptive filenames (they become page titles)
3. The system automatically discovers and indexes new files

### File Naming Conventions

- Use kebab-case for filenames: `my-document-name.md`
- Filenames are converted to Title Case for display
- Common abbreviations (API, PHP, CSS, etc.) are properly capitalized

### Markdown Features

The system supports standard markdown with enhancements:
- Headers (h1-h6)
- Bold, italic, strikethrough text
- Ordered and unordered lists
- Code blocks with syntax highlighting
- Inline code
- Links and images
- Tables
- Blockquotes
- Horizontal rules

## Automated Documentation

### Documentation Check

Run the documentation check to verify:
```bash
composer docs:check
```

This checks for:
- Missing documentation files
- Outdated content
- Broken internal links
- Incomplete sections

### Documentation Update

Update documentation automatically:
```bash
composer docs:update
```

This will:
- Generate missing documentation stubs
- Update table of contents
- Sync documentation indexes
- Update cross-references

## Best Practices

### Writing Guidelines

1. **Start with Overview**: Begin each document with a clear overview section
2. **Use Clear Headers**: Structure content with logical header hierarchy
3. **Include Examples**: Provide code examples and practical use cases
4. **Keep It Current**: Update documentation when features change
5. **Cross-reference**: Link to related documentation where appropriate

### Organization Tips

1. **Logical Grouping**: Place documents in the most logical category
2. **Avoid Duplication**: Don't duplicate content across categories
3. **Use Subheadings**: Break long documents into digestible sections
4. **Progressive Disclosure**: Start simple, add complexity gradually

## Technical Details

### Implementation

The documentation system is implemented in:
- `/iso-admin/documentation.php` - Main documentation viewer
- `/iso-automation/src/Generators/UserdocsGenerator.php` - Documentation generator
- `/iso-automation/src/Analyzers/DocumentationAnalyzer.php` - Documentation analyzer

### Category Configuration

Categories are defined with:
- **title**: Display name
- **description**: Brief explanation of category purpose
- **icon**: Visual identifier
- **order**: Display order (1-10)
- **pages**: Dynamically populated document list

### Performance

- Documents are loaded on-demand (not pre-cached)
- Navigation structure is built once per page load
- Markdown parsing is done server-side for security
- Client-side enhancements use vanilla JavaScript (no heavy frameworks)

## Future Enhancements

Planned improvements include:
- Full-text search across all documentation
- Version history and change tracking
- Multi-language support
- PDF export functionality
- Interactive API testing from documentation
- Video tutorial integration
- Community contribution system
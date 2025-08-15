# Isotone User Documentation

This directory contains user-facing documentation for the Isotone CMS.

## 📚 Documentation Structure

```
user-docs/
├── index.md                    # Documentation home
├── getting-started/            # Installation and setup
│   ├── installation.md
│   ├── configuration.md
│   └── first-steps.md
├── developers/                 # Developer documentation
│   ├── themes.md              # Theme development
│   ├── plugins.md             # Plugin development
│   ├── hooks.md               # Hooks reference
│   └── template-functions.md  # Template functions
├── api/                       # API documentation
│   ├── theme-api.md
│   ├── content-api.md
│   ├── models.md
│   └── rest.md
├── guide/                     # User guides
│   ├── admin.md
│   ├── content.md
│   ├── themes.md
│   └── plugins.md
└── .vitepress/               # Static site generator config
    └── config.js

```

## 🚀 Viewing Documentation

### Local Development
The documentation can be viewed directly as Markdown files or served using a static site generator.

### Using VitePress (Recommended)
```bash
# Install VitePress
npm install -D vitepress

# Start dev server
npx vitepress dev user-docs

# Build static site
npx vitepress build user-docs
```

### Using MkDocs
```bash
# Install MkDocs
pip install mkdocs

# Start dev server
mkdocs serve

# Build static site
mkdocs build
```

### Using Docusaurus
```bash
# Install Docusaurus
npm init docusaurus

# Start dev server
npm start

# Build static site
npm run build
```

## 📝 Documentation Guidelines

1. **Keep it Simple**: Write clear, concise documentation
2. **Use Examples**: Include code examples where relevant
3. **Stay Organized**: Follow the established structure
4. **Update Regularly**: Keep docs in sync with code changes
5. **Test Code**: Ensure all code examples work

## 🔄 Auto-generated Files

Some files are automatically generated:
- `development/api-reference.md` - Generated from hooks implementation
- Hook documentation is generated via `composer docs:hooks`

## 🎯 Future Plans

- Deploy to GitHub Pages or Netlify
- Add search functionality
- Include video tutorials
- Multi-language support
- Interactive examples

## 📄 License

Documentation is licensed under CC BY 4.0. Code examples are MIT licensed.
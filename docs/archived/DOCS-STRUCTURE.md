# Documentation Structure

Isotone maintains two separate documentation systems for different audiences:

## 📁 `/docs/` - LLM & Development Documentation

**Purpose:** AI assistant integration and automated tooling  
**Audience:** LLMs (Claude, Copilot, Cursor), automated scripts, CI/CD

### Why Keep It Flat?
- 95+ hardcoded references across the codebase
- IDE integrations depend on exact paths
- Automated scripts expect this structure
- Breaking changes would affect all AI assistants

### Contents:
- `LLM-*.md` - AI assistant instructions
- `AI-*.md` - Coding standards for AI
- `PROMPT-*.md` - Prompt engineering guides
- Technical implementation details
- Automated workflow documentation

**⚠️ DO NOT REORGANIZE THIS FOLDER** - It will break LLM integrations

## 📁 `/user-docs/` - Human-Readable Documentation

**Purpose:** Well-organized documentation for humans  
**Audience:** Developers, administrators, end users

### Structure:
```
user-docs/
├── installation/     # Setup and deployment
├── configuration/    # Settings and config
├── development/      # Developer guides
├── api/             # API documentation
└── guides/          # User guides
```

### Benefits:
- Logical organization for humans
- Easy navigation
- Category-based structure
- Can be reorganized without breaking systems

## Which Documentation to Update?

### Files That Exist in Both Locations

These files are maintained in `/docs/` and copied to `/user-docs/`:
- `DEVELOPMENT-SETUP.md` → `user-docs/installation/development-setup.md`
- `GETTING-STARTED.md` → `user-docs/development/getting-started.md`
- `ISOTONE-TECH-STACK.md` → `user-docs/installation/tech-stack.md`
- `CONFIGURATION.md` → `user-docs/configuration/config-guide.md`
- `DATABASE-CONNECTION.md` → `user-docs/configuration/database.md`
- `API-REFERENCE.md` → `user-docs/development/api-reference.md`

**Always update the `/docs/` version first**, then copy to `/user-docs/`.

### Update `/docs/` when:
- Adding LLM-specific rules
- Changing AI coding standards
- Updating automated workflows
- Modifying development patterns
- **Updating any shared documentation files**

### Update `/user-docs/` when:
- Writing user guides (unique to user-docs)
- Creating tutorials (unique to user-docs)
- Adding human-specific documentation
- Improving organization/navigation

### Update Both when:
- Shared files change (update `/docs/` first, then sync)
- Major feature additions affect both audiences
- Configuration changes
- Installation process updates

## For LLMs/AI Assistants

When an LLM asks about documentation:
1. Check `/docs/` for technical/development info
2. Check `/user-docs/` for user-facing guides
3. Keep `/docs/` references unchanged
4. Feel free to reorganize `/user-docs/` as needed

## For Humans

- **End Users:** Start with `/user-docs/README.md`
- **Developers:** Check both folders
- **Contributors:** Read `/CONTRIBUTING.md` first
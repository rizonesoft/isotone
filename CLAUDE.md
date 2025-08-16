# CLAUDE.md - LLM Master Instruction File

This file provides instructions to Claude Code (claude.ai/code) and other LLMs on how to use the **Isotone Automation System** for all rules, guidelines, and development practices.

## 🚨 CRITICAL: Use the Automation System for ALL Rules

**DO NOT DUPLICATE RULES HERE** - All rules are managed in the Isotone Automation System at `/iso-automation/config/rules.yaml`

## 🗄️ CRITICAL: Database Connection from WSL

**ALWAYS connect to MySQL from WSL using these methods:**

### Direct MySQL CLI Connection:
```bash
# CORRECT - Always use the Windows host IP
mysql -h 172.19.240.1 -u root isotone_db

# WRONG - Never use these from WSL
mysql -h localhost -u root isotone_db  # FAILS
mysql -h 127.0.0.1 -u root isotone_db  # FAILS
```

### Finding the Windows Host IP:
```bash
# Get the Windows host IP from WSL
ip route | grep default | awk '{print $3}'
# Usually returns: 172.19.240.1
```

### RedBeanPHP Connection:
The DatabaseService automatically detects WSL and uses the correct IP. Do NOT modify this behavior.

## 📋 How to Access Rules

### 1. **Before ANY Task - Check Relevant Rules**
```bash
# View all rules in the system
cat /iso-automation/config/rules.yaml

# Search for specific rules
php iso-automation/cli.php rules:search "database"
php iso-automation/cli.php rules:search "css"
php iso-automation/cli.php rules:search "hooks"

# List all rule categories
php iso-automation/cli.php rules:list

# Check if a rule exists
php iso-automation/cli.php rules:check "branding"
```

### 2. **Rule Categories You MUST Check**

Before implementing anything, check these rule categories:

| Task Type | Required Rule Sections |
|-----------|----------------------|
| **Any task** | `branding`, `search_before_create`, `project_constraints` |
| **Database work** | `database_operations`, `database_connection` |
| **CSS/Styling** | `css_architecture`, `styling_separation` |
| **Configuration** | `configuration`, `llm_config_rules` |
| **Version updates** | `version_management`, `version_bump_process` |
| **Documentation** | `documentation`, `maintenance_system` |
| **Plugins** | `plugin_development`, `hook_naming_conventions` |
| **Themes** | `theme_development`, `template_hierarchy` |
| **Git operations** | `auto_commit`, `git_workflow` |

### 3. **Priority System**
Rules have priorities (0-100). Higher priority rules override lower ones:
- **100**: Critical rules (branding, security)
- **90-99**: Core system rules
- **70-89**: Development practices
- **50-69**: Guidelines and recommendations
- **Below 50**: Optional/situational

## 🔧 Understanding the Automation System

### System Architecture
```
/iso-automation/
├── cli.php                 # CLI entry point
├── config/
│   └── rules.yaml         # ALL RULES ARE HERE
├── src/
│   ├── Analyzers/         # Code analysis tools
│   ├── Core/              # Core automation engine
│   ├── Dashboard/         # Web dashboard components
│   ├── Generators/        # Documentation generators
│   ├── Rules/             # Rule processing engine
│   └── Storage/           # Data persistence
└── cache/                 # Cached analysis results
```

### Key Features
1. **Rule Management**: Centralized rule storage and validation
2. **Code Analysis**: Automatic code quality checks
3. **Documentation Generation**: Auto-generate docs from code
4. **Hook System Integration**: Manages WordPress-compatible hooks
5. **Validation**: Ensures rules are followed

### Available CLI Commands
```bash
# Rule management
php iso-automation/cli.php rules:list              # List all rules
php iso-automation/cli.php rules:search <term>     # Search rules
php iso-automation/cli.php rules:check <name>      # Check specific rule
php iso-automation/cli.php rules:validate          # Validate all rules

# Analysis
php iso-automation/cli.php analyze:code            # Analyze codebase
php iso-automation/cli.php analyze:hooks           # Analyze hook usage
php iso-automation/cli.php analyze:docs            # Check documentation

# Generation
php iso-automation/cli.php generate:docs           # Generate documentation
php iso-automation/cli.php generate:hooks-docs     # Generate hooks documentation

# Dashboard
php iso-automation/cli.php dashboard:serve         # Start web dashboard
```

### Web-Based Tools
1. **Automation Dashboard**: `/isotone/iso-admin/automation.php`
   - View system health and statistics
   - Execute documentation tasks
   - Monitor real-time command output
   - Clear caches and refresh status

2. **Hooks Explorer**: `/isotone/iso-admin/hooks-explorer.php`
   - Interactive hook documentation browser
   - Search and filter hooks by name/type
   - View usage examples and implementation locations
   - Copy hook names with one click
   - See real-time registered hooks and callbacks

## 📝 How to Add or Update Rules

### Adding a New Rule
1. **Check if it exists first**:
```bash
php iso-automation/cli.php rules:search "your topic"
```

2. **Find the appropriate section** in `/iso-automation/config/rules.yaml`

3. **Add your rule** following this structure:
```yaml
  rule_name:
    priority: 80  # 0-100, higher = more important
    enabled: true
    description: "Brief description"
    details:
      - "Specific requirement 1"
      - "Specific requirement 2"
    examples:
      good: "Example of correct usage"
      bad: "Example of incorrect usage"
    violations:
      - "What constitutes a violation"
```

4. **Validate the rules**:
```bash
php iso-automation/cli.php validate:rules
```

### Updating Existing Rules
1. **Never duplicate** - Check if a similar rule exists
2. **Maintain consistency** - Follow existing formatting
3. **Preserve priority** - Don't change priorities without reason
4. **Document changes** - Commit with clear message

## 🔍 How to Verify Rules Apply to Current Code

### 1. Check Rule Relevance
```bash
# Check if a rule's files/patterns still exist
php iso-automation/cli.php analyze:code --rule=<rule_name>

# Example: Check if database rules still apply
php iso-automation/cli.php analyze:code --rule=database_operations
```

### 2. Validate Against Codebase
```bash
# Run full validation
php iso-automation/cli.php validate:all

# Check specific areas
php iso-automation/cli.php validate:routes
php iso-automation/cli.php validate:hooks
php iso-automation/cli.php validate:database
```

### 3. Update Outdated Rules
If you find an outdated rule:
1. Check current implementation
2. Update the rule in `rules.yaml`
3. Validate changes
4. Document the update

## 🤖 Your Workflow as an LLM

### For EVERY Task:

1. **START** - Read the automation rules:
```bash
# Check relevant rules for your task
php iso-automation/cli.php rules:search "<task keyword>"
```

2. **IMPLEMENT** - Follow the rules exactly:
- Check `priority` - Higher priority rules override lower ones
- Check `enabled` - Only follow enabled rules
- Use `examples` - Follow the good examples, avoid the bad

3. **VALIDATE** - Ensure compliance:
```bash
# Validate your changes
php iso-automation/cli.php validate:all
composer docs:check
```

4. **UPDATE** - If rules need changing:
- Edit `/iso-automation/config/rules.yaml`
- Run validation
- Update this file (CLAUDE.md) if automation system changes

## 📚 Critical Rules to Always Remember

These are pointers to the most critical rules in the automation system:

1. **Branding** (`branding`): Always "Isotone", never "Isotone CMS"
2. **Search First** (`search_before_create`): Always search before creating
3. **Database** (`database_operations`): RedBeanPHP only, no raw SQL
4. **Config** (`configuration`): Use config.php, never .env files
5. **CSS** (`css_architecture`): No inline CSS, use modular system
6. **Hooks** (`hook_naming_conventions`): Use iso_ prefix for WP equivalents
7. **Version** (`version_management`): Follow proper bump process
8. **Auto-commit** (`auto_commit`): Commit on user satisfaction signals

## 🔄 Keeping CLAUDE.md Updated

This file should ONLY be updated when:

1. **Automation system structure changes**
   - New directories added
   - CLI commands changed
   - New features added

2. **Rule access methods change**
   - New search capabilities
   - New validation tools
   - Changed CLI interface

3. **Critical workflow changes**
   - New required validation steps
   - Changed priority system
   - New rule categories

**DO NOT** add specific rules here - they belong in `/iso-automation/config/rules.yaml`

## 🎯 Quick Reference

### Most Used Commands
```bash
# Before starting work
php iso-automation/cli.php rules:list
php iso-automation/cli.php analyze:code

# During development
php iso-automation/cli.php rules:search "topic"
php iso-automation/cli.php validate:all

# Before committing
composer docs:check
php iso-automation/cli.php validate:rules

# Documentation
php iso-automation/cli.php generate:docs
php iso-automation/cli.php generate:hooks-docs
```

### Where to Find Specific Information
- **All rules**: `/iso-automation/config/rules.yaml`
- **Hooks documentation**: Run `php iso-automation/cli.php generate:hooks-docs`
- **Project structure**: Check `project_structure` rule section
- **Common tasks**: Check `llm_task_reference` rule section
- **Version management**: Check `version_management` rule section

## 🚀 Your Mission as an LLM

1. **Always consult the automation system** before making decisions
2. **Never duplicate rules** - use the centralized system
3. **Validate everything** - use the provided tools
4. **Keep rules updated** - remove outdated, add missing
5. **Document changes** - maintain clear history

Remember: The Isotone Automation System is your single source of truth for all development rules and practices. This file (CLAUDE.md) is just your guide on HOW to use that system effectively.

---
*Last updated: 2025-01-16*
*Automation System Version: 1.0.0*
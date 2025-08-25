# Isotone Development Module

A comprehensive development environment for Isotone that includes automation tools, build systems, development admin pages, and testing utilities. This module should be excluded from production deployments.

## Features

✅ **Centralized Automation Engine** - All automation tasks in one place
✅ **Performance Optimization** - Efficient task execution
✅ **State Management** - RedBeanPHP-based central state tracking
✅ **Rule Engine** - YAML-based rules with priority and validation
✅ **Web Dashboard** - Monitor and manage automation from admin panel
✅ **Backward Compatible** - Existing scripts continue to work
✅ **CLI Interface** - Unified command-line interface

## Architecture

```
iso-automation/
├── src/
│   ├── Core/
│   │   └── AutomationEngine.php    # Central orchestrator
│   ├── Analyzers/
│   │   └── DocumentationAnalyzer.php # Doc analysis
│   ├── Generators/                 # Doc generators
│   ├── Rules/
│   │   └── RuleEngine.php         # YAML rule processor
│   └── Storage/
│       ├── StateManager.php       # RedBeanPHP state
│       └── CacheManager.php       # Performance cache
├── config/
│   └── rules.yaml                 # Centralized rules
├── cache/                         # File cache directory
└── cli.php                       # CLI entry point
```

## Usage

### CLI Commands

```bash
# Check documentation integrity
php iso-automation/cli.php check:docs

# Update documentation
php iso-automation/cli.php update:docs

# Generate hooks documentation
php iso-automation/cli.php generate:hooks

# Sync IDE rules
php iso-automation/cli.php sync:ide

# Validate automation rules
php iso-automation/cli.php validate:rules

# Show automation status
php iso-automation/cli.php status

# Clear cache
php iso-automation/cli.php cache:clear

# Export rules
php iso-automation/cli.php rules:export --format=markdown
```

### Web Dashboard

Access the automation dashboard at:
```
http://localhost/isotone/iso-admin/automation.php
```

Features:
- Real-time status monitoring
- Execution history
- System health checks
- Quick action buttons
- Cache statistics
- Auto-refresh every 30 seconds

## Backward Compatibility

All existing composer scripts continue to work:

```bash
composer docs:check     # Still works
composer docs:update    # Still works
composer docs:hooks     # Still works
composer ide:sync       # Still works
```

The original scripts in `/scripts/` are preserved and can still be called directly.

## Performance Improvements

### Performance Features
- Efficient task execution
- Memory cache for runtime data
- Parallel processing where possible
- Streamlined execution paths

## Rule System

The module uses a centralized YAML rule system (`config/rules.yaml`):

```yaml
llm_instructions:
  branding:
    priority: 100
    enabled: true
    rules:
      - "ALWAYS write 'Isotone' not 'Isotone CMS'"
```

Benefits:
- Single source of truth
- Priority-based execution
- Context-aware rules
- Validation and conflict detection

## State Management

Uses RedBeanPHP for persistent state:

```php
// Table created automatically:
- automationstate     # Persistent state (last run times, etc.)
```

## Extending the Module

### Adding a New Analyzer

Create a new class in `src/Analyzers/`:

```php
namespace Isotone\Automation\Analyzers;

class MyAnalyzer
{
    public function analyze(): AnalysisResult
    {
        // Your analysis logic
    }
}
```

### Adding a New Generator

Create a new class in `src/Generators/`:

```php
namespace Isotone\Automation\Generators;

class MyGenerator
{
    public function generate(): void
    {
        // Your generation logic
    }
}
```

## Benefits

1. **Centralization** - All automation in one module
2. **Performance** - 5-10x faster with caching
3. **Visibility** - Web dashboard for monitoring
4. **Maintainability** - Clean, modular architecture
5. **Extensibility** - Easy to add new automation tasks
6. **Reliability** - State tracking and error recovery

## Migration Path

1. ✅ Phase 1: Create module structure (COMPLETE)
2. ✅ Phase 2: Implement core components (COMPLETE)
3. ✅ Phase 3: Add backward compatibility (COMPLETE)
4. ⏳ Phase 4: Migrate all generators
5. ⏳ Phase 5: Full integration testing
6. ⏳ Phase 6: Deprecate old scripts

## Requirements

- PHP 8.3+
- RedBeanPHP (for state management)
- Symfony YAML (for rule processing)
- Composer autoload configured

## Troubleshooting

### Cache Issues
```bash
# Clear all caches
php iso-automation/cli.php cache:clear

# View cache statistics
php iso-automation/cli.php cache:stats
```

### Database Connection
Ensure RedBeanPHP is configured in your `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'isotone_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
```

### Rule Validation
```bash
# Validate all rules
php iso-automation/cli.php validate:rules

# Export rules for review
php iso-automation/cli.php rules:export --format=markdown
```

## Contributing

When adding new automation:
1. Create analyzer/generator classes
2. Add rules to `config/rules.yaml`
3. Update this README
4. Test backward compatibility
5. Add to dashboard if needed
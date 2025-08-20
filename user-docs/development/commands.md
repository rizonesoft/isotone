# Isotone CLI Commands Reference

This document provides a comprehensive reference for all CLI commands available in Isotone.

## Table of Contents
- [Isotone Core CLI](#isotone-core-cli)
- [Automation CLI](#automation-cli)
- [Composer Scripts](#composer-scripts)

## Isotone Core CLI

The main Isotone CLI is accessed via `php isotone <command>`.

### Version Management
| Command | Description |
|---------|-------------|
| `php isotone version:bump <type> [stage]` | Bump version (patch/minor/major) |
| `php isotone version:show` | Show current version |
| `php isotone changelog` | Generate changelog |

### Database Commands
| Command | Description |
|---------|-------------|
| `php isotone db:migrate` | Run database migrations |
| `php isotone db:rollback` | Rollback last migration |
| `php isotone db:reset` | Reset database |

### Hooks Commands
| Command | Description |
|---------|-------------|
| `php isotone hooks:list` | List all registered hooks |
| `php isotone hooks:debug <hook>` | Debug specific hook |

## Automation CLI

The automation system CLI is accessed via `php iso-automation/cli.php <command>`.

### Documentation Commands
| Command | Description |
|---------|-------------|
| `check:docs` | Check documentation integrity |
| `update:docs` | Update documentation from code |
| `generate:hooks` | Generate hooks documentation |

### Rules Management Commands
| Command | Description |
|---------|-------------|
| `rules:list` | List all automation rules |
| `rules:search <keyword>` | Search for rules by keyword |
| `rules:check <name>` | Show details of a specific rule |
| `rules:validate` | Validate all rules |
| `rules:export [--format=yaml\|json\|markdown]` | Export rules in specified format |

### Tailwind CSS Commands
| Command | Description |
|---------|-------------|
| `tailwind:build` | Build Tailwind CSS for production |
| `tailwind:watch` | Watch files and rebuild on changes |
| `tailwind:minify` | Build minified production CSS |
| `tailwind:install` | Install build dependencies |
| `tailwind:update` | Update Tailwind to latest version |
| `tailwind:status` | Show build status and versions |

### System Commands
| Command | Description |
|---------|-------------|
| `sync:ide` | Sync IDE rules from CLAUDE.md |
| `status` | Show automation status |
| `help` | Show help message |

### Options
- `--quiet, -q` - Suppress output
- `--debug` - Show debug information

### Examples
```bash
# Check documentation
php iso-automation/cli.php check:docs

# Update documentation quietly
php iso-automation/cli.php update:docs --quiet

# Export rules as markdown
php iso-automation/cli.php rules:export --format=markdown

# Search for database rules
php iso-automation/cli.php rules:search database

# Build Tailwind CSS
php iso-automation/cli.php tailwind:build
```

## Composer Scripts

Composer scripts provide convenient shortcuts for common tasks.

### Testing & Quality
| Command | Description |
|---------|-------------|
| `composer test` | Run all tests |
| `composer test:unit` | Run unit tests only |
| `composer test:integration` | Run integration tests |
| `composer analyse` | Run static analysis with PHPStan |
| `composer check-style` | Check code style (PSR-12) |
| `composer fix-style` | Fix code style automatically |
| `composer pre-commit` | Run pre-commit checks |

### Documentation
| Command | Description |
|---------|-------------|
| `composer docs:check` | Check documentation integrity |
| `composer docs:update` | Update documentation from code |
| `composer docs:hooks` | Generate hooks documentation |
| `composer docs:all` | Run all documentation tasks |
| `composer hooks:docs` | Alias for docs:hooks |
| `composer hooks:scan` | Scan and document hooks |

### Tailwind CSS Build
| Command | Description |
|---------|-------------|
| `composer tailwind:build` | Build Tailwind CSS |
| `composer tailwind:watch` | Watch and rebuild CSS |
| `composer tailwind:minify` | Build minified CSS |
| `composer tailwind:install` | Install Tailwind dependencies |
| `composer tailwind:update` | Update Tailwind to latest |
| `composer tailwind:status` | Check build status |

### Version Management
| Command | Description |
|---------|-------------|
| `composer version:patch` | Bump patch version (0.0.x) |
| `composer version:minor` | Bump minor version (0.x.0) |
| `composer version:major` | Bump major version (x.0.0) |

### IDE Integration
| Command | Description |
|---------|-------------|
| `composer ide:sync` | Sync IDE rules from CLAUDE.md |
| `composer validate:rules` | Validate automation rules |

## Quick Reference

### Most Common Commands

```bash
# Development workflow
composer docs:check          # Before committing
composer tailwind:build       # Build CSS for production
composer test                 # Run tests
composer fix-style           # Fix code style

# Automation
php iso-automation/cli.php status           # Check system status
php iso-automation/cli.php rules:list       # View all rules
php iso-automation/cli.php tailwind:status  # Check Tailwind build

# Version management
composer version:patch       # Bump patch version
php isotone changelog        # Generate changelog
```

### Command Execution Locations

- **Isotone CLI**: Run from project root
- **Automation CLI**: Run from project root
- **Composer Scripts**: Run from project root
- **Tailwind Build**: Handled automatically via automation

### Notes

1. **Documentation Commands**: Always run `docs:check` before committing to ensure documentation is up-to-date
2. **Tailwind CSS**: Build CSS before deployment using `composer tailwind:build`
3. **Rules Management**: Use the automation system to manage all development rules
4. **Version Bumping**: Always generates changelog and updates documentation automatically

## Automation Dashboard

Many of these commands are also available through the web-based Automation Dashboard at `/isotone/iso-admin/automation.php`, which provides:

- Visual command execution
- Real-time output display
- System health monitoring
- Quick access to common tasks

## Troubleshooting

### Command Not Found
- Ensure you're in the project root directory
- Check that Composer dependencies are installed: `composer install`
- Verify the automation system is properly set up

### Permission Errors
- Ensure proper file permissions on executable scripts
- Check that PHP CLI is properly configured

### Build Errors
- For Tailwind issues, run `composer tailwind:status` to check setup
- For documentation issues, run with `--debug` flag for detailed output

---
*Last updated: 2025-01-18*

## Composer Commands

> Auto-generated on 2025-08-18 16:00:43

### Testing

#### `composer test`

```bash
phpunit
```

#### `composer test:unit`

```bash
phpunit --testsuite Unit
```

#### `composer test:integration`

```bash
phpunit --testsuite Integration
```

#### `composer analyse`

```bash
phpstan analyse iso-core --level=6
```

#### `composer check-style`

```bash
phpcs iso-core --standard=PSR12
```

#### `composer fix-style`

```bash
phpcbf iso-core --standard=PSR12
```

### Documentation

#### `composer docs:check`

```bash
php iso-automation/cli.php check:docs
```

#### `composer docs:update`

```bash
php iso-automation/cli.php update:docs
```

#### `composer docs:hooks`

```bash
php iso-automation/cli.php hooks:scan
```

#### `composer docs:all`

Runs the following commands in sequence:
1. `@docs:update`
1. `@docs:hooks`
1. `@docs:check`

#### `composer hooks:docs`

```bash
@docs:hooks
```

#### `composer hooks:scan`

```bash
php iso-automation/cli.php hooks:scan
```

### IDE & Development

#### `composer ide:sync`

```bash
php iso-automation/cli.php sync:ide
```

#### `composer validate:rules`

```bash
php iso-automation/cli.php validate:rules
```

### Version Management

#### `composer version:patch`

Runs the following commands in sequence:
1. `php isotone version:bump patch alpha`
1. `php isotone changelog`
1. `@docs:all`

#### `composer version:minor`

Runs the following commands in sequence:
1. `php isotone version:bump minor alpha`
1. `php isotone changelog`
1. `@docs:all`

#### `composer version:major`

Runs the following commands in sequence:
1. `php isotone version:bump major alpha`
1. `php isotone changelog`
1. `@docs:all`

### Other

#### `composer pre-commit`

Runs the following commands in sequence:
1. `@docs:check`
1. `@check-style`

### Tailwind CSS

#### `composer tailwind:build`

```bash
php iso-automation/cli.php tailwind:build
```

#### `composer tailwind:watch`

```bash
php iso-automation/cli.php tailwind:watch
```

#### `composer tailwind:minify`

```bash
php iso-automation/cli.php tailwind:minify
```

#### `composer tailwind:install`

```bash
php iso-automation/cli.php tailwind:install
```

#### `composer tailwind:update`

```bash
php iso-automation/cli.php tailwind:update
```

#### `composer tailwind:status`

```bash
php iso-automation/cli.php tailwind:status
```

## Testing

#### `composer test`

```bash
phpunit
```

#### `composer test:unit`

```bash
phpunit --testsuite Unit
```

#### `composer test:integration`

```bash
phpunit --testsuite Integration
```

#### `composer analyse`

```bash
phpstan analyse iso-core --level=6
```

#### `composer check-style`

```bash
phpcs iso-core --standard=PSR12
```

#### `composer fix-style`

```bash
phpcbf iso-core --standard=PSR12
```

### Documentation

#### `composer docs:check`

```bash
php iso-automation/cli.php check:docs
```

#### `composer docs:update`

```bash
php iso-automation/cli.php update:docs
```

#### `composer docs:hooks`

```bash
php iso-automation/cli.php hooks:scan
```

#### `composer docs:all`

Runs the following commands in sequence:
1. `@docs:update`
1. `@docs:hooks`
1. `@docs:check`

#### `composer hooks:docs`

```bash
@docs:hooks
```

#### `composer hooks:scan`

```bash
php iso-automation/cli.php hooks:scan
```

### IDE & Development

#### `composer ide:sync`

```bash
php iso-automation/cli.php sync:ide
```

#### `composer validate:rules`

```bash
php iso-automation/cli.php validate:rules
```

### Version Management

#### `composer version:patch`

Runs the following commands in sequence:
1. `php isotone version:bump patch alpha`
1. `php isotone changelog`
1. `@docs:all`

#### `composer version:minor`

Runs the following commands in sequence:
1. `php isotone version:bump minor alpha`
1. `php isotone changelog`
1. `@docs:all`

#### `composer version:major`

Runs the following commands in sequence:
1. `php isotone version:bump major alpha`
1. `php isotone changelog`
1. `@docs:all`

### Other

#### `composer pre-commit`

Runs the following commands in sequence:
1. `@docs:check`
1. `@check-style`

### Tailwind CSS

#### `composer tailwind:build`

```bash
php iso-automation/cli.php tailwind:build
```

#### `composer tailwind:watch`

```bash
php iso-automation/cli.php tailwind:watch
```

#### `composer tailwind:minify`

```bash
php iso-automation/cli.php tailwind:minify
```

#### `composer tailwind:install`

```bash
php iso-automation/cli.php tailwind:install
```

#### `composer tailwind:update`

```bash
php iso-automation/cli.php tailwind:update
```

#### `composer tailwind:status`

```bash
php iso-automation/cli.php tailwind:status
```

## Testing

#### `composer test`

```bash
phpunit
```

#### `composer test:unit`

```bash
phpunit --testsuite Unit
```

#### `composer test:integration`

```bash
phpunit --testsuite Integration
```

#### `composer analyse`

```bash
phpstan analyse iso-core --level=6
```

#### `composer check-style`

```bash
phpcs iso-core --standard=PSR12
```

#### `composer fix-style`

```bash
phpcbf iso-core --standard=PSR12
```

### Documentation

#### `composer docs:check`

```bash
php iso-automation/cli.php check:docs
```

#### `composer docs:update`

```bash
php iso-automation/cli.php update:docs
```

#### `composer docs:hooks`

```bash
php iso-automation/cli.php hooks:scan
```

#### `composer docs:all`

Runs the following commands in sequence:
1. `@docs:update`
1. `@docs:hooks`
1. `@docs:check`

#### `composer hooks:docs`

```bash
@docs:hooks
```

#### `composer hooks:scan`

```bash
php iso-automation/cli.php hooks:scan
```

### IDE & Development

#### `composer ide:sync`

```bash
php iso-automation/cli.php sync:ide
```

#### `composer validate:rules`

```bash
php iso-automation/cli.php validate:rules
```

### Version Management

#### `composer version:patch`

Runs the following commands in sequence:
1. `php isotone version:bump patch alpha`
1. `php isotone changelog`
1. `@docs:all`

#### `composer version:minor`

Runs the following commands in sequence:
1. `php isotone version:bump minor alpha`
1. `php isotone changelog`
1. `@docs:all`

#### `composer version:major`

Runs the following commands in sequence:
1. `php isotone version:bump major alpha`
1. `php isotone changelog`
1. `@docs:all`

### Other

#### `composer pre-commit`

Runs the following commands in sequence:
1. `@docs:check`
1. `@check-style`

### Tailwind CSS

#### `composer tailwind:build`

```bash
php iso-automation/cli.php tailwind:build
```

#### `composer tailwind:watch`

```bash
php iso-automation/cli.php tailwind:watch
```

#### `composer tailwind:minify`

```bash
php iso-automation/cli.php tailwind:minify
```

#### `composer tailwind:install`

```bash
php iso-automation/cli.php tailwind:install
```

#### `composer tailwind:update`

```bash
php iso-automation/cli.php tailwind:update
```

#### `composer tailwind:status`

```bash
php iso-automation/cli.php tailwind:status
```



## Available Commands

### `composer test`
Run all tests

### `composer test:unit`
Run unit tests only

### `composer test:integration`
Run integration tests

### `composer analyse`
Run static analysis with PHPStan

### `composer check-style`
Check code style with PHP_CodeSniffer

### `composer fix-style`
Fix code style issues automatically

### `composer docs:check`
Check documentation integrity

### `composer docs:update`
Update documentation from code

### `composer docs:index`
Runs the docs:index task

### `composer docs:lint`
Runs the docs:lint task

### `composer docs:build`
Runs the docs:build task

### `composer docs:html`
Runs the docs:html task

### `composer docs:hooks`
Generate hooks documentation

### `composer docs:all`
Run all documentation tasks

Runs:
- `@docs:update`
- `@docs:hooks`
- `@docs:index`

### `composer hooks:docs`
Runs the hooks:docs task

### `composer hooks:scan`
Runs the hooks:scan task

### `composer ide:sync`
Sync IDE helper files

### `composer validate:rules`
Validate automation rules

### `composer version:patch`
Bump patch version

Runs:
- `php isotone version:bump patch alpha`
- `php isotone changelog`

### `composer version:minor`
Bump minor version

Runs:
- `php isotone version:bump minor alpha`
- `php isotone changelog`

### `composer version:major`
Bump major version

Runs:
- `php isotone version:bump major alpha`
- `php isotone changelog`

### `composer pre-commit`
Runs the pre-commit task

Runs:
- `@check-style`

### `composer tailwind:build`
Runs the tailwind:build task

### `composer tailwind:watch`
Runs the tailwind:watch task

### `composer tailwind:minify`
Runs the tailwind:minify task

### `composer tailwind:install`
Runs the tailwind:install task

### `composer tailwind:update`
Runs the tailwind:update task

### `composer tailwind:status`
Runs the tailwind:status task


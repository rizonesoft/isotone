## Composer Commands

| Command | Description |
|---------|-------------|
| `composer test` | Run all tests |
| `composer test:unit` | Run unit tests only |
| `composer test:integration` | Run integration tests |
| `composer analyse` | Run static analysis with PHPStan |
| `composer check-style` | Check code style (PSR-12) |
| `composer fix-style` | Fix code style automatically |
| `composer docs:check` | Check documentation integrity |
| `composer docs:update` | Auto-update documentation |
| `composer docs:sync` | Custom command |
| `composer docs:hooks` | Custom command |
| `composer docs:all` | Custom command |
| `composer hooks:docs` | Custom command |
| `composer ide:sync` | Custom command |
| `composer version:patch` | Custom command |
| `composer version:minor` | Custom command |
| `composer version:major` | Custom command |
| `composer pre-commit` | Custom command |


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

### `composer docs:hooks`
Generate hooks documentation

### `composer docs:all`
Run all documentation tasks

Runs:
- `@docs:update`
- `@docs:hooks`
- `@docs:check`

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
- `@docs:all`

### `composer version:minor`
Bump minor version

Runs:
- `php isotone version:bump minor alpha`
- `php isotone changelog`
- `@docs:all`

### `composer version:major`
Bump major version

Runs:
- `php isotone version:bump major alpha`
- `php isotone changelog`
- `@docs:all`

### `composer pre-commit`
Runs the pre-commit task

Runs:
- `@docs:check`
- `@check-style`

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

### `composer docs:hooks`
Generate hooks documentation

### `composer docs:all`
Run all documentation tasks

Runs:
- `@docs:update`
- `@docs:hooks`
- `@docs:check`

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
- `@docs:all`

### `composer version:minor`
Bump minor version

Runs:
- `php isotone version:bump minor alpha`
- `php isotone changelog`
- `@docs:all`

### `composer version:major`
Bump major version

Runs:
- `php isotone version:bump major alpha`
- `php isotone changelog`
- `@docs:all`

### `composer pre-commit`
Runs the pre-commit task

Runs:
- `@docs:check`
- `@check-style`


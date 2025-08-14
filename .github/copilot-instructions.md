# GitHub Copilot Instructions for Isotone

## Project Overview
Isotone is a lightweight PHP content management system designed for shared hosting, developed primarily by AI assistants.

## Critical Rules for Code Generation

### NEVER Generate:
- Node.js/npm code or dependencies
- Database migration files
- Laravel/Symfony patterns
- Complex build processes

### ALWAYS Generate:
- PHP 8.3+ compatible code
- PSR-12 compliant code style
- PHPDoc comments for all methods
- Escaped output (prevent XSS)
- Code that works on shared hosting

## Required Documentation
When working on this project, consult:
- `CLAUDE.md` - Project-specific rules
- `docs/LLM-DEVELOPMENT-GUIDE.md` - LLM guide
- `docs/AI-CODING-STANDARDS.md` - Standards

## Project Structure
```
app/          # Core application (PSR-4)
public/       # Web root
plugins/      # WordPress-like plugins
themes/       # Theme files
docs/         # Documentation
```

## Key Technologies
- PHP 8.3+
- RedBeanPHP ORM (no migrations!)
- Composer for dependencies
- WordPress-like hooks: add_action(), add_filter()

## Testing Requirements
Before suggesting code completion:
1. Check PSR-12 compliance
2. Verify no npm dependencies
3. Ensure documentation is updated
4. Run: `composer docs:check`

## Database Patterns
```php
// Use RedBeanPHP - it creates tables automatically
$item = R::dispense('tablename');
$item->field = 'value';
R::store($item);
```

## Example Code Style
```php
<?php
declare(strict_types=1);

namespace Isotone\Core;

/**
 * Class description
 */
class ClassName
{
    /**
     * Method description
     * 
     * @param string $param Description
     * @return string
     */
    public function methodName(string $param): string
    {
        return htmlspecialchars($param, ENT_QUOTES, 'UTF-8');
    }
}
```
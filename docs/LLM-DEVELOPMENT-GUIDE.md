# LLM Development Guide for Isotone CMS

This guide is specifically designed for Large Language Models (LLMs) like Claude, GPT-4, and other AI assistants working on Isotone CMS.

## 🤖 Quick Context for LLMs

**Isotone CMS** is a lightweight PHP CMS designed for shared hosting. Key facts:
- PHP 8.3+ with PSR standards
- RedBeanPHP ORM (no migrations needed)
- WordPress-like hooks/filters
- Runs on XAMPP/shared hosting
- No Node.js required in production

## 📁 Critical Files to Read First

When starting a task, always check these files in order:

1. **`.env`** - Current environment configuration
2. **`app/Core/Application.php`** - Main application class, routing
3. **`composer.json`** - Dependencies and available commands
4. **`app/helpers.php`** - Available helper functions
5. **`CLAUDE.md`** - Project-specific instructions

## 🎯 Task-Specific Instructions

### When Creating a New Feature

1. **Check existing patterns first:**
   ```
   Look at: app/Core/*.php for similar implementations
   ```

2. **Follow the architecture:**
   - Controllers go in `app/Http/Controllers/`
   - Models extend RedBeanPHP's SimpleModel in `app/Models/`
   - Services contain business logic in `app/Services/`

3. **Use existing helpers:**
   - `env()` for environment variables
   - `config()` for configuration
   - `public_path()`, `storage_path()`, `content_path()` for paths

### When Adding a Route

Always add routes in `app/Core/Application.php::initializeRoutes()`:

```php
$this->routes->add('route_name', new Route('/path', [
    '_controller' => [$this, 'handleMethodName']
]));
```

### When Working with Database

1. **Never create migration files** - RedBeanPHP handles schema
2. **Use RedBean's syntax:**
   ```php
   $record = R::dispense('tablename');
   $record->field = 'value';
   R::store($record);
   ```

3. **Model files are optional** but go in `app/Models/`:
   ```php
   class Model_Tablename extends \RedBeanPHP\SimpleModel {}
   ```

### When Creating a Plugin

1. **Directory structure:**
   ```
   plugins/plugin-name/
   ├── plugin-name.php    # Main file
   ├── includes/          # PHP classes
   ├── assets/            # CSS, JS
   └── views/             # Templates
   ```

2. **Use hooks pattern:**
   ```php
   add_action('isotone_init', 'function_name');
   add_filter('isotone_content', 'filter_function');
   ```

### When Creating a Theme

1. **Directory structure:**
   ```
   themes/theme-name/
   ├── index.php         # Main template
   ├── style.css        # Theme info + styles
   ├── functions.php    # Theme functions
   └── templates/       # Template parts
   ```

2. **Theme header in style.css:**
   ```css
   /*
   Theme Name: Theme Name
   Version: 1.0.0
   */
   ```

## ⚠️ Critical Rules for LLMs

### NEVER Do These:

1. **NEVER use `npm` commands** - This is a PHP-only project
2. **NEVER create database migrations** - RedBeanPHP handles schema
3. **NEVER modify `vendor/` directory** - Use composer.json
4. **NEVER commit `.env` file** - Only .env.example
5. **NEVER use Laravel/Symfony patterns** - This is custom, lightweight
6. **NEVER assume Node.js is available** - Pure PHP only
7. **NEVER create complex build processes** - Keep it simple

### ALWAYS Do These:

1. **ALWAYS check if a pattern exists** before creating new one
2. **ALWAYS use PSR-12 code style**
3. **ALWAYS escape output** to prevent XSS
4. **ALWAYS use prepared statements** (RedBean does this)
5. **ALWAYS test on `/isotone/` URL path** not root
6. **ALWAYS preserve backward compatibility**
7. **ALWAYS update documentation** when adding features

## 📝 Code Generation Templates

### New Controller Method

```php
/**
 * Handle [description] request
 * 
 * @param Request $request
 * @return Response
 */
private function handleName(Request $request): Response
{
    try {
        // Logic here
        return new Response($html);
    } catch (\Exception $e) {
        return $this->handleError($e);
    }
}
```

### New Model

```php
<?php
declare(strict_types=1);

namespace Isotone\Models;

use RedBeanPHP\SimpleModel;

class ModelName extends SimpleModel
{
    public function method(): string
    {
        return $this->bean->property;
    }
}
```

### New Service

```php
<?php
declare(strict_types=1);

namespace Isotone\Services;

class ServiceName
{
    public function method(): void
    {
        // Business logic
    }
}
```

## 🔍 How to Analyze Codebase

When asked to understand or modify code:

1. **Start with these commands:**
   ```bash
   # See project structure
   ls -la
   
   # Check current routes
   grep "routes->add" app/Core/Application.php
   
   # Find all models
   ls app/Models/
   
   # Check composer scripts
   composer list
   ```

2. **Read in this order:**
   - Entry point: `public/index.php`
   - Bootstrap: `app/Core/Application.php`
   - Routes and handlers
   - Related models/services

## 🧪 Testing Your Changes

After making changes, verify:

1. **Syntax is valid:**
   ```bash
   php -l filename.php
   ```

2. **Code style is correct:**
   ```bash
   composer check-style
   ```

3. **No obvious errors:**
   - Visit the page in browser
   - Check `storage/logs/` for errors
   - Verify `.env` has all needed values

## 💡 Optimization Tips for LLMs

1. **Batch related changes** - Make multiple related edits in one response
2. **Use existing code as templates** - Copy patterns from similar files
3. **Keep responses focused** - One feature at a time
4. **Document while coding** - Add PHPDoc blocks
5. **Think about shared hosting** - Avoid heavy operations

## 🚀 Common Tasks Reference

### Add Admin Page
1. Add route in `Application.php`
2. Create handler method
3. Return HTML response

### Add Database Table
1. Just use `R::dispense('tablename')`
2. RedBean creates it automatically
3. Optionally add Model class

### Add Configuration Option
1. Add to `.env.example`
2. Update existing `.env`
3. Use `env('KEY')` to access

### Add Plugin Hook
1. Define hook point with `do_action()`
2. Document in plugin guide
3. Add example usage

## 📋 Checklist Before Completing Task

- [ ] Code follows PSR-12 standard
- [ ] All methods have PHPDoc comments
- [ ] No hardcoded paths (use helpers)
- [ ] Output is escaped for security
- [ ] Changes work with XAMPP setup
- [ ] Documentation is updated
- [ ] No Node.js dependencies added
- [ ] Backward compatibility maintained

## 🆘 When Stuck

If you're unsure:

1. **Check existing similar code** in the project
2. **Look for patterns** in `app/Core/`
3. **Follow WordPress conventions** for plugins/hooks
4. **Keep it simple** - this is lightweight CMS
5. **Ask for clarification** rather than assume

## 📚 Reference Documents

- `CLAUDE.md` - Project-specific rules
- `docs/AI-CODING-STANDARDS.md` - AI-specific coding standards
- `docs/getting-started.md` - Basic concepts
- `docs/development-setup.md` - Environment details

---

*This guide is specifically for LLM developers. Always prioritize simplicity, security, and shared-hosting compatibility.*
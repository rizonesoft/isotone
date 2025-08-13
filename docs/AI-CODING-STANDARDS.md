# AI Coding Standards for Isotone CMS

This document defines coding standards specifically for AI/LLM developers working on Isotone CMS.

## 🎯 Core Principles

1. **Simplicity First** - Avoid over-engineering
2. **Shared Hosting Compatible** - Must work on basic PHP hosting
3. **No Build Steps** - PHP/HTML/CSS only, no compilation
4. **WordPress Patterns** - Familiar to PHP developers
5. **Security by Default** - Escape output, validate input

## 📝 Code Style Rules

### PHP Files

```php
<?php
declare(strict_types=1);  // ALWAYS use strict types

namespace Isotone\Module;  // ALWAYS use namespaces

use Required\Classes;  // Group use statements

/**
 * Class description (REQUIRED)
 * 
 * @package Isotone
 */
class ClassName
{
    /**
     * Method description (REQUIRED)
     * 
     * @param string $param Parameter description
     * @return string What it returns
     * @throws \Exception When it throws
     */
    public function methodName(string $param): string
    {
        // Implementation
    }
}
```

### Naming Conventions

| Type | Convention | Example |
|------|-----------|---------|
| Classes | PascalCase | `UserController` |
| Methods | camelCase | `getUserById()` |
| Properties | camelCase | `$userName` |
| Constants | UPPER_SNAKE | `MAX_UPLOAD_SIZE` |
| Files | PascalCase.php | `UserModel.php` |
| Directories | lowercase | `controllers/` |
| Database tables | snake_case | `user_posts` |
| Database columns | snake_case | `created_at` |

### File Organization

```php
<?php
// 1. File header
declare(strict_types=1);

// 2. Namespace
namespace Isotone\Core;

// 3. Use statements (alphabetically)
use Isotone\Models\User;
use RedBeanPHP\R;
use Symfony\Component\HttpFoundation\Request;

// 4. Class/Interface definition
class Example
{
    // 5. Constants
    private const DEFAULT_LIMIT = 10;
    
    // 6. Properties
    private string $name;
    
    // 7. Constructor
    public function __construct()
    {
    }
    
    // 8. Public methods
    public function publicMethod(): void
    {
    }
    
    // 9. Protected methods
    protected function protectedMethod(): void
    {
    }
    
    // 10. Private methods
    private function privateMethod(): void
    {
    }
}
```

## 🔒 Security Standards

### ALWAYS Apply These:

1. **Escape Output**
   ```php
   // HTML context
   echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
   
   // URL context
   echo urlencode($userInput);
   
   // JavaScript context
   echo json_encode($userInput);
   ```

2. **Validate Input**
   ```php
   // Type checking
   if (!is_string($input)) {
       throw new \InvalidArgumentException('String expected');
   }
   
   // Range checking
   if ($age < 0 || $age > 120) {
       throw new \InvalidArgumentException('Invalid age');
   }
   
   // Pattern matching
   if (!preg_match('/^[a-z0-9_-]+$/i', $username)) {
       throw new \InvalidArgumentException('Invalid username');
   }
   ```

3. **Use Prepared Statements**
   ```php
   // RedBean handles this automatically
   $users = R::find('user', 'email = ?', [$email]);
   
   // NEVER do string concatenation
   // BAD: R::find('user', "email = '$email'");
   ```

4. **CSRF Protection**
   ```php
   // Generate token
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
   
   // Verify token
   if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
       throw new \Exception('CSRF token mismatch');
   }
   ```

## 🏗️ Architecture Standards

### Directory Structure

```
app/
├── Core/           # Core system (Application, Router, etc.)
├── Http/
│   ├── Controllers/  # Request handlers
│   └── Middleware/   # Request filters
├── Models/         # Data models (RedBean)
├── Services/       # Business logic
├── Views/          # Templates (if needed)
└── Helpers/        # Utility functions
```

### Dependency Injection

```php
// GOOD: Inject dependencies
class UserService
{
    public function __construct(
        private UserRepository $repository,
        private EmailService $emailer
    ) {}
}

// BAD: Create dependencies internally
class UserService
{
    public function __construct()
    {
        $this->repository = new UserRepository();  // BAD
        $this->emailer = new EmailService();       // BAD
    }
}
```

### Error Handling

```php
// GOOD: Specific exceptions
class UserNotFoundException extends \Exception {}
class InvalidEmailException extends \Exception {}

// GOOD: Meaningful error messages
throw new UserNotFoundException(
    sprintf('User with ID %d not found', $userId)
);

// BAD: Generic exceptions
throw new \Exception('Error');  // Too vague
```

## 📦 Database Standards

### Using RedBeanPHP

```php
// GOOD: Let RedBean handle schema
$post = R::dispense('post');
$post->title = 'Hello';
R::store($post);

// BAD: Manual SQL for schema
R::exec('CREATE TABLE posts...');  // Let RedBean do this
```

### Model Conventions

```php
namespace Isotone\Models;

// Model class name: Model_[tablename]
class Model_Post extends \RedBeanPHP\SimpleModel
{
    // Validation in update method
    public function update(): void
    {
        if (empty($this->bean->title)) {
            throw new \Exception('Title required');
        }
    }
    
    // Getters for computed properties
    public function getExcerpt(): string
    {
        return substr($this->bean->content, 0, 200) . '...';
    }
}
```

## 🎨 Frontend Standards

### HTML Generation

```php
// GOOD: Semantic HTML
$html = <<<HTML
<article class="post">
    <header>
        <h1>{$title}</h1>
        <time datetime="{$date}">{$formatted_date}</time>
    </header>
    <div class="content">
        {$content}
    </div>
</article>
HTML;

// BAD: Inline styles and non-semantic
$html = '<div style="color:red">' . $content . '</div>';
```

### CSS Classes

```css
/* GOOD: BEM naming */
.post {}
.post__title {}
.post__content {}
.post--featured {}

/* GOOD: Utility classes (if using) */
.mb-4 {}  /* margin-bottom: 1rem */
.text-center {}

/* BAD: Generic names */
.content {}  /* Too generic */
.red {}      /* Color-specific */
```

## 🔄 Git Commit Standards

### Commit Message Format

```
type: Subject line (max 50 chars)

Longer description if needed (wrap at 72 chars)

- Bullet points for details
- Keep it clear and concise

Fixes #123
```

### Types:
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation
- `style:` Code style (no logic change)
- `refactor:` Code restructuring
- `test:` Tests
- `chore:` Maintenance

### Examples:
```
feat: Add user registration system

- Implement registration form
- Add email validation
- Create welcome email template

Fixes #45
```

## ✅ Code Review Checklist

When reviewing AI-generated code, check:

- [ ] **No Node.js dependencies** added
- [ ] **No complex build process** required
- [ ] **Follows PSR-12** standard
- [ ] **All output escaped** for XSS prevention
- [ ] **Database queries use RedBean** properly
- [ ] **Error messages are meaningful**
- [ ] **PHPDoc comments** are complete
- [ ] **No hardcoded paths** or credentials
- [ ] **Works on XAMPP/shared hosting**
- [ ] **Backward compatible** with existing code

## 🚫 Anti-Patterns to Avoid

### DON'T Do This:

```php
// Global variables
global $user;  // BAD

// Mixing logic and presentation
echo '<h1>' . R::count('users') . '</h1>';  // BAD

// Deep nesting
if ($a) {
    if ($b) {
        if ($c) {
            // BAD: Too deep
        }
    }
}

// God objects
class DoEverything {
    // 100+ methods - BAD
}

// Tight coupling
class A {
    public function method() {
        $b = new B();  // BAD: Direct instantiation
    }
}
```

### DO This Instead:

```php
// Dependency injection
public function __construct(User $user) {}

// Separate concerns
$count = $this->userService->getCount();
return $this->view->render('users', ['count' => $count]);

// Early returns
if (!$a) return;
if (!$b) return;
if (!$c) return;
// Good: Flat structure

// Single responsibility
class UserValidator {}  // One job
class UserRepository {} // One job

// Loose coupling
public function __construct(BInterface $b) {}
```

## 📊 Performance Standards

1. **Lazy load when possible**
2. **Cache expensive operations**
3. **Minimize database queries**
4. **Use indexes on frequent lookups**
5. **Avoid N+1 query problems**

```php
// GOOD: Eager loading
$posts = R::findAll('post');
R::preload($posts, ['author', 'comments']);

// BAD: N+1 queries
foreach ($posts as $post) {
    $author = $post->author;  // Query each time
}
```

## 🔍 Testing Standards

```php
class UserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test names describe behavior
     */
    public function testUserCanBeCreatedWithValidEmail(): void
    {
        // Arrange
        $email = 'test@example.com';
        
        // Act
        $user = new User($email);
        
        // Assert
        $this->assertEquals($email, $user->getEmail());
    }
}
```

## 📋 Documentation Standards

Every file should have:

```php
<?php
/**
 * File description
 * 
 * @package    Isotone
 * @subpackage Core
 * @author     AI Assistant
 * @since      1.0.0
 */
```

Every class should have:

```php
/**
 * Class description explaining purpose
 * 
 * @package Isotone
 * @since   1.0.0
 */
```

Every method should have:

```php
/**
 * Method description
 * 
 * @param  string $param Description
 * @return string Description
 * @throws \Exception When/why thrown
 * @since  1.0.0
 */
```

---

*These standards ensure AI-generated code is consistent, secure, and maintainable. Always prioritize simplicity and shared-hosting compatibility.*
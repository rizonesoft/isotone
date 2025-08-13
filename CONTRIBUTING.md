# Contributing to Isotone CMS

Thank you for your interest in contributing to Isotone CMS! We welcome contributions from both human developers and AI assistants.

## 🤖 Note for AI/LLM Contributors

**This project is AI-friendly!** Please read our LLM-specific guides:
- [LLM Development Guide](docs/LLM-DEVELOPMENT-GUIDE.md)
- [AI Coding Standards](docs/AI-CODING-STANDARDS.md)
- [Prompt Engineering Guide](docs/PROMPT-ENGINEERING-GUIDE.md)

## 📋 Before You Begin

1. **Read the documentation**
   - [Development Setup](docs/development-setup.md)
   - [Getting Started](docs/getting-started.md)
   - [Technology Stack](docs/isotone-tech-stack.md)

2. **Understand our constraints**
   - Must work on shared hosting (no Node.js)
   - PHP 8.3+ only
   - No build processes
   - XAMPP/WAMP/MAMP compatible

## 🔧 Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/YOUR-USERNAME/isotone.git
   cd isotone
   ```
3. Install dependencies:
   ```bash
   composer install
   ```
4. Copy environment file:
   ```bash
   cp .env.example .env
   ```
5. Configure your database in `.env`

## 💻 Making Changes

### Workflow

1. Create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes following our standards:
   - PSR-12 code style
   - Add PHPDoc comments
   - Write/update tests
   - Update documentation

3. Test your changes:
   ```bash
   composer test
   composer check-style
   composer analyse
   ```

4. Commit with descriptive message:
   ```bash
   git commit -m "feat: Add user authentication system
   
   - Implement login/logout
   - Add password hashing
   - Create session management"
   ```

5. Push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

6. Create a Pull Request

## 📝 Commit Message Format

We follow conventional commits:

```
type(scope): Subject

Body (optional)

Footer (optional)
```

**Types:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation only
- `style:` Code style (formatting, semicolons, etc)
- `refactor:` Code restructuring without feature changes
- `perf:` Performance improvements
- `test:` Adding tests
- `chore:` Maintenance tasks

**Examples:**
```
feat: Add social media sharing buttons

fix: Correct database connection timeout issue

docs: Update installation guide for Windows
```

## 🎯 What We're Looking For

### High Priority

- **Core Features**
  - Database integration completion
  - Plugin/hook system
  - Admin authentication
  - Theme system

- **Documentation**
  - API documentation
  - Video tutorials
  - Translation guides

- **Testing**
  - Unit tests for core modules
  - Integration tests
  - Browser testing

### Good First Issues

Look for issues labeled `good first issue` for beginner-friendly tasks:
- Documentation improvements
- Code style fixes
- Simple bug fixes
- Adding PHPDoc comments

## 🚫 What We DON'T Want

- Node.js dependencies
- Complex build processes
- Laravel/Symfony components (we're keeping it lightweight)
- Features requiring VPS/dedicated hosting
- Anything that breaks shared hosting compatibility

## 🔍 Code Review Process

1. **Automated checks** run on every PR:
   - PHPUnit tests
   - Code style (PSR-12)
   - Static analysis (PHPStan)

2. **Manual review** focuses on:
   - Security implications
   - Performance impact
   - Shared hosting compatibility
   - Code maintainability
   - Documentation completeness

3. **Feedback incorporation**
   - Address reviewer comments
   - Update PR as needed
   - Re-request review when ready

## 🏗️ Architecture Guidelines

### File Structure

```
app/
├── Core/        # Core system only
├── Http/        # Web layer
├── Models/      # Data models
├── Services/    # Business logic
└── helpers.php  # Global functions
```

### Design Patterns

- **Dependency Injection** over singletons
- **Service classes** for business logic
- **Repository pattern** for data access (optional)
- **WordPress-style hooks** for extensibility

### Database

- Use RedBeanPHP's conventions
- No manual migrations
- Model classes are optional
- Always use prepared statements

## 🧪 Testing

### Running Tests

```bash
# All tests
composer test

# Specific suite
composer test:unit
composer test:integration

# With coverage
composer test -- --coverage-html coverage
```

### Writing Tests

```php
class ExampleTest extends TestCase
{
    public function testFeatureWorks(): void
    {
        // Arrange
        $input = 'test';
        
        // Act
        $result = processInput($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

## 📚 Documentation

When adding features, update:
1. Code comments (PHPDoc)
2. README if needed
3. Relevant guides in `/docs`
4. CHANGELOG.md (if exists)

## 🔒 Security

- **Never commit secrets** (.env, API keys, passwords)
- **Escape all output** to prevent XSS
- **Use prepared statements** for database queries
- **Validate all input** before processing
- **Report security issues** privately to security@isotone.tech

## 💬 Getting Help

- **GitHub Issues** - Bug reports and feature requests
- **Discussions** - General questions and ideas
- **Discord** - Real-time chat (if available)
- **Documentation** - Check `/docs` folder

## 🙏 Recognition

Contributors are recognized in:
- README.md contributors section
- GitHub contributors page
- Release notes

## 📜 License

By contributing, you agree that your contributions will be licensed under the MIT License.

## 🚀 After Your PR is Merged

- Delete your feature branch
- Update your fork's main branch
- Celebrate! 🎉

Thank you for helping make Isotone CMS better!

---

*Questions? Open an issue or start a discussion.*
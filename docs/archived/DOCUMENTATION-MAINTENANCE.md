# Documentation Maintenance System for Isotone

This document defines how we keep ALL documentation synchronized with code changes.

## 📋 Documentation Inventory

### Critical Documents That Need Constant Updates

| Document | Update Triggers | Owner |
|----------|----------------|-------|
| README.md | Features, status changes, installation | Core |
| CLAUDE.md | New patterns, file changes, rules | Core |
| composer.json | Dependency changes, commands | Core |
| .env.example | New environment variables | Core |
| docs/GETTING-STARTED.md | New features, API changes | Core |
| docs/DEVELOPMENT-SETUP.md | Tool changes, requirements | Core |
| docs/LLM-DEVELOPMENT-GUIDE.md | New patterns, rules for AI | AI |
| docs/AI-CODING-STANDARDS.md | Style changes, new standards | AI |
| .windsurf-rules.md | IDE rule changes (auto-synced) | AI |
| .cursorrules | IDE rule changes (auto-generated) | AI |
| .github/copilot-instructions.md | IDE rule changes (auto-generated) | AI |
| API docs | Endpoint changes | Core |
| Plugin docs | Hook changes | Core |

## 🔄 The DDDD Process (Document-Driven Development & Deployment)

### 1. Pre-Code Documentation

**BEFORE writing code:**
```markdown
1. Update relevant .md files with planned changes
2. Add "🚧 Under Development" badge to features
3. Document expected behavior
4. Create/update examples
```

### 2. During Development

**WHILE coding:**
```markdown
1. Update inline PHPDoc comments
2. Keep CLAUDE.md current with new files/patterns
3. Update .env.example with new variables
4. Add to CHANGELOG.md (unreleased section)
```

### 3. Post-Code Verification

**AFTER coding:**
```markdown
1. Remove "🚧" badges, add "✅"
2. Update all examples to match implementation
3. Verify all referenced files exist
4. Check all code snippets actually work
```

## 🤖 Automated Documentation Checks

### 1. Documentation Validator Script

Create `scripts/check-docs.php`:

```php
<?php
/**
 * Documentation Integrity Checker
 * Ensures documentation matches codebase reality
 */

class DocChecker
{
    private array $errors = [];
    private array $warnings = [];
    
    public function run(): void
    {
        $this->checkReadmeFeatures();
        $this->checkFileReferences();
        $this->checkCodeExamples();
        $this->checkEnvVariables();
        $this->checkRoutes();
        $this->checkComposerScripts();
        $this->report();
    }
    
    private function checkReadmeFeatures(): void
    {
        $readme = file_get_contents('README.md');
        
        // Check if listed features actually exist
        if (strpos($readme, '✅ Database integration') !== false) {
            if (!file_exists('app/Core/Database.php')) {
                $this->errors[] = 'README claims database integration but Database.php missing';
            }
        }
    }
    
    private function checkFileReferences(): void
    {
        $docs = glob('docs/*.md');
        foreach ($docs as $doc) {
            $content = file_get_contents($doc);
            
            // Find all file references
            preg_match_all('/`([^`]+\.php)`/', $content, $matches);
            foreach ($matches[1] as $file) {
                if (!file_exists($file) && !file_exists('app/' . $file)) {
                    $this->warnings[] = "$doc references non-existent file: $file";
                }
            }
        }
    }
    
    private function checkEnvVariables(): void
    {
        $example = parse_ini_file('.env.example');
        $used = [];
        
        // Scan PHP files for env() calls
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('app')
        );
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                preg_match_all("/env\(['\"]([^'\"]+)['\"]/", $content, $matches);
                $used = array_merge($used, $matches[1]);
            }
        }
        
        // Check for undocumented env vars
        $undocumented = array_diff(array_unique($used), array_keys($example));
        foreach ($undocumented as $var) {
            $this->errors[] = "Env variable $var used but not in .env.example";
        }
    }
    
    private function checkRoutes(): void
    {
        $appFile = 'app/Core/Application.php';
        if (!file_exists($appFile)) return;
        
        $content = file_get_contents($appFile);
        preg_match_all("/routes->add\(['\"]([^'\"]+)['\"].*?['\"]([^'\"]+)['\"]/", $content, $matches);
        
        $routes = array_combine($matches[1], $matches[2]);
        
        // Check if documented routes exist
        $gettingStarted = file_get_contents('docs/GETTING-STARTED.md');
        if (strpos($gettingStarted, '/admin') !== false && !isset($routes['admin'])) {
            $this->warnings[] = 'Documentation mentions /admin route but not found in Application.php';
        }
    }
    
    private function checkComposerScripts(): void
    {
        $composer = json_decode(file_get_contents('composer.json'), true);
        $scripts = array_keys($composer['scripts'] ?? []);
        
        // Check if documented scripts exist
        $docs = file_get_contents('README.md') . file_get_contents('docs/DEVELOPMENT-SETUP.md');
        
        foreach ($scripts as $script) {
            if (strpos($docs, "composer $script") === false) {
                $this->warnings[] = "Composer script '$script' exists but not documented";
            }
        }
    }
    
    private function report(): void
    {
        if (empty($this->errors) && empty($this->warnings)) {
            echo "✅ All documentation is in sync!\n";
            return;
        }
        
        if (!empty($this->errors)) {
            echo "❌ ERRORS (must fix):\n";
            foreach ($this->errors as $error) {
                echo "  - $error\n";
            }
        }
        
        if (!empty($this->warnings)) {
            echo "⚠️ WARNINGS (should fix):\n";
            foreach ($this->warnings as $warning) {
                echo "  - $warning\n";
            }
        }
        
        exit(1); // Fail CI if errors
    }
}

(new DocChecker())->run();
```

### 2. Git Hook for Documentation

Create `.githooks/pre-commit`:

```bash
#!/bin/bash
# Pre-commit hook to check documentation

echo "Checking documentation integrity..."

# Run documentation checker
php scripts/check-docs.php

if [ $? -ne 0 ]; then
    echo "Documentation check failed! Please fix errors before committing."
    exit 1
fi

# Check for TODO/FIXME in docs
if grep -r "TODO\|FIXME\|🚧" docs/ README.md --include="*.md"; then
    echo "Warning: Unfinished documentation found (TODO/FIXME/🚧)"
    echo "Continue anyway? (y/n)"
    read answer
    if [ "$answer" != "y" ]; then
        exit 1
    fi
fi

echo "Documentation check passed!"
```

## 📝 Documentation Update Checklist

### For Every Code Change

```markdown
## Pre-Commit Checklist

### If you added a new file:
- [ ] Update CLAUDE.md file list
- [ ] Update project structure in README.md
- [ ] Update GETTING-STARTED.md if user-facing
- [ ] Add to LLM-DEVELOPMENT-GUIDE.md if it's a pattern

### If you added a route:
- [ ] Document in GETTING-STARTED.md
- [ ] Update API documentation
- [ ] Add example to CLAUDE.md

### If you added an env variable:
- [ ] Add to .env.example with comment
- [ ] Document in DEVELOPMENT-SETUP.md
- [ ] Update installation instructions

### If you added a composer dependency:
- [ ] Update installation requirements
- [ ] Document why it was added
- [ ] Update minimum PHP version if needed

### If you added a database table:
- [ ] Update tech-stack.md schema section
- [ ] Document in model docs
- [ ] Update LLM guide with RedBean pattern

### If you changed a public API:
- [ ] Update all code examples
- [ ] Mark old docs as deprecated
- [ ] Provide migration guide
```

## 🤖 IDE Rules Synchronization

### Keeping IDE Rules Updated

The project maintains IDE-specific rule files that are automatically synchronized:

1. **Source of Truth**: `.windsurf-rules.md` in root
2. **Auto-sync Command**: `composer ide:sync`
3. **Checked by**: `composer docs:check`

### Supported IDEs:
- **Windsurf**: `.windsurf/rules/development-guide.md` (synced from `.windsurf-rules.md`)
- **Cursor**: `.cursorrules` (auto-generated)
- **GitHub Copilot**: `.github/copilot-instructions.md` (auto-generated)
- **VS Code**: Can use `.vscode/settings.json` (manual)

### To Update IDE Rules:
```bash
# 1. Edit the source file
edit .windsurf-rules.md

# 2. Sync to all IDEs
composer ide:sync

# 3. Verify sync worked
composer docs:check
```

## 🔄 Living Documentation Strategy

### 1. Documentation as Code

```php
// app/Docs/DocumentationGenerator.php

class DocumentationGenerator
{
    public function generateRoutesDocs(): string
    {
        $routes = $this->extractRoutes();
        $markdown = "# Available Routes\n\n";
        
        foreach ($routes as $name => $route) {
            $markdown .= "## {$route['method']} {$route['path']}\n";
            $markdown .= "Handler: `{$route['handler']}`\n\n";
        }
        
        return $markdown;
    }
    
    public function generateModelsDocs(): string
    {
        $models = glob('app/Models/*.php');
        $markdown = "# Data Models\n\n";
        
        foreach ($models as $model) {
            $class = $this->extractClassInfo($model);
            $markdown .= "## {$class['name']}\n";
            $markdown .= "Table: `{$class['table']}`\n";
            $markdown .= "Properties:\n";
            foreach ($class['properties'] as $prop) {
                $markdown .= "- `{$prop['name']}` ({$prop['type']})\n";
            }
            $markdown .= "\n";
        }
        
        return $markdown;
    }
}
```

### 2. Auto-Update Command

Add to composer.json:

```json
{
    "scripts": {
        "docs:update": "php scripts/update-docs.php",
        "docs:check": "php scripts/check-docs.php",
        "post-update-cmd": [
            "@docs:update"
        ]
    }
}
```

### 3. CI/CD Integration

`.github/workflows/docs.yml`:

```yaml
name: Documentation Integrity

on: [push, pull_request]

jobs:
  check-docs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          
      - name: Install dependencies
        run: composer install
        
      - name: Check documentation
        run: composer docs:check
        
      - name: Generate fresh docs
        run: composer docs:update
        
      - name: Check for changes
        run: |
          if [[ `git status --porcelain` ]]; then
            echo "Documentation is out of date!"
            git diff
            exit 1
          fi
```

## 📊 Documentation Metrics

Track documentation health:

```php
// scripts/doc-metrics.php

$metrics = [
    'total_docs' => count(glob('docs/*.md')),
    'todo_count' => substr_count(file_get_contents('docs'), 'TODO'),
    'example_count' => substr_count(file_get_contents('docs'), '```'),
    'last_updated' => [],
];

foreach (glob('docs/*.md') as $file) {
    $metrics['last_updated'][basename($file)] = date('Y-m-d', filemtime($file));
}

// Flag stale docs (>30 days)
foreach ($metrics['last_updated'] as $file => $date) {
    if (strtotime($date) < strtotime('-30 days')) {
        echo "⚠️ $file is stale (last updated: $date)\n";
    }
}
```

## 🎯 Documentation-First Development Rules

### The Golden Rules

1. **No Code Without Docs** - Document the interface before implementing
2. **No PR Without Doc Updates** - Every PR must update relevant docs
3. **No Release Without Doc Review** - Full documentation audit before release
4. **No Breaking Changes Without Migration Guide** - Always provide upgrade path

### For LLM Contributors

Add to every LLM prompt:

```
After implementing this feature:
1. Update README.md if feature is user-visible
2. Update CLAUDE.md with new patterns/files
3. Update GETTING-STARTED.md with examples
4. Update .env.example with new variables
5. Run: composer docs:check
```

## 🔄 Weekly Documentation Tasks

```markdown
## Every Monday - Documentation Review

- [ ] Run `composer docs:check`
- [ ] Review all TODO/FIXME markers
- [ ] Update README.md feature status
- [ ] Check for stale documentation (>30 days)
- [ ] Verify all examples still work
- [ ] Update CHANGELOG.md

## Every Release

- [ ] Full documentation audit
- [ ] Update version numbers everywhere
- [ ] Generate API documentation
- [ ] Update installation guides
- [ ] Create migration guide if needed
```

## 🚨 Emergency Documentation Fixes

If documentation is found to be wrong:

1. **Immediate**: Add warning badge
   ```markdown
   > ⚠️ **Documentation Under Review** - This section may be outdated
   ```

2. **Within 24 hours**: Fix or remove incorrect information

3. **Within 48 hours**: Full audit of related documentation

## 📈 Success Metrics

Documentation is healthy when:
- ✅ Zero errors from `docs:check`
- ✅ All examples run without errors
- ✅ No docs older than 60 days without review
- ✅ Every public API is documented
- ✅ Every env variable is documented
- ✅ No TODO/FIXME in released versions

---

*This is a living document. Update this guide when adding new documentation!*
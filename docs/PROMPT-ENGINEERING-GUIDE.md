# Prompt Engineering Guide for Isotone

This guide helps humans write effective prompts for LLMs when developing Isotone.

## 🎯 Effective Prompt Structure

### Basic Template

```
Task: [Specific action needed]
Context: [Isotone, PHP 8.3, RedBeanPHP, XAMPP]
Requirements:
- [Specific requirement 1]
- [Specific requirement 2]
Constraints:
- Must work on shared hosting
- No Node.js dependencies
- Follow PSR-12 standards
```

### Example Prompt

```
Create a user authentication system for Isotone.

Requirements:
- Login/logout functionality
- Password hashing with bcrypt
- Session management
- Remember me option

Use RedBeanPHP for database operations and follow the existing pattern in app/Core/Application.php for routing.
```

## 📝 Task-Specific Prompts

### Creating a Feature

```
Add [feature name] to Isotone.

The feature should:
- [Functionality 1]
- [Functionality 2]

Follow existing patterns in:
- Routing: app/Core/Application.php
- Models: app/Models/
- Helpers: app/helpers.php

Ensure it works with XAMPP and requires no Node.js.
```

### Fixing a Bug

```
Fix [describe bug] in Isotone.

Current behavior: [what happens now]
Expected behavior: [what should happen]
File location: [if known]

Maintain backward compatibility and test with XAMPP.
```

### Refactoring Code

```
Refactor [component/file] in Isotone.

Goals:
- [Improvement 1]
- [Improvement 2]

Constraints:
- Maintain all existing functionality
- Keep compatible with PHP 8.3
- No breaking changes to public APIs
```

## 🚀 Prompt Optimizations

### DO: Be Specific

```
GOOD: "Create a plugin that adds social media sharing buttons to posts"
BAD: "Add social features"
```

### DO: Provide Context

```
GOOD: "Using RedBeanPHP, create a model for blog comments with validation"
BAD: "Make a comment system"
```

### DO: Set Boundaries

```
GOOD: "Create a simple contact form using only PHP and HTML, no JavaScript"
BAD: "Make a contact form"
```

### DO: Reference Existing Code

```
GOOD: "Following the pattern in app/Core/Application.php, add a new route for /api/users"
BAD: "Add an API endpoint"
```

## 💡 Advanced Prompting Techniques

### 1. Chain of Thought

```
First, analyze the existing authentication system in Isotone.
Then, create a two-factor authentication addon that:
1. Generates TOTP codes
2. Stores backup codes
3. Integrates with existing login

Show your reasoning for each design decision.
```

### 2. Few-Shot Examples

```
Here's how we create routes in Isotone:
$this->routes->add('home', new Route('/', ['_controller' => [$this, 'handleHome']]));

Now create routes for:
- /blog (list posts)
- /blog/{slug} (show single post)
- /blog/category/{category} (filter by category)
```

### 3. Structured Output

```
Create a user management system. Structure your response as:

1. DATABASE SCHEMA (using RedBean conventions)
2. MODEL CLASSES (in app/Models/)
3. ROUTES (to add to Application.php)
4. CONTROLLER METHODS (with full implementation)
5. SECURITY CONSIDERATIONS
```

### 4. Iterative Refinement

```
Step 1: Create a basic plugin structure for SEO management
Step 2: Add meta tag management
Step 3: Implement XML sitemap generation
Step 4: Add Open Graph support

Complete each step before moving to the next.
```

## 🎨 Prompts for Different LLMs

### Claude (Recommended)

Claude excels at:
- Following detailed instructions
- Maintaining context
- Security considerations

```
Using your knowledge of secure PHP development, create a file upload system for Isotone with:
- File type validation
- Size limits
- Malware scanning hooks
- Organized storage in content/uploads/

Follow Isotone's coding standards and ensure XAMPP compatibility.
```

### GPT-4

GPT-4 excels at:
- Creative solutions
- Broad knowledge
- Code generation

```
Create an innovative caching system for Isotone that:
- Works on shared hosting (file-based)
- Has minimal overhead
- Supports cache tags
- Auto-clears on content updates

Be creative but keep it simple and PHP-only.
```

### Other LLMs

For other LLMs, be more explicit:

```
You are developing Isotone, a PHP 8.3 project.
Key facts:
- Uses RedBeanPHP for database
- No Node.js allowed
- Must work on shared hosting
- Follow PSR-12 standards

Task: [Your specific task]
```

## 📋 Prompt Templates

### Feature Development

```
Feature: [Name]
User Story: As a [role], I want [feature] so that [benefit]

Technical Requirements:
- PHP 8.3 compatible
- Use RedBeanPHP for data
- Follow existing patterns in app/Core/
- Add routes to Application.php
- Include error handling

Deliverables:
1. Model classes
2. Controller methods
3. Routes
4. Basic UI (if needed)
5. Documentation updates
```

### Bug Fix

```
Bug Report: [Title]
Environment: Isotone on XAMPP/Windows

Steps to Reproduce:
1. [Step 1]
2. [Step 2]

Expected: [What should happen]
Actual: [What happens]

Please fix this bug and:
- Explain the root cause
- Implement the fix
- Add validation to prevent recurrence
- Test on XAMPP
```

### Code Review

```
Review this Isotone code for:
- Security vulnerabilities
- Performance issues
- PSR-12 compliance
- Shared hosting compatibility

[Paste code here]

Provide specific suggestions with code examples.
```

## ⚠️ Common Pitfalls

### Avoid Vague Requests

```
BAD: "Make it better"
GOOD: "Optimize database queries by implementing eager loading"
```

### Avoid Assuming Knowledge

```
BAD: "Add the usual security"
GOOD: "Add CSRF protection, XSS prevention, and SQL injection prevention"
```

### Avoid Conflicting Requirements

```
BAD: "Make it simple but add all enterprise features"
GOOD: "Create a minimal viable solution that can be extended later"
```

## 🔧 Debugging Prompts

When LLM output isn't working:

```
The code you provided for [feature] has this error:
[Error message]

File: [filename]
Line: [line number]

Please:
1. Identify the issue
2. Provide the corrected code
3. Explain what was wrong
4. Ensure it works with PHP 8.3 and RedBeanPHP
```

## 📊 Prompt Effectiveness Metrics

Good prompts result in:
- ✅ Working code on first try
- ✅ Follows Isotone patterns
- ✅ No Node.js dependencies
- ✅ Proper error handling
- ✅ Security considered
- ✅ Documentation included

Poor prompts result in:
- ❌ Multiple iterations needed
- ❌ Uses wrong frameworks
- ❌ Includes npm/webpack
- ❌ Missing error handling
- ❌ Security vulnerabilities
- ❌ No documentation

## 🎯 Quick Reference

### Magic Words for Isotone

Include these keywords for better results:
- "RedBeanPHP"
- "PSR-12"
- "shared hosting"
- "XAMPP compatible"
- "no Node.js"
- "WordPress-like hooks"
- "PHP 8.3"

### Context Setters

Start prompts with:
- "In Isotone..."
- "Following Isotone's patterns..."
- "Using RedBeanPHP..."
- "For shared hosting..."

### Quality Markers

End prompts with:
- "Follow PSR-12 standards"
- "Include error handling"
- "Add PHPDoc comments"
- "Ensure XAMPP compatibility"
- "Maintain backward compatibility"

## 💬 Example Conversations

### Good Conversation Flow

```
Human: Create a basic page caching system for Isotone

AI: I'll create a file-based caching system that works on shared hosting...
[Provides code]
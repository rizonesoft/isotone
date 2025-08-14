# Setting Up Isotone on GitHub

This guide will help you publish Isotone to GitHub as a public repository.

## 📋 Prerequisites

- GitHub account
- Git installed locally
- Repository already initialized (✅ Done)

## 🚀 Steps to Create Public Repository

### 1. Create Repository on GitHub

1. Go to [GitHub.com](https://github.com)
2. Click the **"+"** icon → **"New repository"**
3. Configure repository:
   - **Repository name:** `isotone` or `isotone-cms`
   - **Description:** "A lightweight PHP CMS designed for shared hosting, developed with AI assistance"
   - **Visibility:** Public
   - **DO NOT** initialize with README, .gitignore, or license (we already have them)
4. Click **"Create repository"**

### 2. Add Remote Origin

After creating the repository, GitHub will show you commands. Use these:

```bash
# Add remote origin (replace YOUR-USERNAME with your GitHub username)
git remote add origin https://github.com/YOUR-USERNAME/isotone.git

# Or if using SSH
git remote add origin git@github.com:YOUR-USERNAME/isotone.git

# Verify remote was added
git remote -v
```

### 3. Push to GitHub

```bash
# Push main branch to GitHub
git push -u origin main
```

### 4. Configure Repository Settings

On GitHub, go to Settings and configure:

#### General Settings
- **Default branch:** main
- **Features:** Enable Issues, Discussions
- **Pull Requests:** Allow squash merging, auto-delete branches

#### About Section (Right sidebar)
- **Description:** A lightweight PHP CMS for shared hosting
- **Website:** https://isotone.tech (when available)
- **Topics:** Add relevant tags
  - `cms`
  - `php`
  - `content-management`
  - `shared-hosting`
  - `ai-development`
  - `redbeanphp`
  - `lightweight`

#### GitHub Pages (Optional)
If you want to host docs:
1. Go to Settings → Pages
2. Source: Deploy from branch
3. Branch: main
4. Folder: /docs

### 5. Create Initial GitHub Issues

Create these issues to guide contributors:

**Issue 1: Complete Database Integration**
```markdown
Title: Complete RedBeanPHP database integration

We need to finish integrating RedBeanPHP for database operations.

Tasks:
- [ ] Create database connection class
- [ ] Implement basic CRUD operations
- [ ] Add model base class
- [ ] Create installation script

Labels: enhancement, help wanted
```

**Issue 2: Implement Plugin System**
```markdown
Title: Build WordPress-like plugin/hook system

Implement the action/filter hook system for plugins.

Tasks:
- [ ] Create hook manager
- [ ] Implement add_action()
- [ ] Implement add_filter()
- [ ] Document hook API

Labels: enhancement, core
```

**Issue 3: Create Admin Authentication**
```markdown
Title: Add admin login system

Build basic authentication for admin panel.

Tasks:
- [ ] Login form
- [ ] Session management
- [ ] Password hashing
- [ ] Remember me option

Labels: enhancement, security
```

### 6. Add README Badges

Update README.md to add badges (after pushing):

```markdown
# Isotone

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.3-blue)](https://php.net)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)
```

### 7. Set Up Branch Protection (Optional)

For main branch:
1. Settings → Branches
2. Add rule for `main`
3. Enable:
   - Require pull request reviews
   - Dismiss stale reviews
   - Require status checks

### 8. Create a Release (Optional)

After pushing:
1. Go to Releases → Create new release
2. Tag: `v0.1.0-alpha`
3. Title: "Isotone v0.1.0 Alpha"
4. Description:
```markdown
## 🎉 Initial Alpha Release

First public release of Isotone!

### Features
- ✅ Basic routing system
- ✅ Environment configuration
- ✅ PSR-4 autoloading
- ✅ LLM development documentation
- ✅ XAMPP compatible

### Coming Soon
- Database integration
- Plugin system
- Admin panel
- Theme support

### Installation
See [Development Setup Guide](docs/DEVELOPMENT-SETUP.md)
```

## 📝 Repository Description Template

Use this for your GitHub repository description:

```
Isotone - A lightweight, AI-developed PHP content management system designed for shared hosting. Features WordPress-like plugins, RedBeanPHP ORM, and no Node.js requirements. Perfect for developers who need a simple, fast CMS that works everywhere.
```

## 🌟 After Publishing

1. **Share the repository:**
   - Tweet about it with #OpenSource
   - Post on Reddit (r/PHP, r/webdev)
   - Share on Dev.to

2. **Welcome contributors:**
   - Respond to issues quickly
   - Label issues for beginners
   - Thank contributors

3. **Maintain momentum:**
   - Regular commits
   - Clear roadmap
   - Active discussions

## 🔗 Useful Links

- [GitHub Docs](https://docs.github.com)
- [Semantic Versioning](https://semver.org)
- [Keep a Changelog](https://keepachangelog.com)
- [Conventional Commits](https://www.conventionalcommits.org)

## 📌 Commands Summary

```bash
# One-time setup
git remote add origin https://github.com/YOUR-USERNAME/isotone.git
git push -u origin main

# Future pushes
git push

# Create and push tags
git tag -a v0.1.0 -m "Version 0.1.0"
git push origin v0.1.0
```

---

Good luck with your public repository! 🚀
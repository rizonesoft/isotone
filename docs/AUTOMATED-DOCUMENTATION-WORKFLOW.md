# Automated Documentation Workflow

This document explains how Isotone CMS automatically maintains documentation across different triggers.

## Overview

Documentation is automatically updated through multiple triggers:

1. **Git Hooks** (commits/pushes)
2. **Version Bumps** (CLI commands)
3. **AI Satisfaction Detection** (user signals)
4. **Manual Commands** (composer scripts)

## Git Hook Automation

### Pre-Commit Hook
**Triggered**: Every time you run `git commit`

**Actions**:
1. Runs `composer docs:update` - Updates all documentation
2. Runs `composer ide:sync` - Syncs IDE rules to .windsurf/
3. Stages any updated documentation files
4. Runs `composer docs:check` - Validates documentation
5. Blocks commit if documentation has errors

**Files Updated**:
- All documentation in `docs/`
- Project structure references
- API documentation
- Environment variable documentation
- Composer command documentation

### Pre-Push Hook
**Triggered**: Every time you run `git push`

**Actions**:
1. Detects if `config/version.json` changed
2. Auto-generates `CHANGELOG.md` if version changed
3. Stages changelog if updated
4. Final documentation validation check
5. Blocks push if documentation is invalid

## Version Bump Automation

**Triggered**: When running `php isotone version:bump`

**Actions**:
1. Updates version in `config/version.json`
2. Generates `CHANGELOG.md`
3. Runs `composer docs:update`
4. Runs `composer ide:sync`
5. Shows commit instructions

**Example**:
```bash
php isotone version:bump minor
# Automatically:
# - Updates to v0.2.0
# - Generates changelog
# - Updates all documentation
# - Syncs IDE rules
```

## AI Satisfaction Detection → Auto Commit & Push

**Triggered**: When user expresses satisfaction with implementations

### Satisfaction Signals AI Recognizes:
- "Perfect!"
- "Excellent!"
- "That's exactly what I wanted"
- "Great work!"
- "This looks good"
- "Ship it!"
- "Ready to commit"
- "The feature is complete"
- "Implementation finished"

### AI Actions on Satisfaction:
1. Automatically runs `git add .`
2. Creates semantic commit message based on changes
3. Commits with `git commit -m "feat: [description]"`
4. Pushes with `git push origin main`
5. Reports commit and push details

### Example Workflow:
```
User: "Perfect! The API is working exactly as I wanted."

AI Response: "Excellent! I've committed and pushed the API implementation.

📝 Committed: feat: Implement REST API with version endpoints
🚀 Pushed to: origin/main
📊 Changes: 5 files modified, 127 insertions

Your API implementation is now live in the repository!"
```

## Manual Documentation Commands

### Update Documentation
```bash
composer docs:update
```
- Updates all documentation files
- Generates API documentation
- Updates project structure references
- Updates environment variables list

### Check Documentation
```bash
composer docs:check
```
- Validates file references
- Checks for broken links
- Validates code syntax in docs
- Reports errors and warnings

### Sync IDE Rules
```bash
composer ide:sync
```
- Copies development guides to `.windsurf/rules/`
- Ensures AI assistants have current project context

### Generate Changelog
```bash
php isotone changelog
```
- Generates `CHANGELOG.md` from version history
- Formats according to Keep a Changelog standard

## What Gets Updated Automatically

### Always Updated:
- **README.md**: Feature status, installation instructions
- **API Documentation**: Endpoint references, examples
- **Project Structure**: File and directory listings
- **Environment Variables**: .env.example and usage
- **Composer Commands**: Available scripts and descriptions
- **Version Information**: Current version, changelog

### Context-Specific Updates:
- **API Changes** → API documentation, examples
- **CLI Changes** → CLI reference, command help
- **Database Changes** → Migration guides, model docs
- **Configuration Changes** → Setup guides, .env docs
- **UI Changes** → User guides, screenshots (future)

## Workflow Integration

### Development Workflow
```bash
# 1. Develop feature
# 2. User expresses satisfaction
# → AI auto-updates docs

# 3. Commit changes
git commit -m "feat: Add new feature"
# → Pre-commit hook updates and validates docs

# 4. Push to repository
git push
# → Pre-push hook final validation

# 5. Bump version when ready
php isotone version:bump minor
# → Version, changelog, and docs all updated
```

### Continuous Integration
The git hooks ensure:
- ✅ Documentation is always current
- ✅ No commits with invalid documentation
- ✅ No pushes with missing changelog entries
- ✅ Consistent documentation format

## Troubleshooting

### Documentation Check Fails
If `composer docs:check` reports errors:

1. **File Reference Errors**: Update file paths in documentation
2. **Missing Files**: Create referenced files or update references
3. **Syntax Errors**: Fix PHP/code syntax in documentation examples
4. **Broken Links**: Update or remove broken links

### Hook Installation Issues
If git hooks aren't working:

```bash
# Reinstall hooks
php scripts/install-hooks.php

# Check hook permissions
ls -la .git/hooks/pre-*
chmod +x .git/hooks/pre-commit
chmod +x .git/hooks/pre-push
```

### Documentation Not Updating
If documentation seems outdated:

```bash
# Force update
composer docs:update

# Check what changed
git diff docs/

# Check for errors
composer docs:check
```

## Configuration

### Disable Automated Updates
To temporarily disable automatic documentation updates:

```bash
# Rename hooks to disable
mv .git/hooks/pre-commit .git/hooks/pre-commit.disabled
mv .git/hooks/pre-push .git/hooks/pre-push.disabled
```

### Re-enable
```bash
# Restore hooks
mv .git/hooks/pre-commit.disabled .git/hooks/pre-commit
mv .git/hooks/pre-push.disabled .git/hooks/pre-push
```

## Benefits

### For Developers:
- ✅ Always current documentation
- ✅ No manual documentation maintenance
- ✅ Automatic changelog generation
- ✅ Consistent documentation format

### For AI Assistants:
- ✅ Current project context via IDE rules
- ✅ Accurate file references
- ✅ Up-to-date feature information
- ✅ Reliable documentation for help responses

### For Users:
- ✅ Accurate setup instructions
- ✅ Current API documentation
- ✅ Reliable feature information
- ✅ Consistent user experience

## Future Enhancements

Planned improvements:
- [ ] Auto-generate screenshots for UI changes
- [ ] Automatic API example generation
- [ ] Integration with GitHub Actions
- [ ] Documentation quality scoring
- [ ] Auto-update external documentation sites

---

This automated system ensures documentation is never outdated and provides reliable information for both human developers and AI assistants.
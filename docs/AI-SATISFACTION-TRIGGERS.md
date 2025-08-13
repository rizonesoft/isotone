# AI Satisfaction Detection for Documentation Updates

This guide helps AI assistants recognize when to automatically update documentation based on user satisfaction signals.

## User Satisfaction Indicators

### ✅ Strong Satisfaction Signals
- "Perfect!"
- "Excellent!"
- "That's exactly what I wanted"
- "Great work!"
- "This looks good"
- "Ship it!"
- "Ready to commit"
- "Let's push this"
- "I'm happy with this"
- "This is working perfectly"
- "Fantastic implementation"
- "Well done"

### ✅ Implementation Completion Signals
- "The feature is complete"
- "Implementation finished"
- "That completes the [feature]"
- "We're done with [feature]"
- "The [feature] is working"
- "This solves the problem"
- "Feature working as expected"

### ✅ Approval/Acceptance Signals
- User testing functionality successfully
- User confirming features work correctly
- User asking to move to next task/feature
- User expressing no further changes needed
- User asking about next steps/features

### ⚠️ Moderate Satisfaction (Consider Updates)
- "This is better"
- "Good improvement"
- "Much better now"
- "That's an improvement"
- "Getting closer"

### ❌ Do NOT Update Documentation On
- Questions or requests for changes
- Bug reports or issues
- "Can you fix..."
- "This doesn't work"
- "Try again"
- "Almost there"
- User still iterating/testing

## AI Action Triggers

### When to Auto-Commit and Push (SATISFACTION WORKFLOW)

```
IF user expresses satisfaction AND (
    feature implementation completed OR
    implementation working as expected OR
    user says "ship it" / "ready to commit" OR
    user expresses completion satisfaction
) THEN:
    1. Stage all changes: `git add .`
    2. Create descriptive commit message
    3. Commit: `git commit -m "feat: [description]"`
    4. Push: `git push origin main`
    5. Inform user of commit and push
```

### When NOT to Auto-Commit (SAFETY)

- User still testing/iterating
- Errors or issues present
- Work-in-progress
- User didn't express completion
- Experimental changes
- Breaking changes without confirmation

### Example Scenarios

#### Scenario 1: Feature Completion
```
User: "Perfect! The versioning system is working exactly as I wanted."
AI Action: 
- Detect: Strong satisfaction + feature completion
- Execute: git add . && git commit -m "feat: Implement intelligent versioning system" && git push
- Response: "Excellent! I've committed and pushed the versioning system implementation."
```

#### Scenario 2: Implementation Approval
```
User: "Excellent work on the API endpoints. They're working perfectly."
AI Action:
- Detect: Satisfaction + implementation working
- Execute: Auto-commit and push
- Response: "Great! Committed and pushed the API endpoints implementation to the repository."
```

#### Scenario 3: Ready to Ship
```
User: "This looks good, ship it!"
AI Action:
- Detect: Direct ship command
- Execute: Auto-commit and push immediately
- Response: "Shipped! Changes committed and pushed to main branch."
```

### What to Update Based on Context

| If discussing... | Update these docs... |
|------------------|---------------------|
| New features | README.md feature list, getting-started.md |
| API changes | API documentation, examples |
| Version changes | CHANGELOG.md, version docs |
| Configuration | .env.example, setup guides |
| Database changes | Migration docs, models |
| CLI commands | CLI reference, help text |
| UI changes | Screenshots, user guides |

## AI Workflow on Satisfaction Detection

### Step 1: Detect Satisfaction
```php
// AI analyzes user message for satisfaction signals
$satisfaction_level = analyzeSatisfaction($userMessage);
$context = determineContext($conversation);
```

### Step 2: Determine Commit Message
```php
// Generate appropriate commit message based on changes
$commit_type = determineCommitType($context); // feat, fix, chore, etc.
$commit_message = generateCommitMessage($context, $changes);
```

### Step 3: Execute Git Operations
```bash
# Stage all changes
git add .

# Commit with descriptive message
git commit -m "$commit_message"

# Push to remote
git push origin main
```

### Step 4: Inform User
```
"Excellent! I've committed and pushed your changes:

📝 Commit: feat: Implement intelligent versioning system
🚀 Pushed to: origin/main
📊 Files changed: 8 files, 245 insertions

Your implementation is now live in the repository!"
```

## Implementation in AI Responses

### Template for Satisfaction Detection

```
User expresses satisfaction → AI thinks:

1. "User is satisfied with [feature/implementation]"
2. "This seems like a completion point - ready to commit"
3. "Let me commit and push these changes"
4. Execute git add, commit, and push
5. Report back to user what was committed
```

### Example AI Response Pattern

```
AI: "Excellent! The [feature] is working perfectly. 

Let me commit and push these changes...

📝 Committed: feat: Implement [feature description]
🚀 Pushed to: origin/main
📊 Changes: [X] files modified, [Y] insertions

Your [feature] implementation is now live in the repository!"
```

## Commit Message Generation

AI should generate semantic commit messages based on context:

### Commit Types:
- **feat**: New features
- **fix**: Bug fixes  
- **docs**: Documentation changes
- **style**: Code style changes
- **refactor**: Code refactoring
- **test**: Adding/updating tests
- **chore**: Maintenance tasks

### Message Format:
```
<type>: <description>

[optional body]

[optional footer]
```

### Examples:
- `feat: Add intelligent versioning system with CLI commands`
- `fix: Resolve badge text contrast issue`
- `docs: Update API documentation with new endpoints`
- `chore: Bump version to v0.1.1`

## Advanced Detection Patterns

### Context-Aware Satisfaction
- If working on API → Update API docs
- If working on UI → Update user guides
- If working on CLI → Update CLI reference
- If working on setup → Update installation guides

### Conversation Flow Analysis
- Track conversation progression
- Detect when moving from implementation to testing to satisfaction
- Time documentation updates for natural breakpoints

### Proactive Suggestions
When detecting satisfaction, AI can ask:
"Should I also update the documentation to include this new feature?"

## Settings for AI Assistants

### Conservative Mode (Default)
- Only update on explicit satisfaction signals
- Ask before major documentation changes
- Focus on critical documentation only

### Aggressive Mode
- Update on moderate satisfaction
- Proactively suggest documentation updates
- Update related documentation areas

### Auto Mode
- Automatically update on any satisfaction signal
- No confirmation needed
- Comprehensive documentation updates

## Quality Checks

After auto-updating documentation:

1. **Run docs:check** to ensure no errors
2. **Verify file references** are correct
3. **Check for broken links**
4. **Validate code examples**
5. **Ensure consistency** across docs

This system ensures documentation stays current with development while respecting user workflow and preferences.
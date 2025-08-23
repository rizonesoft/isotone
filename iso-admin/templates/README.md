# Admin Page Templates

## Overview
The admin page template system provides a consistent design pattern for all Isotone admin pages, based on the security-login.php styling.

## Main Template
**File:** `admin-page-template.php`

This is the primary template for creating new admin pages. It includes all standard components and follows the established design patterns.

## Quick Start

### Creating a New Admin Page

1. **Copy the template:**
   ```bash
   cp templates/admin-page-template.php your-new-page.php
   ```

2. **IMPORTANT - Fix the paths:**
   Since the template is in `/iso-admin/templates/`, you need to adjust paths when copying to `/iso-admin/`:
   
   **Change these lines:**
   ```php
   // From (template version):
   require_once dirname(__DIR__) . '/auth.php';
   require_once dirname(__DIR__, 2) . '/config.php';
   require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
   require_once dirname(__DIR__) . '/includes/admin-layout.php';
   
   // To (admin root version):
   require_once 'auth.php';
   require_once dirname(__DIR__) . '/config.php';
   require_once dirname(__DIR__) . '/vendor/autoload.php';
   require_once 'includes/admin-layout.php';
   ```
   
   **CSS Path:**
   ```php
   // From (template version):
   $page_styles = '<link rel="stylesheet" href="../css/admin-components.css">';
   
   // To (admin root version):
   $page_styles = '<link rel="stylesheet" href="css/admin-components.css">';
   ```

3. **Update page specifics:**
   - Change `$page_title` at line 344
   - Update `$breadcrumbs` array at line 345-348
   - Replace the page icon at line 108
   - Update requireRole() if needed (line 19)

4. **Customize content sections:**
   - Replace metric cards (lines 153-187)
   - Update main content areas (lines 189-214)
   - Modify form fields (lines 258-270)

## Template Structure

### 1. Authentication & Includes (Lines 17-31)
```php
require_once 'auth.php';  // Adjust path based on location
requireRole('admin');
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
```
**Note:** The template uses paths relative to `/iso-admin/templates/`. Adjust accordingly when moving files.

### 2. Icon Preloading (Lines 33-45)
Preload all icons used on the page for optimal performance:
```php
iso_preload_icons([
    ['name' => 'cog-6-tooth', 'style' => 'outline'],
    ['name' => 'chart-bar', 'style' => 'outline'],
    // ... more icons
]);
```

### 3. AJAX Handlers (Lines 47-70)
Handle AJAX requests with CSRF validation:
```php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    if (!iso_verify_csrf()) {
        echo json_encode(['success' => false, 'error' => 'CSRF token validation failed']);
        exit;
    }
    // Handle actions
}
```

### 4. Form Submissions (Lines 72-84)
Process POST requests with CSRF protection:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!iso_verify_csrf()) {
        $_SESSION['error_message'] = 'Security validation failed.';
    } else {
        // Process form
    }
}
```

### 5. Page Components

#### Page Header (Lines 104-115)
- Large icon with title
- Description text
- Consistent spacing

#### Success/Error Messages (Lines 117-150)
- Auto-dismissible alerts
- Icon indicators
- Session-based messaging

#### Metric Cards (Lines 152-187)
- 4-column responsive grid
- Icon + metric display
- Dark mode support

#### Content Sections (Lines 189-214)
- Card-based layouts
- Section headers
- Flexible content areas

#### Tabbed Content (Lines 216-247)
- Alpine.js powered tabs
- Smooth transitions
- Active state styling

#### Forms (Lines 249-282)
- CSRF protection
- Consistent field styling
- Help text support

### 6. Alpine.js Component (Lines 287-336)
JavaScript functionality for interactive elements:
```javascript
function pageComponent() {
    return {
        activeTab: 'tab1',
        loading: false,
        init() {
            // Initialize
        },
        async performAction() {
            // AJAX actions
        }
    }
}
```

## CSS and Styling

### External CSS Required
All admin pages must include the admin components CSS file:
```php
$page_styles = '<link rel="stylesheet" href="css/admin-components.css">';
```

### Important Style Rules
1. **NEVER use inline styles** - All styles must be in external CSS files
2. **NEVER use `<style>` tags** - Use the admin-components.css file
3. **Use CSS classes** - All custom styling should use predefined classes

### Available CSS Classes
From `admin-components.css`:
- `.chart-container` - For responsive chart containers
- `.chart-loading` - Loading overlay for charts
- `.message-success` - Success message styling with cyan accent
- `.message-error` - Error message styling with red accent
- `.animate-slideDown` - Slide down animation
- `.shield-pulse` - Pulse animation for security icons
- `.metric-card` - Hover effects for metric cards
- `.empty-state` - Empty state containers
- `.glass-effect` - Glassmorphism effect
- `.glow-cyan` - Cyan glow effect
- `.status-indicator` - Status dots with animations

## Design Patterns

### Colors
- Primary: Cyan (`text-cyan-500`, `bg-cyan-500`)
- Success: Green (`text-green-600`, `bg-green-50`)
- Error: Red (`text-red-600`, `bg-red-50`)
- Dark mode: Gray shades (`bg-gray-800`, `text-gray-400`)

### Spacing
- Page sections: `mb-8`
- Card padding: `p-6`
- Grid gaps: `gap-6`
- Form fields: `space-y-6`

### Icons
- Page header: `w-10 h-10`
- Metric cards: `w-9 h-9`
- Inline icons: `w-6 h-6`
- Small icons: `w-5 h-5`

### Animations
- Slide down: `animate-slideDown`
- Transitions: `transition-colors`
- Alpine transitions: `x-transition`

## Best Practices

1. **Always use Icon API:**
   ```php
   <?php echo iso_get_icon('icon-name', 'outline', ['class' => 'w-6 h-6'], false); ?>
   ```

2. **Include CSRF tokens in all forms:**
   ```php
   <?php echo iso_csrf_field(); ?>
   ```

3. **Use session messages for feedback:**
   ```php
   $_SESSION['success_message'] = 'Action completed!';
   ```

4. **Preload all icons at page start:**
   ```php
   iso_preload_icons([...]);
   ```

5. **Follow the standard include order:**
   - auth.php first
   - config.php second
   - autoload.php third

## Example: Creating a User Management Page

```php
<?php
// 1. Copy template to users.php
// 2. Update authentication
requireRole('admin');

// 3. Update page config
$page_title = 'User Management';
$breadcrumbs = [
    ['title' => 'Users', 'url' => '/isotone/iso-admin/users.php'],
    ['title' => 'Management', 'url' => '']
];

// 4. Preload user-specific icons
iso_preload_icons([
    ['name' => 'users', 'style' => 'outline'],
    ['name' => 'user-plus', 'style' => 'outline'],
    ['name' => 'trash', 'style' => 'outline'],
]);

// 5. Add user-specific queries
$total_users = R::count('user');
$active_users = R::count('user', 'status = ?', ['active']);

// 6. Update metric cards with user stats
// 7. Replace content sections with user tables
// 8. Add user management forms
```

## Troubleshooting

### CSRF Token Errors
Ensure CSRF token is included in AJAX requests:
```javascript
const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
formData.append('csrf_token', csrfToken);
```

### Icon Not Displaying
Check icon is preloaded:
```php
iso_preload_icons([
    ['name' => 'your-icon', 'style' => 'outline']
]);
```

### Dark Mode Issues
Ensure all elements have dark variants:
```html
class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
```

## Complete Components Reference

### Components Showcase
**File:** `admin-components-showcase.php`

This file contains a complete reference implementation of ALL admin UI components with proper styling. Use this as your reference when building admin pages.

#### Available Components:

**Form Components:**
- Text inputs (standard, with icon, disabled, with error)
- Textarea with character count
- Select dropdowns (native and custom Alpine.js)
- Checkboxes and radio buttons
- Toggle switches (with descriptions)
- File upload (basic and drag-drop)
- Range sliders
- Date/time pickers

**Data Display:**
- Tables (sortable, with actions)
- Lists (simple and interactive)
- Cards (basic, with image, with actions)
- Badges and tags (status, removable)

**Interactive:**
- Buttons (primary, outline, icon buttons)
- Modals with Alpine.js
- Tooltips
- Pagination

**Feedback:**
- Alert messages (success, error, warning, info)
- Progress bars (basic, striped, multi-segment)
- Loading states (spinner, dots, skeleton)

**Media:**
- Avatars (sizes, with status indicator)
- Image gallery grid
- File list with download

**Charts:**
- Line charts
- Bar charts
- Stat cards with trends

### How to Use Components

1. **View the showcase:** Open `/isotone/iso-admin/templates/admin-components-showcase.php` in your browser
2. **Copy the component:** Find the component you need and copy its HTML structure
3. **Apply consistent styling:** Use the same Tailwind classes and structure
4. **Include necessary Alpine.js:** Copy any x-data attributes and functions needed

### Component Styling Rules

1. **Always use Tailwind utility classes** - Don't create custom CSS
2. **Follow the color scheme:**
   - Primary: cyan-600/cyan-500
   - Success: green-600/green-500
   - Error: red-600/red-500
   - Warning: yellow-600/yellow-500
   - Info: blue-600/blue-500

3. **Maintain consistent spacing:**
   - Component sections: `space-y-8`
   - Form fields: `space-y-6`
   - List items: `space-y-2`
   - Inline items: `space-x-2` or `gap-2`

4. **Dark mode support:** Always include dark variants
   ```html
   class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
   ```

5. **Focus states:** Ensure all interactive elements have focus styles
   ```html
   class="focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
   ```

6. **Hover states:** Add hover effects for better UX
   ```html
   class="hover:bg-gray-50 dark:hover:bg-gray-700"
   ```

### Rebuilding Tailwind

When adding new Tailwind classes, you may need to rebuild the CSS:

```bash
# Navigate to the automation directory
cd /isotone/iso-automation/tailwind/

# Rebuild the CSS
npx tailwindcss -i ./src/input.css -o ../../iso-admin/css/tailwind.css --watch

# For production (minified)
npx tailwindcss -i ./src/input.css -o ../../iso-admin/css/tailwind.min.css --minify
```

Make sure any new utility classes are included in the `content` array in `tailwind.config.js`.

## Related Files
- `/iso-admin/templates/admin-components-showcase.php` - Complete components reference
- `/iso-admin/includes/admin-layout.php` - Main layout wrapper
- `/iso-admin/auth.php` - Authentication system
- `/iso-includes/icon-functions.php` - Icon API
- `/iso-admin/css/tailwind.css` - Tailwind styles
- `/iso-admin/css/admin-components.css` - Custom component styles
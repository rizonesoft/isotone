# Isotone Icon Library

Isotone includes a comprehensive icon library system built on Heroicons v2, providing over 200 high-quality SVG icons for use throughout your application. The library offers three distinct icon styles to suit different design needs.

## Icon Styles

The Isotone Icon Library provides three different icon styles, each optimized for specific use cases:

### [Outline Icons](icon-preview-outline.md)
- **Size**: 24x24 viewport
- **Style**: 2px stroke width, no fill
- **Use Case**: Default UI elements, navigation, buttons
- **Icon Count**: 200+ icons
- **Class**: `IconLibrary`

### [Solid Icons](icon-preview-solid.md)
- **Size**: 24x24 viewport  
- **Style**: Filled paths with no stroke
- **Use Case**: Emphasis, active states, filled buttons
- **Icon Count**: 200+ icons
- **Class**: `IconLibrarySolid`

### [Micro Icons](icon-preview-micro.md)
- **Size**: 16x16 viewport
- **Style**: Optimized for small sizes
- **Use Case**: Compact UI, inline text, small buttons
- **Icon Count**: 200+ icons
- **Class**: `IconLibraryMicro`

## Using Icons in PHP

The icon library is centralized and easy to use throughout your Isotone application:

```php
// Include the appropriate library
require_once dirname(__DIR__) . '/iso-core/Core/IconLibrary.php';        // Outline
require_once dirname(__DIR__) . '/iso-core/Core/IconLibrarySolid.php';   // Solid
require_once dirname(__DIR__) . '/iso-core/Core/IconLibraryMicro.php';   // Micro

// Get an icon with default styling
echo IconLibrary::getIcon('home');                    // Outline home icon
echo IconLibrarySolid::getIcon('home');              // Solid home icon
echo IconLibraryMicro::getIcon('home');              // Micro home icon

// Add custom classes
echo IconLibrary::getIcon('user', ['class' => 'w-6 h-6 text-gray-600']);

// Add other attributes
echo IconLibrary::getIcon('menu', [
    'class' => 'w-5 h-5',
    'aria-label' => 'Menu',
    'role' => 'img'
]);
```

## Available Methods

Each icon library class provides the following methods:

### Core Methods
- `getIcon($name, $attributes = [])` - Get an icon with optional attributes
- `hasIcon($name)` - Check if an icon exists
- `getIconNames()` - Get all available icon names
- `getIconsByCategory($category)` - Get icons in a specific category
- `searchIcons($keyword)` - Search icons by keyword
- `renderGallery()` - Render a complete icon gallery

### Utility Methods
- `getCategories()` - Get all available categories
- `getIconCount()` - Get total number of icons
- `getCategoryCount($category)` - Get icon count for a category

## Icon Categories

Icons are organized into logical categories for easy discovery:

- **Navigation** - Menu, arrows, chevrons, breadcrumbs
- **Actions** - Add, edit, delete, save, download
- **User & Account** - User profiles, authentication, teams
- **Content** - Documents, folders, files, media
- **Communication** - Messages, notifications, sharing
- **Interface** - Layouts, windows, panels, cards
- **Status** - Alerts, badges, progress indicators
- **Data** - Charts, tables, analytics
- **Settings** - Configuration, preferences, tools
- **Commerce** - Shopping, payment, currency
- **And more...**

## Best Practices

### 1. Choose the Right Style
- Use **Outline** icons for most UI elements (default)
- Use **Solid** icons for emphasis or active states
- Use **Micro** icons for space-constrained areas

### 2. Consistent Sizing
Use Tailwind utility classes for consistent sizing:
```php
'w-4 h-4'   // Small (16px) - good for inline text
'w-5 h-5'   // Medium (20px) - compact buttons
'w-6 h-6'   // Default (24px) - standard UI
'w-8 h-8'   // Large (32px) - prominent actions
```

### 3. Accessibility
Always include accessibility attributes for non-decorative icons:
```php
IconLibrary::getIcon('search', [
    'aria-label' => 'Search',
    'role' => 'img'
]);
```

### 4. Performance
- Icons are inlined as SVG for best performance
- No external requests or font loading required
- Reuse icon instances in loops when possible

### 5. Dark Mode Support
Icons automatically adapt to dark mode when using text color utilities:
```php
'class' => 'w-6 h-6 text-gray-600 dark:text-gray-400'
```

## Admin Panel Integration

The Isotone admin panel uses the Icon Library throughout its interface. Common mappings include:

- Dashboard → `home`
- Posts → `newspaper`
- Media → `photograph`
- Pages → `collection`
- Comments → `chat-bubble-left-right`
- Appearance → `swatch`
- Plugins → `puzzle-piece`
- Users → `user-group`
- Tools → `wrench`
- Settings → `cog`

## Extending the Library

To add new icons to the library:

1. Add the SVG path to the appropriate library class
2. Categorize it appropriately in the `$icons` array
3. Update the JavaScript preview if needed
4. Document any new categories or significant additions

## Icon Preview Galleries

Explore all available icons in each style:

- **[View Outline Icons →](icon-preview-outline.md)**
- **[View Solid Icons →](icon-preview-solid.md)**
- **[View Micro Icons →](icon-preview-micro.md)**

## JavaScript Integration

For client-side icon usage, see the JavaScript implementation in each preview page. The icon library is also available as a JavaScript module for dynamic icon rendering.

## Related Documentation

- [Theme Development](../development/theme-development.md)
- [Plugin Development](../development/plugin-development.md)
- [Admin Customization](../customization/admin-customization.md)
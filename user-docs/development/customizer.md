# Theme Customizer Documentation

## Overview

The Isotone Theme Customizer provides a WordPress-compatible live preview interface for customizing your site's appearance and settings. It allows themes and plugins to register custom settings that users can modify in real-time with instant visual feedback.

## Features

- **Live Preview**: See changes instantly without page refresh
- **WordPress-Compatible API**: Familiar hooks and methods for developers
- **Panel Navigation**: Organized sections with slide-in panels
- **Dark Mode Support**: Full dark/light mode interface
- **Transport Methods**: PostMessage for instant updates or refresh for complex changes
- **Extensible**: Themes and plugins can add custom sections and controls

## How It Works

### Architecture

The Customizer consists of several components:

1. **Customizer Core** (`/iso-core/Core/Customizer.php`)
   - Manages sections, settings, and controls
   - Handles data persistence
   - Provides WordPress-compatible API

2. **Admin Interface** (`/iso-admin/customize.php`)
   - Renders the customizer UI
   - Handles panel navigation
   - Manages live preview iframe

3. **Preview Integration**
   - PostMessage transport for instant updates
   - JavaScript API for handling changes
   - Selective refresh for dynamic content

### Data Flow

1. User opens Customizer → Loads current settings from database
2. User modifies control → JavaScript captures change
3. Change sent to preview → Preview updates via PostMessage
4. User clicks Publish → Settings saved to database
5. Front-end uses saved values → Site displays customized content

## Adding Custom Settings

### Basic Example

Here's how themes register customizer settings:

```php
<?php
// In your theme's functions.php
use Isotone\Core\Customizer;

function my_theme_customize_register() {
    $customizer = Customizer::getInstance();
    
    // Add a new section
    $customizer->addSection('my_theme_options', [
        'title'       => 'My Theme Settings',
        'description' => 'Customize theme appearance',
        'priority'    => 120,
        'icon'        => 'sparkles' // Optional icon
    ]);
    
    // Add a setting
    $customizer->addSetting('my_theme_primary_color', [
        'default'           => '#007cba',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage' // or 'refresh'
    ]);
    
    // Add a control for the setting
    $customizer->addControl('my_theme_primary_color', [
        'label'       => 'Primary Color',
        'section'     => 'my_theme_options',
        'setting'     => 'my_theme_primary_color',
        'type'        => 'color',
        'description' => 'Choose your primary brand color'
    ]);
}

// Register the customizer settings
if (class_exists('\\Isotone\\Core\\Customizer')) {
    my_theme_customize_register();
}
```

### Available Control Types

#### Text Input
```php
$customizer->addControl('footer_text', [
    'label'   => 'Footer Text',
    'section' => 'my_section',
    'type'    => 'text'
]);
```

#### Textarea
```php
$customizer->addControl('footer_copyright', [
    'label'   => 'Copyright Notice',
    'section' => 'my_section',
    'type'    => 'textarea'
]);
```

#### Color Picker
```php
$customizer->addControl('accent_color', [
    'label'   => 'Accent Color',
    'section' => 'my_section',
    'type'    => 'color'
]);
```

#### Checkbox
```php
$customizer->addControl('show_header', [
    'label'   => 'Display Header',
    'section' => 'my_section',
    'type'    => 'checkbox'
]);
```

#### Select Dropdown
```php
$customizer->addControl('layout_style', [
    'label'   => 'Layout Style',
    'section' => 'my_section',
    'type'    => 'select',
    'choices' => [
        'boxed'     => 'Boxed',
        'full'      => 'Full Width',
        'contained' => 'Contained'
    ]
]);
```

#### Radio Buttons
```php
$customizer->addControl('sidebar_position', [
    'label'   => 'Sidebar Position',
    'section' => 'my_section',
    'type'    => 'radio',
    'choices' => [
        'left'  => 'Left',
        'right' => 'Right',
        'none'  => 'No Sidebar'
    ]
]);
```

#### Number Input
```php
$customizer->addControl('container_width', [
    'label'       => 'Container Width (px)',
    'section'     => 'my_section',
    'type'        => 'number',
    'input_attrs' => [
        'min'  => 960,
        'max'  => 1920,
        'step' => 10
    ]
]);
```

#### Range Slider
```php
$customizer->addControl('font_size', [
    'label'       => 'Base Font Size',
    'section'     => 'my_section',
    'type'        => 'range',
    'input_attrs' => [
        'min'  => 12,
        'max'  => 24,
        'step' => 1
    ]
]);
```

## Section Icons

Sections can display icons in the customizer navigation:

```php
$customizer->addSection('my_section', [
    'title' => 'My Section',
    'icon'  => 'sparkles' // Icon name
]);
```

### Available Icons

- `cog` - Settings/configuration
- `identification` - User/identity settings
- `swatch` - Color/theme options
- `view-columns` - Layout settings
- `bars-3-bottom-left` - Footer settings
- `sparkles` - Featured/special options
- `template` - Template settings
- `palette` - Color palette
- `photograph` - Image/media settings
- `document-text` - Content settings
- `globe` - Global/international settings

## Using Settings in Templates

### Getting Values

```php
// Get a theme modification
$primary_color = get_theme_mod('my_theme_primary_color', '#007cba');

// Get an option
$site_title = get_option('blogname', 'My Site');
```

### Example Template Usage

```php
<!-- In your theme template -->
<style>
    :root {
        --primary-color: <?php echo esc_attr(get_theme_mod('my_theme_primary_color', '#007cba')); ?>;
    }
</style>

<footer>
    <?php if (get_theme_mod('show_footer', true)): ?>
        <p><?php echo esc_html(get_theme_mod('footer_text', '© 2024')); ?></p>
    <?php endif; ?>
</footer>
```

## Transport Methods

### PostMessage (Instant Preview)

For instant preview without page refresh:

```php
$customizer->addSetting('text_color', [
    'transport' => 'postMessage'
]);
```

Requires JavaScript in the preview:

```javascript
// In your theme's customizer preview JS
wp.customize('text_color', function(value) {
    value.bind(function(newval) {
        $('.site-title').css('color', newval);
    });
});
```

### Refresh (Page Reload)

For complex changes requiring page reload:

```php
$customizer->addSetting('layout_type', [
    'transport' => 'refresh'
]);
```

## Sanitization Callbacks

Always sanitize user input:

| Data Type | Sanitization Function |
|-----------|----------------------|
| Text | `sanitize_text_field` |
| Textarea | `sanitize_textarea_field` |
| Email | `sanitize_email` |
| URL | `esc_url_raw` |
| Number | `absint` or `intval` |
| Color | `sanitize_hex_color` |
| Boolean | `wp_validate_boolean` |
| Select/Radio | `sanitize_key` |
| HTML | `wp_kses_post` |

## Best Practices

### 1. Organize Settings Logically
Group related settings in sections:
```php
// Good: Grouped by function
$customizer->addSection('header_settings', [...]);
$customizer->addSection('footer_settings', [...]);
$customizer->addSection('color_scheme', [...]);
```

### 2. Use Descriptive IDs
```php
// Good: Prefixed and descriptive
'my_theme_header_background_color'

// Bad: Generic
'color1'
```

### 3. Provide Sensible Defaults
```php
$customizer->addSetting('font_size', [
    'default' => '16px' // Good default
]);
```

### 4. Add Help Text
```php
$customizer->addControl('api_key', [
    'label'       => 'API Key',
    'description' => 'Enter your API key from the dashboard'
]);
```

### 5. Validate and Sanitize
```php
$customizer->addSetting('user_age', [
    'sanitize_callback' => function($value) {
        $value = absint($value);
        return ($value >= 0 && $value <= 150) ? $value : 18;
    }
]);
```

## Advanced Features

### Conditional Display

Show/hide controls based on other settings:

```php
$customizer->addControl('header_image', [
    'label'           => 'Header Image',
    'section'         => 'header',
    'active_callback' => function() {
        return get_theme_mod('show_header', true);
    }
]);
```

### Custom Control Types

Create custom controls by extending the base control class:

```php
class Custom_Control extends Isotone\Core\CustomizerControl {
    public $type = 'custom_control';
    
    public function render_content() {
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <!-- Custom control HTML -->
        </label>
        <?php
    }
}
```

### Setting Dependencies

Link settings that depend on each other:

```php
// Parent setting
$customizer->addSetting('enable_feature', [
    'default' => false,
    'type'    => 'theme_mod'
]);

// Dependent setting
$customizer->addSetting('feature_option', [
    'default'         => 'option1',
    'type'            => 'theme_mod',
    'active_callback' => function() {
        return get_theme_mod('enable_feature', false);
    }
]);
```

## Troubleshooting

### Settings Not Saving

1. Check capability requirements:
```php
'capability' => 'edit_theme_options' // User must have this capability
```

2. Verify sanitization callback:
```php
'sanitize_callback' => 'sanitize_text_field' // Must be valid
```

3. Ensure proper nonce verification in save handler

### Preview Not Updating

1. Check transport method:
```php
'transport' => 'postMessage' // Requires JS handler
```

2. Verify preview JavaScript is loaded:
```javascript
// Check console for errors
console.log('Customizer preview loaded');
```

### Controls Not Showing

1. Verify section exists:
```php
$customizer->addSection('my_section', [...]);
```

2. Check control section reference:
```php
'section' => 'my_section' // Must match section ID
```

## Complete Example

Here's a complete theme customizer implementation:

```php
<?php
/**
 * Theme Customizer Settings
 */

use Isotone\Core\Customizer;

function mytheme_customize_register() {
    $customizer = Customizer::getInstance();
    
    // Add Theme Options Section
    $customizer->addSection('mytheme_options', [
        'title'       => 'Theme Options',
        'description' => 'Customize your theme settings',
        'priority'    => 30,
        'icon'        => 'sparkles'
    ]);
    
    // Color Scheme Setting
    $customizer->addSetting('mytheme_color_scheme', [
        'default'           => 'light',
        'type'              => 'theme_mod',
        'sanitize_callback' => 'sanitize_key',
        'transport'         => 'refresh'
    ]);
    
    $customizer->addControl('mytheme_color_scheme', [
        'label'   => 'Color Scheme',
        'section' => 'mytheme_options',
        'type'    => 'radio',
        'choices' => [
            'light' => 'Light',
            'dark'  => 'Dark',
            'auto'  => 'Auto (System)'
        ]
    ]);
    
    // Primary Color Setting
    $customizer->addSetting('mytheme_primary_color', [
        'default'           => '#007cba',
        'type'              => 'theme_mod',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage'
    ]);
    
    $customizer->addControl('mytheme_primary_color', [
        'label'   => 'Primary Color',
        'section' => 'mytheme_options',
        'type'    => 'color'
    ]);
    
    // Show Sidebar Setting
    $customizer->addSetting('mytheme_show_sidebar', [
        'default'           => true,
        'type'              => 'theme_mod',
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh'
    ]);
    
    $customizer->addControl('mytheme_show_sidebar', [
        'label'   => 'Show Sidebar',
        'section' => 'mytheme_options',
        'type'    => 'checkbox'
    ]);
}

// Register customizer settings
if (class_exists('\\Isotone\\Core\\Customizer')) {
    mytheme_customize_register();
}

// Use in templates
function mytheme_custom_styles() {
    $primary_color = get_theme_mod('mytheme_primary_color', '#007cba');
    ?>
    <style>
        :root {
            --primary-color: <?php echo esc_attr($primary_color); ?>;
        }
        
        .button-primary {
            background-color: var(--primary-color);
        }
    </style>
    <?php
}
add_action('wp_head', 'mytheme_custom_styles');
```

## Related Documentation

- [Theme Development](themes.md) - Creating themes for Isotone
- [Hooks System](../hooks/hooks-reference.md) - Available hooks and filters
- [WordPress Compatibility](wordpress-compatibility.md) - WordPress API compatibility

## API Reference

### Methods

#### `addSection($id, $args)`
Adds a new customizer section.

#### `addSetting($id, $args)`
Registers a new setting.

#### `addControl($id, $args)`
Creates a control for a setting.

#### `getSections()`
Returns all registered sections.

#### `getSettings()`
Returns all registered settings.

#### `save($values)`
Saves customizer values to database.

## Support

For questions or issues with the Customizer:
1. Check the [troubleshooting section](#troubleshooting)
2. Review the [examples](#complete-example)
3. Report issues on GitHub
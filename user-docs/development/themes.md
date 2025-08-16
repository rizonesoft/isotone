# Theme Development

## Introduction

Isotone provides a native Theme API with WordPress-compatible functions, making theme development familiar and straightforward.

## Theme Structure

```
iso-content/themes/your-theme/
├── style.css           # Theme metadata (required)
├── index.php           # Main template (required)
├── functions.php       # Theme functions
├── screenshot.png      # Theme preview (1200x900)
├── home.php           # Blog listing template
├── single.php         # Single post template
├── page.php           # Page template
├── header.php         # Header partial
├── footer.php         # Footer partial
└── assets/
    ├── css/
    ├── js/
    └── images/
```

## Creating a Theme

### 1. Theme Metadata (style.css)

```css
/*
Theme Name: My Awesome Theme
Theme URI: https://example.com/
Author: Your Name
Author URI: https://example.com/
Description: A beautiful theme for Isotone
Version: 1.0.0
License: MIT
Text Domain: my-theme
*/
```

### 2. Basic Template (index.php)

```php
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> - <?php bloginfo('description'); ?></title>
    <?php iso_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php iso_body_open(); ?>
    
    <header>
        <h1><?php bloginfo('name'); ?></h1>
        <nav><?php wp_nav_menu(['theme_location' => 'primary']); ?></nav>
    </header>
    
    <main>
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class(); ?>>
                    <h2><?php the_title(); ?></h2>
                    <?php the_content(); ?>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <p>No content found.</p>
        <?php endif; ?>
    </main>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></p>
    </footer>
    
    <?php iso_footer(); ?>
</body>
</html>
```

### 3. Theme Functions (functions.php)

```php
<?php
/**
 * Theme Functions
 */

// Theme Setup
add_action('after_setup_theme', function() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    
    // Register navigation menus
    register_nav_menus([
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu'
    ]);
});

// Enqueue Assets
add_action('iso_enqueue_scripts', function() {
    // Theme styles
    wp_enqueue_style(
        'theme-style', 
        get_stylesheet_uri(), 
        [], 
        '1.0.0'
    );
    
    // Theme scripts
    wp_enqueue_script(
        'theme-script',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        '1.0.0',
        true
    );
});

// Custom Functions
function my_theme_custom_function() {
    // Your custom code
}
```

## Template Functions

All WordPress-compatible template functions are available:

### Site Information
- `bloginfo($show)` - Display site information
- `get_bloginfo($show)` - Get site information
- `home_url($path)` - Get home URL
- `site_url($path)` - Get site URL

### The Loop
- `have_posts()` - Check if posts exist
- `the_post()` - Set up post data
- `the_title()` - Display post title
- `the_content()` - Display post content
- `the_excerpt()` - Display post excerpt
- `the_permalink()` - Display post URL
- `the_date()` - Display post date
- `the_author()` - Display post author

### Template Parts
- `get_header($name)` - Include header template
- `get_footer($name)` - Include footer template
- `get_sidebar($name)` - Include sidebar template
- `get_template_part($slug, $name)` - Include template part

### Conditional Tags
- `is_home()` - Is blog home page
- `is_front_page()` - Is front page
- `is_single()` - Is single post
- `is_page()` - Is page
- `is_archive()` - Is archive page
- `is_category()` - Is category archive
- `is_tag()` - Is tag archive
- `is_404()` - Is 404 page

## Hooks

### Action Hooks

```php
// Header
add_action('iso_head', function() {
    echo '<meta name="theme-color" content="#000">';
});

// Footer
add_action('iso_footer', function() {
    echo '<script>console.log("Footer loaded");</script>';
});

// After theme setup
add_action('after_setup_theme', function() {
    // Theme initialization
});

// Init
add_action('init', function() {
    // Register post types, taxonomies, etc.
});
```

### Filter Hooks

```php
// Modify content
add_filter('the_content', function($content) {
    return $content . '<p>Added by filter</p>';
});

// Modify title
add_filter('the_title', function($title) {
    return '★ ' . $title;
});

// Body classes
add_filter('body_class', function($classes) {
    $classes[] = 'my-custom-class';
    return $classes;
});
```

## Theme Customization

### Theme Mods

```php
// Set theme mod
set_theme_mod('primary_color', '#007cba');

// Get theme mod
$color = get_theme_mod('primary_color', '#000000');
```

### Custom Logo

```php
// Add theme support
add_theme_support('custom-logo', [
    'height' => 100,
    'width' => 400,
    'flex-height' => true,
    'flex-width' => true,
]);

// Display logo
if (function_exists('the_custom_logo')) {
    the_custom_logo();
}
```

## Best Practices

1. **Escape Output**: Always escape dynamic output
   ```php
   echo esc_html($title);
   echo esc_url($url);
   echo esc_attr($attribute);
   ```

2. **Internationalization**: Make themes translatable
   ```php
   _e('Hello World', 'my-theme');
   __('Hello World', 'my-theme');
   ```

3. **Enqueue Assets**: Use proper enqueueing
   ```php
   wp_enqueue_style('handle', $src, $deps, $ver);
   wp_enqueue_script('handle', $src, $deps, $ver, $in_footer);
   ```

4. **Child Theme Support**: Make themes child-theme friendly
   ```php
   get_template_directory_uri(); // Parent theme
   get_stylesheet_directory_uri(); // Child theme
   ```

## Example: Neutron Theme

The Neutron theme demonstrates modern theme development with:
- Tailwind CSS for styling
- Alpine.js for interactivity
- Dark mode support
- Responsive design
- Hero sections
- Custom navigation

View the source: `/iso-content/themes/neutron/`

## Resources

- [Template Functions Reference](./template-functions.md)
- [Hooks Reference](./hooks.md)
- [Plugin Development](./plugins.md)
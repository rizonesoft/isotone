# Isotone Theme Developer Guide

## Introduction

Welcome to Isotone theme development! If you're familiar with WordPress theme development, you'll feel right at home. Isotone provides a native Theme API with WordPress-compatible functions, making the transition seamless.

## Native Theme API

Isotone includes a complete native theming system that provides WordPress-compatible functions without requiring WordPress. All standard template functions work out of the box.

## Quick Start

### Theme Structure

```
iso-content/themes/your-theme/
├── style.css           # Theme header (required)
├── index.php           # Main template (required)
├── functions.php       # Theme functions
├── screenshot.png      # Theme preview (1200x900)
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── templates/
│   ├── header.php
│   ├── footer.php
│   └── sidebar.php
├── partials/
│   └── content-*.php
└── includes/
    └── customizer.php
```

### Minimum Required Files

#### style.css
```css
/*
Theme Name: My Awesome Theme
Theme URI: https://example.com/themes/awesome
Author: Your Name
Author URI: https://example.com
Description: A beautiful theme for Isotone
Version: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: my-awesome-theme
Tags: responsive, dark-mode, modern, blog
*/
```

#### index.php
```php
<?php
/**
 * Main Template File
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            get_template_part('template-parts/content', get_post_format());
        endwhile;
        
        the_posts_navigation();
    else :
        get_template_part('template-parts/content', 'none');
    endif;
    ?>
</main>

<?php
get_sidebar();
get_footer();
```

## Using Hooks

### Essential Action Hooks

```php
// In your theme's functions.php

// Theme setup
add_action('after_setup_theme', function() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery']);
    
    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'textdomain'),
        'footer' => __('Footer Menu', 'textdomain')
    ]);
});

// Enqueue styles and scripts
add_action('iso_enqueue_scripts', function() {
    // Theme stylesheet
    iso_enqueue_style('theme-style', get_stylesheet_uri(), [], '1.0.0');
    
    // Custom CSS
    iso_enqueue_style('theme-custom', get_template_directory_uri() . '/assets/css/custom.css', ['theme-style'], '1.0.0');
    
    // JavaScript
    iso_enqueue_script('theme-script', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], '1.0.0', true);
    
    // Localize script
    iso_localize_script('theme-script', 'theme_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => iso_create_nonce('theme_nonce')
    ]);
});

// Add custom head elements
add_action('iso_head', function() {
    ?>
    <meta name="theme-color" content="#00D9FF">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <?php
}, 5);

// Add footer scripts
add_action('iso_footer', function() {
    ?>
    <script>
        console.log('Theme loaded!');
    </script>
    <?php
}, 100);
```

### Essential Filter Hooks

```php
// Modify content
add_filter('the_content', function($content) {
    if (is_single()) {
        $content .= '<div class="share-buttons">Share this post!</div>';
    }
    return $content;
});

// Modify title
add_filter('the_title', function($title, $id) {
    if (is_admin()) {
        return $title;
    }
    return '⭐ ' . $title;
}, 10, 2);

// Modify excerpt length
add_filter('excerpt_length', function() {
    return 30; // words
});

// Modify excerpt more
add_filter('excerpt_more', function() {
    return '... <a href="' . get_permalink() . '">Read More</a>';
});

// Add custom body classes
add_filter('body_class', function($classes) {
    if (is_front_page()) {
        $classes[] = 'front-page';
    }
    return $classes;
});
```

## Template Hierarchy

Isotone follows WordPress's template hierarchy:

```
Home Page: front-page.php → home.php → index.php
Single Post: single-{post-type}-{slug}.php → single-{post-type}.php → single.php → singular.php → index.php
Page: page-{slug}.php → page-{id}.php → page.php → singular.php → index.php
Category: category-{slug}.php → category-{id}.php → category.php → archive.php → index.php
Tag: tag-{slug}.php → tag-{id}.php → tag.php → archive.php → index.php
Author: author-{nicename}.php → author-{id}.php → author.php → archive.php → index.php
Date: date.php → archive.php → index.php
Archive: archive-{post-type}.php → archive.php → index.php
Search: search.php → index.php
404: 404.php → index.php
```

## Template Tags

All WordPress-compatible template tags are natively supported by Isotone's Theme API. These functions are available globally in your theme templates.

### Basic Template Tags

```php
// Site information
bloginfo('name');           // Site title
bloginfo('description');    // Site tagline
bloginfo('url');           // Site URL
bloginfo('charset');       // Charset (UTF-8)

// URLs
home_url('/');              // Home URL
site_url('/');              // Site URL
admin_url('/');             // Admin URL
get_template_directory_uri(); // Theme URL
get_stylesheet_directory_uri(); // Child theme URL

// Head and Footer
iso_head();                  // Essential head hooks
iso_footer();                // Essential footer hooks
iso_body_open();            // After opening body tag

// Navigation
wp_nav_menu([
    'theme_location' => 'primary',
    'container' => 'nav',
    'container_class' => 'main-navigation',
    'menu_class' => 'menu',
    'fallback_cb' => false
]);

// Content
the_title();                // Post/page title
the_content();              // Post/page content
the_excerpt();              // Post excerpt
the_permalink();            // Post URL
the_time('F j, Y');        // Post date
the_author();               // Post author
the_category(', ');         // Post categories
the_tags('Tags: ', ', ');   // Post tags
```

### The Loop

```php
<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div class="meta">
                    Posted on <?php the_time('F j, Y'); ?> by <?php the_author(); ?>
                </div>
            </header>
            
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
            
            <footer>
                Categories: <?php the_category(', '); ?>
                <?php the_tags('Tags: ', ', ', ''); ?>
            </footer>
        </article>
    <?php endwhile; ?>
    
    <?php the_posts_navigation(); ?>
    
<?php else : ?>
    <p>No posts found.</p>
<?php endif; ?>
```

## Sidebars and Widgets

### Registering Sidebars

```php
add_action('widgets_init', function() {
    register_sidebar([
        'name' => __('Primary Sidebar', 'textdomain'),
        'id' => 'sidebar-primary',
        'description' => __('Main sidebar that appears on the right.', 'textdomain'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ]);
    
    register_sidebar([
        'name' => __('Footer Widgets', 'textdomain'),
        'id' => 'footer-widgets',
        'description' => __('Appears in the footer section.', 'textdomain'),
        'before_widget' => '<div class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ]);
});
```

### Displaying Sidebars

```php
<!-- In sidebar.php -->
<?php if (is_active_sidebar('sidebar-primary')) : ?>
    <aside id="secondary" class="widget-area">
        <?php dynamic_sidebar('sidebar-primary'); ?>
    </aside>
<?php endif; ?>
```

## Theme Customizer

### Adding Customizer Options

```php
add_action('customize_register', function($wp_customize) {
    // Add section
    $wp_customize->add_section('theme_options', [
        'title' => __('Theme Options', 'textdomain'),
        'priority' => 30,
    ]);
    
    // Add setting
    $wp_customize->add_setting('primary_color', [
        'default' => '#00D9FF',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    
    // Add control
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', [
        'label' => __('Primary Color', 'textdomain'),
        'section' => 'theme_options',
        'settings' => 'primary_color',
    ]));
});

// Use in theme
$primary_color = get_theme_mod('primary_color', '#00D9FF');
```

## Custom Post Types and Taxonomies

```php
add_action('init', function() {
    // Register custom post type
    register_post_type('portfolio', [
        'labels' => [
            'name' => __('Portfolio', 'textdomain'),
            'singular_name' => __('Portfolio Item', 'textdomain'),
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon' => 'dashicons-portfolio',
        'rewrite' => ['slug' => 'portfolio'],
    ]);
    
    // Register custom taxonomy
    register_taxonomy('portfolio_category', 'portfolio', [
        'labels' => [
            'name' => __('Portfolio Categories', 'textdomain'),
            'singular_name' => __('Portfolio Category', 'textdomain'),
        ],
        'hierarchical' => true,
        'rewrite' => ['slug' => 'portfolio-category'],
    ]);
});
```

## AJAX in Themes

```php
// JavaScript
jQuery(document).ready(function($) {
    $('#load-more').click(function() {
        $.ajax({
            url: theme_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_more_posts',
                nonce: theme_ajax.nonce,
                page: $(this).data('page')
            },
            success: function(response) {
                if (response.success) {
                    $('#posts-container').append(response.data.html);
                }
            }
        });
    });
});

// PHP Handler
add_action('iso_ajax_load_more_posts', 'handle_load_more');
add_action('iso_ajax_nopriv_load_more_posts', 'handle_load_more');

function handle_load_more() {
    check_ajax_referer('theme_nonce', 'nonce');
    
    $page = intval($_POST['page']);
    
    $query = new WP_Query([
        'post_type' => 'post',
        'paged' => $page,
        'posts_per_page' => 10
    ]);
    
    ob_start();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/content');
        }
    }
    
    $html = ob_get_clean();
    
    wp_send_json_success(['html' => $html]);
}
```

## Best Practices

### 1. Security

```php
// Escape output
echo esc_html($text);
echo esc_attr($attribute);
echo esc_url($url);
echo esc_js($javascript);

// Sanitize input
$clean = sanitize_text_field($_POST['field']);
$clean = sanitize_email($_POST['email']);
$clean = sanitize_file_name($_FILES['file']['name']);

// Nonces
iso_nonce_field('action_name', 'nonce_name');
check_admin_referer('action_name', 'nonce_name');

// Capabilities
if (!current_user_can('edit_posts')) {
    wp_die('Unauthorized');
}
```

### 2. Performance

```php
// Enqueue scripts properly
iso_enqueue_script('handle', 'url', ['dependencies'], 'version', true);

// Use transients for expensive operations
$data = get_transient('expensive_data');
if (false === $data) {
    $data = expensive_operation();
    set_transient('expensive_data', $data, 12 * HOUR_IN_SECONDS);
}

// Optimize database queries
$results = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = %s", 'post')
);
```

### 3. Internationalization

```php
// Load text domain
add_action('after_setup_theme', function() {
    load_theme_textdomain('textdomain', get_template_directory() . '/languages');
});

// Translate strings
__('Hello World', 'textdomain');           // Return translation
_e('Hello World', 'textdomain');           // Echo translation
_n('One item', '%s items', $count, 'textdomain'); // Plurals
_x('Post', 'noun', 'textdomain');          // Context
esc_html__('Hello World', 'textdomain');   // Escaped translation
```

## Testing Your Theme

### Unit Testing

```php
class ThemeTest extends WP_UnitTestCase {
    public function test_theme_setup() {
        $this->assertTrue(current_theme_supports('post-thumbnails'));
        $this->assertTrue(has_nav_menu('primary'));
    }
    
    public function test_scripts_enqueued() {
        do_action('iso_enqueue_scripts');
        $this->assertTrue(iso_script_is('theme-script', 'enqueued'));
    }
}
```

### Debugging

```php
// Enable debug mode in config.php
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);

// Debug helpers
var_dump($variable);
print_r($array);
error_log('Debug message');
wp_die('Stop execution');

// Query debugging
echo $wpdb->last_query;
```

## Resources

- [Isotone Hooks Reference](../HOOKS.md)
- [WordPress Theme Developer Handbook](https://developer.wordpress.org/themes/)
- [Theme Check Plugin](https://wordpress.org/plugins/theme-check/)
- [Isotone Community Forum](https://isotone.tech/forum)

## Theme Submission Guidelines

Before submitting your theme:

1. ✅ All required files present (style.css, index.php)
2. ✅ Screenshot included (1200x900 PNG/JPG)
3. ✅ No PHP errors or warnings
4. ✅ Properly escaped output
5. ✅ Sanitized input
6. ✅ Responsive design
7. ✅ Accessibility standards met
8. ✅ GPL-compatible license
9. ✅ No hardcoded URLs
10. ✅ Internationalization ready

---

Happy theme development! 🎨
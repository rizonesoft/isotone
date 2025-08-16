# Theme API Reference

The Isotone Theme API provides WordPress-compatible functions for theme development.

## Core Classes

### ThemeAPI

The main class that handles all theme functionality.

```php
use Isotone\Core\ThemeAPI;

$api = ThemeAPI::getInstance();
```

## Site Information

### bloginfo()
Display site information.

```php
bloginfo('name');        // Site title
bloginfo('description'); // Site tagline  
bloginfo('url');        // Site URL
bloginfo('charset');    // UTF-8
bloginfo('language');   // Site language
```

### get_bloginfo()
Get site information (returns value instead of echoing).

```php
$site_name = get_bloginfo('name');
$site_url = get_bloginfo('url');
```

### home_url()
Get the home URL with optional path.

```php
$url = home_url();           // https://example.com/isotone
$url = home_url('/about');   // https://example.com/isotone/about
```

### get_template_directory_uri()
Get the current theme's directory URL.

```php
$theme_url = get_template_directory_uri();
// Returns: https://example.com/isotone/iso-content/themes/your-theme
```

## The Loop

### have_posts()
Check if there are posts to display.

```php
if (have_posts()) {
    // Display posts
}
```

### the_post()
Set up the current post data.

```php
while (have_posts()) {
    the_post();
    // Display post content
}
```

## Post Data

### the_title()
Display the post title.

```php
the_title();                          // Just the title
the_title('<h1>', '</h1>');          // With HTML wrapper
the_title('<h2 class="title">', '</h2>', false); // Return instead of echo
```

### get_the_title()
Get the post title.

```php
$title = get_the_title();
```

### the_content()
Display the post content.

```php
the_content();
the_content('Read more...');  // With custom "more" text
```

### the_excerpt()
Display the post excerpt.

```php
the_excerpt();
```

### the_permalink()
Display the post URL.

```php
<a href="<?php the_permalink(); ?>">Read More</a>
```

### the_date()
Display the post date.

```php
the_date();                    // Using default format
the_date('F j, Y');           // Custom format
the_date('', '<time>', '</time>'); // With HTML wrapper
```

### the_author()
Display the post author.

```php
the_author();
```

## Conditional Tags

### is_home()
Check if on the blog home page.

```php
if (is_home()) {
    // Blog home page specific code
}
```

### is_front_page()
Check if on the site front page.

```php
if (is_front_page()) {
    // Front page specific code
}
```

### is_single()
Check if viewing a single post.

```php
if (is_single()) {
    // Single post specific code
}
```

### is_page()
Check if viewing a page.

```php
if (is_page()) {
    // Page specific code
}
```

### is_archive()
Check if viewing an archive page.

```php
if (is_archive()) {
    // Archive page specific code
}
```

### is_404()
Check if on a 404 error page.

```php
if (is_404()) {
    // 404 page specific code
}
```

## Template Functions

### body_class()
Output body CSS classes.

```php
<body <?php body_class(); ?>>
<body <?php body_class('custom-class'); ?>>
```

### post_class()
Output post CSS classes.

```php
<article <?php post_class(); ?>>
<article <?php post_class('featured-post'); ?>>
```

### get_header()
Include the header template.

```php
get_header();           // Includes header.php
get_header('custom');   // Includes header-custom.php
```

### get_footer()
Include the footer template.

```php
get_footer();           // Includes footer.php
get_footer('custom');   // Includes footer-custom.php
```

### get_sidebar()
Include the sidebar template.

```php
get_sidebar();          // Includes sidebar.php
get_sidebar('left');    // Includes sidebar-left.php
```

### get_template_part()
Include a template part.

```php
get_template_part('content');           // Includes content.php
get_template_part('content', 'single'); // Includes content-single.php
get_template_part('partials/card');     // Includes partials/card.php

// With arguments (PHP 5.5+)
get_template_part('content', null, [
    'title' => 'Custom Title',
    'class' => 'featured'
]);
```

## Navigation

### wp_nav_menu()
Display a navigation menu.

```php
wp_nav_menu([
    'theme_location' => 'primary',
    'menu_class'     => 'nav-menu',
    'container'      => 'nav',
    'container_class' => 'primary-navigation',
    'fallback_cb'    => false,
    'depth'          => 2
]);
```

### has_nav_menu()
Check if a menu location has a menu assigned.

```php
if (has_nav_menu('primary')) {
    wp_nav_menu(['theme_location' => 'primary']);
}
```

## Theme Mods

### get_theme_mod()
Get a theme customization value.

```php
$color = get_theme_mod('primary_color', '#007cba');
$layout = get_theme_mod('layout_style', 'full-width');
```

### set_theme_mod()
Set a theme customization value.

```php
set_theme_mod('primary_color', '#ff0000');
```

## Escaping Functions

### esc_html()
Escape HTML.

```php
echo esc_html($user_input);
```

### esc_attr()
Escape HTML attributes.

```php
<input value="<?php echo esc_attr($value); ?>">
```

### esc_url()
Escape URLs.

```php
<a href="<?php echo esc_url($link); ?>">Link</a>
```

### esc_js()
Escape JavaScript.

```php
<script>
var data = '<?php echo esc_js($data); ?>';
</script>
```

## Utility Functions

### __()
Translate text (returns).

```php
$text = __('Hello World', 'text-domain');
```

### _e()
Translate and echo text.

```php
_e('Hello World', 'text-domain');
```

### wp_kses()
Strip unwanted HTML.

```php
$clean = wp_kses($html, $allowed_html);
```

### wp_kses_post()
Strip HTML not allowed in posts.

```php
$clean = wp_kses_post($content);
```

## Hooks Integration

The Theme API integrates with Isotone's hook system:

### Actions
- `iso_head` - Fired in the `<head>` section
- `iso_footer` - Fired before `</body>`
- `iso_body_open` - Fired after `<body>`
- `after_setup_theme` - Theme initialization
- `init` - General initialization
- `iso_enqueue_scripts` - Enqueue assets

### Filters
- `the_content` - Filter post content
- `the_title` - Filter post title
- `the_excerpt` - Filter post excerpt
- `body_class` - Filter body classes
- `post_class` - Filter post classes

## Example Usage

```php
<?php
// Complete template example
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php bloginfo('name'); ?></title>
    <?php iso_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php iso_body_open(); ?>
    
    <header>
        <h1><?php bloginfo('name'); ?></h1>
        <?php if (has_nav_menu('primary')) : ?>
            <?php wp_nav_menu(['theme_location' => 'primary']); ?>
        <?php endif; ?>
    </header>
    
    <main>
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class(); ?>>
                    <h2><?php the_title(); ?></h2>
                    <time><?php the_date(); ?></time>
                    <?php the_content(); ?>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </main>
    
    <?php get_footer(); ?>
    <?php iso_footer(); ?>
</body>
</html>
```
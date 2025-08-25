<?php
/**
 * Isotone Default Theme
 * Main template file
 */

// Prevent direct access
if (!defined('ISOTONE_ROOT')) {
    die('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_site_title(); ?> - <?php echo get_page_title(); ?></title>
    <link rel="stylesheet" href="<?php echo get_theme_url(); ?>/assets/css/isotone.css">
    <link rel="stylesheet" href="<?php echo get_theme_url(); ?>/style.css">
    <?php do_action('isotone_head'); ?>
</head>
<body class="iso-app iso-background">
    <div class="grid-bg"></div>
    
    <header class="iso-header-main">
        <div class="iso-container iso-container-lg">
            <nav class="iso-nav">
                <a href="/" class="iso-brand">
                    <img src="/iso-includes/assets/logo.svg" alt="<?php echo get_site_title(); ?>" class="iso-logo">
                    <span class="iso-brand-text"><?php echo get_site_title(); ?></span>
                </a>
                <?php wp_nav_menu(['theme_location' => 'primary']); ?>
            </nav>
        </div>
    </header>

    <main class="iso-main">
        <div class="iso-container iso-container-lg iso-glass">
            <?php do_action('isotone_before_content'); ?>
            
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article class="iso-article">
                        <h1 class="iso-title"><?php the_title(); ?></h1>
                        <div class="iso-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="iso-no-content">
                    <h2 class="iso-title-md">No Content Found</h2>
                    <p class="iso-subtitle">The page you're looking for doesn't exist.</p>
                </div>
            <?php endif; ?>
            
            <?php do_action('isotone_after_content'); ?>
        </div>
    </main>

    <footer class="iso-footer">
        <div class="iso-container iso-container-lg">
            <div class="iso-footer-content">
                <p>&copy; <?php echo date('Y'); ?> <?php echo get_site_title(); ?>. Powered by Isotone.</p>
            </div>
        </div>
    </footer>

    <?php do_action('isotone_footer'); ?>
</body>
</html>
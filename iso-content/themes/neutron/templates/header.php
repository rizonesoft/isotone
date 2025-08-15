<?php
/**
 * Header Template Part
 * 
 * @package Neutron
 */

use function NeutronTheme\get_heroicon;
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" class="<?php echo get_theme_mod('neutron_dark_mode_default', 'auto') === 'dark' ? 'dark' : ''; ?>">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '<?php echo get_theme_mod('neutron_primary_color', '#00D9FF'); ?>',
                        secondary: '#00FF88',
                    }
                }
            }
        }
    </script>
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100'); ?>>

<header class="sticky top-0 z-50 bg-white dark:bg-gray-800 shadow-sm" x-data="{ mobileMenuOpen: false }">
    <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?php echo home_url(); ?>" class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-lg"></div>
                    <span class="text-xl font-bold"><?php bloginfo('name'); ?></span>
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:block">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'flex space-x-8',
                    'fallback_cb' => false,
                ]);
                ?>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center space-x-4">
                <!-- Search -->
                <button data-search-toggle class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <?php echo get_heroicon('search', 'outline', 'w-5 h-5'); ?>
                </button>
                
                <!-- Dark Mode Toggle -->
                <button @click="document.documentElement.classList.toggle('dark')" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </button>
                
                <!-- Mobile Menu -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2">
                    <span x-show="!mobileMenuOpen"><?php echo get_heroicon('menu', 'outline', 'w-6 h-6'); ?></span>
                    <span x-show="mobileMenuOpen"><?php echo get_heroicon('x', 'outline', 'w-6 h-6'); ?></span>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden py-4">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'space-y-2',
                'fallback_cb' => false,
            ]);
            ?>
        </div>
    </nav>
</header>
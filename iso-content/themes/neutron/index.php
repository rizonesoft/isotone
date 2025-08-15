<?php
/**
 * Neutron Theme - Main Template
 * 
 * This is the main template file for the Neutron theme.
 * It serves as the default template when no more specific template is found.
 * 
 * @package Neutron
 * @version 1.0.0
 */

// Theme functions are loaded by Isotone
// require_once __DIR__ . '/functions.php'; // Already loaded by theme system
use function NeutronTheme\get_heroicon;
use function NeutronTheme\get_theme_option;

// Get dark mode preference
$dark_mode = get_theme_option('dark_mode_default', 'auto');
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $dark_mode === 'dark' ? 'dark' : ''; ?>" data-theme-mode="<?php echo esc_attr($dark_mode); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isotone - Modern CMS</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '<?php echo get_theme_option('primary_color', '#00D9FF'); ?>',
                        secondary: '#00FF88',
                        dark: '#0A0E27',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
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
    
    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Gradient without animation - static */
        .hero-gradient {
            /* Static gradient - no animation */
        }
        
        /* Glassmorphism effect for cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Subtle glow effect */
        .glow {
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.3);
        }
    </style>
    
    <?php if (function_exists('iso_head')) iso_head(); ?>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    <?php if (function_exists('iso_body_open')) iso_body_open(); ?>
    
    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700" x-data="{ mobileMenuOpen: false }">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo home_url(); ?>" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-lg"></div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">
                            <?php echo get_bloginfo('name'); ?>
                        </span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">Home</a>
                    <a href="/about" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">About</a>
                    <a href="/blog" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">Blog</a>
                    <a href="/contact" class="text-gray-700 dark:text-gray-300 hover:text-primary transition">Contact</a>
                </div>
                
                <!-- Right Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Search Button -->
                    <button class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary transition">
                        <?php echo get_heroicon('search', 'outline', 'w-5 h-5'); ?>
                    </button>
                    
                    <!-- Dark Mode Toggle -->
                    <button 
                        @click="document.documentElement.classList.toggle('dark')"
                        class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary transition"
                    >
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                        <svg class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                    
                    <!-- Mobile Menu Button -->
                    <button 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden p-2 text-gray-600 dark:text-gray-400 hover:text-primary transition"
                    >
                        <span x-show="!mobileMenuOpen"><?php echo get_heroicon('menu', 'outline', 'w-6 h-6'); ?></span>
                        <span x-show="mobileMenuOpen" x-cloak><?php echo get_heroicon('x', 'outline', 'w-6 h-6'); ?></span>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div 
                x-show="mobileMenuOpen" 
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="md:hidden py-4 border-t border-gray-200 dark:border-gray-700"
            >
                <div class="flex flex-col space-y-2">
                    <a href="/" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Home</a>
                    <a href="/about" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">About</a>
                    <a href="/blog" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Blog</a>
                    <a href="/contact" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Contact</a>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Hero Section -->
    <section class="relative overflow-hidden text-white">
        <!-- Dark Blue to Cyan to Teal Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900 via-cyan-500 to-teal-500"></div>
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 pt-20 lg:pt-28 pb-48 lg:pb-56">
            <div class="max-w-3xl relative z-10">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                    Welcome to Neutron Theme
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">
                    A modern, responsive theme for Isotone powered by Tailwind CSS and Heroicons.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="#features" class="inline-flex items-center px-6 py-3 bg-white text-gray-900 rounded font-medium hover:bg-gray-100 transition-all duration-200 shadow-lg">
                        Get Started
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                    <a href="#demo" class="inline-flex items-center px-6 py-3 bg-transparent border border-white/60 text-white rounded font-medium hover:bg-white/10 transition-all duration-200">
                        View Demo
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Modern Wave Shape -->
        <div class="absolute -bottom-px left-0 right-0 pointer-events-none">
            <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block">
                <path d="M0 80L48 72C96 64 192 48 288 32C384 16 480 0 576 0C672 0 768 16 864 28C960 40 1056 48 1152 52C1248 56 1344 56 1392 56L1440 56V80H1392C1344 80 1248 80 1152 80C1056 80 960 80 864 80C768 80 672 80 576 80C480 80 384 80 288 80C192 80 96 80 48 80H0Z" class="fill-gray-50 dark:fill-gray-900"/>
            </svg>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Theme Features
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Built with modern web technologies for the best user experience
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature Card 1 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Responsive Design</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Perfectly optimized for all devices from mobile to desktop screens.
                    </p>
                </div>
                
                <!-- Feature Card 2 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Dark Mode</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Built-in dark mode support with system preference detection.
                    </p>
                </div>
                
                <!-- Feature Card 3 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Lightning Fast</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Optimized for speed with minimal dependencies and efficient code.
                    </p>
                </div>
                
                <!-- Feature Card 4 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Customizable</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Easy to customize with Tailwind CSS utilities and theme options.
                    </p>
                </div>
                
                <!-- Feature Card 5 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Clean Code</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Well-structured and documented code following best practices.
                    </p>
                </div>
                
                <!-- Feature Card 6 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">SEO Friendly</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Optimized for search engines with proper meta tags and structure.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-lg"></div>
                        <span class="text-xl font-bold text-white">Neutron</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        A modern theme for Isotone built with Tailwind CSS and designed for performance.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.374 0 0 5.373 0 12s5.374 12 12 12c6.627 0 12-5.373 12-12S18.627 0 12 0zm4.441 16.892c-2.102.144-6.784.144-8.883 0C5.282 16.736 5.017 15.622 5 12c.017-3.629.285-4.736 2.558-4.892 2.099-.144 6.782-.144 8.883 0C18.718 7.264 18.982 8.378 19 12c-.018 3.629-.285 4.736-2.559 4.892zM10 9.658l4.917 2.338L10 14.342V9.658z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="/about" class="text-gray-400 hover:text-white transition">About</a></li>
                        <li><a href="/features" class="text-gray-400 hover:text-white transition">Features</a></li>
                        <li><a href="/pricing" class="text-gray-400 hover:text-white transition">Pricing</a></li>
                        <li><a href="/contact" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Resources -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Resources</h3>
                    <ul class="space-y-2">
                        <li><a href="/docs" class="text-gray-400 hover:text-white transition">Documentation</a></li>
                        <li><a href="/blog" class="text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="/support" class="text-gray-400 hover:text-white transition">Support</a></li>
                        <li><a href="/privacy" class="text-gray-400 hover:text-white transition">Privacy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; <?php echo date('Y'); ?> Neutron Theme. Powered by <a href="https://isotone.tech" class="text-primary hover:text-white transition">Isotone</a>.
                </p>
            </div>
        </div>
    </footer>
    
    <?php if (function_exists('iso_footer')) iso_footer(); ?>
</body>
</html>
<?php
/**
 * Footer Template Part
 * 
 * @package Neutron
 */
?>

<footer class="bg-gray-900 text-gray-300 mt-auto">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (is_active_sidebar('footer-widgets')) : ?>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8 mb-8">
                <?php dynamic_sidebar('footer-widgets'); ?>
            </div>
        <?php endif; ?>
        
        <div class="border-t border-gray-800 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <!-- Footer Menu -->
                <?php if (has_nav_menu('footer')) : ?>
                    <nav class="mb-4 md:mb-0">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer',
                            'container' => false,
                            'menu_class' => 'flex flex-wrap gap-6',
                            'fallback_cb' => false,
                        ]);
                        ?>
                    </nav>
                <?php endif; ?>
                
                <!-- Copyright -->
                <div class="text-center md:text-right">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. 
                        Powered by <a href="https://isotone.tech" class="text-primary hover:text-white transition">Isotone</a>
                    </p>
                </div>
            </div>
            
            <!-- Social Links -->
            <?php if (has_nav_menu('social')) : ?>
                <div class="mt-6 flex justify-center space-x-6">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'social',
                        'container' => false,
                        'menu_class' => 'flex space-x-6',
                        'fallback_cb' => false,
                        'link_before' => '<span class="sr-only">',
                        'link_after' => '</span>',
                    ]);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button data-back-to-top class="hidden fixed bottom-8 right-8 p-3 bg-primary text-white rounded-full shadow-lg hover:bg-opacity-90 transition">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
    </svg>
</button>

<!-- Search Modal -->
<div data-search-modal class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-start justify-center min-h-screen pt-20 px-4">
        <div class="fixed inset-0 bg-black opacity-50" data-search-close></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6" x-data="themeSearch">
            <input 
                type="text" 
                data-search-input
                x-model="query"
                @input="search"
                placeholder="Search..."
                class="w-full px-4 py-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary"
            >
            
            <div x-show="loading" class="mt-4 text-center">
                <div class="spinner mx-auto"></div>
            </div>
            
            <div x-show="results.length > 0" class="mt-4 space-y-2">
                <template x-for="result in results" :key="result.url">
                    <a :href="result.url" class="block p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                        <span x-text="result.title"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
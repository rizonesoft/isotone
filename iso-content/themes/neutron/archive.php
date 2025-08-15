<?php
/**
 * Archive Template
 * 
 * @package Neutron
 */

get_template_part('templates/header');
?>

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Archive Header -->
    <div class="text-center mb-12">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
            <?php
            if (is_category()) {
                single_cat_title('Category: ');
            } elseif (is_tag()) {
                single_tag_title('Tag: ');
            } elseif (is_author()) {
                echo 'Author: ' . get_the_author();
            } elseif (is_date()) {
                echo 'Archives';
            } else {
                echo 'Archives';
            }
            ?>
        </h1>
        
        <?php if (is_category() || is_tag()) : ?>
            <?php $description = term_description(); ?>
            <?php if ($description) : ?>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    <?php echo $description; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Posts Grid -->
    <?php if (have_posts()) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php while (have_posts()) : the_post(); ?>
                <article class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden card-hover">
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="block aspect-w-16 aspect-h-9">
                            <?php the_post_thumbnail('medium_large', ['class' => 'w-full h-48 object-cover']); ?>
                        </a>
                    <?php else : ?>
                        <a href="<?php the_permalink(); ?>" class="block h-48 bg-gradient-to-br from-primary to-secondary"></a>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <!-- Post Meta -->
                        <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-3">
                            <time datetime="<?php echo get_the_date('c'); ?>">
                                <?php echo get_the_date(); ?>
                            </time>
                            <?php if (has_category()) : ?>
                                <span>•</span>
                                <?php the_category(', '); ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Title -->
                        <h2 class="text-xl font-bold mb-3">
                            <a href="<?php the_permalink(); ?>" 
                               class="text-gray-900 dark:text-white hover:text-primary transition">
                                <?php the_title(); ?>
                            </a>
                        </h2>
                        
                        <!-- Excerpt -->
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                        </p>
                        
                        <!-- Read More -->
                        <a href="<?php the_permalink(); ?>" 
                           class="inline-flex items-center text-primary hover:underline">
                            Read More
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <div class="mt-12 flex justify-center">
            <nav class="flex space-x-2">
                <?php
                echo paginate_links([
                    'prev_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>',
                    'next_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>',
                    'before_page_number' => '<span class="sr-only">Page </span>',
                    'class' => 'px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition',
                ]);
                ?>
            </nav>
        </div>
    <?php else : ?>
        <!-- No Posts Found -->
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">No posts found</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Sorry, but nothing matched your search terms. Please try again with different keywords.
            </p>
            <a href="<?php echo home_url(); ?>" 
               class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-opacity-90 transition">
                Go to Homepage
            </a>
        </div>
    <?php endif; ?>
</main>

<?php get_template_part('templates/footer'); ?>
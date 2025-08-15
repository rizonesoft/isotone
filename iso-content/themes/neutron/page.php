<?php
/**
 * Page Template
 * 
 * @package Neutron
 */

get_template_part('templates/header');
?>

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-4xl mx-auto">
        <?php while (have_posts()) : the_post(); ?>
            <article class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
                <h1 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900 dark:text-white">
                    <?php the_title(); ?>
                </h1>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="mb-8 rounded-lg overflow-hidden">
                        <?php the_post_thumbnail('large', ['class' => 'w-full h-auto']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    <?php the_content(); ?>
                </div>
                
                <?php if (get_edit_post_link()) : ?>
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="<?php echo get_edit_post_link(); ?>" 
                           class="inline-flex items-center text-primary hover:underline">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit this page
                        </a>
                    </div>
                <?php endif; ?>
            </article>
            
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="mt-8">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</main>

<?php get_template_part('templates/footer'); ?>
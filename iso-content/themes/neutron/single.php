<?php
/**
 * Single Post Template
 * 
 * @package Neutron
 */

get_template_part('templates/header');
?>

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-4xl mx-auto">
        <?php while (have_posts()) : the_post(); ?>
            <article class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="aspect-w-16 aspect-h-9">
                        <?php the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="p-8">
                    <!-- Post Meta -->
                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400 mb-4">
                        <time datetime="<?php echo get_the_date('c'); ?>">
                            <?php echo get_the_date(); ?>
                        </time>
                        <span>•</span>
                        <span><?php the_author(); ?></span>
                        <?php if (has_category()) : ?>
                            <span>•</span>
                            <span><?php the_category(', '); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900 dark:text-white">
                        <?php the_title(); ?>
                    </h1>
                    
                    <!-- Content -->
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        <?php the_content(); ?>
                    </div>
                    
                    <!-- Tags -->
                    <?php if (has_tag()) : ?>
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap gap-2">
                                <?php
                                $tags = get_the_tags();
                                foreach ($tags as $tag) :
                                ?>
                                    <a href="<?php echo get_tag_link($tag); ?>" 
                                       class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-sm rounded-full hover:bg-primary hover:text-white transition">
                                        #<?php echo $tag->name; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            
            <!-- Author Bio -->
            <div class="mt-8 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="flex items-start space-x-4">
                    <?php echo get_avatar(get_the_author_meta('ID'), 64, '', '', ['class' => 'rounded-full']); ?>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            <?php the_author(); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            <?php the_author_meta('description'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-8 flex justify-between">
                <?php
                $prev_post = get_previous_post();
                $next_post = get_next_post();
                ?>
                
                <?php if ($prev_post) : ?>
                    <a href="<?php echo get_permalink($prev_post); ?>" 
                       class="flex items-center space-x-2 text-primary hover:underline">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Previous Post</span>
                    </a>
                <?php endif; ?>
                
                <?php if ($next_post) : ?>
                    <a href="<?php echo get_permalink($next_post); ?>" 
                       class="flex items-center space-x-2 text-primary hover:underline ml-auto">
                        <span>Next Post</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </nav>
            
            <!-- Comments -->
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="mt-12">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
            
        <?php endwhile; ?>
    </div>
</main>

<?php get_template_part('templates/footer'); ?>
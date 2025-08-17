<?php
/**
 * Isotone Content Service
 * 
 * Manages posts, pages, and other content types
 * 
 * @package Isotone\Services
 * @since 1.0.0
 */

namespace Isotone\Services;

use RedBeanPHP\R;
use RedBeanPHP\OODBBean;

class ContentService
{
    /**
     * Get all published posts
     */
    public function getPosts($args = [])
    {
        $defaults = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 10,
            'offset' => 0
        ];
        
        $args = array_merge($defaults, $args);
        
        // For now, return demo posts since we don't have a content table yet
        return $this->getDemoPosts();
    }
    
    /**
     * Get a single post by ID or slug
     */
    public function getPost($id_or_slug)
    {
        if (is_numeric($id_or_slug)) {
            // Get by ID
            return $this->getDemoPost($id_or_slug);
        } else {
            // Get by slug
            return $this->getDemoPostBySlug($id_or_slug);
        }
    }
    
    /**
     * Create a new post
     */
    public function createPost($data)
    {
        if (!R::testConnection()) {
            return false;
        }
        
        $post = R::dispense('isotonepost');
        $post->title = $data['title'] ?? 'Untitled';
        $post->content = $data['content'] ?? '';
        $post->excerpt = $data['excerpt'] ?? '';
        $post->slug = $data['slug'] ?? $this->generateSlug($post->title);
        $post->post_type = $data['post_type'] ?? 'post';
        $post->post_status = $data['post_status'] ?? 'draft';
        $post->author = $data['author'] ?? 'Admin';
        $post->created_at = date('Y-m-d H:i:s');
        $post->updated_at = date('Y-m-d H:i:s');
        
        return R::store($post);
    }
    
    /**
     * Update a post
     */
    public function updatePost($id, $data)
    {
        if (!R::testConnection()) {
            return false;
        }
        
        $post = R::load('isotonepost', $id);
        if (!$post || !$post->id) {
            return false;
        }
        
        if (isset($data['title'])) $post->title = $data['title'];
        if (isset($data['content'])) $post->content = $data['content'];
        if (isset($data['excerpt'])) $post->excerpt = $data['excerpt'];
        if (isset($data['slug'])) $post->slug = $data['slug'];
        if (isset($data['post_type'])) $post->post_type = $data['post_type'];
        if (isset($data['post_status'])) $post->post_status = $data['post_status'];
        if (isset($data['author'])) $post->author = $data['author'];
        
        $post->updated_at = date('Y-m-d H:i:s');
        
        return R::store($post);
    }
    
    /**
     * Delete a post
     */
    public function deletePost($id)
    {
        if (!R::testConnection()) {
            return false;
        }
        
        $post = R::load('isotonepost', $id);
        if (!$post || !$post->id) {
            return false;
        }
        
        R::trash($post);
        return true;
    }
    
    /**
     * Generate a URL-friendly slug from a title
     */
    private function generateSlug($title)
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $original = $slug;
        $counter = 1;
        
        if (R::testConnection()) {
            while (R::findOne('isotonepost', 'slug = ?', [$slug])) {
                $slug = $original . '-' . $counter;
                $counter++;
            }
        }
        
        return $slug;
    }
    
    /**
     * Get demo posts for testing
     */
    private function getDemoPosts()
    {
        $posts = [];
        
        // Create demo post objects
        $post1 = new \stdClass();
        $post1->id = 1;
        $post1->title = 'Welcome to Isotone CMS';
        $post1->content = '<p>Welcome to Isotone, a modern content management system built with PHP. This is your first post. Edit or delete it, then start writing!</p>
        <p>Isotone features a WordPress-compatible hooks system, making it easy for developers familiar with WordPress to build themes and plugins.</p>
        <h2>Key Features</h2>
        <ul>
            <li>Modern PHP architecture with PSR standards</li>
            <li>WordPress-compatible hooks and filters</li>
            <li>Built-in theme system with template hierarchy</li>
            <li>RedBeanPHP ORM for database management</li>
            <li>Responsive admin interface</li>
        </ul>';
        $post1->excerpt = 'Welcome to Isotone, a modern content management system built with PHP.';
        $post1->slug = 'welcome-to-isotone';
        $post1->post_type = 'post';
        $post1->post_status = 'publish';
        $post1->author = 'Admin';
        $post1->created_at = date('Y-m-d H:i:s', strtotime('-1 week'));
        $posts[] = $post1;
        
        $post2 = new \stdClass();
        $post2->id = 2;
        $post2->title = 'Getting Started with Themes';
        $post2->content = '<p>Isotone makes it easy to create beautiful, responsive themes. The theme system is designed to be familiar to WordPress developers while offering modern improvements.</p>
        <h2>Theme Structure</h2>
        <p>A basic Isotone theme includes:</p>
        <ul>
            <li><code>index.php</code> - Main template file</li>
            <li><code>functions.php</code> - Theme functions and hooks</li>
            <li><code>style.css</code> - Theme stylesheet with metadata</li>
            <li>Template files for different content types</li>
        </ul>
        <p>The Neutron theme is a great example of a modern Isotone theme using Tailwind CSS and Alpine.js.</p>';
        $post2->excerpt = 'Learn how to create and customize themes in Isotone CMS.';
        $post2->slug = 'getting-started-with-themes';
        $post2->post_type = 'post';
        $post2->post_status = 'publish';
        $post2->author = 'Admin';
        $post2->created_at = date('Y-m-d H:i:s', strtotime('-3 days'));
        $posts[] = $post2;
        
        $post3 = new \stdClass();
        $post3->id = 3;
        $post3->title = 'Building Plugins for Isotone';
        $post3->content = '<p>Extend Isotone\'s functionality with plugins. The plugin system uses a familiar hooks and filters approach.</p>
        <h2>Creating Your First Plugin</h2>
        <p>To create a plugin:</p>
        <ol>
            <li>Create a folder in <code>/iso-content/plugins/</code></li>
            <li>Add your main plugin file with metadata header</li>
            <li>Use hooks and filters to modify behavior</li>
            <li>Activate through the admin panel</li>
        </ol>
        <p>Isotone uses <code>iso_</code> prefixes for its hooks, maintaining compatibility while establishing its own identity.</p>';
        $post3->excerpt = 'Discover how to extend Isotone with custom plugins.';
        $post3->slug = 'building-plugins-for-isotone';
        $post3->post_type = 'post';
        $post3->post_status = 'publish';
        $post3->author = 'Admin';
        $post3->created_at = date('Y-m-d H:i:s', strtotime('-1 day'));
        $posts[] = $post3;
        
        return $posts;
    }
    
    /**
     * Get a demo post by ID
     */
    private function getDemoPost($id)
    {
        $posts = $this->getDemoPosts();
        foreach ($posts as $post) {
            if ($post->id == $id) {
                return $post;
            }
        }
        return null;
    }
    
    /**
     * Get a demo post by slug
     */
    private function getDemoPostBySlug($slug)
    {
        $posts = $this->getDemoPosts();
        foreach ($posts as $post) {
            if ($post->slug == $slug) {
                return $post;
            }
        }
        return null;
    }
    
    /**
     * Check if posts table exists
     */
    public function ensurePostsTable()
    {
        if (!R::testConnection()) {
            return false;
        }
        
        // RedBeanPHP will create the table automatically when we first use it
        // But we can check if it exists
        $tables = R::inspect();
        return in_array('isotonepost', $tables);
    }
    
    /**
     * Initialize demo content
     */
    public function initializeDemoContent()
    {
        if (!R::testConnection()) {
            return false;
        }
        
        // Check if we already have posts
        $count = R::count('isotonepost');
        if ($count > 0) {
            return true; // Already have content
        }
        
        // Create demo posts
        $demoPosts = $this->getDemoPosts();
        foreach ($demoPosts as $demoPost) {
            $this->createPost((array) $demoPost);
        }
        
        return true;
    }
}
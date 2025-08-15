<?php
/**
 * Isotone Theme API
 * 
 * Native theming functions for Isotone
 * 
 * @package Isotone\Core
 * @since 1.0.0
 */

namespace Isotone\Core;

use RedBeanPHP\R;
use Isotone\Services\ThemeService;
use Isotone\Services\ContentService;

class ThemeAPI
{
    private static $instance = null;
    private $themeService;
    public $currentTheme;  // Made public for template functions
    private $siteInfo = [];
    private $currentPost = null;
    private $posts = [];
    private $postIndex = -1;
    
    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct()
    {
        $this->themeService = new ThemeService();
        $this->currentTheme = $this->themeService->getActiveTheme();
        $this->initializeSiteInfo();
        $this->loadPosts();
    }
    
    /**
     * Load posts for the loop
     */
    private function loadPosts()
    {
        $contentService = new ContentService();
        $this->posts = $contentService->getPosts();
        $this->postIndex = -1;
    }
    
    /**
     * Initialize site information from database
     */
    private function initializeSiteInfo()
    {
        // Default site info
        $this->siteInfo = [
            'name' => 'Isotone',
            'description' => 'Modern Content Management System',
            'url' => $this->getSiteUrl(),
            'charset' => 'UTF-8',
            'language' => 'en',
            'admin_email' => 'admin@example.com',
            'theme' => $this->currentTheme['name'] ?? 'Default'
        ];
        
        // Load from database if available
        if (R::testConnection()) {
            $settings = R::findAll('isotonesetting');
            foreach ($settings as $setting) {
                $key = str_replace('site_', '', $setting->setting_key);
                $this->siteInfo[$key] = $setting->setting_value;
            }
        }
    }
    
    /**
     * Get site URL
     */
    private function getSiteUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $path = rtrim($path, '/');
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Get site information
     */
    public function getSiteInfo($key = '')
    {
        if (empty($key)) {
            return $this->siteInfo;
        }
        
        // Handle special keys
        switch ($key) {
            case 'home':
            case 'url':
            case 'wpurl':
            case 'siteurl':
                return $this->siteInfo['url'];
            case 'name':
            case 'blogname':
                return $this->siteInfo['name'] ?? 'Isotone';
            case 'description':
            case 'blogdescription':
                return $this->siteInfo['description'] ?? 'Modern CMS';
            case 'charset':
                return $this->siteInfo['charset'] ?? 'UTF-8';
            case 'language':
                return $this->siteInfo['language'] ?? 'en';
            case 'admin_email':
                return $this->siteInfo['admin_email'] ?? '';
            case 'template':
            case 'stylesheet':
                return $this->currentTheme['slug'] ?? 'default';
            case 'template_url':
            case 'template_directory_uri':
                return $this->getTemplateDirectoryUri();
            case 'stylesheet_url':
                return $this->getStylesheetUri();
            case 'stylesheet_directory_uri':
                return $this->getStylesheetDirectoryUri();
            default:
                return $this->siteInfo[$key] ?? '';
        }
    }
    
    /**
     * Get home URL
     */
    public function getHomeUrl($path = '')
    {
        $url = $this->siteInfo['url'];
        if (!empty($path)) {
            $url .= '/' . ltrim($path, '/');
        }
        return $url;
    }
    
    /**
     * Get template directory URI
     */
    public function getTemplateDirectoryUri()
    {
        if ($this->currentTheme) {
            return $this->siteInfo['url'] . '/iso-content/themes/' . $this->currentTheme['slug'];
        }
        return $this->siteInfo['url'] . '/iso-content/themes/default';
    }
    
    /**
     * Get stylesheet URI
     */
    public function getStylesheetUri()
    {
        return $this->getTemplateDirectoryUri() . '/style.css';
    }
    
    /**
     * Get stylesheet directory URI (for child themes, same as template for now)
     */
    public function getStylesheetDirectoryUri()
    {
        return $this->getTemplateDirectoryUri();
    }
    
    /**
     * Get theme mod (customizer setting)
     */
    public function getThemeMod($name, $default = false)
    {
        if (R::testConnection()) {
            $setting = R::findOne('isotonesetting', 'setting_key = ?', ['theme_mod_' . $name]);
            if ($setting) {
                return $setting->setting_value;
            }
        }
        return $default;
    }
    
    /**
     * Set theme mod
     */
    public function setThemeMod($name, $value)
    {
        if (R::testConnection()) {
            $setting = R::findOne('isotonesetting', 'setting_key = ?', ['theme_mod_' . $name]);
            if (!$setting) {
                $setting = R::dispense('isotonesetting');
                $setting->setting_key = 'theme_mod_' . $name;
                $setting->setting_type = 'theme_mod';
            }
            $setting->setting_value = $value;
            $setting->updated_at = date('Y-m-d H:i:s');
            R::store($setting);
        }
    }
    
    /**
     * Check if we have posts
     */
    public function havePosts()
    {
        return !empty($this->posts) && $this->postIndex < count($this->posts) - 1;
    }
    
    /**
     * Setup the current post
     */
    public function thePost()
    {
        if ($this->havePosts()) {
            $this->postIndex++;
            $this->currentPost = $this->posts[$this->postIndex];
        }
    }
    
    /**
     * Get the title
     */
    public function getTitle()
    {
        if ($this->currentPost) {
            return $this->currentPost->title ?? 'Untitled';
        }
        return $this->siteInfo['name'] . ' - ' . $this->siteInfo['description'];
    }
    
    /**
     * Get the content
     */
    public function getContent()
    {
        if ($this->currentPost) {
            return $this->currentPost->content ?? '';
        }
        return '<p>Welcome to ' . $this->siteInfo['name'] . '. This is your homepage.</p>';
    }
    
    /**
     * Get the excerpt
     */
    public function getExcerpt()
    {
        if ($this->currentPost && isset($this->currentPost->excerpt)) {
            return $this->currentPost->excerpt;
        }
        
        $content = $this->getContent();
        $content = strip_tags($content);
        $content = substr($content, 0, 200);
        return $content . '...';
    }
    
    /**
     * Get permalink
     */
    public function getPermalink($post = null)
    {
        if ($post || $this->currentPost) {
            $postToUse = $post ?? $this->currentPost;
            return $this->siteInfo['url'] . '/post/' . ($postToUse->slug ?? $postToUse->id ?? '');
        }
        return '#';
    }
    
    /**
     * Get the date
     */
    public function getDate($format = 'F j, Y')
    {
        if ($this->currentPost && isset($this->currentPost->created_at)) {
            return date($format, strtotime($this->currentPost->created_at));
        }
        return date($format);
    }
    
    /**
     * Get the author
     */
    public function getAuthor()
    {
        if ($this->currentPost && isset($this->currentPost->author)) {
            return $this->currentPost->author;
        }
        return 'Admin';
    }
    
    /**
     * Get author meta
     */
    public function getAuthorMeta($field, $user_id = false)
    {
        // Placeholder implementation
        switch ($field) {
            case 'display_name':
                return 'Admin User';
            case 'description':
                return 'Site administrator';
            case 'email':
                return $this->siteInfo['admin_email'];
            default:
                return '';
        }
    }
    
    /**
     * Get avatar
     */
    public function getAvatar($id_or_email, $size = 96, $default = '', $alt = '', $args = null)
    {
        $avatar_url = $this->siteInfo['url'] . '/iso-includes/assets/default-avatar.png';
        return '<img src="' . $avatar_url . '" class="avatar" width="' . $size . '" height="' . $size . '" alt="' . $alt . '">';
    }
    
    /**
     * Check if has category
     */
    public function hasCategory($category = '', $post = null)
    {
        // Placeholder - categories not implemented yet
        return false;
    }
    
    /**
     * Get categories
     */
    public function getCategories($separator = ', ')
    {
        // Placeholder - categories not implemented yet
        return 'Uncategorized';
    }
    
    /**
     * Check if has tag
     */
    public function hasTag($tag = '', $post = null)
    {
        // Placeholder - tags not implemented yet
        return false;
    }
    
    /**
     * Get tags
     */
    public function getTags()
    {
        // Placeholder - tags not implemented yet
        return [];
    }
    
    /**
     * Get tag link
     */
    public function getTagLink($tag)
    {
        return '#';
    }
    
    /**
     * Check if has post thumbnail
     */
    public function hasPostThumbnail($post = null)
    {
        // Placeholder - thumbnails not implemented yet
        return false;
    }
    
    /**
     * Get post thumbnail
     */
    public function getPostThumbnail($size = 'thumbnail', $attr = '')
    {
        return '';
    }
    
    /**
     * Get edit post link
     */
    public function getEditPostLink($id = 0, $context = 'display')
    {
        if ($id || $this->currentPost) {
            $postId = $id ?: ($this->currentPost->id ?? 0);
            return $this->siteInfo['url'] . '/iso-admin/edit-post.php?id=' . $postId;
        }
        return '';
    }
    
    /**
     * Get locale
     */
    public function getLocale()
    {
        return $this->siteInfo['language'] . '_' . strtoupper($this->siteInfo['language']);
    }
    
    /**
     * Check page type functions
     */
    public function isHome() { return true; } // For now, always home
    public function isFrontPage() { return true; }
    public function isSingle() { return false; }
    public function isPage() { return false; }
    public function isArchive() { return false; }
    public function isCategory($category = '') { return false; }
    public function isTag($tag = '') { return false; }
    public function isAuthor($author = '') { return false; }
    public function isDate() { return false; }
    public function isSearch() { return false; }
    public function is404() { return false; }
    public function isAdmin() { return false; }
    
    /**
     * Body class
     */
    public function getBodyClass($class = '')
    {
        $classes = is_array($class) ? $class : explode(' ', $class);
        
        // Add conditional classes
        $classes[] = 'isotone-theme';
        $classes[] = $this->currentTheme['slug'] ?? 'no-theme';
        
        if ($this->isHome()) $classes[] = 'home';
        if ($this->isFrontPage()) $classes[] = 'front-page';
        if ($this->isSingle()) $classes[] = 'single';
        if ($this->isPage()) $classes[] = 'page';
        if ($this->isArchive()) $classes[] = 'archive';
        
        // Remove duplicates and empty values
        $classes = array_unique(array_filter($classes));
        
        return implode(' ', $classes);
    }
    
    /**
     * Post class
     */
    public function getPostClass($class = '', $post_id = null)
    {
        $classes = is_array($class) ? $class : explode(' ', $class);
        
        $classes[] = 'post';
        if ($this->currentPost) {
            $classes[] = 'post-' . ($this->currentPost->id ?? '0');
            $classes[] = 'type-' . ($this->currentPost->type ?? 'post');
            $classes[] = 'status-' . ($this->currentPost->status ?? 'publish');
        }
        
        $classes = array_unique(array_filter($classes));
        return implode(' ', $classes);
    }
    
    /**
     * Navigation menu
     */
    public function getNavMenu($args = [])
    {
        $defaults = [
            'theme_location' => '',
            'menu' => '',
            'container' => 'div',
            'container_class' => 'menu-container',
            'container_id' => '',
            'menu_class' => 'menu',
            'menu_id' => '',
            'echo' => true,
            'fallback_cb' => false,
            'before' => '',
            'after' => '',
            'link_before' => '',
            'link_after' => '',
            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'depth' => 0
        ];
        
        $args = array_merge($defaults, $args);
        
        // For now, return a default menu
        $menu = '<ul class="' . esc_attr($args['menu_class']) . '">';
        $menu .= '<li><a href="' . $this->getHomeUrl() . '">Home</a></li>';
        $menu .= '<li><a href="' . $this->getHomeUrl('about') . '">About</a></li>';
        $menu .= '<li><a href="' . $this->getHomeUrl('blog') . '">Blog</a></li>';
        $menu .= '<li><a href="' . $this->getHomeUrl('contact') . '">Contact</a></li>';
        $menu .= '</ul>';
        
        if ($args['container']) {
            $container_class = $args['container_class'] ? ' class="' . esc_attr($args['container_class']) . '"' : '';
            $container_id = $args['container_id'] ? ' id="' . esc_attr($args['container_id']) . '"' : '';
            $menu = '<' . $args['container'] . $container_id . $container_class . '>' . $menu . '</' . $args['container'] . '>';
        }
        
        if ($args['echo']) {
            echo $menu;
        }
        
        return $menu;
    }
    
    /**
     * Check if nav menu exists
     */
    public function hasNavMenu($location)
    {
        // Placeholder - menu system not implemented yet
        return false;
    }
    
    /**
     * Template part loading
     */
    public function getTemplatePart($slug, $name = null, $args = [])
    {
        $templates = [];
        
        if ($name !== null) {
            $templates[] = "{$slug}-{$name}.php";
        }
        $templates[] = "{$slug}.php";
        
        $theme_path = dirname(dirname(__DIR__)) . '/iso-content/themes/' . ($this->currentTheme['slug'] ?? 'default');
        
        foreach ($templates as $template) {
            $file = $theme_path . '/' . $template;
            if (file_exists($file)) {
                // Make args available to template
                if (!empty($args)) {
                    extract($args);
                }
                include $file;
                return;
            }
        }
    }
}

/**
 * Helper function to escape attributes
 */
function esc_attr($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to escape HTML
 */
function esc_html($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to escape URLs
 */
function esc_url($url)
{
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}
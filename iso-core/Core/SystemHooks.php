<?php
/**
 * System Hooks Registration
 * 
 * This class registers all system hooks that themes and plugins can use.
 * Hooks are the extension points - the system defines them, extensions use them.
 * 
 * @package Isotone\Core
 * @since 1.0.0
 */

namespace Isotone\Core;

class SystemHooks
{
    /**
     * All system hooks with their descriptions
     */
    private static $systemHooks = [
        // Initialization Hooks
        'init' => [
            'type' => 'action',
            'description' => 'Fires after Isotone has finished loading but before any headers are sent',
            'since' => '1.0.0'
        ],
        'iso_loaded' => [
            'type' => 'action',
            'description' => 'Fires when Isotone core has been fully loaded',
            'since' => '1.0.0'
        ],
        'after_setup_theme' => [
            'type' => 'action',
            'description' => 'Fires after the theme is loaded',
            'since' => '1.0.0'
        ],
        
        // Head and Footer Hooks
        'iso_head' => [
            'type' => 'action',
            'description' => 'Fires in the <head> section of the site',
            'since' => '1.0.0'
        ],
        'iso_footer' => [
            'type' => 'action',
            'description' => 'Fires before the closing </body> tag',
            'since' => '1.0.0'
        ],
        'iso_body_open' => [
            'type' => 'action',
            'description' => 'Fires immediately after the opening <body> tag',
            'since' => '1.0.0'
        ],
        
        // Script and Style Hooks
        'iso_enqueue_scripts' => [
            'type' => 'action',
            'description' => 'Fires when scripts and styles should be enqueued',
            'since' => '1.0.0'
        ],
        
        // Content Hooks
        'iso_before_content' => [
            'type' => 'action',
            'description' => 'Fires before the main content area',
            'since' => '1.0.0'
        ],
        'iso_after_content' => [
            'type' => 'action',
            'description' => 'Fires after the main content area',
            'since' => '1.0.0'
        ],
        'iso_before_post' => [
            'type' => 'action',
            'description' => 'Fires before a post is displayed',
            'since' => '1.0.0'
        ],
        'iso_after_post' => [
            'type' => 'action',
            'description' => 'Fires after a post is displayed',
            'since' => '1.0.0'
        ],
        
        // Admin Hooks
        'admin_init' => [
            'type' => 'action',
            'description' => 'Fires as an admin screen or script is being initialized',
            'since' => '1.0.0'
        ],
        'admin_menu' => [
            'type' => 'action',
            'description' => 'Fires before the administration menu loads',
            'since' => '1.0.0'
        ],
        'admin_head' => [
            'type' => 'action',
            'description' => 'Fires in the admin <head> section',
            'since' => '1.0.0'
        ],
        'admin_footer' => [
            'type' => 'action',
            'description' => 'Fires in the admin footer',
            'since' => '1.0.0'
        ],
        'admin_dashboard_top' => [
            'type' => 'action',
            'description' => 'Fires at the top of the admin dashboard',
            'since' => '1.0.0'
        ],
        
        // User and Authentication Hooks
        'iso_login' => [
            'type' => 'action',
            'description' => 'Fires when a user logs in',
            'since' => '1.0.0'
        ],
        'iso_logout' => [
            'type' => 'action',
            'description' => 'Fires when a user logs out',
            'since' => '1.0.0'
        ],
        'iso_register_user' => [
            'type' => 'action',
            'description' => 'Fires when a new user registers',
            'since' => '1.0.0'
        ],
        
        // Database Hooks
        'iso_before_save' => [
            'type' => 'action',
            'description' => 'Fires before data is saved to database',
            'since' => '1.0.0'
        ],
        'iso_after_save' => [
            'type' => 'action',
            'description' => 'Fires after data is saved to database',
            'since' => '1.0.0'
        ],
        'iso_before_delete' => [
            'type' => 'action',
            'description' => 'Fires before data is deleted from database',
            'since' => '1.0.0'
        ],
        'iso_after_delete' => [
            'type' => 'action',
            'description' => 'Fires after data is deleted from database',
            'since' => '1.0.0'
        ],
        
        // Plugin Hooks
        'iso_plugin_activation' => [
            'type' => 'action',
            'description' => 'Fires when a plugin is activated',
            'since' => '1.0.0'
        ],
        'iso_plugin_deactivation' => [
            'type' => 'action',
            'description' => 'Fires when a plugin is deactivated',
            'since' => '1.0.0'
        ],
        'iso_plugin_uninstall' => [
            'type' => 'action',
            'description' => 'Fires when a plugin is uninstalled',
            'since' => '1.0.0'
        ],
        
        // Theme Hooks
        'iso_theme_activation' => [
            'type' => 'action',
            'description' => 'Fires when a theme is activated',
            'since' => '1.0.0'
        ],
        'iso_theme_deactivation' => [
            'type' => 'action',
            'description' => 'Fires when a theme is deactivated',
            'since' => '1.0.0'
        ],
        
        // Widget Hooks
        'widgets_init' => [
            'type' => 'action',
            'description' => 'Fires after all default widgets have been registered',
            'since' => '1.0.0'
        ],
        'dynamic_sidebar_before' => [
            'type' => 'action',
            'description' => 'Fires before widgets are rendered in a sidebar',
            'since' => '1.0.0'
        ],
        'dynamic_sidebar_after' => [
            'type' => 'action',
            'description' => 'Fires after widgets are rendered in a sidebar',
            'since' => '1.0.0'
        ],
        
        // AJAX Hooks (dynamic)
        'iso_ajax_{action}' => [
            'type' => 'action',
            'description' => 'Fires for authenticated AJAX requests (replace {action} with your action name)',
            'since' => '1.0.0',
            'dynamic' => true
        ],
        'iso_ajax_nopriv_{action}' => [
            'type' => 'action',
            'description' => 'Fires for non-authenticated AJAX requests (replace {action} with your action name)',
            'since' => '1.0.0',
            'dynamic' => true
        ],
        
        // REST API Hooks
        'rest_api_init' => [
            'type' => 'action',
            'description' => 'Fires when preparing to serve a REST API request',
            'since' => '1.0.0'
        ],
        'iso_rest_before_request' => [
            'type' => 'action',
            'description' => 'Fires before processing a REST request',
            'since' => '1.0.0'
        ],
        'iso_rest_after_request' => [
            'type' => 'action',
            'description' => 'Fires after processing a REST request',
            'since' => '1.0.0'
        ],
        
        // Routing Hooks
        'template_redirect' => [
            'type' => 'action',
            'description' => 'Fires before determining which template to load',
            'since' => '1.0.0'
        ],
        'iso_before_route' => [
            'type' => 'action',
            'description' => 'Fires before route processing',
            'since' => '1.0.0'
        ],
        'iso_after_route' => [
            'type' => 'action',
            'description' => 'Fires after route processing',
            'since' => '1.0.0'
        ],
        
        // Shutdown Hook
        'shutdown' => [
            'type' => 'action',
            'description' => 'Fires just before PHP shuts down execution',
            'since' => '1.0.0'
        ],
        
        // ============================================
        // FILTERS
        // ============================================
        
        // Content Filters
        'the_content' => [
            'type' => 'filter',
            'description' => 'Filters the post content',
            'since' => '1.0.0'
        ],
        'the_title' => [
            'type' => 'filter',
            'description' => 'Filters the post title',
            'since' => '1.0.0'
        ],
        'the_excerpt' => [
            'type' => 'filter',
            'description' => 'Filters the post excerpt',
            'since' => '1.0.0'
        ],
        
        // URL Filters
        'iso_site_url' => [
            'type' => 'filter',
            'description' => 'Filters the site URL',
            'since' => '1.0.0'
        ],
        'iso_home_url' => [
            'type' => 'filter',
            'description' => 'Filters the home URL',
            'since' => '1.0.0'
        ],
        'iso_admin_url' => [
            'type' => 'filter',
            'description' => 'Filters the admin URL',
            'since' => '1.0.0'
        ],
        
        // Script/Style Filters
        'iso_enqueue_style' => [
            'type' => 'filter',
            'description' => 'Filters style enqueue data before adding',
            'since' => '1.0.0'
        ],
        'iso_enqueue_script' => [
            'type' => 'filter',
            'description' => 'Filters script enqueue data before adding',
            'since' => '1.0.0'
        ],
        
        // Query Filters
        'iso_query_vars' => [
            'type' => 'filter',
            'description' => 'Filters the query variables',
            'since' => '1.0.0'
        ],
        'iso_request' => [
            'type' => 'filter',
            'description' => 'Filters the request variables',
            'since' => '1.0.0'
        ],
        
        // Template Filters
        'iso_template_include' => [
            'type' => 'filter',
            'description' => 'Filters the path of the template to include',
            'since' => '1.0.0'
        ],
        'iso_theme_directory' => [
            'type' => 'filter',
            'description' => 'Filters the active theme directory',
            'since' => '1.0.0'
        ],
        
        // User Filters
        'iso_user_capabilities' => [
            'type' => 'filter',
            'description' => 'Filters user capabilities',
            'since' => '1.0.0'
        ],
        'iso_authentication' => [
            'type' => 'filter',
            'description' => 'Filters authentication result',
            'since' => '1.0.0'
        ],
        
        // Admin Filters
        'iso_admin_menu_items' => [
            'type' => 'filter',
            'description' => 'Filters admin menu items',
            'since' => '1.0.0'
        ],
        'iso_admin_bar_items' => [
            'type' => 'filter',
            'description' => 'Filters admin bar items',
            'since' => '1.0.0'
        ],
        
        // Settings Filters
        'iso_option_{option_name}' => [
            'type' => 'filter',
            'description' => 'Filters specific option values (replace {option_name} with option)',
            'since' => '1.0.0',
            'dynamic' => true
        ],
        'iso_default_settings' => [
            'type' => 'filter',
            'description' => 'Filters default settings',
            'since' => '1.0.0'
        ]
    ];
    
    /**
     * Initialize and register all system hooks
     */
    public static function registerSystemHooks()
    {
        // System hooks don't need callbacks - they're just defined as extension points
        // The system will call do_action() or apply_filters() at appropriate times
        // Themes and plugins will add_action() or add_filter() to hook into them
        
        // This method is primarily for documentation and validation
        // We can use it to ensure hooks are properly defined before being fired
    }
    
    /**
     * Get all registered system hooks
     * 
     * @return array
     */
    public static function getSystemHooks()
    {
        return self::$systemHooks;
    }
    
    /**
     * Check if a hook is a system hook
     * 
     * @param string $hook Hook name
     * @return bool
     */
    public static function isSystemHook($hook)
    {
        // Check static hooks
        if (isset(self::$systemHooks[$hook])) {
            return true;
        }
        
        // Check dynamic hooks
        foreach (self::$systemHooks as $pattern => $info) {
            if (!empty($info['dynamic'])) {
                // Convert pattern to regex
                $regex = str_replace('{action}', '.*', $pattern);
                $regex = str_replace('{option_name}', '.*', $regex);
                if (preg_match('/^' . $regex . '$/', $hook)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get hook information
     * 
     * @param string $hook Hook name
     * @return array|null
     */
    public static function getHookInfo($hook)
    {
        if (isset(self::$systemHooks[$hook])) {
            return self::$systemHooks[$hook];
        }
        
        // Check dynamic hooks
        foreach (self::$systemHooks as $pattern => $info) {
            if (!empty($info['dynamic'])) {
                $regex = str_replace(['{action}', '{option_name}'], '.*', $pattern);
                if (preg_match('/^' . $regex . '$/', $hook)) {
                    return $info;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Fire a system hook (with validation)
     * 
     * @param string $hook Hook name
     * @param mixed ...$args Arguments to pass
     * @return mixed
     */
    public static function fireHook($hook, ...$args)
    {
        // Log/validate that this is a registered system hook
        if (!self::isSystemHook($hook)) {
            // In development, we might want to warn about unregistered hooks
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Warning: Firing unregistered hook: $hook");
            }
        }
        
        $info = self::getHookInfo($hook);
        
        if ($info && $info['type'] === 'filter') {
            return apply_filters($hook, ...$args);
        } else {
            do_action($hook, ...$args);
        }
    }
}
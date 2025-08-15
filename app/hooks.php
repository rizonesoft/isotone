<?php
/**
 * Global Hook Functions
 * 
 * WordPress-compatible global functions for hooks and filters
 * This file provides the familiar WordPress API for theme and plugin developers
 * 
 * @package Isotone
 * @since 1.0.0
 */

use Isotone\Core\Hook;

// ==================================================================
// Action Functions
// ==================================================================

if (!function_exists('add_action')) {
    /**
     * Hooks a function on to a specific action
     * 
     * @param string $tag The name of the action to which the $callback is hooked
     * @param callable $callback The callback to be run when the action is called
     * @param int $priority Order of execution (lower = earlier)
     * @param int $accepted_args Number of arguments the callback accepts
     * @return true
     */
    function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
        return Hook::addAction($tag, $callback, $priority, $accepted_args);
    }
}

if (!function_exists('do_action')) {
    /**
     * Execute functions hooked on a specific action
     * 
     * @param string $tag The name of the action to execute
     * @param mixed ...$args Optional arguments to pass to the hooked functions
     * @return void
     */
    function do_action($tag, ...$args) {
        return Hook::doAction($tag, ...$args);
    }
}

if (!function_exists('do_action_ref_array')) {
    /**
     * Execute functions hooked on a specific action with array of arguments
     * 
     * @param string $tag The name of the action to execute
     * @param array $args Arguments to pass to the hooked functions
     * @return void
     */
    function do_action_ref_array($tag, $args) {
        return Hook::doAction($tag, ...$args);
    }
}

if (!function_exists('remove_action')) {
    /**
     * Removes a function from a specified action hook
     * 
     * @param string $tag The action hook name
     * @param callable $callback The callback to remove
     * @param int $priority The priority of the callback
     * @return bool True if removed, false otherwise
     */
    function remove_action($tag, $callback, $priority = 10) {
        return Hook::removeAction($tag, $callback, $priority);
    }
}

if (!function_exists('remove_all_actions')) {
    /**
     * Remove all of the hooks from an action
     * 
     * @param string $tag The action hook name
     * @param int|false $priority Optional priority to remove
     * @return true
     */
    function remove_all_actions($tag, $priority = false) {
        return Hook::removeAllActions($tag, $priority);
    }
}

if (!function_exists('has_action')) {
    /**
     * Check if any action has been registered for a hook
     * 
     * @param string $tag The action hook name
     * @param callable|false $callback Optional specific callback to check for
     * @return bool|int True/priority if exists, false otherwise
     */
    function has_action($tag, $callback = false) {
        return Hook::hasAction($tag, $callback);
    }
}

if (!function_exists('did_action')) {
    /**
     * Retrieve the number of times an action has been fired
     * 
     * @param string $tag The action hook name
     * @return int Number of times the action has been executed
     */
    function did_action($tag) {
        return Hook::didAction($tag);
    }
}

if (!function_exists('doing_action')) {
    /**
     * Returns whether or not an action is currently being executed
     * 
     * @param string|null $action Optional specific action to check
     * @return bool
     */
    function doing_action($action = null) {
        return Hook::doingAction($action);
    }
}

if (!function_exists('current_action')) {
    /**
     * Retrieve the name of the current action
     * 
     * @return string|false Current action or false if none
     */
    function current_action() {
        return Hook::currentAction();
    }
}

// ==================================================================
// Filter Functions
// ==================================================================

if (!function_exists('add_filter')) {
    /**
     * Hook a function to a specific filter action
     * 
     * @param string $tag The name of the filter to hook the $callback to
     * @param callable $callback The callback to be run when the filter is applied
     * @param int $priority Order of execution (lower = earlier)
     * @param int $accepted_args Number of arguments the callback accepts
     * @return true
     */
    function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
        return Hook::addFilter($tag, $callback, $priority, $accepted_args);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Call the functions added to a filter hook
     * 
     * @param string $tag The name of the filter hook
     * @param mixed $value The value to filter
     * @param mixed ...$args Additional parameters to pass to the callback functions
     * @return mixed The filtered value after all hooked functions are applied
     */
    function apply_filters($tag, $value, ...$args) {
        return Hook::applyFilters($tag, $value, ...$args);
    }
}

if (!function_exists('apply_filters_ref_array')) {
    /**
     * Execute functions hooked on a specific filter with array of arguments
     * 
     * @param string $tag The name of the filter hook
     * @param array $args Arguments array where first element is the value to filter
     * @return mixed The filtered value
     */
    function apply_filters_ref_array($tag, $args) {
        $value = array_shift($args);
        return Hook::applyFilters($tag, $value, ...$args);
    }
}

if (!function_exists('remove_filter')) {
    /**
     * Removes a function from a specified filter hook
     * 
     * @param string $tag The filter hook name
     * @param callable $callback The callback to remove
     * @param int $priority The priority of the callback
     * @return bool True if removed, false otherwise
     */
    function remove_filter($tag, $callback, $priority = 10) {
        return Hook::removeFilter($tag, $callback, $priority);
    }
}

if (!function_exists('remove_all_filters')) {
    /**
     * Remove all of the hooks from a filter
     * 
     * @param string $tag The filter hook name
     * @param int|false $priority Optional priority to remove
     * @return true
     */
    function remove_all_filters($tag, $priority = false) {
        return Hook::removeAllFilters($tag, $priority);
    }
}

if (!function_exists('has_filter')) {
    /**
     * Check if any filter has been registered for a hook
     * 
     * @param string $tag The filter hook name
     * @param callable|false $callback Optional specific callback to check for
     * @return bool|int True/priority if exists, false otherwise
     */
    function has_filter($tag, $callback = false) {
        return Hook::hasFilter($tag, $callback);
    }
}

if (!function_exists('current_filter')) {
    /**
     * Retrieve the name of the current filter
     * 
     * @return string|false Current filter or false if none
     */
    function current_filter() {
        return Hook::currentFilter();
    }
}

if (!function_exists('doing_filter')) {
    /**
     * Returns whether or not a filter is currently being executed
     * 
     * @param string|null $filter Optional specific filter to check
     * @return bool
     */
    function doing_filter($filter = null) {
        return Hook::doingFilter($filter);
    }
}

if (!function_exists('did_filter')) {
    /**
     * Retrieve the number of times a filter has been applied
     * 
     * @param string $tag The filter hook name
     * @return int Number of times the filter has been applied
     */
    function did_filter($tag) {
        return Hook::didFilter($tag);
    }
}

// ==================================================================
// Deprecated Functions (for backward compatibility)
// ==================================================================

if (!function_exists('apply_filters_deprecated')) {
    /**
     * Fires functions attached to a deprecated filter hook
     * 
     * @param string $tag The name of the filter hook
     * @param array $args Arguments to pass to the filter
     * @param string $version Version when deprecated
     * @param string $replacement Replacement filter to use
     * @param string $message Additional message
     * @return mixed
     */
    function apply_filters_deprecated($tag, $args, $version, $replacement = '', $message = '') {
        // Log deprecation notice
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $notice = sprintf('Filter "%s" is deprecated since version %s', $tag, $version);
            if ($replacement) {
                $notice .= sprintf('. Use "%s" instead', $replacement);
            }
            if ($message) {
                $notice .= '. ' . $message;
            }
            error_log($notice);
        }
        
        $value = array_shift($args);
        return Hook::applyFilters($tag, $value, ...$args);
    }
}

if (!function_exists('do_action_deprecated')) {
    /**
     * Fires functions attached to a deprecated action hook
     * 
     * @param string $tag The name of the action hook
     * @param array $args Arguments to pass to the action
     * @param string $version Version when deprecated
     * @param string $replacement Replacement action to use
     * @param string $message Additional message
     * @return void
     */
    function do_action_deprecated($tag, $args, $version, $replacement = '', $message = '') {
        // Log deprecation notice
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $notice = sprintf('Action "%s" is deprecated since version %s', $tag, $version);
            if ($replacement) {
                $notice .= sprintf('. Use "%s" instead', $replacement);
            }
            if ($message) {
                $notice .= '. ' . $message;
            }
            error_log($notice);
        }
        
        Hook::doAction($tag, ...$args);
    }
}

// ==================================================================
// Utility Functions
// ==================================================================

if (!function_exists('all_hooks')) {
    /**
     * Get all registered hooks (for debugging)
     * 
     * @return array
     */
    function all_hooks() {
        return Hook::getAllHooks();
    }
}

if (!function_exists('hook_stats')) {
    /**
     * Get hook statistics (for debugging)
     * 
     * @return array
     */
    function hook_stats() {
        return Hook::getHookStats();
    }
}

// ==================================================================
// Isotone-Specific Helper Functions
// ==================================================================

if (!function_exists('iso_head')) {
    /**
     * Fire the iso_head action
     * 
     * This is used to add elements to <head>
     * 
     * @return void
     */
    function iso_head() {
        do_action('iso_head');
    }
}

if (!function_exists('iso_footer')) {
    /**
     * Fire the iso_footer action
     * 
     * This is used to add elements before </body>
     * 
     * @return void
     */
    function iso_footer() {
        do_action('iso_footer');
    }
}

if (!function_exists('iso_body_open')) {
    /**
     * Fire the iso_body_open action
     * 
     * This is used to add elements after <body>
     * 
     * @return void
     */
    function iso_body_open() {
        do_action('iso_body_open');
    }
}

if (!function_exists('iso_enqueue_scripts')) {
    /**
     * Fire the iso_enqueue_scripts action
     * 
     * This is the proper hook to use when enqueuing scripts and styles
     * 
     * @return void
     */
    function iso_enqueue_scripts() {
        do_action('iso_enqueue_scripts');
    }
}

if (!function_exists('iso_enqueue_style')) {
    /**
     * Enqueue a CSS stylesheet
     * 
     * @param string $handle Name of the stylesheet
     * @param string $src Full URL of the stylesheet
     * @param array $deps Array of handles this stylesheet depends on
     * @param string|bool|null $ver Version number
     * @param string $media The media for which this stylesheet has been defined
     * @return void
     */
    function iso_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
        // This will be implemented with the asset management system
        // For now, just fire a filter to allow customization
        $data = apply_filters('iso_enqueue_style', [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'media' => $media
        ]);
        
        // Store for later output
        global $iso_styles;
        if (!isset($iso_styles)) {
            $iso_styles = [];
        }
        $iso_styles[$handle] = $data;
    }
}

if (!function_exists('iso_enqueue_script')) {
    /**
     * Enqueue a JavaScript file
     * 
     * @param string $handle Name of the script
     * @param string $src Full URL of the script
     * @param array $deps Array of handles this script depends on
     * @param string|bool|null $ver Version number
     * @param bool $in_footer Whether to enqueue in footer
     * @return void
     */
    function iso_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        // This will be implemented with the asset management system
        // For now, just fire a filter to allow customization
        $data = apply_filters('iso_enqueue_script', [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer
        ]);
        
        // Store for later output
        global $iso_scripts;
        if (!isset($iso_scripts)) {
            $iso_scripts = [];
        }
        $iso_scripts[$handle] = $data;
    }
}

if (!function_exists('iso_localize_script')) {
    /**
     * Localize a script
     * 
     * @param string $handle Script handle
     * @param string $object_name Name for the JavaScript object
     * @param array $l10n Data to localize
     * @return bool
     */
    function iso_localize_script($handle, $object_name, $l10n) {
        global $iso_localized_scripts;
        if (!isset($iso_localized_scripts)) {
            $iso_localized_scripts = [];
        }
        
        $iso_localized_scripts[$handle] = [
            'object_name' => $object_name,
            'data' => $l10n
        ];
        
        return true;
    }
}

if (!function_exists('iso_create_nonce')) {
    /**
     * Create a nonce
     * 
     * @param string $action Action name
     * @return string Nonce token
     */
    function iso_create_nonce($action = -1) {
        // Simple nonce implementation - will be enhanced later
        $salt = defined('NONCE_SALT') ? NONCE_SALT : 'isotone-nonce-salt';
        return substr(hash('sha256', $action . $salt . time()), 0, 10);
    }
}

if (!function_exists('iso_verify_nonce')) {
    /**
     * Verify a nonce
     * 
     * @param string $nonce Nonce to verify
     * @param string $action Action name
     * @return bool|int False if invalid, 1 if valid and recent, 2 if valid but old
     */
    function iso_verify_nonce($nonce, $action = -1) {
        // Simple verification - will be enhanced later
        // For now, just check if nonce exists
        return !empty($nonce) ? 1 : false;
    }
}

if (!function_exists('iso_nonce_field')) {
    /**
     * Retrieve or display nonce hidden field for forms
     * 
     * @param string $action Action name
     * @param string $name Nonce name
     * @param bool $referer Whether to set the referer field
     * @param bool $echo Whether to display or return
     * @return string Nonce field HTML markup
     */
    function iso_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        $nonce = iso_create_nonce($action);
        $field = '<input type="hidden" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($nonce) . '" />';
        
        if ($referer) {
            $field .= '<input type="hidden" name="_wp_http_referer" value="' . esc_attr($_SERVER['REQUEST_URI'] ?? '') . '" />';
        }
        
        if ($echo) {
            echo $field;
        }
        
        return $field;
    }
}

if (!function_exists('iso_die')) {
    /**
     * Kill execution and display message
     * 
     * @param string $message Message to display
     * @param string $title Title
     * @param array $args Additional arguments
     * @return void
     */
    function iso_die($message = '', $title = '', $args = []) {
        if (!empty($title)) {
            echo "<h1>{$title}</h1>";
        }
        echo $message;
        exit;
    }
}

if (!function_exists('iso_send_json_success')) {
    /**
     * Send a JSON response back to an Ajax request, indicating success
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $status_code HTTP status code
     * @return void
     */
    function iso_send_json_success($data = null, $status_code = 200) {
        $response = ['success' => true];
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        header('Content-Type: application/json');
        http_response_code($status_code);
        echo json_encode($response);
        exit;
    }
}

if (!function_exists('iso_send_json_error')) {
    /**
     * Send a JSON response back to an Ajax request, indicating failure
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $status_code HTTP status code
     * @return void
     */
    function iso_send_json_error($data = null, $status_code = 400) {
        $response = ['success' => false];
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        header('Content-Type: application/json');
        http_response_code($status_code);
        echo json_encode($response);
        exit;
    }
}

if (!function_exists('iso_script_is')) {
    /**
     * Check whether a script has been registered, enqueued, etc.
     * 
     * @param string $handle Script handle
     * @param string $list Status to check
     * @return bool
     */
    function iso_script_is($handle, $list = 'enqueued') {
        global $iso_scripts;
        
        if (!isset($iso_scripts)) {
            return false;
        }
        
        switch ($list) {
            case 'enqueued':
            case 'registered':
                return isset($iso_scripts[$handle]);
            default:
                return false;
        }
    }
}
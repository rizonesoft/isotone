<?php
/**
 * Hook System for Isotone
 * 
 * Implements WordPress-compatible hooks and filters system
 * 
 * @package Isotone\Core
 * @since 1.0.0
 */

namespace Isotone\Core;

class Hook
{
    /**
     * Registered actions
     * @var array
     */
    private static $actions = [];
    
    /**
     * Registered filters
     * @var array
     */
    private static $filters = [];
    
    /**
     * Current action being executed
     * @var array
     */
    private static $current_action = [];
    
    /**
     * Current filter being executed
     * @var array
     */
    private static $current_filter = [];
    
    /**
     * Action execution history
     * @var array
     */
    private static $action_history = [];
    
    /**
     * Filter execution history
     * @var array
     */
    private static $filter_history = [];
    
    /**
     * Add an action hook
     * 
     * @param string $tag The name of the action to add the callback to
     * @param callable $callback The callback to be run when the action is called
     * @param int $priority Order of execution (lower = earlier)
     * @param int $accepted_args Number of arguments the callback accepts
     * @return true
     */
    public static function addAction($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        if (!isset(self::$actions[$tag])) {
            self::$actions[$tag] = [];
        }
        
        if (!isset(self::$actions[$tag][$priority])) {
            self::$actions[$tag][$priority] = [];
        }
        
        self::$actions[$tag][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args
        ];
        
        // Sort by priority
        ksort(self::$actions[$tag]);
        
        return true;
    }
    
    /**
     * Add a filter hook
     * 
     * @param string $tag The name of the filter to add the callback to
     * @param callable $callback The callback to be run when the filter is applied
     * @param int $priority Order of execution (lower = earlier)
     * @param int $accepted_args Number of arguments the callback accepts
     * @return true
     */
    public static function addFilter($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        if (!isset(self::$filters[$tag])) {
            self::$filters[$tag] = [];
        }
        
        if (!isset(self::$filters[$tag][$priority])) {
            self::$filters[$tag][$priority] = [];
        }
        
        self::$filters[$tag][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args
        ];
        
        // Sort by priority
        ksort(self::$filters[$tag]);
        
        return true;
    }
    
    /**
     * Execute an action
     * 
     * @param string $tag The name of the action to execute
     * @param mixed ...$args Arguments to pass to the callbacks
     * @return void
     */
    public static function doAction($tag, ...$args)
    {
        // Track current action
        self::$current_action[] = $tag;
        
        // Track action history
        if (!isset(self::$action_history[$tag])) {
            self::$action_history[$tag] = 0;
        }
        self::$action_history[$tag]++;
        
        // Execute callbacks if they exist
        if (isset(self::$actions[$tag])) {
            foreach (self::$actions[$tag] as $priority => $callbacks) {
                foreach ($callbacks as $callback_data) {
                    $callback = $callback_data['callback'];
                    $accepted_args = $callback_data['accepted_args'];
                    
                    // Limit arguments to what the callback accepts
                    $callback_args = array_slice($args, 0, $accepted_args);
                    
                    // Call the callback
                    if (is_callable($callback)) {
                        call_user_func_array($callback, $callback_args);
                    }
                }
            }
        }
        
        // Remove from current action stack
        array_pop(self::$current_action);
    }
    
    /**
     * Apply filters to a value
     * 
     * @param string $tag The name of the filter to apply
     * @param mixed $value The value to filter
     * @param mixed ...$args Additional arguments to pass to callbacks
     * @return mixed The filtered value
     */
    public static function applyFilters($tag, $value, ...$args)
    {
        // Track current filter
        self::$current_filter[] = $tag;
        
        // Track filter history
        if (!isset(self::$filter_history[$tag])) {
            self::$filter_history[$tag] = 0;
        }
        self::$filter_history[$tag]++;
        
        // Apply filters if they exist
        if (isset(self::$filters[$tag])) {
            foreach (self::$filters[$tag] as $priority => $callbacks) {
                foreach ($callbacks as $callback_data) {
                    $callback = $callback_data['callback'];
                    $accepted_args = $callback_data['accepted_args'];
                    
                    // Build arguments array
                    $callback_args = [$value];
                    if ($accepted_args > 1) {
                        $callback_args = array_merge($callback_args, array_slice($args, 0, $accepted_args - 1));
                    }
                    
                    // Apply the filter
                    if (is_callable($callback)) {
                        $value = call_user_func_array($callback, $callback_args);
                    }
                }
            }
        }
        
        // Remove from current filter stack
        array_pop(self::$current_filter);
        
        return $value;
    }
    
    /**
     * Remove an action
     * 
     * @param string $tag The action hook name
     * @param callable $callback The callback to remove
     * @param int $priority The priority of the callback
     * @return bool True if removed, false otherwise
     */
    public static function removeAction($tag, $callback, $priority = 10)
    {
        if (!isset(self::$actions[$tag][$priority])) {
            return false;
        }
        
        foreach (self::$actions[$tag][$priority] as $key => $callback_data) {
            if ($callback_data['callback'] === $callback) {
                unset(self::$actions[$tag][$priority][$key]);
                
                // Clean up empty arrays
                if (empty(self::$actions[$tag][$priority])) {
                    unset(self::$actions[$tag][$priority]);
                }
                if (empty(self::$actions[$tag])) {
                    unset(self::$actions[$tag]);
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Remove a filter
     * 
     * @param string $tag The filter hook name
     * @param callable $callback The callback to remove
     * @param int $priority The priority of the callback
     * @return bool True if removed, false otherwise
     */
    public static function removeFilter($tag, $callback, $priority = 10)
    {
        if (!isset(self::$filters[$tag][$priority])) {
            return false;
        }
        
        foreach (self::$filters[$tag][$priority] as $key => $callback_data) {
            if ($callback_data['callback'] === $callback) {
                unset(self::$filters[$tag][$priority][$key]);
                
                // Clean up empty arrays
                if (empty(self::$filters[$tag][$priority])) {
                    unset(self::$filters[$tag][$priority]);
                }
                if (empty(self::$filters[$tag])) {
                    unset(self::$filters[$tag]);
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if an action has callbacks registered
     * 
     * @param string $tag The action hook name
     * @param callable|false $callback Optional specific callback to check for
     * @return bool|int True/priority if exists, false otherwise
     */
    public static function hasAction($tag, $callback = false)
    {
        if (!isset(self::$actions[$tag])) {
            return false;
        }
        
        if ($callback === false) {
            return true;
        }
        
        foreach (self::$actions[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback_data) {
                if ($callback_data['callback'] === $callback) {
                    return $priority;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if a filter has callbacks registered
     * 
     * @param string $tag The filter hook name
     * @param callable|false $callback Optional specific callback to check for
     * @return bool|int True/priority if exists, false otherwise
     */
    public static function hasFilter($tag, $callback = false)
    {
        if (!isset(self::$filters[$tag])) {
            return false;
        }
        
        if ($callback === false) {
            return true;
        }
        
        foreach (self::$filters[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback_data) {
                if ($callback_data['callback'] === $callback) {
                    return $priority;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get current action being executed
     * 
     * @return string|false Current action or false if none
     */
    public static function currentAction()
    {
        return end(self::$current_action) ?: false;
    }
    
    /**
     * Get current filter being executed
     * 
     * @return string|false Current filter or false if none
     */
    public static function currentFilter()
    {
        return end(self::$current_filter) ?: false;
    }
    
    /**
     * Check if currently executing an action
     * 
     * @param string|null $action Specific action to check
     * @return bool
     */
    public static function doingAction($action = null)
    {
        if ($action === null) {
            return !empty(self::$current_action);
        }
        
        return in_array($action, self::$current_action);
    }
    
    /**
     * Check if currently executing a filter
     * 
     * @param string|null $filter Specific filter to check
     * @return bool
     */
    public static function doingFilter($filter = null)
    {
        if ($filter === null) {
            return !empty(self::$current_filter);
        }
        
        return in_array($filter, self::$current_filter);
    }
    
    /**
     * Get number of times an action has been executed
     * 
     * @param string $tag The action hook name
     * @return int Number of times executed
     */
    public static function didAction($tag)
    {
        return self::$action_history[$tag] ?? 0;
    }
    
    /**
     * Get number of times a filter has been applied
     * 
     * @param string $tag The filter hook name
     * @return int Number of times applied
     */
    public static function didFilter($tag)
    {
        return self::$filter_history[$tag] ?? 0;
    }
    
    /**
     * Remove all callbacks from a hook
     * 
     * @param string $tag The hook name
     * @param int|false $priority Optional priority to remove
     * @return true
     */
    public static function removeAllActions($tag, $priority = false)
    {
        if ($priority !== false) {
            unset(self::$actions[$tag][$priority]);
        } else {
            unset(self::$actions[$tag]);
        }
        
        return true;
    }
    
    /**
     * Remove all callbacks from a filter
     * 
     * @param string $tag The hook name
     * @param int|false $priority Optional priority to remove
     * @return true
     */
    public static function removeAllFilters($tag, $priority = false)
    {
        if ($priority !== false) {
            unset(self::$filters[$tag][$priority]);
        } else {
            unset(self::$filters[$tag]);
        }
        
        return true;
    }
    
    /**
     * Get all registered hooks (for debugging/documentation)
     * 
     * @return array
     */
    public static function getAllHooks()
    {
        return [
            'actions' => self::$actions,
            'filters' => self::$filters
        ];
    }
    
    /**
     * Get hook statistics (for debugging/documentation)
     * 
     * @return array
     */
    public static function getHookStats()
    {
        return [
            'total_actions' => count(self::$actions),
            'total_filters' => count(self::$filters),
            'action_history' => self::$action_history,
            'filter_history' => self::$filter_history,
            'current_action' => self::currentAction(),
            'current_filter' => self::currentFilter()
        ];
    }
}
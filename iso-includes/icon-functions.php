<?php
/**
 * Icon Helper Functions
 * 
 * Global functions for theme and plugin developers to easily use icons
 * 
 * @package Isotone
 * @since 0.3.0
 */

// Include the IconAPI class
require_once dirname(__DIR__) . '/iso-core/Core/IconAPI.php';

/**
 * Display an icon (echoes the output)
 * 
 * @param string $name Icon name
 * @param string $style Icon style (outline, solid, micro)
 * @param array $attributes HTML/SVG attributes
 * @param bool $lazy Whether to use lazy loading (default: true)
 */
function iso_icon($name, $style = 'outline', $attributes = [], $lazy = true)
{
    echo iso_get_icon($name, $style, $attributes, $lazy);
}

/**
 * Get an icon (returns the output)
 * 
 * @param string $name Icon name
 * @param string $style Icon style (outline, solid, micro)
 * @param array $attributes HTML/SVG attributes
 * @param bool $lazy Whether to use lazy loading (default: true)
 * @return string HTML for the icon
 */
function iso_get_icon($name, $style = 'outline', $attributes = [], $lazy = true)
{
    if ($lazy) {
        // Use img tag for lazy loading
        return IconAPI::getIconImg($name, $style, $attributes);
    } else {
        // Use inline SVG for immediate display
        return IconAPI::getIconSvg($name, $style, $attributes);
    }
}

/**
 * Get icon URL for custom implementation
 * 
 * @param string $name Icon name
 * @param string $style Icon style (outline, solid, micro)
 * @param array $params URL parameters (size, class, color)
 * @return string Icon URL
 */
function iso_get_icon_url($name, $style = 'outline', $params = [])
{
    return IconAPI::getIconUrl($name, $style, $params);
}

/**
 * Display an outline icon (most common)
 * 
 * @param string $name Icon name
 * @param array $attributes HTML/SVG attributes
 * @param bool $lazy Whether to use lazy loading
 */
function iso_icon_outline($name, $attributes = [], $lazy = true)
{
    iso_icon($name, 'outline', $attributes, $lazy);
}

/**
 * Display a solid icon
 * 
 * @param string $name Icon name
 * @param array $attributes HTML/SVG attributes
 * @param bool $lazy Whether to use lazy loading
 */
function iso_icon_solid($name, $attributes = [], $lazy = true)
{
    iso_icon($name, 'solid', $attributes, $lazy);
}

/**
 * Display a micro icon (16x16)
 * 
 * @param string $name Icon name
 * @param array $attributes HTML/SVG attributes
 * @param bool $lazy Whether to use lazy loading
 */
function iso_icon_micro($name, $attributes = [], $lazy = true)
{
    iso_icon($name, 'micro', $attributes, $lazy);
}

/**
 * Preload icons for better performance
 * Call this in your theme/plugin init to preload frequently used icons
 * 
 * @param array $icons Array of icons to preload
 * 
 * Example:
 * iso_preload_icons([
 *     ['name' => 'home', 'style' => 'outline'],
 *     ['name' => 'user', 'style' => 'solid'],
 *     ['name' => 'cog', 'style' => 'outline']
 * ]);
 */
function iso_preload_icons($icons)
{
    IconAPI::preloadIcons($icons);
}

/**
 * Register icons for auto-preloading
 * Icons registered here will be automatically preloaded on init
 * 
 * @param array $icons Array of icons to register
 */
function iso_register_icons($icons)
{
    static $registered_icons = [];
    
    foreach ($icons as $icon) {
        $key = ($icon['name'] ?? '') . '_' . ($icon['style'] ?? 'outline');
        $registered_icons[$key] = $icon;
    }
    
    // Hook to preload on init if not already done
    if (!has_action('iso_init', 'iso_auto_preload_icons')) {
        add_action('iso_init', function() use (&$registered_icons) {
            iso_preload_icons(array_values($registered_icons));
        });
    }
}

/**
 * Icon button helper
 * Creates a button with an icon
 * 
 * @param string $icon Icon name
 * @param string $text Button text
 * @param array $button_attrs Button attributes
 * @param array $icon_attrs Icon attributes
 * @param string $icon_position Icon position (left or right)
 * @return string HTML for button with icon
 */
function iso_icon_button($icon, $text = '', $button_attrs = [], $icon_attrs = [], $icon_position = 'left')
{
    // Default button attributes
    $default_button_attrs = [
        'type' => 'button',
        'class' => 'iso-icon-button'
    ];
    $button_attrs = array_merge($default_button_attrs, $button_attrs);
    
    // Default icon attributes
    $default_icon_attrs = [
        'class' => 'iso-icon-button__icon',
        'width' => '20',
        'height' => '20'
    ];
    $icon_attrs = array_merge($default_icon_attrs, $icon_attrs);
    
    // Get icon
    $icon_html = iso_get_icon($icon, 'outline', $icon_attrs, false);
    
    // Build button
    $button = '<button';
    foreach ($button_attrs as $key => $value) {
        $button .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    $button .= '>';
    
    // Add icon and text
    if ($icon_position === 'left') {
        $button .= $icon_html;
        if (!empty($text)) {
            $button .= '<span class="iso-icon-button__text">' . htmlspecialchars($text) . '</span>';
        }
    } else {
        if (!empty($text)) {
            $button .= '<span class="iso-icon-button__text">' . htmlspecialchars($text) . '</span>';
        }
        $button .= $icon_html;
    }
    
    $button .= '</button>';
    
    return $button;
}

/**
 * Icon link helper
 * Creates a link with an icon
 * 
 * @param string $icon Icon name
 * @param string $text Link text
 * @param string $url Link URL
 * @param array $link_attrs Link attributes
 * @param array $icon_attrs Icon attributes
 * @return string HTML for link with icon
 */
function iso_icon_link($icon, $text, $url, $link_attrs = [], $icon_attrs = [])
{
    // Default link attributes
    $default_link_attrs = [
        'href' => $url,
        'class' => 'iso-icon-link'
    ];
    $link_attrs = array_merge($default_link_attrs, $link_attrs);
    
    // Default icon attributes
    $default_icon_attrs = [
        'class' => 'iso-icon-link__icon',
        'width' => '16',
        'height' => '16'
    ];
    $icon_attrs = array_merge($default_icon_attrs, $icon_attrs);
    
    // Get icon
    $icon_html = iso_get_icon($icon, 'outline', $icon_attrs, false);
    
    // Build link
    $link = '<a';
    foreach ($link_attrs as $key => $value) {
        $link .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    $link .= '>';
    $link .= $icon_html;
    $link .= '<span class="iso-icon-link__text">' . htmlspecialchars($text) . '</span>';
    $link .= '</a>';
    
    return $link;
}
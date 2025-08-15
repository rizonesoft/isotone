<?php
/**
 * Neutron Theme - Isotone Compatibility
 * 
 * This file provides compatibility between Neutron theme
 * and Isotone's native theming API.
 * 
 * @package Neutron
 * @version 1.0.0
 */

// Define ABSPATH if not already defined
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}

// Isotone now provides native theme functions in app/theme-functions.php
// Most WordPress-compatible functions are now provided natively by Isotone.

// The iso_body_open function is not yet in the native API, so we provide it here
if (!function_exists('iso_body_open')) {
    function iso_body_open() {
        if (function_exists('do_action')) {
            do_action('iso_body_open');
        }
    }
}

// Ensure WordPress-style body_open hook works too
if (!function_exists('wp_body_open')) {
    function wp_body_open() {
        iso_body_open();
    }
}
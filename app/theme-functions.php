<?php
/**
 * Isotone Theme Template Functions
 * 
 * Global template functions for themes to use.
 * These provide a native Isotone theming API.
 * 
 * @package Isotone
 * @since 1.0.0
 */

use Isotone\Core\ThemeAPI;

/**
 * Get the Theme API instance
 */
function iso_theme() {
    return ThemeAPI::getInstance();
}

/**
 * Site Information Functions
 */
function bloginfo($show = '') {
    echo get_bloginfo($show);
}

function get_bloginfo($show = '') {
    return iso_theme()->getSiteInfo($show);
}

function home_url($path = '') {
    return iso_theme()->getHomeUrl($path);
}

function site_url($path = '') {
    return iso_theme()->getHomeUrl($path);
}

function get_template_directory_uri() {
    return iso_theme()->getTemplateDirectoryUri();
}

function get_stylesheet_uri() {
    return iso_theme()->getStylesheetUri();
}

function get_stylesheet_directory_uri() {
    return iso_theme()->getStylesheetDirectoryUri();
}

/**
 * Theme Customization Functions
 */
function get_theme_mod($name, $default = false) {
    return iso_theme()->getThemeMod($name, $default);
}

function set_theme_mod($name, $value) {
    return iso_theme()->setThemeMod($name, $value);
}

/**
 * Content Loop Functions
 */
function have_posts() {
    return iso_theme()->havePosts();
}

function the_post() {
    iso_theme()->thePost();
}

function the_title($before = '', $after = '', $echo = true) {
    $title = iso_theme()->getTitle();
    $output = $before . $title . $after;
    
    if ($echo) {
        echo $output;
    }
    return $output;
}

function get_the_title() {
    return iso_theme()->getTitle();
}

function the_content($more_link_text = null) {
    echo iso_theme()->getContent();
}

function get_the_content() {
    return iso_theme()->getContent();
}

function the_excerpt() {
    echo iso_theme()->getExcerpt();
}

function get_the_excerpt() {
    return iso_theme()->getExcerpt();
}

function the_permalink() {
    echo get_permalink();
}

function get_permalink($post = null) {
    return iso_theme()->getPermalink($post);
}

function the_date($format = '', $before = '', $after = '', $echo = true) {
    $date = get_the_date($format);
    $output = $before . $date . $after;
    
    if ($echo) {
        echo $output;
    }
    return $output;
}

function get_the_date($format = '') {
    if (empty($format)) {
        $format = get_option('date_format', 'F j, Y');
    }
    return iso_theme()->getDate($format);
}

function the_time($format = '') {
    echo get_the_time($format);
}

function get_the_time($format = '') {
    if (empty($format)) {
        $format = get_option('time_format', 'g:i a');
    }
    return iso_theme()->getDate($format);
}

/**
 * Author Functions
 */
function the_author() {
    echo get_the_author();
}

function get_the_author() {
    return iso_theme()->getAuthor();
}

function get_the_author_meta($field = '', $user_id = false) {
    return iso_theme()->getAuthorMeta($field, $user_id);
}

function the_author_meta($field = '', $user_id = false) {
    echo get_the_author_meta($field, $user_id);
}

function get_avatar($id_or_email, $size = 96, $default = '', $alt = '', $args = null) {
    return iso_theme()->getAvatar($id_or_email, $size, $default, $alt, $args);
}

/**
 * Category and Tag Functions
 */
function has_category($category = '', $post = null) {
    return iso_theme()->hasCategory($category, $post);
}

function get_the_category_list($separator = '') {
    return iso_theme()->getCategories($separator);
}

function the_category($separator = '', $parents = '', $post_id = false) {
    echo iso_theme()->getCategories($separator);
}

function has_tag($tag = '', $post = null) {
    return iso_theme()->hasTag($tag, $post);
}

function get_the_tags() {
    return iso_theme()->getTags();
}

function the_tags($before = null, $sep = ', ', $after = '') {
    $tags = iso_theme()->getTags();
    if ($tags && !empty($tags)) {
        echo $before . implode($sep, $tags) . $after;
    }
}

function get_tag_link($tag) {
    return iso_theme()->getTagLink($tag);
}

/**
 * Post Thumbnail Functions
 */
function has_post_thumbnail($post = null) {
    return iso_theme()->hasPostThumbnail($post);
}

function the_post_thumbnail($size = 'post-thumbnail', $attr = '') {
    echo get_the_post_thumbnail(null, $size, $attr);
}

function get_the_post_thumbnail($post = null, $size = 'post-thumbnail', $attr = '') {
    return iso_theme()->getPostThumbnail($size, $attr);
}

/**
 * Edit Link Functions
 */
function get_edit_post_link($id = 0, $context = 'display') {
    return iso_theme()->getEditPostLink($id, $context);
}

function edit_post_link($text = null, $before = '', $after = '', $id = 0, $class = 'post-edit-link') {
    if (!$url = get_edit_post_link($id)) {
        return;
    }

    if (null === $text) {
        $text = 'Edit';
    }

    $link = '<a class="' . esc_attr($class) . '" href="' . esc_url($url) . '">' . $text . '</a>';
    echo $before . $link . $after;
}

/**
 * Conditional Tags
 */
function is_home() {
    return iso_theme()->isHome();
}

function is_front_page() {
    return iso_theme()->isFrontPage();
}

function is_single($post = '') {
    return iso_theme()->isSingle();
}

function is_page($page = '') {
    return iso_theme()->isPage();
}

function is_archive() {
    return iso_theme()->isArchive();
}

function is_category($category = '') {
    return iso_theme()->isCategory($category);
}

function is_tag($tag = '') {
    return iso_theme()->isTag($tag);
}

function is_author($author = '') {
    return iso_theme()->isAuthor($author);
}

function is_date() {
    return iso_theme()->isDate();
}

function is_year() {
    return iso_theme()->isDate();
}

function is_month() {
    return iso_theme()->isDate();
}

function is_day() {
    return iso_theme()->isDate();
}

function is_search() {
    return iso_theme()->isSearch();
}

function is_404() {
    return iso_theme()->is404();
}

function is_admin() {
    return iso_theme()->isAdmin();
}

/**
 * Template Functions
 */
function body_class($class = '') {
    echo 'class="' . esc_attr(get_body_class($class)) . '"';
}

function get_body_class($class = '') {
    return iso_theme()->getBodyClass($class);
}

function post_class($class = '', $post_id = null) {
    echo 'class="' . esc_attr(get_post_class($class, $post_id)) . '"';
}

function get_post_class($class = '', $post_id = null) {
    return iso_theme()->getPostClass($class, $post_id);
}

/**
 * Navigation Menu Functions
 */
function wp_nav_menu($args = []) {
    return iso_theme()->getNavMenu($args);
}

function has_nav_menu($location) {
    return iso_theme()->hasNavMenu($location);
}

/**
 * Template Part Functions
 */
function get_template_part($slug, $name = null, $args = []) {
    iso_theme()->getTemplatePart($slug, $name, $args);
}

function get_header($name = null) {
    $name = (string) $name;
    if ('' !== $name) {
        $templates = ["header-{$name}.php"];
    }
    $templates[] = 'header.php';
    
    locate_template($templates, true);
}

function get_footer($name = null) {
    $name = (string) $name;
    if ('' !== $name) {
        $templates = ["footer-{$name}.php"];
    }
    $templates[] = 'footer.php';
    
    locate_template($templates, true);
}

function get_sidebar($name = null) {
    $name = (string) $name;
    if ('' !== $name) {
        $templates = ["sidebar-{$name}.php"];
    }
    $templates[] = 'sidebar.php';
    
    locate_template($templates, true);
}

/**
 * Template Loading Functions
 */
function locate_template($template_names, $load = false, $require_once = true) {
    $located = '';
    $theme_path = dirname(__DIR__) . '/iso-content/themes/' . (iso_theme()->currentTheme['slug'] ?? 'default');
    
    foreach ((array) $template_names as $template_name) {
        if (!$template_name) {
            continue;
        }
        
        $path = $theme_path . '/' . $template_name;
        if (file_exists($path)) {
            $located = $path;
            break;
        }
    }
    
    if ($load && '' !== $located) {
        load_template($located, $require_once);
    }
    
    return $located;
}

function load_template($_template_file, $require_once = true) {
    global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
    
    if (is_array($wp_query->query_vars)) {
        extract($wp_query->query_vars, EXTR_SKIP);
    }
    
    if ($require_once) {
        require_once $_template_file;
    } else {
        require $_template_file;
    }
}

/**
 * Options API Functions
 */
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        // This will integrate with Isotone's settings system
        // For now, return common defaults
        $defaults = [
            'blogname' => 'Isotone',
            'blogdescription' => 'Modern CMS',
            'date_format' => 'F j, Y',
            'time_format' => 'g:i a',
            'start_of_week' => 1,
            'timezone_string' => 'UTC',
        ];
        
        return $defaults[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        // Will integrate with Isotone's settings system
        return true;
    }
}

/**
 * Language Functions
 */
function get_locale() {
    return iso_theme()->getLocale();
}

function __($text, $domain = 'default') {
    // Translation system placeholder
    return $text;
}

function _e($text, $domain = 'default') {
    echo __($text, $domain);
}

function esc_html__($text, $domain = 'default') {
    return esc_html(__($text, $domain));
}

function esc_attr__($text, $domain = 'default') {
    return esc_attr(__($text, $domain));
}

/**
 * Escaping Functions (if not already defined)
 */
if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_js')) {
    function esc_js($text) {
        return str_replace(
            ['\\', "'", '"', "\n", "\r"],
            ['\\\\', "\\'", '\\"', '\\n', '\\r'],
            $text
        );
    }
}

/**
 * URL Functions
 */
function admin_url($path = '', $scheme = 'admin') {
    return iso_theme()->getHomeUrl('iso-admin/' . ltrim($path, '/'));
}

function includes_url($path = '', $scheme = null) {
    return iso_theme()->getHomeUrl('iso-includes/' . ltrim($path, '/'));
}

function content_url($path = '') {
    return iso_theme()->getHomeUrl('iso-content/' . ltrim($path, '/'));
}

function plugins_url($path = '', $plugin = '') {
    return iso_theme()->getHomeUrl('iso-content/plugins/' . ltrim($path, '/'));
}

/**
 * Script and Style Functions
 */
function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
    // Will integrate with Isotone's asset system
    return true;
}

function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
    // Will integrate with Isotone's asset system
    return true;
}

function wp_register_script($handle, $src, $deps = [], $ver = false, $in_footer = false) {
    // Will integrate with Isotone's asset system
    return true;
}

function wp_register_style($handle, $src, $deps = [], $ver = false, $media = 'all') {
    // Will integrate with Isotone's asset system
    return true;
}

/**
 * Nonce Functions
 */
function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
    $field = '<input type="hidden" name="' . esc_attr($name) . '" value="' . wp_create_nonce($action) . '" />';
    
    if ($referer) {
        $field .= wp_referer_field(false);
    }
    
    if ($echo) {
        echo $field;
    }
    
    return $field;
}

function wp_create_nonce($action = -1) {
    // Simple nonce implementation
    return substr(md5($action . date('Y-m-d')), 0, 10);
}

function wp_verify_nonce($nonce, $action = -1) {
    return $nonce === wp_create_nonce($action);
}

function wp_referer_field($echo = true) {
    $field = '<input type="hidden" name="_wp_http_referer" value="' . esc_attr($_SERVER['REQUEST_URI'] ?? '') . '" />';
    
    if ($echo) {
        echo $field;
    }
    
    return $field;
}

/**
 * AJAX Functions
 */
function wp_die($message = '', $title = '', $args = []) {
    if (is_string($message)) {
        echo $message;
    }
    exit;
}

function is_user_logged_in() {
    // Will integrate with Isotone's auth system
    return false;
}

function current_user_can($capability) {
    // Will integrate with Isotone's auth system
    return false;
}

/**
 * Sanitization Functions
 */
function sanitize_text_field($str) {
    $str = wp_strip_all_tags($str, true);
    return $str;
}

function sanitize_textarea_field($str) {
    $str = wp_strip_all_tags($str, false);
    return $str;
}

function sanitize_email($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

function sanitize_title($title, $fallback_title = '', $context = 'save') {
    $title = wp_strip_all_tags($title);
    $title = strtolower($title);
    $title = preg_replace('/[^a-z0-9-]/', '-', $title);
    $title = preg_replace('/-+/', '-', $title);
    $title = trim($title, '-');
    return $title;
}

function sanitize_key($key) {
    $key = strtolower($key);
    $key = preg_replace('/[^a-z0-9_\-]/', '', $key);
    return $key;
}

function wp_strip_all_tags($string, $remove_breaks = false) {
    $string = strip_tags($string);
    
    if ($remove_breaks) {
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }
    
    return trim($string);
}

/**
 * Kses Functions
 */
function wp_kses($string, $allowed_html, $allowed_protocols = []) {
    // Basic implementation - will be enhanced
    return strip_tags($string);
}

function wp_kses_post($data) {
    // Allow common HTML tags in posts
    return $data;
}

/**
 * Utility Functions
 */
function wp_parse_args($args, $defaults = '') {
    if (is_object($args)) {
        $args = get_object_vars($args);
    } elseif (!is_array($args)) {
        wp_parse_str($args, $args);
    }
    
    if (is_array($defaults)) {
        return array_merge($defaults, $args);
    }
    
    return $args;
}

function wp_parse_str($string, &$array) {
    parse_str($string, $array);
    
    if (get_magic_quotes_gpc()) {
        $array = stripslashes_deep($array);
    }
}

function stripslashes_deep($value) {
    if (is_array($value)) {
        $value = array_map('stripslashes_deep', $value);
    } elseif (is_object($value)) {
        $vars = get_object_vars($value);
        foreach ($vars as $key => $data) {
            $value->{$key} = stripslashes_deep($data);
        }
    } elseif (is_string($value)) {
        $value = stripslashes($value);
    }
    
    return $value;
}

function absint($maybeint) {
    return abs(intval($maybeint));
}

/**
 * Plugin API Functions (Placeholders)
 */
function register_activation_hook($file, $callback) {
    // Will integrate with Isotone's plugin system
    return true;
}

function register_deactivation_hook($file, $callback) {
    // Will integrate with Isotone's plugin system
    return true;
}

function plugin_dir_path($file) {
    return trailingslashit(dirname($file));
}

function plugin_dir_url($file) {
    $plugin_dir = str_replace(ABSPATH, '', dirname($file));
    return iso_theme()->getHomeUrl($plugin_dir);
}

function trailingslashit($string) {
    return rtrim($string, '/\\') . '/';
}

function untrailingslashit($string) {
    return rtrim($string, '/\\');
}

/**
 * Database Functions (Placeholders)
 */
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'iso_';
$wpdb->posts = 'iso_posts';
$wpdb->postmeta = 'iso_postmeta';
$wpdb->terms = 'iso_terms';
$wpdb->term_taxonomy = 'iso_term_taxonomy';
$wpdb->term_relationships = 'iso_term_relationships';
$wpdb->users = 'iso_users';
$wpdb->usermeta = 'iso_usermeta';
$wpdb->options = 'iso_options';
$wpdb->comments = 'iso_comments';
$wpdb->commentmeta = 'iso_commentmeta';

/**
 * Global Query Variables
 */
global $wp_query, $wp_the_query, $post, $posts;
$wp_query = new stdClass();
$wp_the_query = $wp_query;
$post = null;
$posts = [];
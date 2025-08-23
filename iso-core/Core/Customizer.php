<?php
/**
 * Isotone Theme Customizer API
 * 
 * Provides a live preview customization interface for themes
 * Compatible with WordPress-style customizer registration
 * 
 * @package Isotone\Core
 * @since 1.0.0
 */

namespace Isotone\Core;

use RedBeanPHP\R;

class Customizer
{
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Registered panels
     * @var array
     */
    private $panels = [];
    
    /**
     * Registered sections
     * @var array
     */
    private $sections = [];
    
    /**
     * Registered settings
     * @var array
     */
    private $settings = [];
    
    /**
     * Registered controls
     * @var array
     */
    private $controls = [];
    
    /**
     * Current theme
     * @var array
     */
    private $current_theme = null;
    
    /**
     * Customizer capabilities
     * @var string
     */
    private $capability = 'customize';
    
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
    
    /**
     * Private constructor
     */
    private function __construct()
    {
        $this->current_theme = ThemeAPI::getInstance()->currentTheme;
        $this->registerDefaultSections();
    }
    
    /**
     * Register default customizer sections
     */
    private function registerDefaultSections()
    {
        // Site Identity Section
        $this->addSection('site_identity', [
            'title' => 'Site Identity',
            'priority' => 20,
            'description' => 'Customize your site title, tagline, and logo',
            'icon' => 'identification'
        ]);
        
        // Colors Section
        $this->addSection('colors', [
            'title' => 'Colors',
            'priority' => 40,
            'description' => 'Customize your site colors',
            'icon' => 'swatch'
        ]);
        
        // Header Section
        $this->addSection('header', [
            'title' => 'Header',
            'priority' => 60,
            'description' => 'Customize your site header',
            'icon' => 'rectangle-stack'
        ]);
        
        // Footer Section
        $this->addSection('footer', [
            'title' => 'Footer',
            'priority' => 80,
            'description' => 'Customize your site footer',
            'icon' => 'bars-3-bottom'
        ]);
        
        // Register default settings and controls
        $this->registerDefaultSettings();
    }
    
    /**
     * Register default settings and controls
     */
    private function registerDefaultSettings()
    {
        // Site Title
        $this->addSetting('blogname', [
            'default' => 'Isotone Site',
            'type' => 'option',
            'capability' => 'manage_options',
            'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        
        $this->addControl('blogname', [
            'label' => 'Site Title',
            'section' => 'site_identity',
            'type' => 'text',
            'priority' => 10
        ]);
        
        // Site Tagline
        $this->addSetting('blogdescription', [
            'default' => 'Just another Isotone site',
            'type' => 'option',
            'capability' => 'manage_options',
            'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        
        $this->addControl('blogdescription', [
            'label' => 'Tagline',
            'section' => 'site_identity',
            'type' => 'text',
            'priority' => 20,
            'description' => 'In a few words, explain what this site is about.'
        ]);
        
        // Primary Color
        $this->addSetting('primary_color', [
            'default' => '#00D9FF',
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_hex_color'
        ]);
        
        $this->addControl('primary_color', [
            'label' => 'Primary Color',
            'section' => 'colors',
            'type' => 'color',
            'priority' => 10
        ]);
        
        // Background Color
        $this->addSetting('background_color', [
            'default' => '#0A0E27',
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'postMessage',
            'sanitize_callback' => 'sanitize_hex_color'
        ]);
        
        $this->addControl('background_color', [
            'label' => 'Background Color',
            'section' => 'colors',
            'type' => 'color',
            'priority' => 20
        ]);
    }
    
    /**
     * Add a customizer panel
     */
    public function addPanel($id, $args = [])
    {
        $defaults = [
            'title' => '',
            'description' => '',
            'priority' => 160,
            'capability' => 'edit_theme_options',
            'theme_supports' => ''
        ];
        
        $this->panels[$id] = array_merge($defaults, $args);
        return $this;
    }
    
    /**
     * Add a customizer section
     */
    public function addSection($id, $args = [])
    {
        $defaults = [
            'title' => '',
            'description' => '',
            'panel' => '',
            'priority' => 160,
            'capability' => 'edit_theme_options',
            'theme_supports' => '',
            'icon' => 'cog'  // Default icon
        ];
        
        $this->sections[$id] = array_merge($defaults, $args);
        return $this;
    }
    
    /**
     * Add a customizer setting
     */
    public function addSetting($id, $args = [])
    {
        $defaults = [
            'default' => '',
            'type' => 'theme_mod', // 'theme_mod' or 'option'
            'capability' => 'edit_theme_options',
            'theme_supports' => '',
            'transport' => 'refresh', // 'refresh' or 'postMessage'
            'sanitize_callback' => '',
            'sanitize_js_callback' => ''
        ];
        
        $this->settings[$id] = array_merge($defaults, $args);
        return $this;
    }
    
    /**
     * Add a customizer control
     */
    public function addControl($id, $args = [])
    {
        $defaults = [
            'label' => '',
            'description' => '',
            'section' => '',
            'priority' => 10,
            'type' => 'text', // text, textarea, checkbox, radio, select, dropdown-pages, color, upload, image
            'choices' => [],
            'input_attrs' => [],
            'active_callback' => ''
        ];
        
        $this->controls[$id] = array_merge($defaults, $args);
        $this->controls[$id]['setting'] = $id; // Link control to setting
        return $this;
    }
    
    /**
     * Get all panels
     */
    public function getPanels()
    {
        return $this->panels;
    }
    
    /**
     * Get all sections
     */
    public function getSections()
    {
        // Sort by priority
        uasort($this->sections, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        return $this->sections;
    }
    
    /**
     * Get all settings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Get all controls
     */
    public function getControls()
    {
        // Sort by priority
        uasort($this->controls, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        return $this->controls;
    }
    
    /**
     * Get controls for a specific section
     */
    public function getSectionControls($section_id)
    {
        $section_controls = array_filter($this->controls, function($control) use ($section_id) {
            return $control['section'] === $section_id;
        });
        
        // Sort by priority
        uasort($section_controls, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        return $section_controls;
    }
    
    /**
     * Get setting value
     */
    public function getSettingValue($setting_id)
    {
        if (!isset($this->settings[$setting_id])) {
            return null;
        }
        
        $setting = $this->settings[$setting_id];
        
        if ($setting['type'] === 'option') {
            // Get from options table
            if (R::testConnection()) {
                $option = R::findOne('setting', 'setting_key = ?', [$setting_id]);
                if ($option) {
                    return $option->setting_value;
                }
            }
        } else {
            // Get theme mod
            return ThemeAPI::getInstance()->getThemeMod($setting_id, $setting['default']);
        }
        
        return $setting['default'];
    }
    
    /**
     * Save setting value
     */
    public function saveSettingValue($setting_id, $value)
    {
        if (!isset($this->settings[$setting_id])) {
            return false;
        }
        
        $setting = $this->settings[$setting_id];
        
        // Sanitize value
        if (!empty($setting['sanitize_callback']) && is_callable($setting['sanitize_callback'])) {
            $value = call_user_func($setting['sanitize_callback'], $value);
        }
        
        if ($setting['type'] === 'option') {
            // Save to options table
            if (R::testConnection()) {
                $option = R::findOne('setting', 'setting_key = ?', [$setting_id]);
                if (!$option) {
                    $option = R::dispense('setting');
                    $option->setting_key = $setting_id;
                    $option->setting_type = 'option';
                }
                $option->setting_value = $value;
                $option->updated_at = date('Y-m-d H:i:s');
                R::store($option);
            }
        } else {
            // Save as theme mod
            ThemeAPI::getInstance()->setThemeMod($setting_id, $value);
        }
        
        return true;
    }
    
    /**
     * Save all customizer values
     */
    public function save($values)
    {
        $saved = [];
        
        foreach ($values as $setting_id => $value) {
            if ($this->saveSettingValue($setting_id, $value)) {
                $saved[$setting_id] = $value;
            }
        }
        
        // Trigger action after save
        if (function_exists('do_action')) {
            do_action('customize_save_after', $saved);
        }
        
        return $saved;
    }
    
    /**
     * Check if user can customize
     */
    public function canCustomize()
    {
        // Simply check if user is logged in as admin
        // The customize.php page already uses requireRole('admin')
        // so if we got here, the user is an admin
        
        // Just verify the session exists
        if (isset($_SESSION['isotone_admin_logged_in']) && $_SESSION['isotone_admin_logged_in'] === true) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Render control HTML
     */
    public function renderControl($control_id)
    {
        if (!isset($this->controls[$control_id])) {
            return '';
        }
        
        $control = $this->controls[$control_id];
        $setting_id = $control['setting'];
        $value = $this->getSettingValue($setting_id);
        
        $html = '<div class="customize-control customize-control-' . esc_attr($control['type']) . '" data-control-id="' . esc_attr($control_id) . '">';
        
        // Label
        if (!empty($control['label'])) {
            $html .= '<label for="customize-control-' . esc_attr($control_id) . '">';
            $html .= '<span class="customize-control-title">' . esc_html($control['label']) . '</span>';
            $html .= '</label>';
        }
        
        // Description
        if (!empty($control['description'])) {
            $html .= '<span class="description customize-control-description">' . esc_html($control['description']) . '</span>';
        }
        
        // Control input
        switch ($control['type']) {
            case 'text':
                $html .= '<input type="text" id="customize-control-' . esc_attr($control_id) . '" ';
                $html .= 'name="' . esc_attr($setting_id) . '" ';
                $html .= 'value="' . esc_attr($value) . '" ';
                $html .= 'class="customize-control-input" ';
                $html .= 'data-customize-setting-link="' . esc_attr($setting_id) . '" />';
                break;
                
            case 'textarea':
                $html .= '<textarea id="customize-control-' . esc_attr($control_id) . '" ';
                $html .= 'name="' . esc_attr($setting_id) . '" ';
                $html .= 'class="customize-control-input" ';
                $html .= 'data-customize-setting-link="' . esc_attr($setting_id) . '">';
                $html .= esc_textarea($value);
                $html .= '</textarea>';
                break;
                
            case 'checkbox':
                $html .= '<input type="checkbox" id="customize-control-' . esc_attr($control_id) . '" ';
                $html .= 'name="' . esc_attr($setting_id) . '" ';
                $html .= 'value="1" ';
                $html .= checked($value, true, false) . ' ';
                $html .= 'class="customize-control-input" ';
                $html .= 'data-customize-setting-link="' . esc_attr($setting_id) . '" />';
                break;
                
            case 'select':
                $html .= '<select id="customize-control-' . esc_attr($control_id) . '" ';
                $html .= 'name="' . esc_attr($setting_id) . '" ';
                $html .= 'class="customize-control-input" ';
                $html .= 'data-customize-setting-link="' . esc_attr($setting_id) . '">';
                foreach ($control['choices'] as $choice_value => $choice_label) {
                    $html .= '<option value="' . esc_attr($choice_value) . '" ';
                    $html .= selected($value, $choice_value, false) . '>';
                    $html .= esc_html($choice_label);
                    $html .= '</option>';
                }
                $html .= '</select>';
                break;
                
            case 'color':
                $html .= '<input type="color" id="customize-control-' . esc_attr($control_id) . '" ';
                $html .= 'name="' . esc_attr($setting_id) . '" ';
                $html .= 'value="' . esc_attr($value) . '" ';
                $html .= 'class="customize-control-input customize-control-color" ';
                $html .= 'data-customize-setting-link="' . esc_attr($setting_id) . '" />';
                break;
                
            case 'range':
                $min = isset($control['input_attrs']['min']) ? $control['input_attrs']['min'] : 0;
                $max = isset($control['input_attrs']['max']) ? $control['input_attrs']['max'] : 100;
                $step = isset($control['input_attrs']['step']) ? $control['input_attrs']['step'] : 1;
                
                $html .= '<input type="range" id="customize-control-' . esc_attr($control_id) . '" ';
                $html .= 'name="' . esc_attr($setting_id) . '" ';
                $html .= 'value="' . esc_attr($value) . '" ';
                $html .= 'min="' . esc_attr($min) . '" ';
                $html .= 'max="' . esc_attr($max) . '" ';
                $html .= 'step="' . esc_attr($step) . '" ';
                $html .= 'class="customize-control-input customize-control-range" ';
                $html .= 'data-customize-setting-link="' . esc_attr($setting_id) . '" />';
                $html .= '<span class="customize-control-range-value">' . esc_html($value) . '</span>';
                break;
                
            case 'radio':
                foreach ($control['choices'] as $choice_value => $choice_label) {
                    $html .= '<label>';
                    $html .= '<input type="radio" ';
                    $html .= 'name="' . esc_attr($setting_id) . '" ';
                    $html .= 'value="' . esc_attr($choice_value) . '" ';
                    $html .= checked($value, $choice_value, false) . ' ';
                    $html .= 'class="customize-control-input" ';
                    $html .= 'data-customize-setting-link="' . esc_attr($setting_id) . '" />';
                    $html .= ' ' . esc_html($choice_label);
                    $html .= '</label><br>';
                }
                break;
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get all setting values for preview
     */
    public function getPreviewValues()
    {
        $values = [];
        
        foreach ($this->settings as $setting_id => $setting) {
            $values[$setting_id] = [
                'value' => $this->getSettingValue($setting_id),
                'transport' => $setting['transport']
            ];
        }
        
        return $values;
    }
}

// Helper function for sanitizing hex colors
if (!function_exists('sanitize_hex_color')) {
    function sanitize_hex_color($color) {
        if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            return $color;
        }
        return '';
    }
}

// Helper function for checking values
if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {
        return $checked == $current ? 'checked="checked"' : '';
    }
}

if (!function_exists('selected')) {
    function selected($selected, $current = true, $echo = true) {
        return $selected == $current ? 'selected="selected"' : '';
    }
}
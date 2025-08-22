<?php
/**
 * Customizer Section Class
 * 
 * Represents a section in the customizer
 * 
 * @package Isotone
 * @since 1.0.0
 */

class CustomizerSection {
    /**
     * Section ID
     * @var string
     */
    public $id;
    
    /**
     * Section title
     * @var string
     */
    public $title = '';
    
    /**
     * Section description
     * @var string
     */
    public $description = '';
    
    /**
     * Section priority for ordering
     * @var int
     */
    public $priority = 10;
    
    /**
     * Panel ID this section belongs to
     * @var string
     */
    public $panel = '';
    
    /**
     * Section capability required
     * @var string
     */
    public $capability = 'edit_theme_options';
    
    /**
     * Theme supports required
     * @var array|string
     */
    public $theme_supports = '';
    
    /**
     * Active callback
     * @var callable|null
     */
    public $active_callback = null;
    
    /**
     * Section type
     * @var string
     */
    public $type = 'default';
    
    /**
     * Controls in this section
     * @var array
     */
    protected $controls = [];
    
    /**
     * Constructor
     * 
     * @param string $id Section ID
     * @param array $args Section arguments
     */
    public function __construct($id, $args = []) {
        $this->id = $id;
        
        // Set properties from args
        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Check if section is active
     * 
     * @return bool
     */
    public function isActive() {
        // Check capability
        if ($this->capability && !current_user_can($this->capability)) {
            return false;
        }
        
        // Check theme supports
        if ($this->theme_supports) {
            $supports = is_array($this->theme_supports) ? $this->theme_supports : [$this->theme_supports];
            foreach ($supports as $support) {
                if (!current_theme_supports($support)) {
                    return false;
                }
            }
        }
        
        // Check active callback
        if (is_callable($this->active_callback)) {
            return call_user_func($this->active_callback);
        }
        
        return true;
    }
    
    /**
     * Add control to section
     * 
     * @param CustomizerControl $control
     */
    public function addControl($control) {
        $this->controls[$control->id] = $control;
    }
    
    /**
     * Remove control from section
     * 
     * @param string $control_id
     */
    public function removeControl($control_id) {
        unset($this->controls[$control_id]);
    }
    
    /**
     * Get all controls in section
     * 
     * @return array
     */
    public function getControls() {
        // Sort by priority
        uasort($this->controls, function($a, $b) {
            return $a->priority <=> $b->priority;
        });
        
        return $this->controls;
    }
    
    /**
     * Check if section has controls
     * 
     * @return bool
     */
    public function hasControls() {
        return !empty($this->controls);
    }
    
    /**
     * Render the section
     * 
     * @return string
     */
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $class = 'customize-section';
        if ($this->type !== 'default') {
            $class .= ' customize-section-' . esc_attr($this->type);
        }
        
        $output = '<div class="' . $class . '" data-section="' . esc_attr($this->id) . '">';
        
        // Section header
        $output .= '<div class="customize-section-header">';
        $output .= '<h3 class="customize-section-title">' . esc_html($this->title) . '</h3>';
        
        if ($this->description) {
            $output .= '<p class="customize-section-description">' . esc_html($this->description) . '</p>';
        }
        
        $output .= '</div>';
        
        // Section content
        $output .= '<div class="customize-section-content">';
        
        // Render controls
        foreach ($this->getControls() as $control) {
            $output .= $control->render();
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Export section data for JavaScript
     * 
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'panel' => $this->panel,
            'type' => $this->type,
            'active' => $this->isActive(),
            'controls' => array_keys($this->controls)
        ];
    }
}

/**
 * Extended section types
 */

/**
 * Collapsible Section
 */
class CustomizerCollapsibleSection extends CustomizerSection {
    public $type = 'collapsible';
    
    /**
     * Initial collapsed state
     * @var bool
     */
    public $collapsed = false;
    
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $class = 'customize-section customize-section-collapsible';
        if ($this->collapsed) {
            $class .= ' collapsed';
        }
        
        $output = '<div class="' . $class . '" data-section="' . esc_attr($this->id) . '">';
        
        // Collapsible header
        $output .= '<div class="customize-section-header customize-section-collapse">';
        $output .= '<h3 class="customize-section-title">' . esc_html($this->title) . '</h3>';
        
        if ($this->description) {
            $output .= '<p class="customize-section-description">' . esc_html($this->description) . '</p>';
        }
        
        $output .= '</div>';
        
        // Collapsible content
        $output .= '<div class="customize-section-content">';
        
        foreach ($this->getControls() as $control) {
            $output .= $control->render();
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Outer Section (appears outside panels)
 */
class CustomizerOuterSection extends CustomizerSection {
    public $type = 'outer';
    
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $output = '<div class="customize-section customize-section-outer" data-section="' . esc_attr($this->id) . '">';
        
        // Simplified header for outer sections
        if ($this->title) {
            $output .= '<h3 class="customize-section-title">' . esc_html($this->title) . '</h3>';
        }
        
        // Render controls directly
        foreach ($this->getControls() as $control) {
            $output .= $control->render();
        }
        
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Helper functions
 */

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        // Check if user is logged in and has admin role
        if (isset($_SESSION['isotone_admin_user_data']['role'])) {
            // For now, assume admin users have all capabilities
            return $_SESSION['isotone_admin_user_data']['role'] === 'admin';
        }
        
        // Fallback: Check if user ID exists and verify role directly
        if (isset($_SESSION['isotone_admin_user_id'])) {
            // Ensure database is connected
            require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
            require_once dirname(__DIR__, 2) . '/iso-includes/database.php';
            
            if (!class_exists('R') || !R::testConnection()) {
                isotone_db_connect();
            }
            
            require_once dirname(__DIR__, 2) . '/iso-includes/class-user.php';
            $user = new \IsotoneUser();
            return $user->hasRole($_SESSION['isotone_admin_user_id'], 'admin');
        }
        
        return false;
    }
}

if (!function_exists('current_theme_supports')) {
    function current_theme_supports($feature) {
        // Check if current theme supports a feature
        // This would be implemented based on theme's functions.php
        return true; // Default to true for now
    }
}
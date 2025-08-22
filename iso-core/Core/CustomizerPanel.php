<?php
/**
 * Customizer Panel Class
 * 
 * Represents a panel (group of sections) in the customizer
 * 
 * @package Isotone
 * @since 1.0.0
 */

class CustomizerPanel {
    /**
     * Panel ID
     * @var string
     */
    public $id;
    
    /**
     * Panel title
     * @var string
     */
    public $title = '';
    
    /**
     * Panel description
     * @var string
     */
    public $description = '';
    
    /**
     * Panel priority for ordering
     * @var int
     */
    public $priority = 10;
    
    /**
     * Panel capability required
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
     * Panel type
     * @var string
     */
    public $type = 'default';
    
    /**
     * Auto expand single section
     * @var bool
     */
    public $auto_expand_sole_section = false;
    
    /**
     * Sections in this panel
     * @var array
     */
    protected $sections = [];
    
    /**
     * Constructor
     * 
     * @param string $id Panel ID
     * @param array $args Panel arguments
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
     * Check if panel is active
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
        
        // Check if panel has active sections
        if (empty($this->sections)) {
            return false;
        }
        
        foreach ($this->sections as $section) {
            if ($section->isActive()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Add section to panel
     * 
     * @param CustomizerSection $section
     */
    public function addSection($section) {
        $section->panel = $this->id;
        $this->sections[$section->id] = $section;
    }
    
    /**
     * Remove section from panel
     * 
     * @param string $section_id
     */
    public function removeSection($section_id) {
        unset($this->sections[$section_id]);
    }
    
    /**
     * Get all sections in panel
     * 
     * @return array
     */
    public function getSections() {
        // Sort by priority
        uasort($this->sections, function($a, $b) {
            return $a->priority <=> $b->priority;
        });
        
        return $this->sections;
    }
    
    /**
     * Check if panel has sections
     * 
     * @return bool
     */
    public function hasSections() {
        return !empty($this->sections);
    }
    
    /**
     * Count active sections
     * 
     * @return int
     */
    public function countActiveSections() {
        $count = 0;
        foreach ($this->sections as $section) {
            if ($section->isActive()) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Render the panel
     * 
     * @return string
     */
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $class = 'customize-panel';
        if ($this->type !== 'default') {
            $class .= ' customize-panel-' . esc_attr($this->type);
        }
        
        // Auto-expand if only one section
        if ($this->auto_expand_sole_section && $this->countActiveSections() === 1) {
            $class .= ' auto-expanded';
        }
        
        $output = '<div class="' . $class . '" data-panel="' . esc_attr($this->id) . '">';
        
        // Panel header
        $output .= $this->renderHeader();
        
        // Panel content (sections)
        $output .= '<div class="customize-panel-content">';
        
        foreach ($this->getSections() as $section) {
            $output .= $section->render();
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render panel header
     * 
     * @return string
     */
    protected function renderHeader() {
        $output = '<div class="customize-panel-header">';
        
        // Back button
        $output .= '<button type="button" class="customize-panel-back" aria-label="Back">';
        $output .= '<span class="screen-reader-text">Back</span>';
        $output .= '</button>';
        
        // Panel title
        $output .= '<div class="customize-panel-title-wrapper">';
        $output .= '<h3 class="customize-panel-title">' . esc_html($this->title) . '</h3>';
        
        if ($this->description) {
            $output .= '<p class="customize-panel-description">' . esc_html($this->description) . '</p>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Export panel data for JavaScript
     * 
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'type' => $this->type,
            'active' => $this->isActive(),
            'sections' => array_keys($this->sections),
            'auto_expand_sole_section' => $this->auto_expand_sole_section
        ];
    }
}

/**
 * Extended panel types
 */

/**
 * Accordion Panel
 */
class CustomizerAccordionPanel extends CustomizerPanel {
    public $type = 'accordion';
    
    /**
     * Allow multiple sections open
     * @var bool
     */
    public $allow_multiple = false;
    
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $class = 'customize-panel customize-panel-accordion';
        if (!$this->allow_multiple) {
            $class .= ' single-open';
        }
        
        $output = '<div class="' . $class . '" data-panel="' . esc_attr($this->id) . '">';
        
        // Accordion header
        $output .= '<div class="customize-panel-header customize-panel-accordion-header">';
        $output .= '<h3 class="customize-panel-title">' . esc_html($this->title) . '</h3>';
        
        if ($this->description) {
            $output .= '<p class="customize-panel-description">' . esc_html($this->description) . '</p>';
        }
        
        $output .= '</div>';
        
        // Accordion sections
        $output .= '<div class="customize-panel-accordion-content">';
        
        foreach ($this->getSections() as $section) {
            // Wrap sections in accordion items
            $output .= '<div class="customize-accordion-item">';
            $output .= $section->render();
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Tabbed Panel
 */
class CustomizerTabbedPanel extends CustomizerPanel {
    public $type = 'tabbed';
    
    /**
     * Tab position
     * @var string
     */
    public $tab_position = 'top'; // top, left, right, bottom
    
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $class = 'customize-panel customize-panel-tabbed';
        $class .= ' tabs-' . esc_attr($this->tab_position);
        
        $output = '<div class="' . $class . '" data-panel="' . esc_attr($this->id) . '">';
        
        // Panel header
        $output .= '<div class="customize-panel-header">';
        $output .= '<h3 class="customize-panel-title">' . esc_html($this->title) . '</h3>';
        
        if ($this->description) {
            $output .= '<p class="customize-panel-description">' . esc_html($this->description) . '</p>';
        }
        
        $output .= '</div>';
        
        // Tab navigation
        $output .= '<div class="customize-panel-tabs">';
        $output .= '<ul class="customize-panel-tab-list">';
        
        foreach ($this->getSections() as $section) {
            if ($section->isActive()) {
                $output .= '<li class="customize-panel-tab">';
                $output .= '<button type="button" data-section="' . esc_attr($section->id) . '">';
                $output .= esc_html($section->title);
                $output .= '</button>';
                $output .= '</li>';
            }
        }
        
        $output .= '</ul>';
        $output .= '</div>';
        
        // Tab content
        $output .= '<div class="customize-panel-tab-content">';
        
        foreach ($this->getSections() as $section) {
            $output .= '<div class="customize-tab-pane" data-section="' . esc_attr($section->id) . '">';
            $output .= $section->render();
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Navigation Panel (for theme menus)
 */
class CustomizerNavMenuPanel extends CustomizerPanel {
    public $type = 'nav_menu';
    
    /**
     * Menu locations
     * @var array
     */
    public $locations = [];
    
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $output = '<div class="customize-panel customize-panel-nav-menu" data-panel="' . esc_attr($this->id) . '">';
        
        // Panel header with menu selector
        $output .= '<div class="customize-panel-header">';
        $output .= '<h3 class="customize-panel-title">' . esc_html($this->title) . '</h3>';
        
        // Menu location selector
        if (!empty($this->locations)) {
            $output .= '<div class="customize-menu-locations">';
            $output .= '<label>Menu Location:</label>';
            $output .= '<select class="customize-menu-location-select">';
            
            foreach ($this->locations as $location => $label) {
                $output .= '<option value="' . esc_attr($location) . '">';
                $output .= esc_html($label) . '</option>';
            }
            
            $output .= '</select>';
            $output .= '</div>';
        }
        
        if ($this->description) {
            $output .= '<p class="customize-panel-description">' . esc_html($this->description) . '</p>';
        }
        
        $output .= '</div>';
        
        // Menu sections
        $output .= '<div class="customize-panel-content">';
        
        foreach ($this->getSections() as $section) {
            $output .= $section->render();
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Widget Panel
 */
class CustomizerWidgetPanel extends CustomizerPanel {
    public $type = 'widget';
    
    /**
     * Widget areas
     * @var array
     */
    public $widget_areas = [];
    
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $output = '<div class="customize-panel customize-panel-widget" data-panel="' . esc_attr($this->id) . '">';
        
        // Panel header
        $output .= '<div class="customize-panel-header">';
        $output .= '<h3 class="customize-panel-title">' . esc_html($this->title) . '</h3>';
        
        if ($this->description) {
            $output .= '<p class="customize-panel-description">' . esc_html($this->description) . '</p>';
        }
        
        $output .= '</div>';
        
        // Widget areas list
        $output .= '<div class="customize-panel-content">';
        
        if (!empty($this->widget_areas)) {
            $output .= '<div class="customize-widget-areas">';
            
            foreach ($this->widget_areas as $area_id => $area) {
                $output .= '<div class="customize-widget-area" data-area="' . esc_attr($area_id) . '">';
                $output .= '<h4>' . esc_html($area['name']) . '</h4>';
                
                if (isset($area['description'])) {
                    $output .= '<p class="description">' . esc_html($area['description']) . '</p>';
                }
                
                $output .= '<div class="widget-area-widgets">';
                // Widget list would go here
                $output .= '</div>';
                
                $output .= '<button type="button" class="add-widget-btn">Add Widget</button>';
                $output .= '</div>';
            }
            
            $output .= '</div>';
        }
        
        // Regular sections
        foreach ($this->getSections() as $section) {
            $output .= $section->render();
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}
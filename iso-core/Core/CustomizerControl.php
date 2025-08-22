<?php
/**
 * Customizer Control Base Class
 * 
 * Base class for all customizer control types
 * 
 * @package Isotone
 * @since 1.0.0
 */

class CustomizerControl {
    /**
     * Control ID
     * @var string
     */
    public $id;
    
    /**
     * Control type
     * @var string
     */
    public $type = 'text';
    
    /**
     * Control label
     * @var string
     */
    public $label = '';
    
    /**
     * Control description
     * @var string
     */
    public $description = '';
    
    /**
     * Section ID this control belongs to
     * @var string
     */
    public $section = '';
    
    /**
     * Setting ID this control is linked to
     * @var string
     */
    public $setting = '';
    
    /**
     * Control priority for ordering
     * @var int
     */
    public $priority = 10;
    
    /**
     * Control choices (for select, radio, etc.)
     * @var array
     */
    public $choices = [];
    
    /**
     * Input attributes
     * @var array
     */
    public $input_attrs = [];
    
    /**
     * Active callback
     * @var callable|null
     */
    public $active_callback = null;
    
    /**
     * Constructor
     * 
     * @param string $id Control ID
     * @param array $args Control arguments
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
     * Check if control is active
     * 
     * @return bool
     */
    public function isActive() {
        if (is_callable($this->active_callback)) {
            return call_user_func($this->active_callback);
        }
        return true;
    }
    
    /**
     * Get the value of the control's setting
     * 
     * @return mixed
     */
    public function getValue() {
        if ($this->setting) {
            return get_theme_mod($this->setting);
        }
        return '';
    }
    
    /**
     * Render the control
     * 
     * @return string
     */
    public function render() {
        if (!$this->isActive()) {
            return '';
        }
        
        $output = '<div class="customize-control customize-control-' . esc_attr($this->type) . '" ';
        $output .= 'data-control-id="' . esc_attr($this->id) . '" ';
        $output .= 'data-control-type="' . esc_attr($this->type) . '">';
        
        if ($this->label) {
            $output .= '<label class="customize-control-title">';
            $output .= esc_html($this->label);
            $output .= '</label>';
        }
        
        if ($this->description) {
            $output .= '<span class="customize-control-description">';
            $output .= esc_html($this->description);
            $output .= '</span>';
        }
        
        $output .= $this->renderContent();
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render the control's content
     * 
     * @return string
     */
    protected function renderContent() {
        $value = $this->getValue();
        $attrs = $this->getInputAttributes();
        
        switch ($this->type) {
            case 'text':
                return $this->renderTextInput($value, $attrs);
                
            case 'textarea':
                return $this->renderTextarea($value, $attrs);
                
            case 'select':
                return $this->renderSelect($value, $attrs);
                
            case 'radio':
                return $this->renderRadio($value, $attrs);
                
            case 'checkbox':
                return $this->renderCheckbox($value, $attrs);
                
            case 'color':
                return $this->renderColorPicker($value, $attrs);
                
            case 'range':
                return $this->renderRange($value, $attrs);
                
            case 'url':
                return $this->renderUrlInput($value, $attrs);
                
            case 'email':
                return $this->renderEmailInput($value, $attrs);
                
            case 'number':
                return $this->renderNumberInput($value, $attrs);
                
            case 'dropdown-pages':
                return $this->renderDropdownPages($value, $attrs);
                
            case 'upload':
            case 'image':
                return $this->renderUpload($value, $attrs);
                
            default:
                return $this->renderCustomContent($value, $attrs);
        }
    }
    
    /**
     * Get input attributes string
     * 
     * @return string
     */
    protected function getInputAttributes() {
        $attrs = 'data-customize-setting-link="' . esc_attr($this->setting) . '" ';
        
        foreach ($this->input_attrs as $key => $value) {
            $attrs .= esc_attr($key) . '="' . esc_attr($value) . '" ';
        }
        
        return $attrs;
    }
    
    /**
     * Render text input
     */
    protected function renderTextInput($value, $attrs) {
        return '<input type="text" class="customize-control-input" value="' . esc_attr($value) . '" ' . $attrs . '>';
    }
    
    /**
     * Render textarea
     */
    protected function renderTextarea($value, $attrs) {
        $rows = isset($this->input_attrs['rows']) ? $this->input_attrs['rows'] : 5;
        return '<textarea class="customize-control-input" rows="' . $rows . '" ' . $attrs . '>' . esc_textarea($value) . '</textarea>';
    }
    
    /**
     * Render select dropdown
     */
    protected function renderSelect($value, $attrs) {
        $output = '<select class="customize-control-input" ' . $attrs . '>';
        
        foreach ($this->choices as $key => $label) {
            $selected = selected($value, $key, false);
            $output .= '<option value="' . esc_attr($key) . '" ' . $selected . '>';
            $output .= esc_html($label) . '</option>';
        }
        
        $output .= '</select>';
        return $output;
    }
    
    /**
     * Render radio buttons
     */
    protected function renderRadio($value, $attrs) {
        $output = '<div class="customize-control-radio-group">';
        
        foreach ($this->choices as $key => $label) {
            $checked = checked($value, $key, false);
            $output .= '<label class="customize-control-radio-label">';
            $output .= '<input type="radio" name="' . esc_attr($this->id) . '" ';
            $output .= 'value="' . esc_attr($key) . '" ' . $checked . ' ' . $attrs . '>';
            $output .= '<span>' . esc_html($label) . '</span>';
            $output .= '</label>';
        }
        
        $output .= '</div>';
        return $output;
    }
    
    /**
     * Render checkbox
     */
    protected function renderCheckbox($value, $attrs) {
        $checked = checked($value, '1', false);
        
        $output = '<label class="customize-control-checkbox-label">';
        $output .= '<input type="checkbox" class="customize-control-input" ';
        $output .= 'value="1" ' . $checked . ' ' . $attrs . '>';
        
        if (isset($this->choices['label'])) {
            $output .= '<span>' . esc_html($this->choices['label']) . '</span>';
        }
        
        $output .= '</label>';
        return $output;
    }
    
    /**
     * Render color picker
     */
    protected function renderColorPicker($value, $attrs) {
        $value = $value ?: '#000000';
        return '<input type="color" class="customize-control-input customize-control-color" value="' . esc_attr($value) . '" ' . $attrs . '>';
    }
    
    /**
     * Render range slider
     */
    protected function renderRange($value, $attrs) {
        $min = isset($this->input_attrs['min']) ? $this->input_attrs['min'] : 0;
        $max = isset($this->input_attrs['max']) ? $this->input_attrs['max'] : 100;
        $step = isset($this->input_attrs['step']) ? $this->input_attrs['step'] : 1;
        
        $output = '<div class="customize-control-range-wrapper">';
        $output .= '<input type="range" class="customize-control-input customize-control-range" ';
        $output .= 'min="' . esc_attr($min) . '" max="' . esc_attr($max) . '" ';
        $output .= 'step="' . esc_attr($step) . '" value="' . esc_attr($value) . '" ' . $attrs . '>';
        $output .= '<span class="customize-control-range-value">' . esc_html($value) . '</span>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render URL input
     */
    protected function renderUrlInput($value, $attrs) {
        return '<input type="url" class="customize-control-input" value="' . esc_attr($value) . '" placeholder="https://" ' . $attrs . '>';
    }
    
    /**
     * Render email input
     */
    protected function renderEmailInput($value, $attrs) {
        return '<input type="email" class="customize-control-input" value="' . esc_attr($value) . '" ' . $attrs . '>';
    }
    
    /**
     * Render number input
     */
    protected function renderNumberInput($value, $attrs) {
        $min = isset($this->input_attrs['min']) ? 'min="' . esc_attr($this->input_attrs['min']) . '"' : '';
        $max = isset($this->input_attrs['max']) ? 'max="' . esc_attr($this->input_attrs['max']) . '"' : '';
        $step = isset($this->input_attrs['step']) ? 'step="' . esc_attr($this->input_attrs['step']) . '"' : '';
        
        return '<input type="number" class="customize-control-input" value="' . esc_attr($value) . '" ' . $min . ' ' . $max . ' ' . $step . ' ' . $attrs . '>';
    }
    
    /**
     * Render dropdown pages selector
     */
    protected function renderDropdownPages($value, $attrs) {
        $output = '<select class="customize-control-input" ' . $attrs . '>';
        $output .= '<option value="">— Select —</option>';
        
        // Get all pages from database
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (class_exists('R')) {
            $pages = R::findAll('page', 'ORDER BY title');
            foreach ($pages as $page) {
                $selected = selected($value, $page->id, false);
                $output .= '<option value="' . esc_attr($page->id) . '" ' . $selected . '>';
                $output .= esc_html($page->title) . '</option>';
            }
        }
        
        $output .= '</select>';
        return $output;
    }
    
    /**
     * Render upload/image control
     */
    protected function renderUpload($value, $attrs) {
        $output = '<div class="customize-control-upload">';
        $output .= '<input type="hidden" class="customize-control-input" value="' . esc_attr($value) . '" ' . $attrs . '>';
        
        $output .= '<div class="upload-preview">';
        if ($value) {
            $output .= '<img src="' . esc_attr($value) . '" alt="Preview">';
        } else {
            $output .= '<span class="no-image">No image selected</span>';
        }
        $output .= '</div>';
        
        $output .= '<div class="upload-actions">';
        $output .= '<button type="button" class="upload-btn">Select Image</button>';
        $output .= '<button type="button" class="remove-btn" ' . ($value ? '' : 'style="display:none"') . '>Remove</button>';
        $output .= '</div>';
        
        $output .= '</div>';
        return $output;
    }
    
    /**
     * Render custom content
     * Override this method in custom control classes
     */
    protected function renderCustomContent($value, $attrs) {
        return '<!-- Custom control type: ' . esc_html($this->type) . ' -->';
    }
}

/**
 * Helper functions
 */

if (!function_exists('selected')) {
    function selected($selected, $current, $echo = true) {
        $result = $selected == $current ? ' selected="selected"' : '';
        if ($echo) echo $result;
        return $result;
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current, $echo = true) {
        $result = $checked == $current ? ' checked="checked"' : '';
        if ($echo) echo $result;
        return $result;
    }
}

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

if (!function_exists('esc_textarea')) {
    function esc_textarea($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
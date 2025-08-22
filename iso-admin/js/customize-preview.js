/**
 * Isotone Customizer Preview JavaScript
 * 
 * Handles live preview updates in the customizer iframe
 */

(function() {
    'use strict';
    
    // Check if we're in customize preview mode
    if (!window.location.search.includes('customize_preview=1')) {
        return;
    }
    
    // Customizer preview API
    const IsotoneCustomizePreview = {
        settings: {},
        
        /**
         * Initialize preview
         */
        init: function() {
            // Listen for messages from parent (customizer controls)
            window.addEventListener('message', this.handleMessage.bind(this), false);
            
            // Add customize preview class to body
            document.body.classList.add('customize-preview');
            
            // Send ready signal to parent
            this.sendToParent('preview-ready', {});
        },
        
        /**
         * Handle messages from customizer controls
         */
        handleMessage: function(event) {
            // Verify origin (adjust for your domain)
            // if (event.origin !== window.location.origin) return;
            
            if (!event.data || !event.data.type) return;
            
            switch (event.data.type) {
                case 'customize-settings':
                    this.setSettings(event.data.data);
                    break;
                    
                case 'customize-setting-change':
                    this.updateSetting(event.data.data.setting, event.data.data.value);
                    break;
                    
                case 'customize-refresh':
                    window.location.reload();
                    break;
            }
        },
        
        /**
         * Set all settings
         */
        setSettings: function(settings) {
            this.settings = settings;
            
            // Apply all settings
            for (const [settingId, settingData] of Object.entries(settings)) {
                if (settingData.transport === 'postMessage') {
                    this.applySetting(settingId, settingData.value);
                }
            }
        },
        
        /**
         * Update a single setting
         */
        updateSetting: function(settingId, value) {
            this.settings[settingId] = { value: value };
            this.applySetting(settingId, value);
        },
        
        /**
         * Apply setting to preview
         */
        applySetting: function(settingId, value) {
            switch (settingId) {
                // Site Identity
                case 'blogname':
                    this.updateTextContent('.site-title', value);
                    this.updateTextContent('[data-customize="blogname"]', value);
                    document.title = value + ' - ' + document.title.split(' - ').slice(1).join(' - ');
                    break;
                    
                case 'blogdescription':
                    this.updateTextContent('.site-description', value);
                    this.updateTextContent('.site-tagline', value);
                    this.updateTextContent('[data-customize="blogdescription"]', value);
                    break;
                    
                // Colors
                case 'primary_color':
                    this.updateCSSVariable('--primary-color', value);
                    this.updateCSSVariable('--theme-primary', value);
                    this.updateStyle('.text-primary', 'color', value);
                    this.updateStyle('.bg-primary', 'background-color', value);
                    this.updateStyle('.border-primary', 'border-color', value);
                    break;
                    
                case 'background_color':
                    this.updateCSSVariable('--background-color', value);
                    this.updateStyle('body', 'background-color', value);
                    this.updateStyle('.site-background', 'background-color', value);
                    break;
                    
                case 'text_color':
                    this.updateCSSVariable('--text-color', value);
                    this.updateStyle('body', 'color', value);
                    this.updateStyle('.site-content', 'color', value);
                    break;
                    
                case 'link_color':
                    this.updateCSSVariable('--link-color', value);
                    this.updateStyle('a', 'color', value);
                    break;
                    
                // Header
                case 'header_background':
                    this.updateStyle('header', 'background-color', value);
                    this.updateStyle('.site-header', 'background-color', value);
                    this.updateStyle('[data-customize="header_background"]', 'background-color', value);
                    break;
                    
                case 'header_text_color':
                    this.updateStyle('header', 'color', value);
                    this.updateStyle('.site-header', 'color', value);
                    this.updateStyle('.site-header a', 'color', value);
                    break;
                    
                // Footer
                case 'footer_text':
                    this.updateTextContent('.footer-text', value);
                    this.updateTextContent('[data-customize="footer_text"]', value);
                    break;
                    
                case 'copyright_text':
                    this.updateTextContent('.copyright', value);
                    this.updateTextContent('[data-customize="copyright_text"]', value);
                    break;
                    
                // Typography
                case 'font_size':
                    this.updateCSSVariable('--font-size-base', value + 'px');
                    this.updateStyle('body', 'font-size', value + 'px');
                    break;
                    
                case 'font_family':
                    this.updateCSSVariable('--font-family', value);
                    this.updateStyle('body', 'font-family', value);
                    break;
                    
                case 'line_height':
                    this.updateCSSVariable('--line-height', value);
                    this.updateStyle('body', 'line-height', value);
                    break;
                    
                // Layout
                case 'container_width':
                    this.updateCSSVariable('--container-width', value + 'px');
                    this.updateStyle('.container', 'max-width', value + 'px');
                    this.updateStyle('.site-container', 'max-width', value + 'px');
                    break;
                    
                case 'sidebar_position':
                    document.body.classList.remove('sidebar-left', 'sidebar-right', 'no-sidebar');
                    document.body.classList.add('sidebar-' + value);
                    break;
                    
                // Custom CSS
                case 'custom_css':
                    this.updateCustomCSS(value);
                    break;
                    
                // Logo
                case 'custom_logo':
                    this.updateLogo(value);
                    break;
                    
                // Default handler for data attributes
                default:
                    // Try to update elements with data-customize attribute
                    const elements = document.querySelectorAll('[data-customize="' + settingId + '"]');
                    elements.forEach(element => {
                        if (element.tagName === 'IMG') {
                            element.src = value;
                        } else if (element.tagName === 'A') {
                            element.href = value;
                        } else {
                            element.textContent = value;
                        }
                    });
                    
                    // Trigger custom event for theme-specific handling
                    this.triggerCustomEvent(settingId, value);
            }
        },
        
        /**
         * Update text content of elements
         */
        updateTextContent: function(selector, value) {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                element.textContent = value;
            });
        },
        
        /**
         * Update style property of elements
         */
        updateStyle: function(selector, property, value) {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                element.style[property] = value;
            });
        },
        
        /**
         * Update CSS variable
         */
        updateCSSVariable: function(variable, value) {
            document.documentElement.style.setProperty(variable, value);
        },
        
        /**
         * Update custom CSS
         */
        updateCustomCSS: function(css) {
            let styleElement = document.getElementById('customize-preview-custom-css');
            
            if (!styleElement) {
                styleElement = document.createElement('style');
                styleElement.id = 'customize-preview-custom-css';
                document.head.appendChild(styleElement);
            }
            
            styleElement.textContent = css;
        },
        
        /**
         * Update logo
         */
        updateLogo: function(logoUrl) {
            const logos = document.querySelectorAll('.custom-logo, .site-logo img, [data-customize="custom_logo"]');
            logos.forEach(logo => {
                if (logo.tagName === 'IMG') {
                    logo.src = logoUrl;
                } else {
                    logo.style.backgroundImage = 'url(' + logoUrl + ')';
                }
            });
        },
        
        /**
         * Trigger custom event for theme handling
         */
        triggerCustomEvent: function(settingId, value) {
            const event = new CustomEvent('customize-setting-change', {
                detail: {
                    setting: settingId,
                    value: value
                }
            });
            document.dispatchEvent(event);
        },
        
        /**
         * Send message to parent window
         */
        sendToParent: function(type, data) {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    type: type,
                    data: data
                }, '*');
            }
        },
        
        /**
         * Selective refresh - refresh only part of the page
         */
        selectiveRefresh: function(selector, url) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector(selector);
                    const oldContent = document.querySelector(selector);
                    
                    if (newContent && oldContent) {
                        oldContent.innerHTML = newContent.innerHTML;
                        
                        // Re-initialize any JavaScript that might be needed
                        this.reinitialize(selector);
                    }
                })
                .catch(error => {
                    console.error('Selective refresh error:', error);
                });
        },
        
        /**
         * Reinitialize JavaScript after selective refresh
         */
        reinitialize: function(selector) {
            // Trigger event for themes to reinitialize their JavaScript
            const event = new CustomEvent('customize-selective-refresh', {
                detail: { selector: selector }
            });
            document.dispatchEvent(event);
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            IsotoneCustomizePreview.init();
        });
    } else {
        IsotoneCustomizePreview.init();
    }
    
    // Expose API for themes to use
    window.IsotoneCustomizePreview = IsotoneCustomizePreview;
})();
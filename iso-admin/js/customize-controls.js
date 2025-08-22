/**
 * Isotone Customizer Controls JavaScript
 * 
 * Handles control interactions and communication with preview
 */

(function() {
    'use strict';
    
    // Customizer Controls API
    const IsotoneCustomizeControls = {
        settings: {},
        controls: {},
        dirty: false,
        
        /**
         * Initialize customizer controls
         */
        init: function() {
            this.bindEvents();
            this.initializeControls();
            this.setupDevicePreview();
            this.setupPanelNavigation();
            
            // Mark as ready
            document.body.classList.add('customize-controls-ready');
        },
        
        /**
         * Bind global events
         */
        bindEvents: function() {
            // Prevent accidental navigation away with unsaved changes
            window.addEventListener('beforeunload', (e) => {
                if (this.dirty) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
            
            // Listen for messages from preview
            window.addEventListener('message', this.handlePreviewMessage.bind(this), false);
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + S to save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    this.save();
                }
                
                // Escape to close panels
                if (e.key === 'Escape') {
                    this.closeOpenPanels();
                }
            });
        },
        
        /**
         * Initialize all controls
         */
        initializeControls: function() {
            // Text inputs
            document.querySelectorAll('.customize-control-text input').forEach(input => {
                this.initTextControl(input);
            });
            
            // Textarea controls
            document.querySelectorAll('.customize-control-textarea textarea').forEach(textarea => {
                this.initTextareaControl(textarea);
            });
            
            // Color pickers
            document.querySelectorAll('.customize-control-color input[type="color"]').forEach(input => {
                this.initColorControl(input);
            });
            
            // Range sliders
            document.querySelectorAll('.customize-control-range input[type="range"]').forEach(input => {
                this.initRangeControl(input);
            });
            
            // Select dropdowns
            document.querySelectorAll('.customize-control-select select').forEach(select => {
                this.initSelectControl(select);
            });
            
            // Radio buttons
            document.querySelectorAll('.customize-control-radio').forEach(container => {
                this.initRadioControl(container);
            });
            
            // Checkboxes
            document.querySelectorAll('.customize-control-checkbox input[type="checkbox"]').forEach(input => {
                this.initCheckboxControl(input);
            });
            
            // Image upload controls
            document.querySelectorAll('.customize-control-upload').forEach(container => {
                this.initUploadControl(container);
            });
            
            // URL inputs
            document.querySelectorAll('.customize-control-url input').forEach(input => {
                this.initUrlControl(input);
            });
        },
        
        /**
         * Initialize text control
         */
        initTextControl: function(input) {
            const settingId = input.dataset.customizeSettingLink;
            
            // Debounced input handler
            let timeout;
            input.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.updateSetting(settingId, input.value);
                }, 300);
            });
            
            // Immediate update on blur
            input.addEventListener('blur', () => {
                clearTimeout(timeout);
                this.updateSetting(settingId, input.value);
            });
        },
        
        /**
         * Initialize textarea control
         */
        initTextareaControl: function(textarea) {
            const settingId = textarea.dataset.customizeSettingLink;
            
            // Auto-resize textarea
            const autoResize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            };
            
            textarea.addEventListener('input', () => {
                autoResize();
                this.updateSetting(settingId, textarea.value);
            });
            
            // Initial resize
            autoResize();
        },
        
        /**
         * Initialize color control
         */
        initColorControl: function(input) {
            const settingId = input.dataset.customizeSettingLink;
            const container = input.closest('.customize-control-color');
            
            // Create color preview and hex input
            const wrapper = document.createElement('div');
            wrapper.className = 'color-picker-wrapper';
            
            const hexInput = document.createElement('input');
            hexInput.type = 'text';
            hexInput.className = 'color-hex-input';
            hexInput.value = input.value;
            hexInput.pattern = '^#[0-9A-Fa-f]{6}$';
            
            // Sync color picker with hex input
            input.addEventListener('input', () => {
                hexInput.value = input.value;
                this.updateSetting(settingId, input.value);
            });
            
            // Sync hex input with color picker
            hexInput.addEventListener('input', () => {
                if (/^#[0-9A-Fa-f]{6}$/.test(hexInput.value)) {
                    input.value = hexInput.value;
                    this.updateSetting(settingId, hexInput.value);
                }
            });
            
            // Add transparency support if needed
            if (container.dataset.allowTransparency === 'true') {
                const alphaSlider = document.createElement('input');
                alphaSlider.type = 'range';
                alphaSlider.min = '0';
                alphaSlider.max = '100';
                alphaSlider.value = '100';
                alphaSlider.className = 'color-alpha-slider';
                
                alphaSlider.addEventListener('input', () => {
                    const alpha = alphaSlider.value / 100;
                    const color = this.hexToRgba(input.value, alpha);
                    this.updateSetting(settingId, color);
                });
                
                wrapper.appendChild(alphaSlider);
            }
            
            wrapper.appendChild(hexInput);
            input.parentNode.insertBefore(wrapper, input.nextSibling);
        },
        
        /**
         * Initialize range control
         */
        initRangeControl: function(input) {
            const settingId = input.dataset.customizeSettingLink;
            const container = input.closest('.customize-control-range');
            
            // Create value display
            const valueDisplay = document.createElement('span');
            valueDisplay.className = 'range-value-display';
            valueDisplay.textContent = input.value;
            
            // Update display and setting on input
            input.addEventListener('input', () => {
                valueDisplay.textContent = input.value;
                this.updateSetting(settingId, input.value);
                
                // Update slider fill
                const percent = ((input.value - input.min) / (input.max - input.min)) * 100;
                input.style.background = `linear-gradient(to right, #00D9FF ${percent}%, #374151 ${percent}%)`;
            });
            
            // Add reset button if default value exists
            if (input.dataset.defaultValue) {
                const resetBtn = document.createElement('button');
                resetBtn.type = 'button';
                resetBtn.className = 'range-reset-btn';
                resetBtn.textContent = '↺';
                resetBtn.title = 'Reset to default';
                
                resetBtn.addEventListener('click', () => {
                    input.value = input.dataset.defaultValue;
                    input.dispatchEvent(new Event('input'));
                });
                
                container.appendChild(resetBtn);
            }
            
            container.appendChild(valueDisplay);
            
            // Initial fill
            input.dispatchEvent(new Event('input'));
        },
        
        /**
         * Initialize select control
         */
        initSelectControl: function(select) {
            const settingId = select.dataset.customizeSettingLink;
            
            select.addEventListener('change', () => {
                this.updateSetting(settingId, select.value);
            });
            
            // Add search functionality for long lists
            if (select.options.length > 10) {
                this.addSelectSearch(select);
            }
        },
        
        /**
         * Initialize radio control
         */
        initRadioControl: function(container) {
            const radios = container.querySelectorAll('input[type="radio"]');
            const settingId = radios[0]?.dataset.customizeSettingLink;
            
            radios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.checked) {
                        this.updateSetting(settingId, radio.value);
                    }
                });
            });
        },
        
        /**
         * Initialize checkbox control
         */
        initCheckboxControl: function(input) {
            const settingId = input.dataset.customizeSettingLink;
            
            input.addEventListener('change', () => {
                this.updateSetting(settingId, input.checked ? '1' : '');
            });
        },
        
        /**
         * Initialize upload control
         */
        initUploadControl: function(container) {
            const input = container.querySelector('input[type="hidden"]');
            const settingId = input?.dataset.customizeSettingLink;
            const uploadBtn = container.querySelector('.upload-btn');
            const removeBtn = container.querySelector('.remove-btn');
            const preview = container.querySelector('.upload-preview');
            
            if (!input || !uploadBtn) return;
            
            // Upload button click
            uploadBtn.addEventListener('click', () => {
                this.openMediaUploader((url) => {
                    input.value = url;
                    this.updateSetting(settingId, url);
                    this.updateUploadPreview(preview, url);
                    
                    if (removeBtn) {
                        removeBtn.style.display = 'inline-block';
                    }
                });
            });
            
            // Remove button click
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    input.value = '';
                    this.updateSetting(settingId, '');
                    this.updateUploadPreview(preview, '');
                    removeBtn.style.display = 'none';
                });
                
                // Initial state
                if (!input.value) {
                    removeBtn.style.display = 'none';
                }
            }
        },
        
        /**
         * Initialize URL control
         */
        initUrlControl: function(input) {
            const settingId = input.dataset.customizeSettingLink;
            
            // Validate URL on input
            input.addEventListener('input', () => {
                const isValid = this.validateUrl(input.value);
                input.classList.toggle('invalid', !isValid && input.value !== '');
                
                if (isValid || input.value === '') {
                    this.updateSetting(settingId, input.value);
                }
            });
        },
        
        /**
         * Update setting value
         */
        updateSetting: function(settingId, value) {
            if (!settingId) return;
            
            // Mark as dirty
            this.dirty = true;
            document.getElementById('customize-save').classList.add('has-changes');
            
            // Store value
            if (!this.settings[settingId]) {
                this.settings[settingId] = {};
            }
            this.settings[settingId].value = value;
            
            // Send to preview
            this.sendToPreview('customize-setting-change', {
                setting: settingId,
                value: value
            });
        },
        
        /**
         * Send message to preview iframe
         */
        sendToPreview: function(type, data) {
            const iframe = window.parent.document.getElementById('customize-preview-iframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.postMessage({
                    type: type,
                    data: data
                }, '*');
            }
        },
        
        /**
         * Handle messages from preview
         */
        handlePreviewMessage: function(event) {
            if (!event.data || !event.data.type) return;
            
            switch (event.data.type) {
                case 'preview-ready':
                    // Preview is ready, send all current values
                    this.sendAllSettings();
                    break;
                    
                case 'preview-clicked':
                    // Handle clicks in preview to focus related control
                    this.focusControl(event.data.data.settingId);
                    break;
            }
        },
        
        /**
         * Send all settings to preview
         */
        sendAllSettings: function() {
            const allSettings = {};
            
            document.querySelectorAll('[data-customize-setting-link]').forEach(element => {
                const settingId = element.dataset.customizeSettingLink;
                let value;
                
                if (element.type === 'checkbox') {
                    value = element.checked ? '1' : '';
                } else if (element.type === 'radio') {
                    if (element.checked) {
                        value = element.value;
                    }
                } else {
                    value = element.value;
                }
                
                if (value !== undefined) {
                    allSettings[settingId] = { value: value };
                }
            });
            
            this.sendToPreview('customize-settings', allSettings);
        },
        
        /**
         * Focus control by setting ID
         */
        focusControl: function(settingId) {
            const control = document.querySelector(`[data-customize-setting-link="${settingId}"]`);
            if (control) {
                // Scroll to control
                control.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Highlight control
                const container = control.closest('.customize-control');
                if (container) {
                    container.classList.add('highlight');
                    setTimeout(() => {
                        container.classList.remove('highlight');
                    }, 2000);
                }
                
                // Focus input
                control.focus();
            }
        },
        
        /**
         * Setup device preview buttons
         */
        setupDevicePreview: function() {
            const deviceButtons = document.querySelectorAll('.device-preview-btn');
            const preview = document.querySelector('.customize-preview');
            
            deviceButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const device = btn.dataset.device;
                    
                    // Update active button
                    deviceButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    // Update preview class
                    preview.classList.remove('device-desktop', 'device-tablet', 'device-mobile');
                    preview.classList.add(`device-${device}`);
                    
                    // Store preference
                    localStorage.setItem('customize-preview-device', device);
                });
            });
            
            // Restore preference
            const savedDevice = localStorage.getItem('customize-preview-device') || 'desktop';
            document.querySelector(`[data-device="${savedDevice}"]`)?.click();
        },
        
        /**
         * Setup panel navigation
         */
        setupPanelNavigation: function() {
            // Section collapse/expand
            document.querySelectorAll('.customize-section-title').forEach(title => {
                title.addEventListener('click', () => {
                    const section = title.closest('.customize-section');
                    section.classList.toggle('collapsed');
                    
                    // Store state
                    const sectionId = section.dataset.section;
                    const collapsed = section.classList.contains('collapsed');
                    localStorage.setItem(`customize-section-${sectionId}`, collapsed);
                });
                
                // Restore state
                const section = title.closest('.customize-section');
                const sectionId = section.dataset.section;
                const collapsed = localStorage.getItem(`customize-section-${sectionId}`) === 'true';
                if (collapsed) {
                    section.classList.add('collapsed');
                }
            });
        },
        
        /**
         * Save customizer settings
         */
        save: function() {
            const saveBtn = document.getElementById('customize-save');
            if (saveBtn) {
                saveBtn.click();
                this.dirty = false;
            }
        },
        
        /**
         * Close open panels
         */
        closeOpenPanels: function() {
            // Close any open modals or panels
            document.querySelectorAll('.panel.open').forEach(panel => {
                panel.classList.remove('open');
            });
        },
        
        /**
         * Open media uploader (placeholder for actual implementation)
         */
        openMediaUploader: function(callback) {
            // This would integrate with the media library
            // For now, prompt for URL
            const url = prompt('Enter image URL:');
            if (url) {
                callback(url);
            }
        },
        
        /**
         * Update upload preview
         */
        updateUploadPreview: function(preview, url) {
            if (!preview) return;
            
            if (url) {
                preview.innerHTML = `<img src="${url}" alt="Preview">`;
            } else {
                preview.innerHTML = '<span class="no-image">No image selected</span>';
            }
        },
        
        /**
         * Add search to select dropdown
         */
        addSelectSearch: function(select) {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'select-search';
            searchInput.placeholder = 'Search...';
            
            select.parentNode.insertBefore(searchInput, select);
            
            searchInput.addEventListener('input', () => {
                const search = searchInput.value.toLowerCase();
                
                for (let option of select.options) {
                    const text = option.textContent.toLowerCase();
                    option.style.display = text.includes(search) ? '' : 'none';
                }
            });
        },
        
        /**
         * Validate URL
         */
        validateUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },
        
        /**
         * Convert hex to rgba
         */
        hexToRgba: function(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            IsotoneCustomizeControls.init();
        });
    } else {
        IsotoneCustomizeControls.init();
    }
    
    // Expose API for extensions
    window.IsotoneCustomizeControls = IsotoneCustomizeControls;
})();
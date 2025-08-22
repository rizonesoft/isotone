/**
 * Isotone Icon Library - JavaScript Client
 * Fetches icons from PHP backend via AJAX
 * 
 * @package Isotone
 * @version 2.0.0
 */

// Base configuration
const ICON_ENDPOINT = '/iso-admin/ajax-icons.php';

/**
 * Icon Library Base Class
 */
class IconLibrary {
    constructor(style = 'outline') {
        this.style = style;
        this.cache = new Map();
    }

    /**
     * Get an icon SVG by name
     * 
     * @param {string} name - Icon name
     * @param {object} attributes - Optional HTML attributes
     * @returns {Promise<string>} Complete SVG HTML
     */
    async getIcon(name, attributes = {}) {
        const cacheKey = `${this.style}-${name}-${JSON.stringify(attributes)}`;
        
        // Check cache first
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        try {
            const params = new URLSearchParams({
                action: 'get',
                icon: name,
                style: this.style,
                attributes: JSON.stringify(attributes)
            });

            const response = await fetch(`${ICON_ENDPOINT}?${params}`);
            const data = await response.json();
            
            if (data.error) {
                console.error(`Icon error: ${data.error}`);
                return this.getFallbackIcon();
            }
            
            // Cache the result
            this.cache.set(cacheKey, data.svg);
            return data.svg;
        } catch (error) {
            console.error('Failed to fetch icon:', error);
            return this.getFallbackIcon();
        }
    }

    /**
     * Get an icon synchronously (if cached)
     * Falls back to async if not cached
     * 
     * @param {string} name - Icon name
     * @param {object} attributes - Optional HTML attributes
     * @returns {string|Promise<string>} SVG HTML or Promise
     */
    getIconSync(name, attributes = {}) {
        const cacheKey = `${this.style}-${name}-${JSON.stringify(attributes)}`;
        
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }
        
        // Return promise if not cached
        return this.getIcon(name, attributes);
    }

    /**
     * Get just the path element for an icon
     * 
     * @param {string} name - Icon name
     * @returns {Promise<string>} SVG path element(s)
     */
    async getIconPath(name) {
        const cacheKey = `${this.style}-path-${name}`;
        
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        try {
            const params = new URLSearchParams({
                action: 'path',
                icon: name,
                style: this.style
            });

            const response = await fetch(`${ICON_ENDPOINT}?${params}`);
            const data = await response.json();
            
            if (data.error) {
                console.error(`Icon path error: ${data.error}`);
                return '';
            }
            
            this.cache.set(cacheKey, data.path);
            return data.path;
        } catch (error) {
            console.error('Failed to fetch icon path:', error);
            return '';
        }
    }

    /**
     * Check if an icon exists
     * 
     * @param {string} name - Icon name
     * @returns {Promise<boolean>}
     */
    async hasIcon(name) {
        try {
            const params = new URLSearchParams({
                action: 'exists',
                icon: name,
                style: this.style
            });

            const response = await fetch(`${ICON_ENDPOINT}?${params}`);
            const data = await response.json();
            return data.exists || false;
        } catch (error) {
            console.error('Failed to check icon existence:', error);
            return false;
        }
    }

    /**
     * Get all available icon names
     * 
     * @returns {Promise<array>} List of icon names
     */
    async getIconNames() {
        const cacheKey = `${this.style}-names`;
        
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        try {
            const params = new URLSearchParams({
                action: 'list',
                style: this.style
            });

            const response = await fetch(`${ICON_ENDPOINT}?${params}`);
            const data = await response.json();
            
            if (data.error) {
                console.error(`Icon list error: ${data.error}`);
                return [];
            }
            
            this.cache.set(cacheKey, data.icons);
            return data.icons || [];
        } catch (error) {
            console.error('Failed to fetch icon names:', error);
            return [];
        }
    }

    /**
     * Search icons by keyword
     * 
     * @param {string} keyword - Search term
     * @returns {Promise<array>} Matching icon names
     */
    async searchIcons(keyword) {
        try {
            const params = new URLSearchParams({
                action: 'search',
                keyword: keyword,
                style: this.style
            });

            const response = await fetch(`${ICON_ENDPOINT}?${params}`);
            const data = await response.json();
            
            if (data.error) {
                console.error(`Icon search error: ${data.error}`);
                return [];
            }
            
            return data.results || [];
        } catch (error) {
            console.error('Failed to search icons:', error);
            return [];
        }
    }

    /**
     * Preload commonly used icons
     * 
     * @param {array} iconNames - Array of icon names to preload
     */
    async preloadIcons(iconNames) {
        const promises = iconNames.map(name => this.getIcon(name));
        await Promise.all(promises);
    }

    /**
     * Clear the icon cache
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Get fallback icon for errors
     * 
     * @returns {string} Fallback SVG
     */
    getFallbackIcon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>';
    }

    /**
     * Insert icon into DOM element
     * 
     * @param {string|HTMLElement} selector - CSS selector or element
     * @param {string} iconName - Icon name
     * @param {object} attributes - Optional attributes
     */
    async insertIcon(selector, iconName, attributes = {}) {
        const element = typeof selector === 'string' 
            ? document.querySelector(selector) 
            : selector;
            
        if (!element) {
            console.error('Element not found:', selector);
            return;
        }

        const svg = await this.getIcon(iconName, attributes);
        element.innerHTML = svg;
    }

    /**
     * Replace placeholder elements with icons
     * Looks for elements with data-icon attribute
     */
    async replacePlaceholders() {
        const elements = document.querySelectorAll(`[data-icon-${this.style}]`);
        
        for (const element of elements) {
            const iconName = element.getAttribute(`data-icon-${this.style}`);
            if (iconName) {
                // Get any data attributes for the icon
                const attributes = {};
                for (const attr of element.attributes) {
                    if (attr.name.startsWith('data-icon-attr-')) {
                        const attrName = attr.name.replace('data-icon-attr-', '');
                        attributes[attrName] = attr.value;
                    }
                }
                
                await this.insertIcon(element, iconName, attributes);
            }
        }
    }
}

// Create global instances for each icon style
window.IsotoneIcons = new IconLibrary('outline');
window.IsotoneIconsSolid = new IconLibrary('solid');
window.IsotoneIconsMicro = new IconLibrary('micro');

// Auto-replace placeholders on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        IsotoneIcons.replacePlaceholders();
        IsotoneIconsSolid.replacePlaceholders();
        IsotoneIconsMicro.replacePlaceholders();
    });
} else {
    // DOM already loaded
    IsotoneIcons.replacePlaceholders();
    IsotoneIconsSolid.replacePlaceholders();
    IsotoneIconsMicro.replacePlaceholders();
}

// Backward compatibility - provide synchronous-like interface
// Note: These will return promises if not cached
window.IsotoneIcons.getIcon = function(name, attributes) {
    return this.getIconSync(name, attributes);
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        IconLibrary,
        IsotoneIcons: window.IsotoneIcons,
        IsotoneIconsSolid: window.IsotoneIconsSolid,
        IsotoneIconsMicro: window.IsotoneIconsMicro
    };
}
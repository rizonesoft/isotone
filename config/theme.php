<?php
/**
 * Isotone CMS Theme Configuration
 * 
 * Defines the brand colors and visual identity for Isotone CMS
 */

return [
    /**
     * Brand Colors - Modern dark theme with electric accents
     * Unique color scheme avoiding typical AI purple gradients
     */
    'colors' => [
        // Primary Colors
        'primary' => '#0A0E27',       // Deep Space Blue - Main brand color
        'primary-dark' => '#050714',  // Darker Space Blue
        'primary-light' => '#141B3C', // Lighter Space Blue
        
        // Accent Colors
        'accent' => '#00D9FF',        // Electric Cyan - Primary accent
        'accent-green' => '#00FF88',  // Neon Green - Secondary accent
        'accent-pink' => '#FF3366',   // Hot Pink - Error/warning accent
        
        // Neutral Colors
        'background' => '#0A0E27',    // Deep Space Blue
        'surface' => 'rgba(255, 255, 255, 0.03)', // Glass surface
        'surface-hover' => 'rgba(255, 255, 255, 0.08)', // Glass hover
        'text-primary' => '#FFFFFF',  // White
        'text-secondary' => 'rgba(255, 255, 255, 0.7)', // Semi-transparent white
        'text-muted' => 'rgba(255, 255, 255, 0.5)', // Muted white
        'border' => 'rgba(255, 255, 255, 0.1)', // Light border
        
        // Status Colors
        'success' => '#00FF88',       // Neon Green
        'warning' => '#FFB800',       // Amber
        'danger' => '#FF3366',        // Hot Pink
        'info' => '#00D9FF',          // Electric Cyan
    ],
    
    /**
     * Gradients for backgrounds and buttons
     */
    'gradients' => [
        'primary' => 'linear-gradient(135deg, #00D9FF 0%, #00FF88 100%)',
        'secondary' => 'linear-gradient(135deg, #FF3366 0%, #00D9FF 100%)',
        'background' => 'radial-gradient(circle at 25% 25%, #00D9FF15 0%, transparent 50%)',
        'surface' => 'linear-gradient(135deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%)',
        'success' => 'linear-gradient(135deg, #00FF88 0%, #00D9FF 100%)',
        'warning' => 'linear-gradient(135deg, #FFB800 0%, #FF3366 100%)',
    ],
    
    /**
     * Typography
     */
    'typography' => [
        'font-family' => '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        'font-family-mono' => '"Consolas", "Monaco", "Courier New", monospace',
        'font-size-base' => '16px',
        'line-height-base' => '1.6',
        'letter-spacing' => '0.02em',
    ],
    
    /**
     * Spacing
     */
    'spacing' => [
        'xs' => '0.25rem',  // 4px
        'sm' => '0.5rem',   // 8px
        'md' => '1rem',     // 16px
        'lg' => '1.5rem',   // 24px
        'xl' => '2rem',     // 32px
        'xxl' => '3rem',    // 48px
    ],
    
    /**
     * Border Radius
     */
    'radius' => [
        'sm' => '6px',
        'md' => '12px',
        'lg' => '16px',
        'xl' => '24px',
        'full' => '9999px',
    ],
    
    /**
     * Shadows (with glow effects)
     */
    'shadows' => [
        'sm' => '0 2px 4px rgba(0, 0, 0, 0.2)',
        'md' => '0 4px 20px rgba(0, 0, 0, 0.3)',
        'lg' => '0 10px 40px rgba(0, 0, 0, 0.5)',
        'xl' => '0 20px 60px rgba(0, 0, 0, 0.7)',
        'glow-cyan' => '0 0 30px rgba(0, 217, 255, 0.4)',
        'glow-green' => '0 0 30px rgba(0, 255, 136, 0.4)',
        'glow-pink' => '0 0 30px rgba(255, 51, 102, 0.4)',
    ],
    
    /**
     * Brand Identity
     */
    'brand' => [
        'name' => 'Isotone CMS',
        'tagline' => 'Lightweight. Powerful. Everywhere.',
        'logo_svg' => '/assets/logo.svg',  // SVG logo path
        'favicon' => '/favicon.png',  // Favicon path
        'favicon_ico' => '/favicon.ico',  // ICO favicon for legacy support
        'apple_touch_icon' => '/apple-touch-icon.png',  // iOS home screen icon
        'description' => 'A modern, high-performance CMS built for the future',
    ],
    
    /**
     * CSS Variables Export
     * These will be available as CSS custom properties
     */
    'css_variables' => true,
    
    /**
     * Theme Mode
     */
    'mode' => 'dark', // 'light', 'dark', or 'auto'
];
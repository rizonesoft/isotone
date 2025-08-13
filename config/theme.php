<?php
/**
 * Isotone CMS Theme Configuration
 * 
 * Defines the brand colors and visual identity for Isotone CMS
 */

return [
    /**
     * Brand Colors - Nature-inspired palette
     * Moving away from the overused purple gradients to a unique green theme
     */
    'colors' => [
        // Primary Colors
        'primary' => '#2C5F2D',       // Forest Green - Main brand color
        'primary-dark' => '#1A3A1A',  // Deep Forest - Darker variant
        'primary-light' => '#4A7C59', // Sea Green - Lighter variant
        
        // Secondary Colors
        'secondary' => '#97BC62',     // Fresh Lime - Accent color
        'secondary-dark' => '#7FA050', // Darker Lime
        'secondary-light' => '#B2D083', // Lighter Lime
        
        // Neutral Colors
        'background' => '#FFFFFF',    // White
        'surface' => '#E8F5E9',       // Mint Cream - Light green tint
        'text-primary' => '#333333',  // Dark Gray
        'text-secondary' => '#666666', // Medium Gray
        'border' => 'rgba(151, 188, 98, 0.2)', // Light green border
        
        // Status Colors
        'success' => '#4CAF50',       // Success Green
        'warning' => '#FF9800',       // Amber
        'danger' => '#F44336',        // Red
        'info' => '#2196F3',          // Blue
    ],
    
    /**
     * Gradients for backgrounds and buttons
     */
    'gradients' => [
        'primary' => 'linear-gradient(135deg, #2C5F2D 0%, #4A7C59 100%)',
        'secondary' => 'linear-gradient(135deg, #4A7C59 0%, #97BC62 100%)',
        'background' => 'linear-gradient(135deg, #2C5F2D 0%, #4A7C59 50%, #97BC62 100%)',
        'surface' => 'linear-gradient(135deg, #E8F5E9 0%, #F1F8F1 100%)',
        'success' => 'linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%)',
        'warning' => 'linear-gradient(135deg, #FF9800 0%, #FFB74D 100%)',
    ],
    
    /**
     * Typography
     */
    'typography' => [
        'font-family' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        'font-family-mono' => '"Courier New", Consolas, "Liberation Mono", monospace',
        'font-size-base' => '16px',
        'line-height-base' => '1.5',
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
        'sm' => '4px',
        'md' => '8px',
        'lg' => '12px',
        'xl' => '16px',
        'full' => '9999px',
    ],
    
    /**
     * Shadows
     */
    'shadows' => [
        'sm' => '0 2px 4px rgba(44, 95, 45, 0.1)',
        'md' => '0 4px 15px rgba(44, 95, 45, 0.2)',
        'lg' => '0 10px 40px rgba(44, 95, 45, 0.2)',
        'xl' => '0 20px 60px rgba(44, 95, 45, 0.3)',
    ],
    
    /**
     * Brand Identity
     */
    'brand' => [
        'name' => 'Isotone CMS',
        'tagline' => 'Lightweight. Powerful. Everywhere.',
        'logo_emoji' => '🌿',  // Leaf emoji for nature theme
        'description' => 'A nature-inspired CMS that grows with your needs',
    ],
    
    /**
     * CSS Variables Export
     * These will be available as CSS custom properties
     */
    'css_variables' => true,
    
    /**
     * Theme Mode
     */
    'mode' => 'light', // 'light', 'dark', or 'auto'
];
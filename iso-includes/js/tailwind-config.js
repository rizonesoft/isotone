/**
 * Tailwind CSS Configuration for Isotone
 * Global Tailwind configuration used across admin, themes, and plugins
 * This uses the Tailwind CSS Play CDN for development
 * For production, consider using a build process
 * 
 * @package Isotone
 * @version 1.0.0
 */

// Custom Tailwind configuration
tailwind.config = {
    darkMode: 'class', // Enable dark mode with class strategy
    theme: {
        extend: {
            colors: {
                cyan: {
                    400: '#00D9FF',
                    500: '#00B8D9',
                    600: '#0097B2',
                    700: '#007A8C'
                },
                green: {
                    400: '#00FF88',
                    500: '#00E676',
                    600: '#00C853',
                    700: '#00A844'
                },
                gray: {
                    700: '#1F2937',
                    800: '#111827',
                    900: '#0F1419'
                }
            }
        }
    }
}
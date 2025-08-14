/**
 * Tailwind CSS Configuration for Isotone Admin
 * This uses the Tailwind CSS Play CDN for development
 * For production, consider using a build process
 */

// Custom Tailwind configuration
tailwind.config = {
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
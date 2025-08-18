// Tailwind CSS Configuration for Isotone Admin
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                cyan: {
                    50: '#ecfeff',
                    100: '#cffafe',
                    200: '#a5f3fc',
                    300: '#67e8f9',
                    400: '#00d9ff',
                    500: '#00bcd4',
                    600: '#0097a7',
                    700: '#00838f',
                    800: '#006064',
                    900: '#004d40',
                },
            },
            animation: {
                'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
        },
    },
}
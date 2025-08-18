# Quantum Theme for Isotone

A stunning premium theme featuring glass morphism design, interactive particle effects, smooth animations, and modern UI/UX patterns.

## Features

- **Glass Morphism Effects**: Beautiful frosted glass cards and surfaces with customizable blur intensity
- **Interactive Particle System**: Animated particles that react to mouse movement and clicks
- **Smooth Animations**: Shimmer effects on buttons, fade-in animations, and smooth transitions
- **Dark Elegant Design**: Professional dark theme with accent color highlights
- **Responsive Navigation**: Sticky header with glass effect and mobile-friendly menu
- **Performance Optimized**: Efficient animations with hardware acceleration
- **Interactive Elements**: Mouse-reactive particles, hover effects, and scroll animations

## Installation

1. Upload the `quantum` folder to `/iso-content/themes/`
2. Navigate to the Isotone admin panel
3. Go to Appearance > Themes
4. Activate the Quantum theme

## Customization

### Theme Options

Configure these options in the theme customizer:

- **Primary Color**: Main brand color (#00D9FF default)
- **Secondary Color**: Accent color for gradients (#FF00D4 default)
- **Accent Color**: Highlight color (#00FF88 default)
- **Glass Blur Intensity**: Adjust the frosted glass effect strength
- **Particle Effects**: Enable/disable animated particles
- **Interactive Particles**: Toggle mouse interaction with particles
- **Animation Speed**: Slow, Normal, or Fast
- **Dark Mode**: Auto, Light, or Dark

### CSS Variables

```css
:root {
    --primary: #00D9FF;
    --secondary: #FF00D4;
    --accent: #00FF88;
    --glass-blur: 20px;
    --glass-opacity: 0.1;
    --header-height: 70px;
    --glow-color: rgba(0, 217, 255, 0.6);
    --particle-color: rgba(0, 217, 255, 0.3);
}
```

## Key Features

### Interactive Particles
- Multiple particle types with different animations
- Mouse repulsion effect within 150px radius
- Click ripple effect that pushes particles
- Glow effect on nearby particles
- Smooth, slow animations for a calm atmosphere

### Glass Morphism
- Backdrop blur effects on cards and surfaces
- Subtle transparency with white tints
- Soft shadows and borders
- Enhanced depth perception

### Button Effects
- Shimmer animation on hover
- Subtle lift effect
- Glow shadows
- Glass morphism style for secondary buttons

### Navigation
- Sticky header with glass effect
- Smooth scroll anchoring
- Mobile-responsive hamburger menu
- GitHub integration button

## Browser Support

- Chrome 76+
- Firefox 78+
- Safari 14+
- Edge 79+

## Requirements

- Isotone 0.2.0 or higher
- PHP 7.4 or higher
- Modern browser with CSS backdrop-filter support

## Performance Notes

- Particles are contained to hero section only for better performance
- Animations use GPU acceleration where possible
- Lazy loading for images
- Optimized for 60fps animations

## License

MIT License - Free to use and modify for your projects.

## Credits

Created by the Isotone Team as a premium theme showcasing modern web design trends including glass morphism, particle effects, and interactive animations.

## Support

For support and questions, visit: https://isotone.dev/support
Report issues at: https://github.com/isotone/themes
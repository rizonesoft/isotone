# Lumina UI v1.0 - Design System

## Overview
Lumina UI is Isotone's modern design system introduced in v0.3.2-alpha. Named after the Latin word for "light", it embodies the luminous, glowing aesthetic that defines the interface through glassmorphism effects and radiant depth enhancements.

## Version Information
- **Codename:** Lumina
- **Version:** 1.0
- **Released:** August 23, 2025
- **Primary Accent:** Cyan (#06b6d4)
- **Etymology:** From Latin "lūmina" meaning "lights" - reflecting the glowing, translucent nature of the design

## Design Principles

### 1. Glassmorphism
- Semi-transparent backgrounds with blur effects
- Gradient overlays for depth
- Subtle borders for definition

### 2. Depth & Hierarchy
- Bottom glow lines with gradient fade
- 1px hover lifts (no floating effects)
- Layered shadows for elevation

### 3. Professional Restraint
- Minimal animations (300ms transitions)
- Subtle color changes on interaction
- No excessive effects or movements

## Core Components

### Cards
```php
<div class="content-card">
    <div class="content-card-header">
        <h2>Title</h2>
    </div>
    <div class="content-card-body">
        Content
    </div>
</div>
```

**Classes:**
- `content-card` - Standard content container
- `tab-card` - Tabbed content container
- `form-card` - Form container
- `info-card` - Status/metric display cards

### Info Cards
```php
<div class="info-card info-cyan">
    <div class="info-card-content">
        <div class="info-card-icon">
            <?php echo iso_get_icon('icon-name', 'micro', [], false); ?>
        </div>
        <div class="info-card-body">
            <p class="info-card-label">Label</p>
            <p class="info-card-value">Value</p>
        </div>
    </div>
</div>
```

**Color Variants:**
- `info-red` - Error/critical states
- `info-yellow` - Warning states
- `info-blue` - Information
- `info-green` - Success/positive
- `info-purple` - Special/premium
- `info-cyan` - Default/primary

### Card Headers
```php
<div class="content-card-header">
    <h2>
        <span class="content-card-header-icon">
            <?php echo iso_get_icon('chart-bar', 'micro', [], false); ?>
        </span>
        Section Title
    </h2>
    <div class="content-card-header-actions">
        <span class="content-card-header-badge">12</span>
        <button class="content-card-header-action">
            <?php echo iso_get_icon('arrow-path', 'micro', [], false); ?>
        </button>
    </div>
</div>
```

## Visual Effects

### Bottom Glow Line
Applied to all cards for depth:
```css
/* Gradient line */
::before {
    background: linear-gradient(90deg, 
        transparent 0%, 
        rgba(6, 182, 212, 0.8) 20%,
        rgba(6, 182, 212, 0.8) 50%,
        rgba(6, 182, 212, 0.8) 80%,
        transparent 100%);
}

/* Glow spread */
::after {
    background: radial-gradient(ellipse at center, 
        rgba(6, 182, 212, 0.4) 0%, 
        transparent 70%);
}
```

### Hover States
- Cards: 1px upward translation
- Icons: 5% scale with slight opacity fade
- Buttons: Color shift to cyan accent
- Glow lines: Expand from 60% to 80% width

## Icon System

### Icon Styles
- **Micro (16x16)** - UI elements, buttons, badges
- **Outline (24x24)** - Navigation, empty states
- **Solid (24x24)** - Active states, emphasis

### Usage
```php
// Inline SVG (recommended for Aurora UI)
<?php echo iso_get_icon('icon-name', 'micro', [], false); ?>

// With preloading
iso_preload_icons([
    ['name' => 'cog', 'style' => 'micro'],
    ['name' => 'users', 'style' => 'micro']
]);
```

## Color Palette

### Primary
- Cyan: #06b6d4 (accent)
- Dark Cyan: #0891b2 (hover)
- Light Cyan: #22d3ee (dark mode)

### Grays
- Gray-900: #111827 (text)
- Gray-800: #1f2937 (dark bg)
- Gray-700: #374151 (borders)
- Gray-600: #4b5563 (muted)
- Gray-400: #9ca3af (disabled)
- Gray-200: #e5e7eb (light borders)
- Gray-50: #f9fafb (light bg)

### Status Colors
- Red: #ef4444 (error)
- Yellow: #fbbf24 (warning)
- Green: #34d399 (success)
- Blue: #60a5fa (info)
- Purple: #a78bfa (special)

## CSS Architecture

### File Structure
```
/iso-admin/css/
├── admin-components.css  # Aurora UI components
├── tailwind.css          # Tailwind utilities
└── tailwind.min.css      # Minified production
```

### Component Sections
1. Chart Components
2. Message Components
3. Alert Components
4. Animations
5. Metric Cards
6. Info Cards
7. Content Cards
8. Tab Components
9. Form Components
10. Table Components
11. Empty States
12. Utility Classes

## Implementation Rules

### 1. No Inline Styles
```php
// ❌ Wrong
<div style="color: red;">

// ✅ Correct
<div class="text-red-600">
```

### 2. Use Component Classes
```php
// ❌ Wrong
<div class="bg-white rounded-lg shadow">

// ✅ Correct
<div class="content-card">
```

### 3. Icon API Only
```php
// ❌ Wrong
<svg>...</svg>

// ✅ Correct
<?php echo iso_get_icon('name', 'style', [], false); ?>
```

### 4. Preload Icons
```php
// Always preload at page start
iso_preload_icons([
    ['name' => 'icon1', 'style' => 'micro'],
    ['name' => 'icon2', 'style' => 'micro']
]);
```

## Migration Guide

### Converting Existing Pages to Lumina UI

1. **Replace generic cards:**
```php
// Old
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">

// Lumina UI
<div class="content-card">
```

2. **Update headers:**
```php
// Old
<div class="p-6 border-b">
    <h2>Title</h2>
</div>

// Lumina UI
<div class="content-card-header">
    <h2>Title</h2>
</div>
```

3. **Add icons to headers:**
```php
<h2>
    <span class="content-card-header-icon">
        <?php echo iso_get_icon('icon', 'micro', [], false); ?>
    </span>
    Title
</h2>
```

4. **Convert status cards:**
```php
// Old metric card
<div class="metric-card">

// Lumina UI info card
<div class="info-card info-cyan">
```

## Best Practices

1. **Consistent Spacing**
   - Page sections: `mb-8`
   - Card padding: Use component classes
   - Grid gaps: `gap-4` or `gap-6`

2. **Icon Usage**
   - Micro for UI elements
   - Outline for content
   - Always preload

3. **Color Usage**
   - Cyan for primary actions
   - Gray for secondary
   - Status colors for feedback

4. **Animation**
   - 300ms transitions
   - Cubic bezier easing
   - Subtle transforms only

## Template Reference
See `/iso-admin/templates/admin-page-template.php` for a complete Lumina UI implementation example.

## Component Showcase
View all Lumina UI components at `/iso-admin/templates/admin-components-showcase.php`
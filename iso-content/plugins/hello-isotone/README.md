# Hello Isotone Plugin

A simple example plugin demonstrating the Isotone CMS plugin API.

## Features

This example plugin demonstrates:

- Plugin activation/deactivation hooks
- Adding admin menu items
- Creating admin pages with forms
- Using Isotone's CSS classes
- Adding dashboard widgets
- Creating shortcodes
- Hooking into WordPress-style actions
- Managing plugin options

## Installation

1. This plugin comes pre-installed with Isotone CMS
2. Activate it from the Plugins admin page (when available)

## Usage

### Shortcode
Use the `[hello_isotone]` shortcode in your content:

```
[hello_isotone name="World"]
```

### Admin Interface
Access the plugin settings at Admin → Hello Isotone

### Hooks
The plugin provides these hooks for extension:
- `hello_isotone_loaded` - Fired when plugin is loaded

## Development Guide

This plugin serves as a template for developing your own Isotone plugins. Key concepts:

1. **Structure**: Each plugin has its own folder in `/iso-content/plugins/`
2. **Metadata**: Define plugin info in `plugin.json`
3. **Main File**: Primary PHP file specified in metadata
4. **Hooks**: Use WordPress-style action/filter system
5. **Styling**: Use Isotone's modular CSS classes

## API Functions Used

- `add_action()` - Hook into system events
- `add_menu_page()` - Add admin menu items
- `add_option()` / `get_option()` - Manage settings
- `add_shortcode()` - Register shortcodes
- `esc_html()` - Security escaping
- `do_action()` - Create custom hooks

## License

MIT License - Use as a starting point for your own plugins!
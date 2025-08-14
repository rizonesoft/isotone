# Isotone Content Directory

This directory contains all user-generated and installable content for Isotone.

## Directory Structure

```
iso-content/
├── plugins/           # Installed plugins
│   ├── hello-isotone/ # Default example plugin
│   └── [user plugins] # User-installed plugins
├── themes/            # Installed themes  
│   ├── isotone-default/ # Default theme
│   └── [user themes]  # User-installed themes
└── uploads/           # Media uploads (ignored by git)
```

**Note:** Cache and logs have been moved to `/iso-runtime/` for better separation of concerns.

## Default Content

The following items are included with Isotone by default:

### Themes
- **isotone-default** - The default Isotone theme with glassmorphism design

### Plugins
- **hello-isotone** - Example plugin demonstrating the plugin API

## Development Note

During development, all plugins and themes are tracked in version control. This allows:
- Easy sharing of development themes/plugins
- Collaborative plugin/theme development  
- Testing and examples to be included

For production deployments, you may want to add specific plugins/themes to your own .gitignore file.

## Important Notes

1. **Don't modify default content directly** - Updates will overwrite your changes
2. **Create child themes** to customize the default theme
3. **Use the example plugin** as a template for your own plugins
4. **Backup your custom content** - User-installed items aren't in version control

## Security

Each subdirectory contains an `index.html` file to prevent directory listing. Do not remove these files.
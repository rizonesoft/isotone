<?php
/**
 * Icon Gallery Command
 * 
 * CLI command to generate the icon gallery documentation
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Commands;

class IconGalleryCommand
{
    /**
     * Execute the command
     * 
     * @param array $args Command arguments
     * @return int Exit code
     */
    public function execute(array $args = []): int
    {
        echo "🎨 Isotone Icon Gallery Generator\n";
        echo "==================================\n\n";
        
        // Load the generator
        $generatorPath = dirname(dirname(__DIR__)) . '/src/Generators/IconGalleryGenerator.php';
        
        if (!file_exists($generatorPath)) {
            echo "❌ Error: Icon gallery generator not found at:\n";
            echo "   $generatorPath\n";
            return 1;
        }
        
        require_once $generatorPath;
        
        // Check if icon library exists
        $iconLibraryPath = dirname(dirname(dirname(__DIR__))) . '/iso-core/Core/IconLibrary.php';
        if (!file_exists($iconLibraryPath)) {
            echo "❌ Error: Icon library not found at:\n";
            echo "   $iconLibraryPath\n";
            return 1;
        }
        
        // Generate the gallery
        try {
            $generator = new \IconGalleryGenerator();
            $result = $generator->generate();
            
            if ($result) {
                echo "\n";
                echo "==================================\n";
                echo "✅ Icon gallery generated successfully!\n";
                echo "📄 Open docs/icon-gallery.html in your browser to view.\n";
                return 0;
            } else {
                echo "\n❌ Failed to generate icon gallery.\n";
                return 1;
            }
        } catch (\Exception $e) {
            echo "\n❌ Error: " . $e->getMessage() . "\n";
            return 1;
        }
    }
    
    /**
     * Get command help text
     * 
     * @return string
     */
    public static function getHelp(): string
    {
        return <<<HELP
Icon Gallery Generator

Generates an HTML documentation page showing all available icons
in the Isotone icon library.

Usage:
  php isotone icons:gallery

The generated file will be saved to:
  /docs/icon-gallery.html

This gallery includes:
  - All 200+ Heroicons organized by category
  - Search functionality
  - Click-to-copy icon names
  - Visual preview of each icon
  - Usage statistics

HELP;
    }
}
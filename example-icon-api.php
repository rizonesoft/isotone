<?php
/**
 * Example: Using the Isotone Icon API
 * 
 * This file demonstrates how to use the new Icon API for efficient icon loading
 */

// Include the icon helper functions
require_once 'iso-includes/icon-functions.php';

// Preload some commonly used icons
iso_preload_icons([
    ['name' => 'home', 'style' => 'outline'],
    ['name' => 'user', 'style' => 'solid'],
    ['name' => 'cog', 'style' => 'micro']
]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isotone Icon API Example</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 2rem; 
            line-height: 1.6;
        }
        .example { 
            background: #f5f5f5; 
            padding: 1rem; 
            margin: 1rem 0; 
            border-radius: 4px; 
        }
        .icon-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); 
            gap: 1rem; 
            margin: 1rem 0; 
        }
        .icon-item { 
            text-align: center; 
            padding: 1rem; 
            background: white; 
            border-radius: 4px; 
        }
        .performance-info {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <h1>Isotone Icon API Examples</h1>
    
    <div class="performance-info">
        <strong>Performance Note:</strong> This page only loads the icons you see, not entire icon libraries. 
        Icons are cached for 1 year and use ETags for efficient browser caching.
    </div>

    <h2>1. Basic Icon Usage</h2>
    <div class="example">
        <h3>Lazy Loading (Recommended)</h3>
        <p>Icons load as needed with better performance:</p>
        <?php iso_icon('home'); ?> Home
        <?php iso_icon('user', 'solid'); ?> User (solid)
        <?php iso_icon('cog', 'micro'); ?> Settings (micro)
    </div>

    <h2>2. Icon Styles</h2>
    <div class="icon-grid">
        <div class="icon-item">
            <h4>Outline</h4>
            <?php iso_icon_outline('heart', ['width' => '32', 'height' => '32']); ?>
            <br>24x24 with stroke
        </div>
        <div class="icon-item">
            <h4>Solid</h4>
            <?php iso_icon_solid('heart', ['width' => '32', 'height' => '32']); ?>
            <br>24x24 filled
        </div>
        <div class="icon-item">
            <h4>Micro</h4>
            <?php iso_icon_micro('heart', ['width' => '32', 'height' => '32']); ?>
            <br>16x16 small
        </div>
    </div>

    <h2>3. Icon Buttons</h2>
    <div class="example">
        <?php 
        echo iso_icon_button('plus', 'Add Item', [
            'class' => 'btn btn-primary',
            'style' => 'background: #3B82F6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; margin: 0.25rem;'
        ]);

        echo iso_icon_button('trash', 'Delete', [
            'class' => 'btn btn-danger',
            'style' => 'background: #EF4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; margin: 0.25rem;'
        ]);

        echo iso_icon_button('download', 'Download', [
            'class' => 'btn btn-success',
            'style' => 'background: #10B981; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; margin: 0.25rem;'
        ]);
        ?>
    </div>

    <h2>4. Icon Links</h2>
    <div class="example">
        <?php 
        echo iso_icon_link('external-link', 'Visit Documentation', '#', [
            'style' => 'color: #3B82F6; text-decoration: none; margin-right: 1rem;'
        ]);

        echo iso_icon_link('envelope', 'Send Email', '#', [
            'style' => 'color: #10B981; text-decoration: none; margin-right: 1rem;'
        ]);

        echo iso_icon_link('share', 'Share', '#', [
            'style' => 'color: #8B5CF6; text-decoration: none;'
        ]);
        ?>
    </div>

    <h2>5. Custom Styling</h2>
    <div class="example">
        <p>Blue icon: <?php iso_icon('star', 'solid', ['style' => 'color: #3B82F6; width: 24px; height: 24px;']); ?></p>
        <p>Large green icon: <?php iso_icon('check-circle', 'solid', ['style' => 'color: #10B981; width: 48px; height: 48px;']); ?></p>
        <p>Small red icon: <?php iso_icon('x-circle', 'solid', ['style' => 'color: #EF4444; width: 16px; height: 16px;']); ?></p>
    </div>

    <h2>6. Direct API URLs</h2>
    <div class="example">
        <p>You can also use direct URLs for custom implementations:</p>
        <ul>
            <li><a href="<?php echo iso_get_icon_url('home', 'outline', ['size' => 32]); ?>" target="_blank">Home icon (32px)</a></li>
            <li><a href="<?php echo iso_get_icon_url('user', 'solid', ['size' => 24, 'color' => 'blue']); ?>" target="_blank">User icon (blue)</a></li>
            <li><a href="<?php echo iso_get_icon_url('cog', 'micro', ['size' => 16]); ?>" target="_blank">Cog icon (micro)</a></li>
        </ul>
        <p><strong>API Structure:</strong> <code>/iso-api/icons.php?name={icon}&style={style}&size={size}</code></p>
    </div>

    <h2>7. Performance Comparison</h2>
    <div class="example">
        <h3>Old Method (Full Library)</h3>
        <p>❌ Loads entire icon library (~300-500KB)<br>
        ❌ All icons parsed even if unused<br>
        ❌ Higher memory usage</p>
        
        <h3>New Method (Icon API)</h3>
        <p>✅ Loads only requested icons (~1-2KB each)<br>
        ✅ Lazy loading support<br>
        ✅ Browser caching with ETags<br>
        ✅ Memory efficient</p>
    </div>

    <h2>8. Available Icons</h2>
    <div class="example">
        <p>All 316 Heroicons v2.1.5 icons are available in three styles:</p>
        <div class="icon-grid">
            <?php 
            $sampleIcons = ['home', 'user', 'cog', 'heart', 'star', 'bell', 'envelope', 'chat'];
            foreach ($sampleIcons as $icon): 
            ?>
            <div class="icon-item">
                <?php iso_icon($icon, 'outline', ['width' => '24', 'height' => '24']); ?>
                <br><small><?php echo $icon; ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <p><a href="/docs/icons/icon-preview.html">→ View all available icons</a></p>
    </div>

    <script>
        console.log('📊 Performance: This page only loaded the specific icons displayed, not entire libraries!');
    </script>
</body>
</html>
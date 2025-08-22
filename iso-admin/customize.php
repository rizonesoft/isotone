<?php
/**
 * Theme Customizer Page
 * 
 * Provides live preview customization interface with WordPress-style panel navigation
 * 
 * @package Isotone
 */

require_once 'auth.php';
requireRole('admin');

require_once dirname(__DIR__) . '/iso-includes/class-security.php';
require_once dirname(__DIR__) . '/iso-core/Core/Customizer.php';
require_once dirname(__DIR__) . '/iso-core/Core/CustomizerControl.php';
require_once dirname(__DIR__) . '/iso-core/Core/CustomizerSection.php';
require_once dirname(__DIR__) . '/iso-core/Core/CustomizerPanel.php';
require_once dirname(__DIR__) . '/iso-core/Core/ThemeAPI.php';
require_once dirname(__DIR__) . '/iso-core/Core/IconLibrary.php';
require_once dirname(__DIR__) . '/iso-core/theme-functions.php';

use Isotone\Core\Customizer;
use Isotone\Core\ThemeAPI;

$customizer = Customizer::getInstance();
$themeAPI = ThemeAPI::getInstance();

// Load active theme's functions.php to register customizer settings
$activeTheme = $themeAPI->currentTheme;
if ($activeTheme) {
    $themeFunctionsFile = dirname(__DIR__) . '/iso-content/themes/' . $activeTheme['slug'] . '/functions.php';
    if (file_exists($themeFunctionsFile)) {
        require_once $themeFunctionsFile;
    }
}

if (!$customizer->canCustomize()) {
    die('You do not have permission to customize this site.');
}

// Handle save action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    if (!iso_verify_csrf()) {
        die(json_encode(['success' => false, 'message' => 'Invalid security token']));
    }
    
    $values = $_POST['customized'] ?? [];
    $saved = $customizer->save($values);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'saved' => $saved]);
    exit;
}

$preview_url = isset($_GET['url']) ? $_GET['url'] : home_url();
$return_url = isset($_GET['return']) ? $_GET['return'] : '/isotone/iso-admin/themes.php';

$sections = $customizer->getSections();
$settings = $customizer->getSettings();
$controls = $customizer->getControls();
$preview_values = $customizer->getPreviewValues();

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize - Isotone</title>
    
    <!-- Tailwind CSS -->
    <?php if (file_exists(__DIR__ . '/css/tailwind.css')): ?>
        <link rel="stylesheet" href="/isotone/iso-admin/css/tailwind.css">
    <?php else: ?>
        <script src="https://cdn.tailwindcss.com"></script>
    <?php endif; ?>
    
    <style>
        .customize-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        .customize-sidebar {
            width: 320px;
            background: #1f2937;
            border-right: 1px solid #374151;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }
        
        .customize-header {
            padding: 1rem;
            background: #111827;
            border-bottom: 1px solid #374151;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
        }
        
        .customize-subheader {
            padding: 0.75rem 1rem;
            background: linear-gradient(to bottom, #1f2937, #111827);
            border-bottom: 1px solid #374151;
            display: none;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .customize-subheader.active {
            display: flex;
        }
        
        .subheader-icon-wrapper {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .subheader-icon {
            width: 32px;
            height: 32px;
            color: #00d9ff;
            filter: drop-shadow(0 0 4px rgba(0, 217, 255, 0.3));
        }
        
        .subheader-content {
            flex: 1;
            padding-left: 0.5rem;
        }
        
        .subheader-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            margin-bottom: 0.125rem;
        }
        
        .subheader-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.2;
        }
        
        .customize-panels-container {
            flex: 1;
            overflow: hidden;
            position: relative;
        }
        
        .customize-panel {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #1f2937;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
        }
        
        .customize-panel:not(.customize-main-panel) {
            padding: 1rem;
        }
        
        .customize-panel.active {
            transform: translateX(0);
        }
        
        .customize-panel.previous {
            transform: translateX(-100%);
        }
        
        /* Main panel (list of sections) */
        .customize-main-panel {
            transform: translateX(0);
        }
        
        .customize-main-panel.slide-left {
            transform: translateX(-100%);
        }
        
        .section-item {
            position: relative;
            overflow: hidden;
        }
        
        .section-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, #374151 20%, #374151 80%, transparent);
        }
        
        .section-item:last-child::after {
            display: none;
        }
        
        .section-button {
            width: 100%;
            text-align: left;
            padding: 1rem;
            color: #9ca3af;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .section-button::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #00D9FF;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .section-button:hover {
            background: linear-gradient(to right, rgba(0, 217, 255, 0.05), rgba(0, 217, 255, 0.02));
            color: #ffffff;
            padding-left: 1.25rem;
        }
        
        .section-button:hover::before {
            transform: translateX(0);
        }
        
        .section-button:hover .section-title {
            transform: translateX(4px);
            color: #00D9FF;
        }
        
        .section-button:hover .chevron-right {
            transform: translateX(4px);
            color: #00D9FF;
        }
        
        .section-button:active {
            background: rgba(0, 217, 255, 0.1);
            transform: scale(0.98);
        }
        
        .section-title {
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-icon {
            width: 20px;
            height: 20px;
            opacity: 0.7;
            transition: all 0.3s;
        }
        
        .section-button:hover .section-icon {
            opacity: 1;
            transform: scale(1.1);
        }
        
        .back-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 0.375rem;
            color: #9ca3af;
            transition: all 0.2s;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .back-button:hover {
            background: rgba(0, 217, 255, 0.1);
            color: #00D9FF;
            transform: translateX(-2px);
        }
        
        .back-button:active {
            transform: translateX(-4px);
        }
        
        .customize-control {
            margin-bottom: 1rem;
        }
        
        .customize-control-title {
            display: block;
            color: #d1d5db;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .customize-control-description {
            display: block;
            color: #9ca3af;
            font-size: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .customize-control-input {
            width: 100%;
            padding: 0.5rem;
            background: #111827;
            border: 1px solid #374151;
            border-radius: 0.375rem;
            color: #fff;
        }
        
        .customize-control-input:focus {
            outline: none;
            border-color: #00D9FF;
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }
        
        .customize-preview {
            flex: 1;
            position: relative;
            background: #fff;
        }
        
        .customize-preview-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .chevron-right {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.4;
        }
    </style>
</head>
<body class="dark:bg-gray-900 bg-gray-50">
    
    <div class="customize-container">
        <!-- Sidebar -->
        <div class="customize-sidebar">
            <!-- Main Header -->
            <div class="customize-header" id="customize-header">
                <button id="customize-close" class="text-gray-400 hover:text-white transition-colors" type="button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <h2 class="text-white font-semibold flex-1 text-center"></h2>
                
                <button id="customize-save" class="px-4 py-1 bg-cyan-600 hover:bg-cyan-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-normal rounded transition-colors" disabled type="button">
                    Publish
                </button>
            </div>
            
            <!-- Section Header (shown when in a section) -->
            <div class="customize-subheader" id="customize-subheader">
                <button id="customize-back" class="back-button">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </button>
                <div class="subheader-icon-wrapper">
                    <svg id="section-icon" class="subheader-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <div class="subheader-content">
                    <div class="subheader-label">You are customizing</div>
                    <div class="subheader-title" id="section-title">Section Title</div>
                </div>
            </div>
            
            <!-- Panels Container -->
            <div class="customize-panels-container">
                <!-- Main Panel - List of Sections -->
                <div class="customize-panel customize-main-panel" id="panel-main">
                    <?php 
                    foreach ($sections as $section_id => $section): 
                        // Get icon from section configuration or use default
                        $iconName = $section['icon'] ?? 'cog';
                        $iconPath = IconLibrary::getIconPath($iconName);
                    ?>
                        <div class="section-item">
                            <button class="section-button" data-section="<?php echo esc_attr($section_id); ?>">
                                <span class="section-title">
                                    <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php echo $iconPath; ?>
                                    </svg>
                                    <?php echo esc_html($section['title']); ?>
                                </span>
                                <svg class="w-5 h-5 chevron-right" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Section Panels - One for each section -->
                <?php foreach ($sections as $section_id => $section): ?>
                    <div class="customize-panel" id="panel-<?php echo esc_attr($section_id); ?>" data-section-title="<?php echo esc_attr($section['title']); ?>">
                        <?php if (!empty($section['description'])): ?>
                            <p class="customize-control-description mb-4"><?php echo esc_html($section['description']); ?></p>
                        <?php endif; ?>
                        
                        <?php 
                        $section_controls = $customizer->getSectionControls($section_id);
                        if (empty($section_controls)): ?>
                            <p class="text-gray-400 text-sm">No controls in this section yet.</p>
                        <?php else:
                            foreach ($section_controls as $control_id => $control):
                                echo $customizer->renderControl($control_id);
                            endforeach;
                        endif;
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Preview Area -->
        <div class="customize-preview">
            <iframe id="customize-preview-iframe" class="customize-preview-iframe" src="<?php echo esc_attr($preview_url); ?>?customize_preview=1"></iframe>
        </div>
    </div>
    
    <script src="/isotone/iso-includes/js/icon-library.js"></script>
    <script>
        // Settings from PHP
        const customizerSettings = <?php echo json_encode($settings ?: []); ?>;
        const csrfToken = '<?php echo IsotoneSecurity::generateCSRFToken(); ?>';
        const returnUrl = '<?php echo esc_js($return_url); ?>';
        
        // State
        let changedValues = {};
        let hasChanges = false;
        let currentPanel = 'main';
        
        // DOM elements
        const saveButton = document.getElementById('customize-save');
        const closeButton = document.getElementById('customize-close');
        const backButton = document.getElementById('customize-back');
        const subheader = document.getElementById('customize-subheader');
        const sectionTitle = document.getElementById('section-title');
        const mainPanel = document.getElementById('panel-main');
        
        // Get sections data from PHP with their icons
        const sectionsData = <?php echo json_encode($sections ?: []); ?>;
        
        // Map section IDs to their icon paths using the IsotoneIcons library
        const sectionIcons = {};
        Object.keys(sectionsData).forEach(sectionId => {
            const iconName = sectionsData[sectionId].icon || 'cog';
            sectionIcons[sectionId] = IsotoneIcons.getIconPath(iconName);
        });
        
        // Panel navigation
        document.querySelectorAll('.section-button').forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.dataset.section;
                const panel = document.getElementById('panel-' + sectionId);
                const title = panel.dataset.sectionTitle;
                
                // Slide main panel left
                mainPanel.classList.add('slide-left');
                
                // Show section panel
                panel.classList.add('active');
                
                // Show subheader with section title and icon
                subheader.classList.add('active');
                sectionTitle.textContent = title;
                
                // Update the icon
                const sectionIconEl = document.getElementById('section-icon');
                if (sectionIconEl && sectionIcons[sectionId]) {
                    sectionIconEl.innerHTML = sectionIcons[sectionId];
                }
                
                currentPanel = sectionId;
            });
        });
        
        // Back button
        backButton.addEventListener('click', function() {
            if (currentPanel !== 'main') {
                // Hide current section panel
                const panel = document.getElementById('panel-' + currentPanel);
                panel.classList.remove('active');
                
                // Show main panel
                mainPanel.classList.remove('slide-left');
                
                // Hide subheader
                subheader.classList.remove('active');
                
                currentPanel = 'main';
            }
        });
        
        // Control changes via delegation
        document.addEventListener('input', function(e) {
            if (!e.target.classList.contains('customize-control-input')) return;
            
            const input = e.target;
            const settingId = input.dataset.customizeSettingLink || input.name;
            let value = input.value;
            
            if (input.type === 'checkbox') {
                value = input.checked ? '1' : '';
            }
            
            changedValues[settingId] = value;
            
            if (!hasChanges && saveButton) {
                hasChanges = true;
                saveButton.disabled = false;
            }
        });
        
        // Close button
        closeButton.addEventListener('click', function() {
            if (hasChanges) {
                if (!confirm('You have unsaved changes. Discard them?')) {
                    return;
                }
            }
            window.location.href = returnUrl;
        });
        
        // Save button
        saveButton.addEventListener('click', function() {
            saveButton.disabled = true;
            saveButton.textContent = 'Publishing...';
            
            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('csrf_token', csrfToken);
            
            for (const [key, value] of Object.entries(changedValues)) {
                formData.append('customized[' + key + ']', value);
            }
            
            fetch('customize.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    saveButton.textContent = 'Published!';
                    setTimeout(() => {
                        saveButton.textContent = 'Publish';
                        saveButton.disabled = true;
                    }, 2000);
                    changedValues = {};
                    hasChanges = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                saveButton.textContent = 'Publish';
                saveButton.disabled = false;
            });
        });
    </script>
</body>
</html>
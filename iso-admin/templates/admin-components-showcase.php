<?php
/**
 * Admin Components Showcase
 * 
 * Complete showcase of all admin UI components for reference and styling.
 * This page demonstrates every component type used in Isotone admin pages.
 * 
 * @package Isotone
 */

require_once dirname(__DIR__) . '/auth.php';
requireRole('admin');

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__, 2) . '/iso-includes/database.php';
require_once dirname(__DIR__, 2) . '/iso-includes/icon-functions.php';

isotone_db_connect();

use RedBeanPHP\R;

// Preload all icons used in components
iso_preload_icons([
    ['name' => 'swatch', 'style' => 'outline'],
    ['name' => 'magnifying-glass', 'style' => 'outline'],
    ['name' => 'funnel', 'style' => 'outline'],
    ['name' => 'arrow-up-tray', 'style' => 'outline'],
    ['name' => 'photo', 'style' => 'outline'],
    ['name' => 'document', 'style' => 'outline'],
    ['name' => 'check', 'style' => 'outline'],
    ['name' => 'x-mark', 'style' => 'outline'],
    ['name' => 'chevron-down', 'style' => 'outline'],
    ['name' => 'chevron-up', 'style' => 'outline'],
    ['name' => 'chevron-left', 'style' => 'outline'],
    ['name' => 'chevron-right', 'style' => 'outline'],
    ['name' => 'star', 'style' => 'solid'],
    ['name' => 'star', 'style' => 'outline'],
    ['name' => 'pencil', 'style' => 'outline'],
    ['name' => 'trash', 'style' => 'outline'],
    ['name' => 'eye', 'style' => 'outline'],
    ['name' => 'download', 'style' => 'outline'],
    ['name' => 'plus', 'style' => 'outline'],
    ['name' => 'minus', 'style' => 'outline'],
    ['name' => 'exclamation-triangle', 'style' => 'micro'],
    ['name' => 'information-circle', 'style' => 'micro'],
    ['name' => 'check-circle', 'style' => 'micro'],
    ['name' => 'x-circle', 'style' => 'micro'],
    ['name' => 'clock', 'style' => 'outline'],
    ['name' => 'calendar', 'style' => 'outline'],
    ['name' => 'bell', 'style' => 'outline'],
    ['name' => 'chart-bar', 'style' => 'outline'],
    ['name' => 'chart-pie', 'style' => 'outline'],
    ['name' => 'arrow-trending-up', 'style' => 'micro'],
    ['name' => 'arrow-trending-down', 'style' => 'micro'],
    ['name' => 'user-circle', 'style' => 'outline'],
    ['name' => 'users', 'style' => 'outline'],
    ['name' => 'folder', 'style' => 'outline'],
    ['name' => 'folder-open', 'style' => 'outline'],
    ['name' => 'paper-clip', 'style' => 'outline'],
    ['name' => 'link', 'style' => 'outline'],
    ['name' => 'globe-alt', 'style' => 'outline'],
    ['name' => 'cog-6-tooth', 'style' => 'outline'],
    ['name' => 'adjustments-horizontal', 'style' => 'outline'],
]);

// Sample data for demonstrations
$sample_table_data = [
    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'Admin', 'status' => 'active'],
    ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'Editor', 'status' => 'active'],
    ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'role' => 'User', 'status' => 'inactive'],
    ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'role' => 'Editor', 'status' => 'active'],
];

ob_start();
?>

<!-- Alpine.js component wrapper -->
<div x-data="componentsShowcase()" x-init="init()">
    
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-4">
            <span class="shield-pulse flex-shrink-0">
                <?php echo iso_get_icon('swatch', 'outline', ['class' => 'w-10 h-10 text-cyan-500'], false); ?>
            </span>
            <span>Admin Components Showcase</span>
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Complete reference for all admin UI components and their styling
        </p>
    </div>

    <!-- Component Categories as Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex flex-wrap -mb-px">
                <button @click="activeCategory = 'forms'" 
                        :class="activeCategory === 'forms' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Form Components
                </button>
                <button @click="activeCategory = 'display'" 
                        :class="activeCategory === 'display' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Data Display
                </button>
                <button @click="activeCategory = 'interactive'" 
                        :class="activeCategory === 'interactive' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Interactive
                </button>
                <button @click="activeCategory = 'feedback'" 
                        :class="activeCategory === 'feedback' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Feedback
                </button>
                <button @click="activeCategory = 'media'" 
                        :class="activeCategory === 'media' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Media
                </button>
                <button @click="activeCategory = 'charts'" 
                        :class="activeCategory === 'charts' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Charts
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Form Components -->
            <div x-show="activeCategory === 'forms'" x-transition>
                <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Form Components</h2>
                
                <div class="space-y-8">
                    <!-- Text Inputs -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Text Inputs</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Standard Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Standard Input
                                </label>
                                <input type="text" placeholder="Enter text..." 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              focus:ring-2 focus:ring-cyan-500 focus:border-transparent
                                              placeholder-gray-400 dark:placeholder-gray-500">
                            </div>
                            
                            <!-- Input with Icon -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Input with Icon
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <?php echo iso_get_icon('magnifying-glass', 'outline', ['class' => 'w-5 h-5 text-gray-400'], false); ?>
                                    </div>
                                    <input type="text" placeholder="Search..." 
                                           class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                                  focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <!-- Disabled Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Disabled Input
                                </label>
                                <input type="text" placeholder="Disabled" disabled
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                              bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-500 
                                              cursor-not-allowed opacity-50">
                            </div>
                            
                            <!-- Input with Error -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Input with Error
                                </label>
                                <input type="text" placeholder="Invalid input" 
                                       class="w-full px-3 py-2 border border-red-500 dark:border-red-500 rounded-lg 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">This field is required</p>
                            </div>
                        </div>
                    </div>

                    <!-- Textarea -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Textarea</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Message
                            </label>
                            <textarea rows="4" placeholder="Enter your message..."
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                             bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                             focus:ring-2 focus:ring-cyan-500 focus:border-transparent
                                             resize-none"></textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Max 500 characters</p>
                        </div>
                    </div>

                    <!-- Select/Dropdown -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Select Dropdown</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Standard Select -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Standard Select
                                </label>
                                <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                               focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                                    <option>Choose an option</option>
                                    <option>Option 1</option>
                                    <option>Option 2</option>
                                    <option>Option 3</option>
                                </select>
                            </div>
                            
                            <!-- Custom Dropdown with Alpine -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Custom Dropdown
                                </label>
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" type="button"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                                   focus:ring-2 focus:ring-cyan-500 focus:border-transparent
                                                   flex items-center justify-between">
                                        <span x-text="selectedOption || 'Select option'"></span>
                                        <?php echo iso_get_icon('chevron-down', 'outline', ['class' => 'w-5 h-5 text-gray-400'], false); ?>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition
                                         class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg">
                                        <button @click="selectedOption = 'Option 1'; open = false" type="button"
                                                class="w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Option 1
                                        </button>
                                        <button @click="selectedOption = 'Option 2'; open = false" type="button"
                                                class="w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Option 2
                                        </button>
                                        <button @click="selectedOption = 'Option 3'; open = false" type="button"
                                                class="w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Option 3
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkboxes and Radio Buttons -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Checkboxes & Radio Buttons</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Checkboxes -->
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Checkboxes</p>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" checked
                                               class="w-4 h-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500 
                                                      dark:bg-gray-700 dark:border-gray-600">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Option 1 (checked)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               class="w-4 h-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500 
                                                      dark:bg-gray-700 dark:border-gray-600">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Option 2</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" disabled
                                               class="w-4 h-4 text-gray-400 border-gray-300 rounded cursor-not-allowed opacity-50">
                                        <span class="ml-2 text-sm text-gray-500">Option 3 (disabled)</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Radio Buttons -->
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Radio Buttons</p>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="radio-group" checked
                                               class="w-4 h-4 text-cyan-600 border-gray-300 focus:ring-cyan-500 
                                                      dark:bg-gray-700 dark:border-gray-600">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Option 1 (selected)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="radio-group"
                                               class="w-4 h-4 text-cyan-600 border-gray-300 focus:ring-cyan-500 
                                                      dark:bg-gray-700 dark:border-gray-600">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Option 2</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="radio-group" disabled
                                               class="w-4 h-4 text-gray-400 border-gray-300 cursor-not-allowed opacity-50">
                                        <span class="ml-2 text-sm text-gray-500">Option 3 (disabled)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Switch -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Toggle Switches</h3>
                        <div class="space-y-4">
                            <!-- Basic Toggle -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Enable notifications</span>
                                <button type="button" @click="toggle1 = !toggle1"
                                        :class="toggle1 ? 'bg-cyan-600' : 'bg-gray-300 dark:bg-gray-600'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                    <span :class="toggle1 ? 'translate-x-6' : 'translate-x-1'"
                                          class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                </button>
                            </div>
                            
                            <!-- Toggle with Description -->
                            <div class="flex items-start">
                                <button type="button" @click="toggle2 = !toggle2"
                                        :class="toggle2 ? 'bg-cyan-600' : 'bg-gray-300 dark:bg-gray-600'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                    <span :class="toggle2 ? 'translate-x-6' : 'translate-x-1'"
                                          class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                </button>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Marketing emails</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Receive emails about new products and features</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">File Upload</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Basic File Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Basic File Input
                                </label>
                                <input type="file" 
                                       class="block w-full text-sm text-gray-500 dark:text-gray-400
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-lg file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-cyan-50 file:text-cyan-700
                                              hover:file:bg-cyan-100
                                              dark:file:bg-cyan-900 dark:file:text-cyan-300
                                              dark:hover:file:bg-cyan-800">
                            </div>
                            
                            <!-- Drag and Drop Area -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Drag & Drop Upload
                                </label>
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center
                                            hover:border-cyan-500 dark:hover:border-cyan-400 transition-colors">
                                    <?php echo iso_get_icon('arrow-up-tray', 'outline', ['class' => 'w-8 h-8 mx-auto text-gray-400 mb-2'], false); ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Range Slider -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Range Slider</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Volume: <span x-text="rangeValue"></span>%
                            </label>
                            <input type="range" x-model="rangeValue" min="0" max="100" 
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700
                                          [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 
                                          [&::-webkit-slider-thumb]:bg-cyan-600 [&::-webkit-slider-thumb]:rounded-full
                                          [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 
                                          [&::-moz-range-thumb]:bg-cyan-600 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-0">
                        </div>
                    </div>

                    <!-- Date/Time Inputs -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Date & Time</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time</label>
                                <input type="time" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date & Time</label>
                                <input type="datetime-local" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Display Components -->
            <div x-show="activeCategory === 'display'" x-transition>
                <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Data Display Components</h2>
                
                <div class="space-y-8">
                    <!-- Tables -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Tables</h3>
                        
                        <!-- Basic Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($sample_table_data as $row): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                <?php echo htmlspecialchars($row['role']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($row['status'] === 'active'): ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300">
                                                    Active
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                    Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button class="text-cyan-600 hover:text-cyan-900 dark:text-cyan-400 dark:hover:text-cyan-300">
                                                    <?php echo iso_get_icon('eye', 'outline', ['class' => 'w-4 h-4'], false); ?>
                                                </button>
                                                <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                    <?php echo iso_get_icon('pencil', 'outline', ['class' => 'w-4 h-4'], false); ?>
                                                </button>
                                                <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    <?php echo iso_get_icon('trash', 'outline', ['class' => 'w-4 h-4'], false); ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Lists -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Lists</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Simple List -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Simple List</h4>
                                <ul class="space-y-2">
                                    <li class="flex items-center p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <?php echo iso_get_icon('check', 'outline', ['class' => 'w-5 h-5 text-green-500 mr-3'], false); ?>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Completed task item</span>
                                    </li>
                                    <li class="flex items-center p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <?php echo iso_get_icon('clock', 'outline', ['class' => 'w-5 h-5 text-yellow-500 mr-3'], false); ?>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Pending task item</span>
                                    </li>
                                    <li class="flex items-center p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <?php echo iso_get_icon('x-mark', 'outline', ['class' => 'w-5 h-5 text-red-500 mr-3'], false); ?>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Failed task item</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Interactive List -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Interactive List</h4>
                                <ul class="space-y-2">
                                    <li class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                        <div class="flex items-center">
                                            <?php echo iso_get_icon('folder', 'outline', ['class' => 'w-5 h-5 text-gray-400 mr-3'], false); ?>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Documents</span>
                                        </div>
                                        <span class="text-xs text-gray-500">24 items</span>
                                    </li>
                                    <li class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                        <div class="flex items-center">
                                            <?php echo iso_get_icon('photo', 'outline', ['class' => 'w-5 h-5 text-gray-400 mr-3'], false); ?>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Images</span>
                                        </div>
                                        <span class="text-xs text-gray-500">142 items</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Cards -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Cards</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Basic Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Card Title</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">This is a basic card with some content.</p>
                            </div>
                            
                            <!-- Card with Image -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                                <div class="h-32 bg-gradient-to-r from-cyan-500 to-blue-500"></div>
                                <div class="p-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Image Card</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Card with image header</p>
                                </div>
                            </div>
                            
                            <!-- Card with Actions -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                                <div class="p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Action Card</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Card with action buttons</p>
                                </div>
                                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t dark:border-gray-600">
                                    <div class="flex justify-end space-x-2">
                                        <button class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                            Cancel
                                        </button>
                                        <button class="px-3 py-1 text-sm bg-cyan-600 text-white rounded hover:bg-cyan-700">
                                            Confirm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Badges & Tags -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Badges & Tags</h3>
                        <div class="space-y-4">
                            <!-- Status Badges -->
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Status Badges</p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300">Success</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300">Warning</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300">Error</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300">Info</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">Default</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-300">Primary</span>
                                </div>
                            </div>
                            
                            <!-- Tags with Remove -->
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Removable Tags</p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        JavaScript
                                        <button class="ml-2 hover:text-gray-900 dark:hover:text-white">
                                            <?php echo iso_get_icon('x-mark', 'outline', ['class' => 'w-3 h-3'], false); ?>
                                        </button>
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        PHP
                                        <button class="ml-2 hover:text-gray-900 dark:hover:text-white">
                                            <?php echo iso_get_icon('x-mark', 'outline', ['class' => 'w-3 h-3'], false); ?>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Components -->
            <div x-show="activeCategory === 'interactive'" x-transition>
                <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Interactive Components</h2>
                
                <div class="space-y-8">
                    <!-- Buttons -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Buttons</h3>
                        <div class="space-y-4">
                            <!-- Primary Buttons -->
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Primary Buttons</p>
                                <div class="flex flex-wrap gap-2">
                                    <button class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Primary</button>
                                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Secondary</button>
                                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Success</button>
                                    <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Danger</button>
                                    <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">Warning</button>
                                </div>
                            </div>
                            
                            <!-- Outline Buttons -->
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Outline Buttons</p>
                                <div class="flex flex-wrap gap-2">
                                    <button class="px-4 py-2 border-2 border-cyan-600 text-cyan-600 dark:text-cyan-400 rounded-lg hover:bg-cyan-50 dark:hover:bg-cyan-900/20 transition">Primary</button>
                                    <button class="px-4 py-2 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">Secondary</button>
                                </div>
                            </div>
                            
                            <!-- Icon Buttons -->
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Icon Buttons</p>
                                <div class="flex flex-wrap gap-2">
                                    <button class="p-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">
                                        <?php echo iso_get_icon('plus', 'outline', ['class' => 'w-5 h-5'], false); ?>
                                    </button>
                                    <button class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                        <?php echo iso_get_icon('trash', 'outline', ['class' => 'w-5 h-5'], false); ?>
                                    </button>
                                    <button class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <?php echo iso_get_icon('cog-6-tooth', 'outline', ['class' => 'w-5 h-5'], false); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modals -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Modal</h3>
                        <button @click="showModal = true" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">
                            Open Modal
                        </button>
                        
                        <!-- Modal -->
                        <div x-show="showModal" x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                             @click.self="showModal = false">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full m-4">
                                <div class="p-6 border-b dark:border-gray-700">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Modal Title</h3>
                                </div>
                                <div class="p-6">
                                    <p class="text-gray-600 dark:text-gray-400">This is a modal dialog example.</p>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t dark:border-gray-600 flex justify-end space-x-2">
                                    <button @click="showModal = false" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                        Cancel
                                    </button>
                                    <button @click="showModal = false" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">
                                        Confirm
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tooltips -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Tooltips</h3>
                        <div class="flex gap-4">
                            <div class="relative" x-data="{ tooltip: false }">
                                <button @mouseenter="tooltip = true" @mouseleave="tooltip = false"
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg">
                                    Hover me
                                </button>
                                <div x-show="tooltip" x-transition
                                     class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-600 rounded">
                                    Tooltip text
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 w-0 h-0 border-4 border-transparent border-t-gray-900 dark:border-t-gray-600"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Pagination</h3>
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-400">
                                Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">97</span> results
                            </p>
                            <nav class="flex space-x-1">
                                <button class="px-3 py-1 text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-l-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <?php echo iso_get_icon('chevron-left', 'outline', ['class' => 'w-5 h-5'], false); ?>
                                </button>
                                <button class="px-3 py-1 bg-cyan-600 text-white border border-cyan-600 ">1</button>
                                <button class="px-3 py-1 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">2</button>
                                <button class="px-3 py-1 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">3</button>
                                <button class="px-3 py-1 text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-r-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <?php echo iso_get_icon('chevron-right', 'outline', ['class' => 'w-5 h-5'], false); ?>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Components -->
            <div x-show="activeCategory === 'feedback'" x-transition>
                <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Feedback Components</h2>
                
                <div class="space-y-8">
                    <!-- Alerts -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Alerts</h3>
                        <div class="space-y-3">
                            <!-- Success Alert -->
                            <div class="alert alert-success">
                                <div class="alert-icon">
                                    <?php echo iso_get_icon('check-circle', 'micro', ['class' => 'w-5 h-5'], false); ?>
                                </div>
                                <div class="alert-content">
                                    <p>Successfully saved changes!</p>
                                </div>
                            </div>
                            
                            <!-- Error Alert -->
                            <div class="alert alert-error">
                                <div class="alert-icon">
                                    <?php echo iso_get_icon('x-circle', 'micro', ['class' => 'w-5 h-5'], false); ?>
                                </div>
                                <div class="alert-content">
                                    <p>An error occurred. Please try again.</p>
                                </div>
                            </div>
                            
                            <!-- Warning Alert -->
                            <div class="alert alert-warning">
                                <div class="alert-icon">
                                    <?php echo iso_get_icon('exclamation-triangle', 'micro', ['class' => 'w-5 h-5'], false); ?>
                                </div>
                                <div class="alert-content">
                                    <p>Warning: This action cannot be undone.</p>
                                </div>
                            </div>
                            
                            <!-- Info Alert -->
                            <div class="alert alert-info">
                                <div class="alert-icon">
                                    <?php echo iso_get_icon('information-circle', 'micro', ['class' => 'w-5 h-5'], false); ?>
                                </div>
                                <div class="alert-content">
                                    <p>New update available. <a href="#">Click here to install</a>.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bars -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Progress Bars</h3>
                        <div class="space-y-4">
                            <!-- Basic Progress -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Basic Progress</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">45%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    <div class="bg-cyan-600 h-2 rounded-full" style="width: 45%"></div>
                                </div>
                            </div>
                            
                            <!-- Striped Progress -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Striped Progress</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">70%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                                    <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 h-2 rounded-full progress-striped" style="width: 70%"></div>
                                </div>
                            </div>
                            
                            <!-- Multi-color Progress -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Multi-segment</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">100%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 flex overflow-hidden">
                                    <div class="bg-green-500 h-2" style="width: 25%"></div>
                                    <div class="bg-yellow-500 h-2" style="width: 25%"></div>
                                    <div class="bg-orange-500 h-2" style="width: 25%"></div>
                                    <div class="bg-red-500 h-2" style="width: 25%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading States -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Loading States</h3>
                        <div class="flex items-center space-x-8">
                            <!-- Spinner -->
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Spinner</p>
                                <div class="chart-loading-spinner"></div>
                            </div>
                            
                            <!-- Dots -->
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Dots</p>
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 bg-cyan-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                    <div class="w-2 h-2 bg-cyan-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                    <div class="w-2 h-2 bg-cyan-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                </div>
                            </div>
                            
                            <!-- Skeleton -->
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Skeleton Loader</p>
                                <div class="animate-pulse">
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Components -->
            <div x-show="activeCategory === 'media'" x-transition>
                <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Media Components</h2>
                
                <div class="space-y-8">
                    <!-- Avatars -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Avatars</h3>
                        <div class="flex items-center space-x-4">
                            <!-- Small Avatar -->
                            <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">JD</span>
                            </div>
                            
                            <!-- Medium Avatar -->
                            <div class="w-10 h-10 bg-cyan-500 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-white">AB</span>
                            </div>
                            
                            <!-- Large Avatar with Status -->
                            <div class="relative">
                                <div class="w-12 h-12 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">MK</span>
                                </div>
                                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                            </div>
                            
                            <!-- Avatar with Image -->
                            <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">
                                <?php echo iso_get_icon('user-circle', 'outline', ['class' => 'w-12 h-12 text-gray-400'], false); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Image Gallery -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Image Gallery</h3>
                        <div class="grid grid-cols-4 gap-4">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                            <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden group cursor-pointer">
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 group-hover:opacity-75 transition">
                                    <?php echo iso_get_icon('photo', 'outline', ['class' => 'w-8 h-8 text-gray-400'], false); ?>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- File List -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">File List</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-center">
                                    <?php echo iso_get_icon('document', 'outline', ['class' => 'w-5 h-5 text-gray-400 mr-3'], false); ?>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">document.pdf</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">2.4 MB</p>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <?php echo iso_get_icon('download', 'outline', ['class' => 'w-5 h-5'], false); ?>
                                </button>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-center">
                                    <?php echo iso_get_icon('photo', 'outline', ['class' => 'w-5 h-5 text-gray-400 mr-3'], false); ?>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">image.jpg</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">1.2 MB</p>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <?php echo iso_get_icon('download', 'outline', ['class' => 'w-5 h-5'], false); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Graphs -->
            <div x-show="activeCategory === 'charts'" x-transition>
                <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Charts & Graphs</h2>
                
                <div class="space-y-8">
                    <!-- Line Chart -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Line Chart</h3>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="chart-container">
                                <canvas id="lineChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Bar Chart -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Bar Chart</h3>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="chart-container">
                                <canvas id="barChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Info Cards (Security/Status Style) -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Info Cards (Icon Left)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="info-card info-red">
                                <div class="info-card-content">
                                    <div class="info-card-icon">
                                        <?php echo iso_get_icon('lock-closed', 'outline', [], false); ?>
                                    </div>
                                    <div class="info-card-body">
                                        <p class="info-card-label">Active Lockouts</p>
                                        <p class="info-card-value">12</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-card info-yellow">
                                <div class="info-card-content">
                                    <div class="info-card-icon">
                                        <?php echo iso_get_icon('exclamation-circle', 'outline', [], false); ?>
                                    </div>
                                    <div class="info-card-body">
                                        <p class="info-card-label">Warnings</p>
                                        <p class="info-card-value">24</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-card info-blue">
                                <div class="info-card-content">
                                    <div class="info-card-icon">
                                        <?php echo iso_get_icon('shield-check', 'outline', [], false); ?>
                                    </div>
                                    <div class="info-card-body">
                                        <p class="info-card-label">Protected</p>
                                        <p class="info-card-value">156</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-card info-green">
                                <div class="info-card-content">
                                    <div class="info-card-icon">
                                        <?php echo iso_get_icon('check-circle', 'outline', [], false); ?>
                                    </div>
                                    <div class="info-card-body">
                                        <p class="info-card-label">All Clear</p>
                                        <p class="info-card-value">OK</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metric Cards (Dashboard Style) -->
                    <div class="component-section">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Metric Cards (Stats with Trends)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="metric-card metric-cyan">
                                <div class="metric-card-content">
                                    <div class="metric-card-info">
                                        <p class="metric-card-label">Total Revenue</p>
                                        <p class="metric-card-value">$45,231</p>
                                        <div class="metric-card-change positive">
                                            <?php echo iso_get_icon('arrow-trending-up', 'micro', ['class' => 'w-4 h-4'], false); ?>
                                            <span>+12.5%</span>
                                        </div>
                                    </div>
                                    <div class="metric-card-icon">
                                        <?php echo iso_get_icon('chart-bar', 'outline', [], false); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="metric-card metric-blue">
                                <div class="metric-card-content">
                                    <div class="metric-card-info">
                                        <p class="metric-card-label">Active Users</p>
                                        <p class="metric-card-value">1,234</p>
                                        <div class="metric-card-change positive">
                                            <?php echo iso_get_icon('arrow-trending-up', 'micro', ['class' => 'w-4 h-4'], false); ?>
                                            <span>+5.2%</span>
                                        </div>
                                    </div>
                                    <div class="metric-card-icon">
                                        <?php echo iso_get_icon('users', 'outline', [], false); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="metric-card metric-green">
                                <div class="metric-card-content">
                                    <div class="metric-card-info">
                                        <p class="metric-card-label">Conversion Rate</p>
                                        <p class="metric-card-value">3.24%</p>
                                        <div class="metric-card-change negative">
                                            <?php echo iso_get_icon('arrow-trending-down', 'micro', ['class' => 'w-4 h-4'], false); ?>
                                            <span>-1.3%</span>
                                        </div>
                                    </div>
                                    <div class="metric-card-icon">
                                        <?php echo iso_get_icon('chart-pie', 'outline', [], false); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Component -->
<script>
function componentsShowcase() {
    return {
        activeCategory: 'forms',
        selectedOption: '',
        toggle1: true,
        toggle2: false,
        rangeValue: 50,
        showModal: false,
        lineChart: null,
        barChart: null,
        
        init() {
            // Initialize charts when the charts tab is shown
            this.$watch('activeCategory', (value) => {
                if (value === 'charts') {
                    this.$nextTick(() => {
                        this.initCharts();
                    });
                }
            });
        },
        
        initCharts() {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.log('Chart.js not loaded, skipping chart initialization');
                return;
            }
            
            const isDarkMode = document.documentElement.classList.contains('dark');
            const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';
            const gridColor = isDarkMode ? '#374151' : '#E5E7EB';
            
            // Line Chart
            const lineCtx = document.getElementById('lineChart');
            if (lineCtx && !this.lineChart) {
                this.lineChart = new Chart(lineCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Sales',
                            data: [30, 45, 35, 50, 40, 60],
                            borderColor: '#06B6D4',
                            backgroundColor: 'rgba(6, 182, 212, 0.1)',
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: textColor }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: textColor },
                                grid: { color: gridColor }
                            },
                            y: {
                                ticks: { color: textColor },
                                grid: { color: gridColor }
                            }
                        }
                    }
                });
            }
            
            // Bar Chart
            const barCtx = document.getElementById('barChart');
            if (barCtx && !this.barChart) {
                this.barChart = new Chart(barCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                        datasets: [{
                            label: 'Revenue',
                            data: [12000, 19000, 15000, 25000],
                            backgroundColor: '#06B6D4'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: textColor }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: textColor },
                                grid: { color: gridColor }
                            },
                            y: {
                                ticks: { color: textColor },
                                grid: { color: gridColor }
                            }
                        }
                    }
                });
            }
        }
    }
}
</script>

<?php
$page_content = ob_get_clean();

// Add CSS for admin components
$page_styles = '<link rel="stylesheet" href="../css/admin-components.css">';

// Set page configuration
$page_title = 'Components Showcase';
$breadcrumbs = [
    ['title' => 'Templates', 'url' => ''],
    ['title' => 'Components Showcase', 'url' => '']
];

// Include the admin layout
require_once dirname(__DIR__) . '/includes/admin-layout.php';
?>
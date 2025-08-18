<!-- General Settings Tab -->
<div x-show="activeTab === 'general'" x-cloak>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Site Title -->
        <div class="sm:col-span-2 lg:col-span-2">
            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                Site Title
            </label>
            <input type="text" 
                   name="site_title" 
                   value="<?php echo htmlspecialchars(getSetting('site_title', 'Isotone')); ?>"
                   class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
        </div>

        <!-- Admin Email -->
        <div>
            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                Admin Email
            </label>
            <input type="email" 
                   name="admin_email" 
                   value="<?php echo htmlspecialchars(getSetting('admin_email', '')); ?>"
                   class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
        </div>

        <!-- Site Tagline -->
        <div class="sm:col-span-2 lg:col-span-3">
            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                Site Tagline
            </label>
            <input type="text" 
                   name="site_tagline" 
                   value="<?php echo htmlspecialchars(getSetting('site_tagline', 'Just another Isotone site')); ?>"
                   class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
        </div>

        <!-- Site URL -->
        <div class="sm:col-span-2 lg:col-span-2">
            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                Site URL
            </label>
            <input type="url" 
                   name="site_url" 
                   value="<?php echo htmlspecialchars(getSetting('site_url', 'http://localhost/isotone')); ?>"
                   class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
        </div>

        <!-- Language -->
        <div>
            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                Language
            </label>
            <select name="language" 
                    class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                <?php
                $languages = [
                    'en_US' => 'English (US)',
                    'en_GB' => 'English (UK)',
                    'es_ES' => 'Spanish',
                    'fr_FR' => 'French',
                    'de_DE' => 'German',
                    'it_IT' => 'Italian',
                    'pt_BR' => 'Portuguese (Brazil)',
                    'zh_CN' => 'Chinese (Simplified)',
                    'ja_JP' => 'Japanese'
                ];
                $current_lang = getSetting('language', 'en_US');
                foreach ($languages as $code => $name) {
                    $selected = ($code === $current_lang) ? 'selected' : '';
                    echo "<option value=\"$code\" $selected>$name</option>";
                }
                ?>
            </select>
        </div>

        <!-- Timezone -->
        <div>
            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                Timezone
            </label>
            <select name="timezone" 
                    class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                <?php
                $timezones = timezone_identifiers_list();
                $current_tz = getSetting('timezone', 'UTC');
                foreach ($timezones as $tz) {
                    $selected = ($tz === $current_tz) ? 'selected' : '';
                    echo "<option value=\"$tz\" $selected>$tz</option>";
                }
                ?>
            </select>
        </div>

        <!-- Date Format -->
        <div>
            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                Date Format
            </label>
            <select name="date_format" 
                    class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                <?php
                $formats = [
                    'Y-m-d' => date('Y-m-d') . ' (Y-m-d)',
                    'd/m/Y' => date('d/m/Y') . ' (d/m/Y)',
                    'm/d/Y' => date('m/d/Y') . ' (m/d/Y)',
                    'F j, Y' => date('F j, Y') . ' (F j, Y)',
                    'j F Y' => date('j F Y') . ' (j F Y)'
                ];
                $current_format = getSetting('date_format', 'Y-m-d');
                foreach ($formats as $value => $display) {
                    $selected = ($value === $current_format) ? 'selected' : '';
                    echo "<option value=\"$value\" $selected>$display</option>";
                }
                ?>
            </select>
        </div>

        <!-- Time Format -->
        <div>
            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                Time Format
            </label>
            <select name="time_format" 
                    class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                <?php
                $time_formats = [
                    'H:i:s' => date('H:i:s') . ' (24-hour)',
                    'h:i:s A' => date('h:i:s A') . ' (12-hour)',
                    'H:i' => date('H:i') . ' (24-hour, no seconds)',
                    'h:i A' => date('h:i A') . ' (12-hour, no seconds)'
                ];
                $current_time_format = getSetting('time_format', 'H:i:s');
                foreach ($time_formats as $value => $display) {
                    $selected = ($value === $current_time_format) ? 'selected' : '';
                    echo "<option value=\"$value\" $selected>$display</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>
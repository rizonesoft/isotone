<!-- SMTP Settings Tab -->
<div x-show="activeTab === 'smtp'" x-cloak>
    <div class="space-y-8">
        <!-- Mail Method Section -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                Email Configuration
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Mail Method
                    </label>
                    <select name="mail_method" 
                            @change="mailMethod = $event.target.value"
                            class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                        <option value="mail" <?php echo getSetting('mail_method', 'mail') === 'mail' ? 'selected' : ''; ?>>PHP Mail</option>
                        <option value="smtp" <?php echo getSetting('mail_method') === 'smtp' ? 'selected' : ''; ?>>SMTP</option>
                        <option value="sendmail" <?php echo getSetting('mail_method') === 'sendmail' ? 'selected' : ''; ?>>Sendmail</option>
                    </select>
                </div>

                <!-- SMTP Settings - Always visible for all fields to maintain grid -->
                <div :class="mailMethod !== 'smtp' && 'opacity-50 pointer-events-none'">
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        SMTP Host
                    </label>
                    <input type="text" 
                           name="smtp_host" 
                           value="<?php echo htmlspecialchars(getSetting('smtp_host', '')); ?>"
                           placeholder="smtp.gmail.com"
                           :disabled="mailMethod !== 'smtp'"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>

                <div :class="mailMethod !== 'smtp' && 'opacity-50 pointer-events-none'">
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        SMTP Port
                    </label>
                    <input type="number" 
                           name="smtp_port" 
                           value="<?php echo htmlspecialchars(getSetting('smtp_port', '587')); ?>"
                           :disabled="mailMethod !== 'smtp'"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>

                <div :class="mailMethod !== 'smtp' && 'opacity-50 pointer-events-none'">
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        SMTP Username
                    </label>
                    <input type="text" 
                           name="smtp_username" 
                           value="<?php echo htmlspecialchars(getSetting('smtp_username', '')); ?>"
                           placeholder="your-email@gmail.com"
                           :disabled="mailMethod !== 'smtp'"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>

                <div :class="mailMethod !== 'smtp' && 'opacity-50 pointer-events-none'">
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        SMTP Password
                    </label>
                    <input type="password" 
                           name="smtp_password" 
                           value="<?php echo htmlspecialchars(getSetting('smtp_password', '')); ?>"
                           placeholder="••••••••"
                           :disabled="mailMethod !== 'smtp'"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>

                <div :class="mailMethod !== 'smtp' && 'opacity-50 pointer-events-none'">
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Encryption
                    </label>
                    <select name="smtp_encryption" 
                            :disabled="mailMethod !== 'smtp'"
                            class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                        <option value="tls" <?php echo getSetting('smtp_encryption', 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                        <option value="ssl" <?php echo getSetting('smtp_encryption') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        <option value="none" <?php echo getSetting('smtp_encryption') === 'none' ? 'selected' : ''; ?>>None</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Email Settings Section -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                Email Settings
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        From Email Address
                    </label>
                    <input type="email" 
                           name="smtp_from_email" 
                           value="<?php echo htmlspecialchars(getSetting('smtp_from_email', '')); ?>"
                           placeholder="noreply@example.com"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>

                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        From Name
                    </label>
                    <input type="text" 
                           name="smtp_from_name" 
                           value="<?php echo htmlspecialchars(getSetting('smtp_from_name', '')); ?>"
                           placeholder="Isotone Site"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>

                <div>
                    <button type="button" 
                            @click="sendTestEmail()"
                            class="w-full mt-7 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Send Test Email
                    </button>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Email Footer Text
                    </label>
                    <textarea name="email_footer" 
                              rows="3"
                              placeholder="This email was sent from your Isotone site."
                              class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500"><?php echo htmlspecialchars(getSetting('email_footer', '')); ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
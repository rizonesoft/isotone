<?php

namespace Isotone\Automation\Commands;

use Isotone\Automation\Core\AutomationEngine;

class TailwindCommand
{
    private AutomationEngine $engine;
    private string $buildDir;
    private bool $quiet = false;
    
    public function __construct(AutomationEngine $engine)
    {
        $this->engine = $engine;
        $this->buildDir = dirname(__DIR__, 3) . '/tailwind-build';
    }
    
    /**
     * Output info message
     */
    private function info(string $message): void
    {
        if (!$this->quiet) {
            echo "ℹ️  $message\n";
        }
    }
    
    /**
     * Output success message
     */
    private function success(string $message): void
    {
        if (!$this->quiet) {
            echo "✅ $message\n";
        }
    }
    
    /**
     * Output error message
     */
    private function error(string $message): void
    {
        echo "❌ $message\n";
    }
    
    /**
     * Output warning message
     */
    private function warning(string $message): void
    {
        if (!$this->quiet) {
            echo "⚠️  $message\n";
        }
    }
    
    /**
     * Output step message
     */
    private function step(string $message): void
    {
        if (!$this->quiet) {
            echo "▶️  $message\n";
        }
    }
    
    /**
     * Output separator
     */
    private function separator(): void
    {
        if (!$this->quiet) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
    }
    
    /**
     * Build production CSS
     */
    public function build(): int
    {
        $this->info("🔨 Building Tailwind CSS...");
        
        if (!is_dir($this->buildDir)) {
            $this->error("Build directory not found at: " . $this->buildDir);
            $this->info("Run 'tailwind:install' to set up the build system.");
            return 1;
        }
        
        $this->step("Step 1/2: Checking dependencies...");
        if (!file_exists($this->buildDir . '/node_modules')) {
            $this->warning("Dependencies not installed. Installing now...");
            $this->runCommand('npm install');
        }
        
        $this->step("Step 2/2: Building CSS...");
        $output = $this->runCommand('npm run build');
        
        if ($output === false) {
            $this->error("Build failed!");
            return 1;
        }
        
        $cssPath = dirname(__DIR__, 3) . '/iso-admin/css/tailwind.css';
        if (file_exists($cssPath)) {
            $size = filesize($cssPath);
            $this->success("✅ Build completed successfully!");
            $this->info("   Output: iso-admin/css/tailwind.css");
            $this->info("   Size: " . $this->formatBytes($size));
            return 0;
        }
        
        $this->error("Build verification failed!");
        return 1;
    }
    
    /**
     * Watch files and rebuild on changes
     */
    public function watch(): int
    {
        $this->info("👁️  Starting Tailwind CSS watch mode...");
        
        if (!is_dir($this->buildDir)) {
            $this->error("Build directory not found. Please run 'tailwind:install' first.");
            return 1;
        }
        
        $this->info("Watching for changes...");
        $this->info("Press Ctrl+C to stop watching");
        
        // This will run until interrupted
        $this->runCommand('npm run watch', true);
        
        return 0;
    }
    
    /**
     * Build minified production CSS
     */
    public function minify(): int
    {
        $this->info("🗜️  Building minified Tailwind CSS...");
        
        if (!is_dir($this->buildDir)) {
            $this->error("Build directory not found. Please run 'tailwind:install' first.");
            return 1;
        }
        
        $this->step("Building minified CSS...");
        $output = $this->runCommand('npm run minify');
        
        if ($output === false) {
            $this->error("Minification failed!");
            return 1;
        }
        
        $cssPath = dirname(__DIR__, 3) . '/iso-admin/css/tailwind.min.css';
        if (file_exists($cssPath)) {
            $size = filesize($cssPath);
            $this->success("✅ Minified build completed!");
            $this->info("   Output: iso-admin/css/tailwind.min.css");
            $this->info("   Size: " . $this->formatBytes($size));
            return 0;
        }
        
        $this->error("Minification verification failed!");
        return 1;
    }
    
    /**
     * Install build system (first-time setup)
     */
    public function install(): int
    {
        $this->info("📦 Installing Tailwind CSS build system...");
        
        if (!is_dir($this->buildDir)) {
            $this->error("Build directory not found at: " . $this->buildDir);
            $this->info("Please ensure the tailwind-build directory exists.");
            return 1;
        }
        
        if (is_dir($this->buildDir . '/node_modules')) {
            $this->warning("Dependencies already installed.");
            $this->info("Use 'tailwind:update' to update packages.");
            return 0;
        }
        
        $this->step("Installing dependencies...");
        $output = $this->runCommand('npm install');
        
        if ($output === false) {
            $this->error("Installation failed!");
            return 1;
        }
        
        $this->success("✅ Installation completed!");
        $this->info("Run 'tailwind:build' to generate CSS.");
        return 0;
    }
    
    /**
     * Update Tailwind CSS to latest version
     */
    public function update(): int
    {
        $this->info("📦 Updating Tailwind CSS...");
        
        if (!is_dir($this->buildDir)) {
            $this->error("Build directory not found. Please run 'tailwind:install' first.");
            return 1;
        }
        
        $this->step("Step 1/3: Updating dependencies...");
        $output = $this->runCommand('npm update');
        
        if ($output === false) {
            $this->error("Update failed!");
            return 1;
        }
        
        $this->step("Step 2/3: Checking version...");
        $packageJson = json_decode(file_get_contents($this->buildDir . '/package.json'), true);
        $version = $packageJson['devDependencies']['@tailwindcss/cli'] ?? 'unknown';
        $this->info("   Tailwind CLI version: " . $version);
        
        $this->step("Step 3/3: Rebuilding CSS...");
        return $this->build();
    }
    
    /**
     * Show build status
     */
    public function status(): int
    {
        $this->info("📊 Tailwind CSS Build Status");
        $this->separator();
        
        // Check if build directory exists
        if (!is_dir($this->buildDir)) {
            $this->error("❌ Build system not installed");
            $this->info("   Directory not found: " . $this->buildDir);
            return 1;
        }
        
        // Check dependencies
        if (file_exists($this->buildDir . '/node_modules')) {
            $this->success("✅ Dependencies installed");
            
            // Check package versions
            $packageJson = json_decode(file_get_contents($this->buildDir . '/package.json'), true);
            $this->info("   Tailwind CLI: " . ($packageJson['devDependencies']['@tailwindcss/cli'] ?? 'N/A'));
            $this->info("   Tailwind CSS: " . ($packageJson['devDependencies']['tailwindcss'] ?? 'N/A'));
            $this->info("   Alpine.js: " . ($packageJson['dependencies']['alpinejs'] ?? 'N/A'));
            $this->info("   Chart.js: " . ($packageJson['dependencies']['chart.js'] ?? 'N/A'));
        } else {
            $this->warning("⚠️  Dependencies not installed");
            $this->info("   Run 'tailwind:install' to install");
        }
        
        // Check built CSS
        $cssPath = dirname(__DIR__, 3) . '/iso-admin/css/tailwind.css';
        if (file_exists($cssPath)) {
            $stats = stat($cssPath);
            $this->success("✅ CSS file exists");
            $this->info("   Path: iso-admin/css/tailwind.css");
            $this->info("   Size: " . $this->formatBytes(filesize($cssPath)));
            $this->info("   Last built: " . date('Y-m-d H:i:s', $stats['mtime']));
        } else {
            $this->warning("⚠️  No CSS file found");
            $this->info("   Run 'tailwind:build' to generate");
        }
        
        // Check minified CSS
        $minPath = dirname(__DIR__, 3) . '/iso-admin/css/tailwind.min.css';
        if (file_exists($minPath)) {
            $this->success("✅ Minified CSS exists");
            $this->info("   Size: " . $this->formatBytes(filesize($minPath)));
        }
        
        return 0;
    }
    
    /**
     * Run a command in the build directory
     */
    private function runCommand(string $command, bool $passthrough = false)
    {
        $cwd = getcwd();
        chdir($this->buildDir);
        
        if ($passthrough) {
            passthru($command, $returnCode);
            chdir($cwd);
            return $returnCode === 0;
        }
        
        exec($command . ' 2>&1', $output, $returnCode);
        chdir($cwd);
        
        if ($returnCode !== 0) {
            foreach ($output as $line) {
                $this->error("   " . $line);
            }
            return false;
        }
        
        return implode("\n", $output);
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
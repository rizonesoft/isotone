<?php
/**
 * User Documentation Sync Script
 * 
 * Syncs shared documentation from /docs/ to /user-docs/
 * Run: php scripts/sync-user-docs.php
 */

declare(strict_types=1);

class UserDocsSyncer
{
    private string $rootPath;
    private array $syncMap = [
        // Currently active documentation files only
        // Source in /docs/ => Destination in /user-docs/
        'docs/API-REFERENCE.md' => 'user-docs/api/api-reference.md',
        'docs/COMMANDS.md' => 'user-docs/developers/commands.md',
        'docs/PROJECT-STRUCTURE.md' => 'user-docs/developers/project-structure.md',
        'docs/ROUTES.md' => 'user-docs/developers/routes.md',
    ];
    private array $synced = [];
    private array $errors = [];
    
    public function __construct()
    {
        $this->rootPath = dirname(__DIR__);
    }
    
    public function run(): void
    {
        echo "🔄 Syncing documentation from /docs/ to /user-docs/...\n\n";
        
        // Ensure user-docs directories exist
        $this->ensureDirectories();
        
        // Sync each file
        foreach ($this->syncMap as $source => $destination) {
            $this->syncFile($source, $destination);
        }
        
        $this->report();
    }
    
    /**
     * Ensure user-docs directory structure exists
     */
    private function ensureDirectories(): void
    {
        $directories = [
            'user-docs',
            'user-docs/installation',
            'user-docs/configuration',
            'user-docs/development',
            'user-docs/api',
            'user-docs/guides',
        ];
        
        foreach ($directories as $dir) {
            $fullPath = $this->rootPath . '/' . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                echo "📁 Created directory: $dir\n";
            }
        }
    }
    
    /**
     * Sync a single file
     */
    private function syncFile(string $source, string $destination): void
    {
        $sourcePath = $this->rootPath . '/' . $source;
        $destPath = $this->rootPath . '/' . $destination;
        
        if (!file_exists($sourcePath)) {
            $this->errors[] = "Source file not found: $source";
            return;
        }
        
        // Read source content
        $content = file_get_contents($sourcePath);
        
        // Check if destination exists and differs
        $needsUpdate = true;
        if (file_exists($destPath)) {
            $existingContent = file_get_contents($destPath);
            if ($content === $existingContent) {
                $needsUpdate = false;
            }
        }
        
        if ($needsUpdate) {
            // Write to destination
            if (file_put_contents($destPath, $content)) {
                $this->synced[] = [
                    'source' => $source,
                    'destination' => $destination,
                    'size' => strlen($content),
                ];
            } else {
                $this->errors[] = "Failed to write: $destination";
            }
        }
    }
    
    /**
     * Generate report
     */
    private function report(): void
    {
        if (!empty($this->errors)) {
            echo "\n❌ Errors occurred:\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            foreach ($this->errors as $error) {
                echo "  ✗ $error\n";
            }
        }
        
        if (!empty($this->synced)) {
            echo "\n✅ Files synced:\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            foreach ($this->synced as $sync) {
                $size = number_format($sync['size'] / 1024, 1) . ' KB';
                echo "  ✓ {$sync['source']} → {$sync['destination']} ($size)\n";
            }
        } else if (empty($this->errors)) {
            echo "✅ All user-docs are up to date!\n";
        }
        
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Summary: " . count($this->synced) . " files synced";
        if (!empty($this->errors)) {
            echo ", " . count($this->errors) . " errors";
        }
        echo "\n";
        
        // Exit with error code if there were errors
        exit(empty($this->errors) ? 0 : 1);
    }
}

// Run the syncer
$syncer = new UserDocsSyncer();
$syncer->run();
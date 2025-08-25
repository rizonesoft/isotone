<?php
/**
 * Sync IDE rule files from source templates
 * This ensures all IDE configurations stay updated
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Generators;

class IdeRuleSync
{
    private string $rootPath;
    private array $synced = [];
    private bool $quietMode = false;
    
    public function __construct()
    {
        // Navigate from /iso-automation/src/Generators/ to root
        $this->rootPath = dirname(dirname(dirname(__DIR__)));
    }
    
    /**
     * Set quiet mode
     */
    public function setQuietMode(bool $quiet = true): void
    {
        $this->quietMode = $quiet;
    }
    
    /**
     * Run IDE rules sync
     */
    public function run(): bool
    {
        if (!$this->quietMode) {
            echo "🔄 Syncing IDE rule files...\n\n";
        }
        
        $this->syncClaudeToWindsurf();
        
        $this->report();
        
        return true;
    }
    
    /**
     * Sync CLAUDE.md to Windsurf
     */
    private function syncClaudeToWindsurf(): void
    {
        $source = $this->rootPath . '/CLAUDE.md';
        $targetDir = $this->rootPath . '/.windsurf/rules';
        $target = $targetDir . '/main.md';
        
        // Windsurf character limit
        $WINDSURF_CHAR_LIMIT = 12000;
        
        if (!file_exists($source)) {
            if (!$this->quietMode) {
                echo "⚠️  Source CLAUDE.md not found\n";
            }
            return;
        }
        
        // Create directory if needed
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Read the source content
        $content = file_get_contents($source);
        
        // Replace Claude-specific references to be more generic for Windsurf
        $content = str_replace('# CLAUDE.md - LLM Master Instruction File', '# Isotone Development Rules - Master Instruction File', $content);
        $content = str_replace('This file provides instructions to Claude Code (claude.ai/code) and other LLMs', 'This file provides instructions to AI assistants and LLMs', $content);
        $content = str_replace('CLAUDE.md', 'this master rules file', $content);
        $content = str_replace('Claude Code', 'the AI assistant', $content);
        $content = str_replace('Claude', 'the AI assistant', $content);
        
        // Add Windsurf header
        $windsurfHeader = "---\ntrigger: always_on\n---\n\n";
        
        // Combine header with modified content
        $finalContent = $windsurfHeader . $content;
        
        // Check character limit
        $charCount = strlen($finalContent);
        
        if ($charCount > $WINDSURF_CHAR_LIMIT) {
            if (!$this->quietMode) {
                echo "⚠️  WARNING: Content exceeds Windsurf's 12,000 character limit!\n";
                echo "   Current size: " . number_format($charCount) . " characters\n";
                echo "   Excess: " . number_format($charCount - $WINDSURF_CHAR_LIMIT) . " characters\n\n";
            }
            
            // Truncate content intelligently
            $finalContent = $this->truncateForWindsurf($finalContent, $WINDSURF_CHAR_LIMIT);
            
            if (!$this->quietMode) {
                echo "   ✂️  Content truncated to fit limit\n";
                echo "   📝  Creating supplementary file for full content...\n";
            }
            
            // Save full content to a supplementary file
            $supplementaryFile = $targetDir . '/full-rules.md';
            file_put_contents($supplementaryFile, $windsurfHeader . $content);
            
            if (!$this->quietMode) {
                echo "   📄  Full rules saved to: .windsurf/rules/full-rules.md\n";
            }
        }
        
        // Write to target
        if (file_put_contents($target, $finalContent)) {
            $status = $charCount > $WINDSURF_CHAR_LIMIT 
                ? "CLAUDE.md → .windsurf/rules/main.md (truncated to 12k chars)"
                : "CLAUDE.md → .windsurf/rules/main.md (with Windsurf adaptations)";
            $this->synced[] = $status;
            
            if (!$this->quietMode) {
                echo "   ✅ Final size: " . number_format(strlen($finalContent)) . " characters\n";
            }
        } else {
            if (!$this->quietMode) {
                echo "❌ Failed to sync CLAUDE.md to Windsurf\n";
            }
        }
    }
    
    /**
     * Intelligently truncate content for Windsurf
     */
    private function truncateForWindsurf(string $content, int $limit): string
    {
        // Reserve space for the truncation notice
        $noticeText = "\n\n---\n⚠️ **Content truncated**: This file exceeds Windsurf's 12,000 character limit.\nSee `.windsurf/rules/full-rules.md` for complete rules.\nKey point: **Always use `/iso-automation/config/rules.yaml` as the source of truth.**";
        $effectiveLimit = $limit - strlen($noticeText) - 50; // Extra buffer
        
        // If content is already within limit with notice, just add notice
        if (strlen($content) <= $effectiveLimit) {
            return $content . $noticeText;
        }
        
        // Priority sections to keep (in order of importance)
        $prioritySections = [
            '## 🚨 CRITICAL: Use the Automation System for ALL Rules',
            '## 📋 How to Access Rules',
            '## 📚 Critical Rules to Always Remember',
            '## 🎯 Quick Reference',
            '### Most Used Commands',
            '## 🗄️ CRITICAL: Database Connection from WSL'
        ];
        
        // Split content into lines
        $lines = explode("\n", $content);
        $truncatedContent = [];
        $currentLength = 0;
        $inPrioritySection = false;
        $prioritySectionDepth = 0;
        
        foreach ($lines as $line) {
            // Check if we're entering a priority section
            foreach ($prioritySections as $section) {
                if (strpos($line, $section) === 0) {
                    $inPrioritySection = true;
                    $prioritySectionDepth = substr_count($section, '#');
                    break;
                }
            }
            
            // Check if we're leaving a priority section (new section at same or higher level)
            if ($inPrioritySection && preg_match('/^#{1,' . $prioritySectionDepth . '}\s/', $line)) {
                // Check if this is a new section (not the current priority section)
                $isNewSection = true;
                foreach ($prioritySections as $section) {
                    if (strpos($line, $section) === 0) {
                        $isNewSection = false;
                        break;
                    }
                }
                if ($isNewSection) {
                    $inPrioritySection = false;
                }
            }
            
            // Add line if we have space
            $lineLength = strlen($line) + 1; // +1 for newline
            if ($currentLength + $lineLength <= $effectiveLimit) {
                $truncatedContent[] = $line;
                $currentLength += $lineLength;
            } else if ($inPrioritySection) {
                // If we're in a priority section but out of space, stop
                break;
            }
        }
        
        // Join the content and add notice
        return implode("\n", $truncatedContent) . $noticeText;
    }
    
    
    /**
     * Generate report
     */
    private function report(): void
    {
        if (!$this->quietMode) {
            echo "\n📊 IDE Rules Sync Summary:\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            
            if (empty($this->synced)) {
                echo "No updates were necessary.\n";
            } else {
                foreach ($this->synced as $sync) {
                    echo "  ✓ $sync\n";
                }
            }
            
            echo "\n✅ IDE rules sync complete!\n";
        }
    }
}
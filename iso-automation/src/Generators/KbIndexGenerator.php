<?php
/**
 * Knowledge Base Index Generator for Isotone Documentation
 * 
 * Generates searchable index and chunks for Toni AI
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Generators;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Environment\Environment;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class KbIndexGenerator
{
    private string $docsPath;
    private string $kbPath;
    private array $index = [];
    private array $chunks = [];
    private int $chunkSize = 800; // Target chunk size in characters
    private array $stats = [
        'files_processed' => 0,
        'chunks_created' => 0,
        'errors' => 0
    ];
    
    public function __construct()
    {
        $this->docsPath = dirname(dirname(dirname(__DIR__))) . '/user-docs';
        $this->kbPath = $this->docsPath . '/.kb';
    }
    
    /**
     * Generate the knowledge base index
     */
    public function generate(bool $incremental = true): array
    {
        echo "📚 Generating Knowledge Base Index...\n";
        
        // Create .kb directory if it doesn't exist
        if (!is_dir($this->kbPath)) {
            mkdir($this->kbPath, 0755, true);
        }
        
        // Load existing index for incremental updates
        $existingIndex = [];
        if ($incremental && file_exists($this->kbPath . '/index.json')) {
            $existingIndex = json_decode(file_get_contents($this->kbPath . '/index.json'), true) ?? [];
        }
        
        // Process markdown files
        $this->processDirectory($this->docsPath, $existingIndex, $incremental);
        
        // Save index
        file_put_contents(
            $this->kbPath . '/index.json',
            json_encode($this->index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        // Save chunks (JSONL format for streaming)
        $chunksFile = fopen($this->kbPath . '/chunks.jsonl', 'w');
        foreach ($this->chunks as $chunk) {
            fwrite($chunksFile, json_encode($chunk, JSON_UNESCAPED_SLASHES) . "\n");
        }
        fclose($chunksFile);
        
        // Save search metadata
        $this->generateSearchMetadata();
        
        echo "\n✅ Knowledge Base Index Generated!\n";
        echo "   Files processed: {$this->stats['files_processed']}\n";
        echo "   Chunks created: {$this->stats['chunks_created']}\n";
        if ($this->stats['errors'] > 0) {
            echo "   ⚠️ Errors: {$this->stats['errors']}\n";
        }
        
        return $this->stats;
    }
    
    /**
     * Process a directory recursively
     */
    private function processDirectory(string $path, array $existingIndex, bool $incremental): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $filePath = $file->getPathname();
                $relativePath = str_replace($this->docsPath . '/', '', $filePath);
                
                // Skip hidden directories and files
                if (strpos($relativePath, '/.') !== false) {
                    continue;
                }
                
                // Check if file needs updating (incremental mode)
                if ($incremental && isset($existingIndex[$relativePath])) {
                    $existingMtime = $existingIndex[$relativePath]['modified'] ?? 0;
                    if (filemtime($filePath) <= $existingMtime) {
                        // File hasn't changed, copy existing data
                        $this->index[$relativePath] = $existingIndex[$relativePath];
                        continue;
                    }
                }
                
                // Process the markdown file
                $this->processMarkdownFile($filePath, $relativePath);
            }
        }
    }
    
    /**
     * Process a single markdown file
     */
    private function processMarkdownFile(string $filePath, string $relativePath): void
    {
        try {
            $content = file_get_contents($filePath);
            
            // Parse markdown with front matter
            $environment = new Environment();
            $environment->addExtension(new FrontMatterExtension());
            
            $converter = new CommonMarkConverter([], $environment);
            $result = $converter->convert($content);
            
            // Extract front matter
            $frontMatter = [];
            if ($result instanceof RenderedContentWithFrontMatter) {
                $frontMatter = $result->getFrontMatter() ?? [];
            }
            
            // Extract headings and structure
            $structure = $this->extractStructure($content);
            
            // Generate document entry
            $docId = md5($relativePath);
            $this->index[$relativePath] = [
                'id' => $docId,
                'path' => $relativePath,
                'url' => $this->generateUrl($relativePath),
                'title' => $frontMatter['title'] ?? $this->extractTitle($content),
                'description' => $frontMatter['description'] ?? '',
                'tags' => $frontMatter['tags'] ?? [],
                'headings' => $structure['headings'],
                'modified' => filemtime($filePath),
                'size' => filesize($filePath)
            ];
            
            // Generate chunks
            $this->generateChunks($content, $docId, $relativePath, $structure);
            
            $this->stats['files_processed']++;
            
        } catch (\Exception $e) {
            echo "   ⚠️ Error processing $relativePath: " . $e->getMessage() . "\n";
            $this->stats['errors']++;
        }
    }
    
    /**
     * Extract structure (headings) from markdown
     */
    private function extractStructure(string $content): array
    {
        $headings = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $text = trim($matches[2]);
                $anchor = $this->generateAnchor($text);
                
                $headings[] = [
                    'level' => $level,
                    'text' => $text,
                    'anchor' => $anchor
                ];
            }
        }
        
        return ['headings' => $headings];
    }
    
    /**
     * Generate chunks from content
     */
    private function generateChunks(string $content, string $docId, string $path, array $structure): void
    {
        // Remove front matter
        $content = preg_replace('/^---\n.*?\n---\n/s', '', $content);
        
        // Split by headings
        $sections = preg_split('/^(#{1,6})\s+(.+)$/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $currentHeading = '';
        $currentLevel = 0;
        $currentContent = $sections[0] ?? ''; // Content before first heading
        
        // Process intro content if exists
        if (trim($currentContent)) {
            $this->createChunk($docId, $path, '', $currentContent, 0);
        }
        
        // Process sections
        for ($i = 1; $i < count($sections); $i += 3) {
            if (isset($sections[$i]) && isset($sections[$i + 1])) {
                $level = strlen($sections[$i]);
                $heading = trim($sections[$i + 1]);
                $sectionContent = $sections[$i + 2] ?? '';
                
                // Create chunks for this section
                $this->createChunk($docId, $path, $heading, $sectionContent, $level);
            }
        }
    }
    
    /**
     * Create a chunk
     */
    private function createChunk(string $docId, string $path, string $heading, string $content, int $level): void
    {
        $content = trim($content);
        if (empty($content)) {
            return;
        }
        
        // Split large content into smaller chunks
        $words = explode(' ', $content);
        $currentChunk = '';
        $wordCount = 0;
        
        foreach ($words as $word) {
            if (strlen($currentChunk) + strlen($word) + 1 > $this->chunkSize && $wordCount > 0) {
                // Save current chunk
                $this->saveChunk($docId, $path, $heading, $currentChunk, $level);
                $currentChunk = '';
                $wordCount = 0;
            }
            
            $currentChunk .= ($wordCount > 0 ? ' ' : '') . $word;
            $wordCount++;
        }
        
        // Save remaining content
        if (trim($currentChunk)) {
            $this->saveChunk($docId, $path, $heading, $currentChunk, $level);
        }
    }
    
    /**
     * Save a chunk
     */
    private function saveChunk(string $docId, string $path, string $heading, string $content, int $level): void
    {
        $chunkId = md5($docId . $heading . $content);
        
        $this->chunks[] = [
            'id' => $chunkId,
            'doc_id' => $docId,
            'path' => $path,
            'heading' => $heading,
            'heading_level' => $level,
            'content' => $content,
            'url' => $this->generateUrl($path) . ($heading ? '#' . $this->generateAnchor($heading) : ''),
            'tokens' => $this->tokenize($content),
            'length' => strlen($content)
        ];
        
        $this->stats['chunks_created']++;
    }
    
    /**
     * Generate URL from path
     */
    private function generateUrl(string $path): string
    {
        // Remove .md extension and convert to URL
        $url = preg_replace('/\.md$/', '', $path);
        return '/docs/' . $url;
    }
    
    /**
     * Generate anchor from heading text
     */
    private function generateAnchor(string $text): string
    {
        // Convert to lowercase, replace spaces with hyphens, remove special chars
        $anchor = strtolower($text);
        $anchor = preg_replace('/[^a-z0-9\s-]/', '', $anchor);
        $anchor = preg_replace('/\s+/', '-', $anchor);
        $anchor = trim($anchor, '-');
        return $anchor;
    }
    
    /**
     * Extract title from content
     */
    private function extractTitle(string $content): string
    {
        // Look for first H1 heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        return 'Untitled';
    }
    
    /**
     * Simple tokenization for search
     */
    private function tokenize(string $text): array
    {
        // Convert to lowercase and split by non-word characters
        $text = strtolower($text);
        $words = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Count term frequencies
        $tokens = [];
        foreach ($words as $word) {
            if (strlen($word) > 2) { // Skip very short words
                $tokens[$word] = ($tokens[$word] ?? 0) + 1;
            }
        }
        
        return $tokens;
    }
    
    /**
     * Generate search metadata for faster queries
     */
    private function generateSearchMetadata(): void
    {
        $metadata = [
            'total_documents' => count($this->index),
            'total_chunks' => count($this->chunks),
            'generated_at' => time(),
            'sections' => $this->analyzeSections(),
            'tags' => $this->collectTags()
        ];
        
        file_put_contents(
            $this->kbPath . '/metadata.json',
            json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
    
    /**
     * Analyze sections
     */
    private function analyzeSections(): array
    {
        $sections = [];
        
        foreach ($this->index as $path => $doc) {
            $section = explode('/', $path)[0] ?? 'root';
            $sections[$section] = ($sections[$section] ?? 0) + 1;
        }
        
        return $sections;
    }
    
    /**
     * Collect all unique tags
     */
    private function collectTags(): array
    {
        $tags = [];
        
        foreach ($this->index as $doc) {
            foreach ($doc['tags'] as $tag) {
                $tags[$tag] = ($tags[$tag] ?? 0) + 1;
            }
        }
        
        arsort($tags);
        return $tags;
    }
}
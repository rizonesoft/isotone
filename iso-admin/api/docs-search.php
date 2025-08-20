<?php
/**
 * Documentation Search API
 * 
 * Provides search functionality for the knowledge base
 * Used by Toni AI assistant to find relevant documentation
 * 
 * @package Isotone
 * @since 0.2.1-alpha
 */

// Check authentication
require_once dirname(__DIR__) . '/auth.php';
requireRole('admin');

// Load dependencies
require_once dirname(dirname(__DIR__)) . '/iso-includes/database.php';
isotone_db_connect();

// Set JSON response headers
header('Content-Type: application/json');

// Get request parameters
$query = $_GET['q'] ?? '';
$limit = (int)($_GET['limit'] ?? 10);
$offset = (int)($_GET['offset'] ?? 0);
$tags = $_GET['tags'] ?? null;
$category = $_GET['category'] ?? null;

// Validate parameters
if (empty($query) && empty($tags) && empty($category)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Query, tags, or category parameter required'
    ]);
    exit;
}

// Limit max results to prevent abuse
$limit = min($limit, 50);

try {
    // KB paths
    $docsPath = dirname(dirname(__DIR__)) . '/user-docs';
    $kbPath = $docsPath . '/.kb';
    
    // Check if KB index exists
    if (!file_exists($kbPath . '/index.json')) {
        // Try to generate index
        require_once dirname(dirname(__DIR__)) . '/iso-automation/src/Generators/KbIndexGenerator.php';
        $generator = new \Isotone\Automation\Generators\KbIndexGenerator();
        $generator->generate();
    }
    
    // Load index
    $index = json_decode(file_get_contents($kbPath . '/index.json'), true);
    if (!$index) {
        throw new Exception('Failed to load KB index');
    }
    
    // Load chunks for searching
    $chunks = [];
    if (file_exists($kbPath . '/chunks.jsonl')) {
        $lines = file($kbPath . '/chunks.jsonl', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $chunk = json_decode($line, true);
            if ($chunk) {
                $chunks[] = $chunk;
            }
        }
    }
    
    // Perform search
    $results = [];
    $scores = [];
    
    // Search in documents
    foreach ($index as $path => $doc) {
        $score = 0;
        $matches = [];
        
        // Check category filter
        if ($category && isset($doc['category']) && $doc['category'] !== $category) {
            continue;
        }
        
        // Check tags filter
        if ($tags) {
            $requestedTags = is_array($tags) ? $tags : explode(',', $tags);
            $docTags = $doc['tags'] ?? [];
            $tagMatch = false;
            foreach ($requestedTags as $tag) {
                if (in_array(trim($tag), $docTags)) {
                    $tagMatch = true;
                    $score += 5; // Boost for tag match
                    break;
                }
            }
            if (!$tagMatch && $tags) {
                continue;
            }
        }
        
        // Search query in title and description
        if (!empty($query)) {
            $queryLower = strtolower($query);
            $queryTerms = explode(' ', $queryLower);
            
            // Title match (highest priority)
            $titleLower = strtolower($doc['title'] ?? '');
            foreach ($queryTerms as $term) {
                if (strpos($titleLower, $term) !== false) {
                    $score += 10;
                    $matches[] = 'title';
                }
            }
            
            // Description match
            $descLower = strtolower($doc['description'] ?? '');
            foreach ($queryTerms as $term) {
                if (strpos($descLower, $term) !== false) {
                    $score += 5;
                    $matches[] = 'description';
                }
            }
            
            // Heading matches
            foreach ($doc['headings'] ?? [] as $heading) {
                $headingLower = strtolower($heading['text']);
                foreach ($queryTerms as $term) {
                    if (strpos($headingLower, $term) !== false) {
                        $score += 3;
                        $matches[] = 'heading: ' . $heading['text'];
                    }
                }
            }
        }
        
        // Add to results if score > 0 or if we're filtering by category/tags only
        if ($score > 0 || (empty($query) && ($category || $tags))) {
            $results[] = [
                'doc_id' => $doc['id'],
                'path' => $path,
                'url' => $doc['url'],
                'title' => $doc['title'],
                'description' => $doc['description'],
                'tags' => $doc['tags'] ?? [],
                'category' => $doc['category'] ?? null,
                'score' => $score,
                'matches' => array_unique($matches)
            ];
            $scores[] = $score;
        }
    }
    
    // Search in chunks for deeper content matches
    if (!empty($query) && !empty($chunks)) {
        $queryLower = strtolower($query);
        $queryTerms = explode(' ', $queryLower);
        
        foreach ($chunks as $chunk) {
            $contentLower = strtolower($chunk['content']);
            $chunkScore = 0;
            
            // Check each query term
            foreach ($queryTerms as $term) {
                $termCount = substr_count($contentLower, $term);
                if ($termCount > 0) {
                    $chunkScore += $termCount * 2;
                }
            }
            
            if ($chunkScore > 0) {
                // Find parent document
                $docId = $chunk['doc_id'];
                $docPath = $chunk['path'];
                
                // Check if we already have this document in results
                $found = false;
                foreach ($results as &$result) {
                    if ($result['doc_id'] === $docId) {
                        $result['score'] += $chunkScore;
                        $result['chunks'][] = [
                            'heading' => $chunk['heading'],
                            'content' => substr($chunk['content'], 0, 200) . '...',
                            'url' => $chunk['url']
                        ];
                        $found = true;
                        break;
                    }
                }
                
                // Add new result if not found
                if (!$found && isset($index[$docPath])) {
                    $doc = $index[$docPath];
                    $results[] = [
                        'doc_id' => $docId,
                        'path' => $docPath,
                        'url' => $doc['url'],
                        'title' => $doc['title'],
                        'description' => $doc['description'],
                        'tags' => $doc['tags'] ?? [],
                        'category' => $doc['category'] ?? null,
                        'score' => $chunkScore,
                        'matches' => ['content'],
                        'chunks' => [[
                            'heading' => $chunk['heading'],
                            'content' => substr($chunk['content'], 0, 200) . '...',
                            'url' => $chunk['url']
                        ]]
                    ];
                    $scores[] = $chunkScore;
                }
            }
        }
    }
    
    // Sort by score
    if (!empty($results)) {
        array_multisort($scores, SORT_DESC, $results);
    }
    
    // Apply pagination
    $total = count($results);
    $results = array_slice($results, $offset, $limit);
    
    // Return results
    echo json_encode([
        'success' => true,
        'query' => $query,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'results' => $results
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Search error: ' . $e->getMessage()
    ]);
}
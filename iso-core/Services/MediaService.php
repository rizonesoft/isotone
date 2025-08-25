<?php
/**
 * Media Service
 * 
 * Handles media uploads and image processing using Intervention Image
 * Organizes uploads by year/month like WordPress
 * 
 * @package Isotone
 * @subpackage Services
 */

namespace Isotone\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Carbon\Carbon;

class MediaService
{
    private ImageManager $manager;
    private string $uploadsPath;
    private string $uploadsUrl;
    private array $allowedTypes = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'
    ];
    
    /**
     * Image size configurations
     */
    private array $sizes = [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'medium' => ['width' => 300, 'height' => 300, 'crop' => false],
        'medium_large' => ['width' => 768, 'height' => 0, 'crop' => false],
        'large' => ['width' => 1024, 'height' => 1024, 'crop' => false],
        'full' => ['width' => 1920, 'height' => 1920, 'crop' => false],
    ];
    
    public function __construct()
    {
        // Use Imagick if available, otherwise GD
        if (extension_loaded('imagick')) {
            $this->manager = new ImageManager(new ImagickDriver());
        } else {
            $this->manager = new ImageManager(new GdDriver());
        }
        
        $this->uploadsPath = dirname(__DIR__, 2) . '/iso-content/uploads';
        $this->uploadsUrl = '/isotone/iso-content/uploads';
    }
    
    /**
     * Process an uploaded file
     * 
     * @param array $uploadedFile The $_FILES array element
     * @param array $options Processing options
     * @return array Information about processed files
     */
    public function processUpload(array $uploadedFile, array $options = []): array
    {
        // Validate upload
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload failed with error code: ' . $uploadedFile['error']);
        }
        
        // Check file type
        $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            throw new \Exception('File type not allowed: ' . $extension);
        }
        
        // Create year/month directory structure
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $uploadDir = $this->uploadsPath . '/' . $year . '/' . $month;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = $this->generateUniqueFilename($uploadedFile['name'], $uploadDir);
        $filepath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($uploadedFile['tmp_name'], $filepath)) {
            throw new \Exception('Failed to move uploaded file');
        }
        
        // Process image if it's not an SVG
        if ($extension !== 'svg') {
            $result = $this->processImage($filepath, $options);
        } else {
            $result = [
                'original' => $this->getFileInfo($filepath, $year, $month)
            ];
        }
        
        // Log the upload
        LogService::info('Media uploaded', [
            'filename' => $filename,
            'size' => $uploadedFile['size'],
            'type' => $uploadedFile['type']
        ]);
        
        return $result;
    }
    
    /**
     * Process image and create different sizes
     */
    private function processImage(string $filepath, array $options = []): array
    {
        $result = [];
        $pathinfo = pathinfo($filepath);
        $directory = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];
        
        // Get year/month from path
        preg_match('/(\d{4})\/(\d{2})/', $directory, $matches);
        $year = $matches[1] ?? date('Y');
        $month = $matches[2] ?? date('m');
        
        // Read the original image
        $image = $this->manager->read($filepath);
        
        // Store original info
        $result['original'] = $this->getFileInfo($filepath, $year, $month);
        
        // Get custom sizes or use defaults
        $sizes = $options['sizes'] ?? $this->sizes;
        
        // Generate each size
        foreach ($sizes as $sizeName => $config) {
            // Skip if original is smaller than target size
            if ($image->width() <= $config['width'] && $config['width'] > 0) {
                continue;
            }
            
            // Create size variant
            $sizedImage = clone $image;
            
            if ($config['crop']) {
                // Crop to exact dimensions
                $sizedImage = $sizedImage->cover($config['width'], $config['height']);
            } else {
                // Scale proportionally
                if ($config['width'] > 0 && $config['height'] > 0) {
                    $sizedImage = $sizedImage->scaleDown($config['width'], $config['height']);
                } elseif ($config['width'] > 0) {
                    $sizedImage = $sizedImage->scaleDown(width: $config['width']);
                } elseif ($config['height'] > 0) {
                    $sizedImage = $sizedImage->scaleDown(height: $config['height']);
                }
            }
            
            // Save sized image
            $sizedFilename = $filename . '-' . $sizeName . '.' . $extension;
            $sizedPath = $directory . '/' . $sizedFilename;
            
            // Set quality
            $quality = $options['quality'] ?? 85;
            $sizedImage->save($sizedPath, quality: $quality);
            
            $result[$sizeName] = $this->getFileInfo($sizedPath, $year, $month);
        }
        
        // Generate WebP version if enabled
        if ($options['generate_webp'] ?? true) {
            $webpPath = $directory . '/' . $filename . '.webp';
            $image->toWebp()->save($webpPath, quality: 85);
            $result['webp'] = $this->getFileInfo($webpPath, $year, $month);
        }
        
        return $result;
    }
    
    /**
     * Get file information
     */
    private function getFileInfo(string $filepath, string $year, string $month): array
    {
        $filename = basename($filepath);
        $size = filesize($filepath);
        
        // Get image dimensions if applicable
        $dimensions = [];
        if (@getimagesize($filepath)) {
            list($width, $height) = getimagesize($filepath);
            $dimensions = [
                'width' => $width,
                'height' => $height
            ];
        }
        
        return array_merge([
            'path' => $filepath,
            'url' => $this->uploadsUrl . '/' . $year . '/' . $month . '/' . $filename,
            'filename' => $filename,
            'size' => $size,
            'size_formatted' => $this->formatFileSize($size),
        ], $dimensions);
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(string $originalName, string $directory): string
    {
        $pathinfo = pathinfo($originalName);
        $filename = $this->sanitizeFilename($pathinfo['filename']);
        $extension = strtolower($pathinfo['extension']);
        
        $finalName = $filename . '.' . $extension;
        $counter = 1;
        
        while (file_exists($directory . '/' . $finalName)) {
            $finalName = $filename . '-' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $finalName;
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '-', $filename);
        // Remove multiple dashes
        $filename = preg_replace('/-+/', '-', $filename);
        // Trim dashes
        $filename = trim($filename, '-');
        // Lowercase
        $filename = strtolower($filename);
        
        return $filename ?: 'file';
    }
    
    /**
     * Format file size
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Delete media and all its sizes
     */
    public function deleteMedia(string $filepath): bool
    {
        if (!file_exists($filepath)) {
            return false;
        }
        
        $pathinfo = pathinfo($filepath);
        $directory = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];
        
        // Delete all size variants
        $patterns = [
            $filepath, // Original
            $directory . '/' . $filename . '-*.' . $extension, // Sizes
            $directory . '/' . $filename . '.webp', // WebP
        ];
        
        $deleted = 0;
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) as $file) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }
        
        LogService::info('Media deleted', [
            'filepath' => $filepath,
            'files_deleted' => $deleted
        ]);
        
        return $deleted > 0;
    }
    
    /**
     * Get upload directory for current month
     */
    public function getCurrentUploadDir(): string
    {
        $now = Carbon::now();
        return $this->uploadsPath . '/' . $now->format('Y/m');
    }
    
    /**
     * Set custom image sizes
     */
    public function setSizes(array $sizes): void
    {
        $this->sizes = $sizes;
    }
    
    /**
     * Add a custom image size
     */
    public function addSize(string $name, int $width, int $height, bool $crop = false): void
    {
        $this->sizes[$name] = [
            'width' => $width,
            'height' => $height,
            'crop' => $crop
        ];
    }
}
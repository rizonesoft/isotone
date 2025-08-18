<?php
/**
 * Isotone - Application Core (Symfony-free version)
 * 
 * Simple routing without Symfony dependencies
 * Saves ~3-5MB of memory compared to Symfony implementation
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */
declare(strict_types=1);

namespace Isotone\Core;

use Isotone\Core\Version;
use Isotone\Services\DatabaseService;
use Isotone\Services\ThemeService;
use Isotone\Core\Hook;

class Application
{
    private array $routes = [];
    private string $basePath;
    
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->initializeRoutes();
    }
    
    private function initializeRoutes(): void
    {
        // Register routes
        $this->addRoute('GET', '/', [$this, 'handleHome']);
        $this->addRoute('GET', '/api/version', [$this, 'handleApiVersion']);
        $this->addRoute('GET', '/api/system', [$this, 'handleApiSystem']);
        // Note: /install and /admin are handled directly by Apache, not routed here
    }
    
    public function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }
    
    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Remove /isotone prefix if present
        if (strpos($uri, '/isotone') === 0) {
            $uri = preg_replace('#^/isotone#', '', $uri);
            if (empty($uri)) {
                $uri = '/';
            }
        }
        
        // Find route
        if (isset($this->routes[$method][$uri])) {
            $response = call_user_func($this->routes[$method][$uri]);
            $this->sendResponse($response);
        } else {
            $this->send404();
        }
    }
    
    private function sendResponse($response): void
    {
        if (is_array($response)) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
        } elseif (is_string($response)) {
            echo $response;
        } else {
            echo 'Invalid response';
        }
    }
    
    private function send404(): void
    {
        header('HTTP/1.0 404 Not Found');
        $html = $this->get404Page();
        echo $html;
    }
    
    // Route handlers
    private function handleHome()
    {
        // Initialize database
        DatabaseService::initialize();
        
        // Load hooks system
        if (file_exists($this->basePath . '/iso-core/hooks.php')) {
            require_once $this->basePath . '/iso-core/hooks.php';
        }
        
        // Load theme functions API
        if (file_exists($this->basePath . '/iso-core/theme-functions.php')) {
            require_once $this->basePath . '/iso-core/theme-functions.php';
        }
        
        // Initialize theme service
        $themeService = new ThemeService();
        $activeTheme = $themeService->getActiveTheme();
        
        if ($activeTheme) {
            // Load theme
            return $this->loadTheme($activeTheme);
        } else {
            // No active theme - show landing page
            ob_start();
            if (file_exists($this->basePath . '/iso-includes/landing-page.php')) {
                include $this->basePath . '/iso-includes/landing-page.php';
            } else {
                echo $this->getDefaultHomePage();
            }
            return ob_get_clean();
        }
    }
    
    /**
     * Load and render active theme
     */
    private function loadTheme(array $themeInfo): string
    {
        $themePath = $themeInfo['path'];
        
        // Load theme functions.php if exists
        $functionsFile = $themePath . '/functions.php';
        if (file_exists($functionsFile)) {
            require_once $functionsFile;
        }
        
        // Fire theme initialization hooks
        if (function_exists('do_action')) {
            do_action('after_setup_theme');
            do_action('init');
            do_action('iso_loaded');
        }
        
        // Check for theme compatibility file
        $compatFile = $themePath . '/compat.php';
        if (file_exists($compatFile)) {
            require_once $compatFile;
        }
        
        // Determine which template to load
        $template = $this->getTemplate($themePath);
        
        if (!file_exists($template)) {
            // Fallback to index.php
            $template = $themePath . '/index.php';
        }
        
        // Start output buffering
        ob_start();
        
        // Set up global variables for theme
        global $isotone_theme;
        $isotone_theme = $themeInfo;
        
        // Fire template redirect hook
        if (function_exists('do_action')) {
            do_action('template_redirect');
        }
        
        // Include the template
        if (file_exists($template)) {
            include $template;
        } else {
            echo '<h1>Theme template not found</h1>';
            echo '<p>The active theme is missing required template files.</p>';
        }
        
        $html = ob_get_clean();
        
        // Fire shutdown hooks
        if (function_exists('do_action')) {
            do_action('shutdown');
        }
        
        return $html;
    }
    
    /**
     * Determine which template file to use
     */
    private function getTemplate(string $themePath): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Remove /isotone from path if present
        $uri = preg_replace('#^/isotone#', '', $uri);
        
        // Template hierarchy (simplified for now)
        if ($uri === '/' || $uri === '') {
            // Check for front-page.php, home.php, then index.php
            if (file_exists($themePath . '/front-page.php')) {
                return $themePath . '/front-page.php';
            }
            if (file_exists($themePath . '/home.php')) {
                return $themePath . '/home.php';
            }
        }
        
        // Default to index.php
        return $themePath . '/index.php';
    }
    
    private function handleApiVersion()
    {
        $versionInfo = Version::current();
        return [
            'version' => $versionInfo['version'],
            'stage' => $versionInfo['stage'],
            'codename' => $versionInfo['codename'],
            'schema' => $versionInfo['schema'],
            'api_version' => '1.0',
            'php_version' => $versionInfo['php_version'],
            'php_required' => $versionInfo['php_required']
        ];
    }
    
    private function handleApiSystem()
    {
        $versionInfo = Version::current();
        
        return [
            'version' => $versionInfo,
            'environment' => [
                'php' => PHP_VERSION,
                'os' => PHP_OS,
                'sapi' => PHP_SAPI,
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ],
            'memory' => [
                'usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
                'limit' => ini_get('memory_limit')
            ],
            'features' => Version::getFeatures(),
            'update_check' => Version::checkForUpdates()
        ];
    }
    
    private function getDefaultHomePage(): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <title>Isotone</title>
            <style>
                body { 
                    font-family: system-ui; 
                    text-align: center; 
                    padding: 50px;
                    background: linear-gradient(135deg, #0A0E27 0%, #0F1433 50%, #0A0E27 100%);
                    color: white;
                }
                h1 { color: #00d9ff; }
                .container {
                    background: rgba(255, 255, 255, 0.02);
                    backdrop-filter: blur(20px);
                    border-radius: 24px;
                    padding: 3rem;
                    max-width: 600px;
                    margin: 0 auto;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Welcome to Isotone</h1>
                <p>Lightweight CMS - No active theme</p>
                <p>Memory Usage: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB</p>
                <p><a href="/isotone/iso-admin/" style="color: #00d9ff;">Admin Panel</a></p>
            </div>
        </body>
        </html>
        HTML;
    }
    
    private function get404Page(): string
    {
        $baseUrl = '';
        if (defined('SITE_URL')) {
            $baseUrl = rtrim(SITE_URL, '/');
        } else {
            $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $scheme . '://' . $host . '/isotone';
        }
        
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Page Not Found | Isotone</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: system-ui;
                    background: linear-gradient(135deg, #0A0E27 0%, #0F1433 50%, #0A0E27 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                }
                .error-container {
                    background: rgba(255, 255, 255, 0.02);
                    backdrop-filter: blur(20px);
                    border-radius: 24px;
                    padding: 4rem;
                    text-align: center;
                    max-width: 550px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
                h1 { 
                    font-size: 8rem;
                    background: linear-gradient(135deg, #FF3366 0%, #00D9FF 50%, #00FF88 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    margin-bottom: 1rem;
                }
                .error-icon {
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 2rem;
                    background: linear-gradient(135deg, #FF3366, #FF6699);
                    border-radius: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 2.5rem;
                }
                .error-icon::before { content: '⚠'; }
                p { color: rgba(255, 255, 255, 0.7); margin-bottom: 2.5rem; }
                a {
                    display: inline-block;
                    padding: 1rem 2.5rem;
                    background: linear-gradient(135deg, #00D9FF, #00FF88);
                    color: #0A0E27;
                    text-decoration: none;
                    border-radius: 12px;
                    font-weight: 600;
                }
                a:hover { transform: translateY(-2px); }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon"></div>
                <h1>404</h1>
                <p>Oops! The page you're looking for has vanished into the digital void.</p>
                <a href="{$baseUrl}">Return Home</a>
            </div>
        </body>
        </html>
        HTML;
    }
}
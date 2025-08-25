<?php
/**
 * Isotone - Router System
 * 
 * Lightweight routing with pattern matching, error handling, and permalink support
 * Works identically on Apache, Nginx, and LiteSpeed
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

class Router
{
    private array $routes = [];
    private array $patterns = [];
    private array $errorHandlers = [];
    private string $basePath;
    private ?array $currentRoute = null;
    private array $routeParams = [];
    
    // Route parameter patterns
    private const PARAM_PATTERNS = [
        'any' => '([^/]+)',
        'id' => '(\d+)',
        'slug' => '([a-z0-9\-]+)',
        'year' => '(\d{4})',
        'month' => '(0[1-9]|1[0-2])',
        'day' => '(0[1-9]|[12][0-9]|3[01])',
        'alpha' => '([a-zA-Z]+)',
        'alphanum' => '([a-zA-Z0-9]+)'
    ];
    
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->initializeDefaultRoutes();
        $this->initializeErrorHandlers();
    }
    
    /**
     * Initialize default system routes
     */
    private function initializeDefaultRoutes(): void
    {
        // System routes
        $this->addRoute('GET', '/', [$this, 'handleHome']);
        
        // API routes - now handled through router
        $this->addRoute('GET', '/api/version', [$this, 'handleApiVersion']);
        $this->addRoute('GET', '/api/system', [$this, 'handleApiSystem']);
        $this->addRoute('GET', '/api', [$this, 'handleApiDiscovery']);
        
        // Add pattern for general API routing
        $this->addRoute('*', '/api/{endpoint:any}', [$this, 'handleApiEndpoint']);
        
        // Admin routes (for future integration)
        // $this->addRoute('*', '/admin/{path:any}', [$this, 'handleAdmin']);
    }
    
    /**
     * Initialize error handlers
     */
    private function initializeErrorHandlers(): void
    {
        // Register error handlers for common HTTP errors
        // Each handler receives the error code and message as parameters
        $errorCodes = [400, 401, 403, 404, 405, 408, 500, 502, 503, 504];
        
        foreach ($errorCodes as $code) {
            $this->setErrorHandler($code, function($errorCode, $message = '') {
                $this->handleError($errorCode, $message);
            });
        }
    }
    
    /**
     * Add a route with pattern matching support
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH, *, etc.)
     * @param string $pattern Route pattern with optional parameters
     * @param callable $handler Route handler
     * @param array $options Additional route options
     */
    public function addRoute(string $method, string $pattern, callable $handler, array $options = []): void
    {
        // Parse pattern for parameters
        $regex = $this->convertPatternToRegex($pattern);
        $params = $this->extractParameterNames($pattern);
        
        $route = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
            'params' => $params,
            'options' => $options
        ];
        
        // Store both exact and pattern routes
        if (strpos($pattern, '{') === false) {
            // Exact route
            $this->routes[$method][$pattern] = $route;
        } else {
            // Pattern route
            $this->patterns[] = $route;
        }
    }
    
    /**
     * Convert route pattern to regex
     * Supports: {param}, {param:type}, {param?}
     */
    private function convertPatternToRegex(string $pattern): string
    {
        // Escape special regex characters (except our param placeholders)
        $pattern = preg_quote($pattern, '#');
        
        // Restore the curly braces for parameters (they were escaped by preg_quote)
        $pattern = str_replace(['\\{', '\\}'], ['{', '}'], $pattern);
        
        // Replace {param:type} with appropriate regex
        $pattern = preg_replace_callback(
            '#{([^}]+):([^}]+)}#',
            function ($matches) {
                $name = $matches[1];
                $type = $matches[2];
                
                // Check for optional parameter
                $optional = '';
                if (substr($name, -1) === '?') {
                    $name = substr($name, 0, -1);
                    $optional = '?';
                }
                
                $regex = self::PARAM_PATTERNS[$type] ?? self::PARAM_PATTERNS['any'];
                return $regex . $optional;
            },
            $pattern
        );
        
        // Replace {param} with default pattern
        $pattern = preg_replace('#{([^}]+)}#', self::PARAM_PATTERNS['any'], $pattern);
        
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Extract parameter names from pattern
     */
    private function extractParameterNames(string $pattern): array
    {
        preg_match_all('/\{([^:}]+)(?::[^}]+)?\}/', $pattern, $matches);
        
        $params = [];
        foreach ($matches[1] as $param) {
            // Remove optional marker if present
            $params[] = rtrim($param, '?');
        }
        
        return $params;
    }
    
    /**
     * Set error handler for specific HTTP status code
     */
    public function setErrorHandler(int $code, callable $handler): void
    {
        $this->errorHandlers[$code] = $handler;
    }
    
    /**
     * Main request handler
     */
    public function handle(): void
    {
        // Check if this is an error document request from Apache
        if (isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] >= 400) {
            $this->handleHttpError((int)$_SERVER['REDIRECT_STATUS']);
            return;
        }
        
        // Check if error code is passed via query string (from ErrorDocument)
        if (isset($_GET['code']) && is_numeric($_GET['code']) && $_GET['code'] >= 400) {
            $this->handleHttpError((int)$_GET['code']);
            return;
        }
        
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $this->parseUri();
        
        // Try to find matching route
        $route = $this->findRoute($method, $uri);
        
        if ($route) {
            $this->currentRoute = $route;
            $response = call_user_func_array($route['handler'], $this->routeParams);
            $this->sendResponse($response);
        } else {
            $this->handleHttpError(404);
        }
    }
    
    /**
     * Parse and clean the request URI
     */
    private function parseUri(): string
    {
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
        
        // Ensure URI starts with /
        if ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        return $uri;
    }
    
    /**
     * Find matching route for method and URI
     */
    private function findRoute(string $method, string $uri): ?array
    {
        // Clear previous route params
        $this->routeParams = [];
        
        // Check exact routes first
        if (isset($this->routes[$method][$uri])) {
            return $this->routes[$method][$uri];
        }
        
        // Check wildcard method exact routes
        if (isset($this->routes['*'][$uri])) {
            return $this->routes['*'][$uri];
        }
        
        // Check pattern routes
        foreach ($this->patterns as $route) {
            // Check method match (including wildcard)
            if ($route['method'] !== '*' && $route['method'] !== $method) {
                continue;
            }
            
            // Check pattern match
            if (preg_match($route['regex'], $uri, $matches)) {
                // Extract parameters
                array_shift($matches); // Remove full match
                
                foreach ($route['params'] as $index => $paramName) {
                    $this->routeParams[$paramName] = $matches[$index] ?? null;
                }
                
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Handle HTTP errors
     */
    public function handleHttpError(int $code, string $message = ''): void
    {
        http_response_code($code);
        
        if (isset($this->errorHandlers[$code])) {
            $response = call_user_func($this->errorHandlers[$code], $code, $message);
            $this->sendResponse($response);
        } else {
            // Fallback to default error handler
            $this->handleError($code, $message);
        }
    }
    
    /**
     * Default error handler
     */
    private function handleError(int $code = 404, string $message = ''): void
    {
        // Set error code for error template
        $_GET['code'] = $code;
        
        // Try to use the universal error handler
        $errorFile = $this->basePath . '/server/error.php';
        if (file_exists($errorFile)) {
            require_once $errorFile;
        } else {
            // Minimal fallback
            echo "Error $code: " . ($message ?: 'An error occurred');
        }
        
        exit;
    }
    
    /**
     * Send response to client
     */
    private function sendResponse($response): void
    {
        if (is_array($response)) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
        } elseif (is_string($response)) {
            echo $response;
        } elseif ($response === null) {
            // No response, already handled
        } else {
            echo 'Invalid response';
        }
    }
    
    /**
     * Get current route information
     */
    public function getCurrentRoute(): ?array
    {
        return $this->currentRoute;
    }
    
    /**
     * Get route parameters
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }
    
    /**
     * Get specific route parameter
     */
    public function getRouteParam(string $name, $default = null)
    {
        return $this->routeParams[$name] ?? $default;
    }
    
    // ========================================================================
    // Route Handlers
    // ========================================================================
    
    /**
     * Handle home/frontend routes
     */
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
            // No active theme - load template
            return $this->loadTemplate('no-theme');
        }
    }
    
    /**
     * Load theme
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
        $template = $this->getThemeTemplate($themePath);
        
        if (!file_exists($template)) {
            // Fallback to index.php
            $template = $themePath . '/index.php';
        }
        
        // Start output buffering
        ob_start();
        
        // Set up global variables for theme
        global $isotone_theme, $isotone_router;
        $isotone_theme = $themeInfo;
        $isotone_router = $this;
        
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
     * Determine which theme template to use
     */
    private function getThemeTemplate(string $themePath): string
    {
        $uri = $this->parseUri();
        
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
    
    /**
     * Load a system template
     */
    private function loadTemplate(string $template): string
    {
        $templateFile = $this->basePath . '/iso-includes/templates/' . $template . '.php';
        
        if (file_exists($templateFile)) {
            ob_start();
            include $templateFile;
            return ob_get_clean();
        }
        
        // Fallback HTML
        return $this->getDefaultPage($template);
    }
    
    /**
     * Get default page HTML (minimal fallback)
     */
    private function getDefaultPage(string $type): string
    {
        switch ($type) {
            case 'no-theme':
                return '<!DOCTYPE html>
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
        a { color: #00d9ff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Isotone</h1>
        <p>Lightweight CMS - No active theme</p>
        <p>Memory Usage: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB</p>
        <p><a href="/isotone/iso-admin/">Admin Panel</a></p>
    </div>
</body>
</html>';
            
            default:
                return '<h1>Page not found</h1>';
        }
    }
    
    /**
     * Handle API version endpoint
     */
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
    
    /**
     * Handle API system endpoint
     */
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
    
    /**
     * Handle API discovery endpoint
     */
    private function handleApiDiscovery()
    {
        // Load the API discovery file
        $apiFile = $this->basePath . '/iso-api/index.php';
        if (file_exists($apiFile)) {
            // Capture output
            ob_start();
            include $apiFile;
            $output = ob_get_clean();
            
            // Try to decode JSON response
            $json = json_decode($output, true);
            if ($json) {
                return $json;
            }
            
            // Return raw output if not JSON
            return $output;
        }
        
        return [
            'error' => 'API discovery not available'
        ];
    }
    
    /**
     * Handle general API endpoints
     */
    private function handleApiEndpoint()
    {
        $endpoint = $this->getRouteParam('endpoint');
        
        // Map to actual API file
        $apiFile = $this->basePath . '/iso-api/' . $endpoint . '.php';
        
        // Check for admin API endpoints
        if (strpos($endpoint, 'admin/') === 0) {
            $apiFile = $this->basePath . '/iso-api/' . $endpoint . '.php';
        }
        
        if (file_exists($apiFile)) {
            // Set proper headers
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            
            // Include the API file
            include $apiFile;
            return null; // Response already sent by API file
        }
        
        // API endpoint not found
        $this->handleHttpError(404, 'API endpoint not found');
    }
    
    /**
     * Add WordPress-style permalink support
     * 
     * @param string $structure Permalink structure (e.g., '/%year%/%monthnum%/%postname%/')
     */
    public function setPermalinkStructure(string $structure): void
    {
        // Convert WordPress-style tokens to route patterns
        $pattern = str_replace(
            ['%year%', '%monthnum%', '%day%', '%postname%', '%category%'],
            ['{year:year}', '{month:month}', '{day:day}', '{slug:slug}', '{category:slug}'],
            $structure
        );
        
        // Add route for this permalink structure
        $this->addRoute('GET', $pattern, [$this, 'handlePermalink']);
    }
    
    /**
     * Handle permalink requests
     */
    private function handlePermalink()
    {
        // This would integrate with a post/page system
        // For now, just return the parameters
        return [
            'type' => 'permalink',
            'params' => $this->routeParams
        ];
    }
}
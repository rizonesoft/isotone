<?php
/**
 * Isotone - Application Core
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */
declare(strict_types=1);

namespace Isotone\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Isotone\Core\Version;
use Isotone\Services\DatabaseService;
use Isotone\Services\ThemeService;
use Isotone\Core\Hook;

class Application
{
    private string $basePath;
    private RouteCollection $routes;
    
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->initializeRoutes();
    }
    
    private function initializeRoutes(): void
    {
        $this->routes = new RouteCollection();
        
        // Home route
        $this->routes->add('home', new Route('/', [
            '_controller' => [$this, 'handleHome']
        ]));
        
        // API routes
        $this->routes->add('api_version', new Route('/api/version', [
            '_controller' => [$this, 'handleApiVersion']
        ]));
        
        $this->routes->add('api_system', new Route('/api/system', [
            '_controller' => [$this, 'handleApiSystem']
        ]));
        
        // Installation check
        $this->routes->add('install', new Route('/install', [
            '_controller' => [$this, 'handleInstall']
        ]));
        
        // Admin routes (placeholder)
        $this->routes->add('admin', new Route('/admin', [
            '_controller' => [$this, 'handleAdmin']
        ]));
    }
    
    public function handle(Request $request): Response
    {
        try {
            // Get the path, removing the base directory from the URL
            $pathInfo = $request->getPathInfo();
            
            // Remove /isotone from the beginning if it exists
            $pathInfo = preg_replace('#^/isotone#', '', $pathInfo);
            if (empty($pathInfo)) {
                $pathInfo = '/';
            }
            
            $context = new RequestContext();
            $context->fromRequest($request);
            
            $matcher = new UrlMatcher($this->routes, $context);
            $parameters = $matcher->match($pathInfo);
            
            $controller = $parameters['_controller'];
            unset($parameters['_controller'], $parameters['_route']);
            
            if (is_callable($controller)) {
                return call_user_func_array($controller, [$request, $parameters]);
            }
            
            return new Response('Internal Server Error', 500);
            
        } catch (ResourceNotFoundException $e) {
            return $this->handle404();
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
    
    public function terminate(Request $request, Response $response): void
    {
        // Cleanup tasks can go here
    }
    
    private function handleHome(Request $request): Response
    {
        // Initialize database if not already done
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
            return $this->loadTheme($activeTheme, $request);
        } else {
            // No active theme - show landing page
            ob_start();
            include $this->basePath . '/iso-includes/landing-page.php';
            $html = ob_get_clean();
            return new Response($html);
        }
    }
    
    /**
     * Load and render active theme
     */
    private function loadTheme(array $themeInfo, Request $request): Response
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
        $template = $this->getTemplate($themePath, $request);
        
        if (!file_exists($template)) {
            // Fallback to index.php
            $template = $themePath . '/index.php';
        }
        
        // Start output buffering
        ob_start();
        
        // Set up global variables for theme
        global $isotone_theme, $isotone_request;
        $isotone_theme = $themeInfo;
        $isotone_request = $request;
        
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
        
        return new Response($html);
    }
    
    /**
     * Determine which template file to use
     */
    private function getTemplate(string $themePath, Request $request): string
    {
        $pathInfo = $request->getPathInfo();
        
        // Remove /isotone from path if present
        $pathInfo = preg_replace('#^/isotone#', '', $pathInfo);
        
        // Template hierarchy (simplified for now)
        if ($pathInfo === '/' || $pathInfo === '') {
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
    
    private function getBaseUrl(Request $request): string
    {
        // Use APP_URL from .env if set
        $appUrl = defined('SITE_URL') ? SITE_URL : '';
        if (!empty($appUrl)) {
            return rtrim($appUrl, '/');
        }
        
        // Otherwise, build it from the request
        $scheme = $request->getScheme();
        $host = $request->getHttpHost();
        $basePath = rtrim(dirname($request->getScriptName()), '/');
        
        // Remove /public from the path if it exists
        $basePath = preg_replace('#/public$#', '', $basePath);
        
        return $scheme . '://' . $host . $basePath;
    }
    
    private function handleInstall(Request $request): Response
    {
        $baseUrl = $this->getBaseUrl($request);
        
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Installation - Isotone</title>
            <meta name="description" content="Install and configure Isotone on your server.">
            <meta name="robots" content="noindex, nofollow">
            <meta name="theme-color" content="#0A0E27">
            <!-- Modern favicon setup using PNG -->
            <link rel="icon" type="image/png" sizes="512x512" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="192x192" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="16x16" href="{$baseUrl}/favicon.png">
            <link rel="apple-touch-icon" href="{$baseUrl}/favicon.png">
            <link rel="manifest" href="{$baseUrl}/site.webmanifest">
            <!-- Fallback for older browsers if ICO exists -->
            <link rel="shortcut icon" href="{$baseUrl}/favicon.ico">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
                
                :root {
                    --primary: #0A0E27;
                    --accent: #00D9FF;
                    --accent-green: #00FF88;
                    --surface: rgba(255, 255, 255, 0.03);
                    --text-primary: #FFFFFF;
                    --text-secondary: rgba(255, 255, 255, 0.7);
                    --border: rgba(255, 255, 255, 0.1);
                }
                
                body { 
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                    background: linear-gradient(135deg, #0A0E27 0%, #0F1433 50%, #0A0E27 100%);
                    background-attachment: fixed;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: var(--text-primary);
                    letter-spacing: 0.01em;
                }
                
                .container {
                    background: rgba(255, 255, 255, 0.02);
                    backdrop-filter: blur(20px);
                    border-radius: 24px;
                    padding: 3rem;
                    max-width: 600px;
                    border: 1px solid var(--border);
                    text-align: center;
                }
                
                h1 {
                    font-size: 2.5rem;
                    margin-bottom: 1rem;
                    background: linear-gradient(135deg, #FFFFFF 0%, #00D9FF 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    letter-spacing: -0.01em;
                    font-weight: 800;
                }
                
                p { 
                    color: var(--text-secondary); 
                    margin-bottom: 2rem; 
                    letter-spacing: 0.03em;
                    line-height: 1.6;
                }
                
                .icon {
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 2rem;
                    background: linear-gradient(135deg, var(--accent), var(--accent-green));
                    border-radius: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 2.5rem;
                }
                
                .icon::before { content: '⚙️'; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="icon"></div>
                <h1>Installation Wizard</h1>
                <p>The installation wizard is coming soon. For now, please configure your database manually in the .env file.</p>
            </div>
        </body>
        </html>
        HTML;
        
        return new Response($html);
    }
    
    private function handleAdmin(Request $request): Response
    {
        $baseUrl = $this->getBaseUrl($request);
        
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Panel - Isotone</title>
            <meta name="description" content="Manage your Isotone content and settings.">
            <meta name="robots" content="noindex, nofollow">
            <meta name="theme-color" content="#0A0E27">
            <!-- Modern favicon setup using PNG -->
            <link rel="icon" type="image/png" sizes="512x512" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="192x192" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="16x16" href="{$baseUrl}/favicon.png">
            <link rel="apple-touch-icon" href="{$baseUrl}/favicon.png">
            <link rel="manifest" href="{$baseUrl}/site.webmanifest">
            <!-- Fallback for older browsers if ICO exists -->
            <link rel="shortcut icon" href="{$baseUrl}/favicon.ico">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
                
                :root {
                    --primary: #0A0E27;
                    --accent: #00D9FF;
                    --accent-green: #00FF88;
                    --surface: rgba(255, 255, 255, 0.03);
                    --text-primary: #FFFFFF;
                    --text-secondary: rgba(255, 255, 255, 0.7);
                    --border: rgba(255, 255, 255, 0.1);
                }
                
                body { 
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                    background: linear-gradient(135deg, #0A0E27 0%, #0F1433 50%, #0A0E27 100%);
                    background-attachment: fixed;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: var(--text-primary);
                    letter-spacing: 0.01em;
                }
                
                .container {
                    background: rgba(255, 255, 255, 0.02);
                    backdrop-filter: blur(20px);
                    border-radius: 24px;
                    padding: 3rem;
                    max-width: 600px;
                    border: 1px solid var(--border);
                    text-align: center;
                }
                
                h1 {
                    font-size: 2.5rem;
                    margin-bottom: 1rem;
                    background: linear-gradient(135deg, #FFFFFF 0%, #00D9FF 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    letter-spacing: -0.01em;
                    font-weight: 800;
                }
                
                p { 
                    color: var(--text-secondary); 
                    margin-bottom: 2rem; 
                    letter-spacing: 0.03em;
                    line-height: 1.6;
                }
                
                .icon {
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 2rem;
                    background: linear-gradient(135deg, var(--accent), var(--accent-green));
                    border-radius: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 2.5rem;
                }
                
                .icon::before { content: '🎛️'; }
                
                a {
                    display: inline-block;
                    margin-top: 1rem;
                    color: var(--accent);
                    text-decoration: none;
                    font-weight: 500;
                    letter-spacing: 0.02em;
                    transition: color 0.3s;
                }
                
                a:hover { color: var(--accent-green); }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="icon"></div>
                <h1>Admin Panel</h1>
                <p>The admin panel is currently under development. Check back soon for content management features!</p>
                <a href="{$baseUrl}">← Back to Homepage</a>
            </div>
        </body>
        </html>
        HTML;
        
        return new Response($html);
    }
    
    private function handle404(): Response
    {
        // Create a dummy request to get the base URL
        $request = Request::createFromGlobals();
        $baseUrl = $this->getBaseUrl($request);
        
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Page Not Found | Isotone</title>
            <meta name="description" content="The page you're looking for could not be found.">
            <meta name="robots" content="noindex, nofollow">
            <meta name="theme-color" content="#0A0E27">
            <!-- Modern favicon setup using PNG -->
            <link rel="icon" type="image/png" sizes="512x512" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="192x192" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="{$baseUrl}/favicon.png">
            <link rel="icon" type="image/png" sizes="16x16" href="{$baseUrl}/favicon.png">
            <link rel="apple-touch-icon" href="{$baseUrl}/favicon.png">
            <link rel="manifest" href="{$baseUrl}/site.webmanifest">
            <!-- Fallback for older browsers if ICO exists -->
            <link rel="shortcut icon" href="{$baseUrl}/favicon.ico">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
                
                :root {
                    --primary: #0A0E27;
                    --accent: #00D9FF;
                    --accent-green: #00FF88;
                    --danger: #FF3366;
                    --surface: rgba(255, 255, 255, 0.03);
                    --text-primary: #FFFFFF;
                    --text-secondary: rgba(255, 255, 255, 0.7);
                    --border: rgba(255, 255, 255, 0.1);
                }
                
                body { 
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                    background: linear-gradient(135deg, #0A0E27 0%, #0F1433 50%, #0A0E27 100%);
                    background-attachment: fixed;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: var(--text-primary);
                    position: relative;
                    overflow-x: hidden;
                    overflow-y: auto;
                    letter-spacing: 0.01em;
                }
                
                /* Static gradient overlay */
                body::before {
                    content: '';
                    position: fixed;
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    background: 
                        radial-gradient(ellipse at top right, rgba(255, 51, 102, 0.06) 0%, transparent 40%),
                        radial-gradient(ellipse at bottom left, rgba(0, 217, 255, 0.06) 0%, transparent 40%);
                    pointer-events: none;
                }
                
                /* Static grid background */
                .grid-bg {
                    position: fixed;
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    background-image: 
                        linear-gradient(rgba(255, 51, 102, 0.02) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(255, 51, 102, 0.02) 1px, transparent 1px);
                    background-size: 50px 50px;
                    pointer-events: none;
                    opacity: 0.4;
                }
                
                .error-container {
                    background: rgba(255, 255, 255, 0.02);
                    backdrop-filter: blur(20px);
                    -webkit-backdrop-filter: blur(20px);
                    border-radius: 24px;
                    padding: 4rem;
                    box-shadow: 
                        0 0 0 1px rgba(255, 51, 102, 0.1),
                        0 10px 40px rgba(0, 0, 0, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
                    text-align: center;
                    max-width: 550px;
                    width: 90%;
                    border: 1px solid var(--border);
                    position: relative;
                    animation: fadeInUp 0.6s ease-out;
                }
                
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                /* Glitch effect for 404 */
                h1 { 
                    font-size: 8rem;
                    font-weight: 900;
                    background: linear-gradient(135deg, #FF3366 0%, #00D9FF 50%, #00FF88 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    margin-bottom: 1rem;
                    letter-spacing: -0.02em;
                    position: relative;
                    animation: glitch 3s ease-in-out infinite;
                    text-shadow: 
                        0 0 30px rgba(255, 51, 102, 0.5),
                        0 0 60px rgba(255, 51, 102, 0.3);
                }
                
                @keyframes glitch {
                    0%, 100% {
                        text-shadow: 
                            0 0 30px rgba(255, 51, 102, 0.5),
                            0 0 60px rgba(255, 51, 102, 0.3);
                    }
                    50% {
                        text-shadow: 
                            -2px 0 30px rgba(0, 217, 255, 0.5),
                            2px 0 60px rgba(0, 255, 136, 0.3);
                    }
                }
                
                /* Glitch layers */
                h1::before,
                h1::after {
                    content: '404';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(135deg, #FF3366 0%, #00D9FF 50%, #00FF88 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                }
                
                h1::before {
                    animation: glitch-1 0.5s ease-in-out infinite;
                    z-index: -1;
                }
                
                h1::after {
                    animation: glitch-2 0.5s ease-in-out infinite;
                    z-index: -2;
                }
                
                @keyframes glitch-1 {
                    0%, 100% {
                        clip-path: inset(0 0 0 0);
                        transform: translate(0);
                    }
                    20% {
                        clip-path: inset(0 100% 0 0);
                        transform: translate(-2px, 2px);
                    }
                    40% {
                        clip-path: inset(0 0 100% 0);
                        transform: translate(2px, -2px);
                    }
                    60% {
                        clip-path: inset(100% 0 0 0);
                        transform: translate(-2px, -2px);
                    }
                    80% {
                        clip-path: inset(0 0 0 100%);
                        transform: translate(2px, 2px);
                    }
                }
                
                @keyframes glitch-2 {
                    0%, 100% {
                        clip-path: inset(0 0 0 0);
                        transform: translate(0);
                    }
                    20% {
                        clip-path: inset(100% 0 0 0);
                        transform: translate(2px, 2px);
                    }
                    40% {
                        clip-path: inset(0 0 0 100%);
                        transform: translate(-2px, 2px);
                    }
                    60% {
                        clip-path: inset(0 100% 0 0);
                        transform: translate(2px, -2px);
                    }
                    80% {
                        clip-path: inset(0 0 100% 0);
                        transform: translate(-2px, -2px);
                    }
                }
                
                .error-icon {
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 2rem;
                    background: linear-gradient(135deg, var(--danger), #FF6699);
                    border-radius: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 2.5rem;
                    animation: pulse 2s ease-in-out infinite;
                    box-shadow: 
                        0 0 40px rgba(255, 51, 102, 0.4),
                        inset 0 2px 4px rgba(255, 255, 255, 0.2);
                }
                
                @keyframes pulse {
                    0%, 100% { 
                        transform: scale(1);
                        box-shadow: 0 0 40px rgba(255, 51, 102, 0.4);
                    }
                    50% { 
                        transform: scale(1.05);
                        box-shadow: 0 0 60px rgba(255, 51, 102, 0.6);
                    }
                }
                
                .error-icon::before {
                    content: '⚠';
                    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
                }
                
                p {
                    color: var(--text-secondary);
                    margin-bottom: 2.5rem;
                    font-size: 1.1rem;
                    font-weight: 500;
                    letter-spacing: 0.03em;
                }
                
                .subtitle {
                    color: var(--text-secondary);
                    font-size: 0.9rem;
                    margin-bottom: 3rem;
                    opacity: 0.8;
                    letter-spacing: 0.04em;
                }
                
                a {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 1rem 2.5rem;
                    background: linear-gradient(135deg, var(--accent), var(--accent-green));
                    color: var(--primary);
                    text-decoration: none;
                    border-radius: 12px;
                    transition: all 0.3s ease;
                    font-weight: 600;
                    font-size: 1rem;
                    letter-spacing: 0.04em;
                    box-shadow: 
                        0 4px 20px rgba(0, 217, 255, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
                    position: relative;
                    overflow-x: hidden;
                    overflow-y: auto;
                }
                
                a::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                    transition: left 0.5s;
                }
                
                a:hover::before {
                    left: 100%;
                }
                
                a:hover {
                    transform: translateY(-2px);
                    box-shadow: 
                        0 6px 30px rgba(0, 217, 255, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
                }
                
                a::after {
                    content: '→';
                    font-size: 1.2rem;
                }
                
                /* Remove floating particles for cleaner look */
            </style>
        </head>
        <body>
            <div class="grid-bg"></div>
            <div class="error-container">
                <div class="error-icon"></div>
                <h1>404</h1>
                <p>Oops! The page you're looking for has vanished into the digital void.</p>
                <p class="subtitle">It might have been moved, deleted, or never existed at all.</p>
                <a href="{$baseUrl}">Return Home</a>
            </div>
        </body>
        </html>
        HTML;
        
        return new Response($html, 404);
    }
    
    /**
     * Handle API version endpoint
     */
    private function handleApiVersion(Request $request): Response
    {
        $versionInfo = Version::current();
        
        $response = new Response(json_encode($versionInfo, JSON_PRETTY_PRINT));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * Handle API system information endpoint
     */
    private function handleApiSystem(Request $request): Response
    {
        $systemInfo = [
            'version' => Version::current(),
            'compatibility' => Version::getCompatibility(),
            'environment' => [
                'php' => PHP_VERSION,
                'os' => PHP_OS,
                'sapi' => PHP_SAPI,
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ],
            'features' => Version::getFeatures(),
            'update_check' => Version::checkForUpdates()
        ];
        
        $response = new Response(json_encode($systemInfo, JSON_PRETTY_PRINT));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    private function handleError(\Exception $e): Response
    {
        $message = (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : 'An error occurred';
        
        return new Response(
            '<h1>Error</h1><p>' . htmlspecialchars($message) . '</p>',
            500
        );
    }
}
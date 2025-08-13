<?php
declare(strict_types=1);

namespace Isotone\Core;

use Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Application
{
    private string $basePath;
    private RouteCollection $routes;
    
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->loadEnvironment();
        $this->initializeRoutes();
    }
    
    private function loadEnvironment(): void
    {
        if (file_exists($this->basePath . '/.env')) {
            $dotenv = Dotenv::createImmutable($this->basePath);
            $dotenv->load();
        }
    }
    
    private function initializeRoutes(): void
    {
        $this->routes = new RouteCollection();
        
        // Home route
        $this->routes->add('home', new Route('/', [
            '_controller' => [$this, 'handleHome']
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
        $baseUrl = $this->getBaseUrl($request);
        $composerInstalled = file_exists($this->basePath . '/vendor/autoload.php');
        $composerStatus = $composerInstalled ? 'Installed' : 'Not installed';
        $composerBadgeClass = $composerInstalled ? 'badge-success' : 'badge-warning';
        $nextStep = $composerInstalled 
            ? 'Your development environment is ready!' 
            : 'Next step: Run <code>composer install</code> to download dependencies';
        
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome to Isotone CMS</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #333;
                }
                .container {
                    background: white;
                    border-radius: 12px;
                    padding: 3rem;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    max-width: 600px;
                    text-align: center;
                }
                h1 {
                    color: #764ba2;
                    margin-bottom: 1rem;
                    font-size: 2.5rem;
                }
                .subtitle {
                    color: #666;
                    margin-bottom: 2rem;
                    font-size: 1.1rem;
                }
                .status {
                    background: #f0f4f8;
                    padding: 1rem;
                    border-radius: 8px;
                    margin: 2rem 0;
                }
                .status-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 0.5rem 0;
                }
                .badge {
                    padding: 0.25rem 0.75rem;
                    border-radius: 12px;
                    font-size: 0.85rem;
                    font-weight: bold;
                }
                .badge-success {
                    background: #10b981;
                    color: white;
                }
                .badge-warning {
                    background: #f59e0b;
                    color: white;
                }
                .btn {
                    display: inline-block;
                    padding: 0.75rem 2rem;
                    background: #764ba2;
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    margin-top: 1rem;
                    transition: transform 0.2s, box-shadow 0.2s;
                }
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3);
                }
                code {
                    background: #f0f4f8;
                    padding: 0.2rem 0.4rem;
                    border-radius: 4px;
                    font-family: 'Courier New', monospace;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🎉 Isotone CMS</h1>
                <p class="subtitle">Your lightweight CMS is ready to go!</p>
                
                <div class="status">
                    <div class="status-item">
                        <span>PHP Version</span>
                        <span class="badge badge-success">" . PHP_VERSION . "</span>
                    </div>
                    <div class="status-item">
                        <span>Environment</span>
                        <span class="badge badge-warning">" . ucfirst(env('APP_ENV', 'development')) . "</span>
                    </div>
                    <div class="status-item">
                        <span>Composer</span>
                        <span class="badge {$composerBadgeClass}">{$composerStatus}</span>
                    </div>
                    <div class="status-item">
                        <span>Database</span>
                        <span class="badge badge-warning">Not configured</span>
                    </div>
                </div>
                
                <p>{$nextStep}</p>
                <a href="{$baseUrl}/admin" class="btn">Go to Admin</a>
            </div>
        </body>
        </html>
        HTML;
        
        return new Response($html);
    }
    
    private function getBaseUrl(Request $request): string
    {
        $scheme = $request->getScheme();
        $host = $request->getHttpHost();
        $basePath = rtrim(dirname($request->getScriptName()), '/');
        
        return $scheme . '://' . $host . $basePath;
    }
    
    private function handleInstall(Request $request): Response
    {
        return new Response('<h1>Installation Wizard Coming Soon</h1>');
    }
    
    private function handleAdmin(Request $request): Response
    {
        return new Response('<h1>Admin Panel Coming Soon</h1>');
    }
    
    private function handle404(): Response
    {
        $baseUrl = env('APP_URL', '/isotone');
        
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>404 - Page Not Found</title>
            <style>
                body { font-family: sans-serif; text-align: center; padding: 50px; }
                h1 { color: #764ba2; }
            </style>
        </head>
        <body>
            <h1>404</h1>
            <p>The page you're looking for doesn't exist.</p>
            <a href="{$baseUrl}">Go Home</a>
        </body>
        </html>
        HTML;
        
        return new Response($html, 404);
    }
    
    private function handleError(\Exception $e): Response
    {
        $message = env('APP_DEBUG', false) ? $e->getMessage() : 'An error occurred';
        
        return new Response(
            '<h1>Error</h1><p>' . htmlspecialchars($message) . '</p>',
            500
        );
    }
}
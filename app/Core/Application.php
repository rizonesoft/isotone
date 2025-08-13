<?php
/**
 * Isotone CMS - Application Core
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */
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
        $phpVersion = PHP_VERSION;
        $environment = ucfirst(env('APP_ENV', 'development'));
        
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Isotone CMS - Lightweight. Powerful. Everywhere.</title>
            
            <!-- SEO Meta Tags -->
            <meta name="description" content="Isotone CMS is a lightweight, powerful PHP content management system designed for shared hosting. Built for developers, optimized for performance, ready for anywhere.">
            <meta name="keywords" content="CMS, PHP, content management, lightweight CMS, shared hosting, RedBeanPHP, open source">
            <meta name="author" content="Isotone CMS">
            <meta name="robots" content="index, follow">
            
            <!-- Open Graph / Facebook -->
            <meta property="og:type" content="website">
            <meta property="og:title" content="Isotone CMS - Lightweight. Powerful. Everywhere.">
            <meta property="og:description" content="A modern, high-performance PHP CMS built for the future. Perfect for shared hosting with no Node.js required.">
            <meta property="og:image" content="{$baseUrl}/public/favicon.png">
            <meta property="og:url" content="{$baseUrl}">
            <meta property="og:site_name" content="Isotone CMS">
            
            <!-- Twitter Card -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="Isotone CMS - Lightweight. Powerful. Everywhere.">
            <meta name="twitter:description" content="A modern, high-performance PHP CMS built for the future. Perfect for shared hosting.">
            <meta name="twitter:image" content="{$baseUrl}/public/favicon.png">
            
            <!-- Theme Color -->
            <meta name="theme-color" content="#0A0E27">
            
            <!-- Modern favicon setup using PNG -->
            <link rel="icon" type="image/png" sizes="512x512" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="192x192" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/isotone/public/favicon.png">
            <link rel="apple-touch-icon" href="/isotone/public/favicon.png">
            <link rel="manifest" href="/isotone/public/site.webmanifest">
            <!-- Fallback for older browsers if ICO exists -->
            <link rel="shortcut icon" href="/isotone/public/favicon.ico">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
                
                :root {
                    --primary: #0A0E27;           /* Deep Space Blue */
                    --accent: #00D9FF;            /* Electric Cyan */
                    --accent-green: #00FF88;      /* Neon Green */
                    --surface: rgba(255, 255, 255, 0.03);
                    --surface-hover: rgba(255, 255, 255, 0.08);
                    --text-primary: #FFFFFF;
                    --text-secondary: rgba(255, 255, 255, 0.7);
                    --text-muted: rgba(255, 255, 255, 0.5);
                    --border: rgba(255, 255, 255, 0.1);
                    --success: #00FF88;
                    --warning: #FFB800;
                    --danger: #FF3366;
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
                    overflow: hidden;
                    letter-spacing: 0.01em;
                }
                
                /* Subtle static gradient overlay */
                body::before {
                    content: '';
                    position: fixed;
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    background: 
                        radial-gradient(ellipse at top left, rgba(0, 217, 255, 0.08) 0%, transparent 40%),
                        radial-gradient(ellipse at bottom right, rgba(0, 255, 136, 0.08) 0%, transparent 40%);
                    pointer-events: none;
                }
                
                .container {
                    background: rgba(255, 255, 255, 0.02);
                    backdrop-filter: blur(20px);
                    -webkit-backdrop-filter: blur(20px);
                    border-radius: 24px;
                    padding: 3rem;
                    max-width: 650px;
                    width: 90%;
                    position: relative;
                    border: 1px solid var(--border);
                    box-shadow: 
                        0 0 0 1px rgba(0, 217, 255, 0.1),
                        0 10px 40px rgba(0, 0, 0, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
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
                
                .header-logo {
                    display: flex;
                    align-items: center;
                    justify-content: flex-start;
                    gap: 1.5rem;
                    margin-bottom: 3rem;
                }
                
                .logo-icon {
                    width: 60px;
                    height: 60px;
                    filter: drop-shadow(0 0 20px rgba(0, 217, 255, 0.5));
                    animation: pulse 2s ease-in-out infinite;
                }
                
                @keyframes pulse {
                    0%, 100% { 
                        transform: scale(1);
                        filter: drop-shadow(0 0 20px rgba(0, 217, 255, 0.5));
                    }
                    50% { 
                        transform: scale(1.05);
                        filter: drop-shadow(0 0 30px rgba(0, 255, 136, 0.6));
                    }
                }
                
                h1 {
                    font-size: 3rem;
                    font-weight: 900;
                    margin: 0;
                    letter-spacing: -0.01em;
                    background: linear-gradient(135deg, #FFFFFF 0%, #00D9FF 50%, #00FF88 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    animation: shimmer 4s ease-in-out infinite;
                    background-size: 200% 200%;
                }
                
                @keyframes shimmer {
                    0%, 100% { background-position: 0% 50%; }
                    50% { background-position: 100% 50%; }
                }
                
                .subtitle {
                    color: var(--text-secondary);
                    margin-bottom: 3rem;
                    font-size: 1.1rem;
                    font-weight: 500;
                    letter-spacing: 0.03em;
                    text-align: left;
                }
                
                .status {
                    background: var(--surface);
                    border: 1px solid var(--border);
                    border-radius: 16px;
                    padding: 1.5rem;
                    margin: 2rem 0;
                    backdrop-filter: blur(10px);
                }
                
                .status-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1rem;
                    margin: 0.5rem 0;
                    background: var(--surface);
                    border-radius: 12px;
                    border: 1px solid var(--border);
                    transition: all 0.3s ease;
                }
                
                .status-item:hover {
                    background: var(--surface-hover);
                    transform: translateX(4px);
                    border-color: rgba(0, 217, 255, 0.3);
                }
                
                .status-item span:first-child {
                    color: var(--text-secondary);
                    font-weight: 500;
                    letter-spacing: 0.02em;
                }
                
                .badge {
                    padding: 0.4rem 1rem;
                    border-radius: 20px;
                    font-size: 0.75rem;
                    font-weight: 600;
                    letter-spacing: 0.08em;
                    text-transform: uppercase;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.3rem;
                }
                
                .badge-success {
                    background: rgba(0, 255, 136, 0.1);
                    color: var(--success);
                    border: 1px solid rgba(0, 255, 136, 0.3);
                    box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
                }
                
                .badge-success::before {
                    content: '✓';
                    font-weight: bold;
                }
                
                .badge-warning {
                    background: rgba(255, 184, 0, 0.1);
                    color: var(--warning);
                    border: 1px solid rgba(255, 184, 0, 0.3);
                    box-shadow: 0 0 20px rgba(255, 184, 0, 0.2);
                }
                
                .badge-warning::before {
                    content: '○';
                }
                
                .btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 1rem 2rem;
                    background: linear-gradient(135deg, var(--accent), var(--accent-green));
                    color: var(--primary);
                    text-decoration: none;
                    border-radius: 12px;
                    margin-top: 2rem;
                    font-weight: 600;
                    font-size: 1rem;
                    letter-spacing: 0.04em;
                    transition: all 0.3s ease;
                    box-shadow: 
                        0 4px 20px rgba(0, 217, 255, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
                    position: relative;
                    overflow: hidden;
                }
                
                .btn::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                    transition: left 0.5s;
                }
                
                .btn:hover::before {
                    left: 100%;
                }
                
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 
                        0 6px 30px rgba(0, 217, 255, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
                }
                
                .btn::after {
                    content: '→';
                    font-size: 1.2rem;
                }
                
                code {
                    background: var(--surface);
                    padding: 0.3rem 0.6rem;
                    border-radius: 6px;
                    font-family: 'Consolas', 'Monaco', monospace;
                    color: var(--accent);
                    border: 1px solid var(--border);
                    font-size: 0.9rem;
                    letter-spacing: 0.02em;
                }
                
                /* Static grid decoration */
                .grid-bg {
                    position: fixed;
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    background-image: 
                        linear-gradient(rgba(0, 217, 255, 0.02) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(0, 217, 255, 0.02) 1px, transparent 1px);
                    background-size: 50px 50px;
                    pointer-events: none;
                    opacity: 0.5;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header-logo">
                    <svg class="logo-icon" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="isotone-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00D9FF;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#00FF88;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        <g fill="url(#isotone-gradient)">
                            <path clip-rule="evenodd" d="m12.7712 5.04198c-1.2919-.14261-2.598.07714-3.77211.63464-1.17413.55751-2.16993 1.43077-2.87596 2.52206-.70603 1.09128-1.09442 2.35752-1.12171 3.65702s.30761 2.5809.96721 3.7008c.28027.4759.12169 1.0889-.3542 1.3692-.47588.2803-1.08887.1217-1.36914-.3542-.84797-1.4398-1.27851-3.0872-1.24343-4.7578s.5344-3.29848 1.44206-4.70142c.90767-1.40295 2.18787-2.52561 3.69731-3.24233 1.50945-.71673 3.18857-.99923 4.84947-.8159.5489.0606.9448.55473.8842 1.10369-.0606.54895-.5547.94483-1.1037.88424zm5.6143 2.03237c.4759-.28027 1.0889-.12169 1.3691.35419.848 1.43982 1.2785 3.08726 1.2435 4.75776-.0351 1.6706-.5344 3.2985-1.4421 4.7015-.9077 1.4029-2.1879 2.5256-3.6973 3.2423-1.5095.7167-3.1886.9992-4.8495.8159-.5489-.0606-.9448-.5548-.8842-1.1037.0606-.549.5547-.9448 1.1037-.8842 1.2919.1426 2.598-.0772 3.7721-.6347 1.1742-.5575 2.17-1.4308 2.876-2.522.706-1.0913 1.0944-2.3576 1.1217-3.657.0273-1.2995-.3076-2.58095-.9672-3.70091-.2803-.47588-.1217-1.08887.3542-1.36914z" fill-rule="evenodd"></path>
                            <path d="m16.2428 7.75723c.781.78105 2.0473.78105 2.8284 0 .781-.78105.781-2.04738 0-2.82843-.7811-.78104-2.0474-.78104-2.8284 0-.7811.78105-.7811 2.04738 0 2.82843z"></path>
                            <path clip-rule="evenodd" d="m18.3641 5.63591c-.3905-.39052-1.0237-.39052-1.4142 0-.3905.39053-.3905 1.02369 0 1.41421.3905.39053 1.0237.39053 1.4142 0 .3905-.39052.3905-1.02368 0-1.41421zm-2.8284-1.41421c1.1715-1.17158 3.071-1.17158 4.2426 0 1.1716 1.17157 1.1716 3.07106 0 4.24264-1.1716 1.17157-3.0711 1.17157-4.2426 0-1.1716-1.17157-1.1716-3.07107 0-4.24264z" fill-rule="evenodd"></path>
                            <path d="m4.9288 19.0712c.78105.781 2.04738.781 2.82843 0 .78105-.7811.78105-2.0474 0-2.8284-.78105-.7811-2.04738-.7811-2.82843 0-.78104.781-.78104 2.0473 0 2.8284z"></path>
                            <path clip-rule="evenodd" d="m7.05012 16.9499c-.39052-.3905-1.02368-.3905-1.41421 0-.39052.3905-.39052 1.0237 0 1.4142.39053.3905 1.02369.3905 1.41421 0 .39053-.3905.39053-1.0237 0-1.4142zm-2.82842-1.4142c1.17157-1.1716 3.07106-1.1716 4.24264 0 1.17157 1.1715 1.17157 3.071 0 4.2426s-3.07107 1.1716-4.24264 0c-1.17158-1.1716-1.17158-3.0711 0-4.2426z" fill-rule="evenodd"></path>
                            <path d="m10.5858 13.4142c.781.7811 2.0474.7811 2.8284 0 .7811-.781.7811-2.0474 0-2.8284-.781-.78106-2.0474-.78106-2.8284 0-.78106.781-.78106 2.0474 0 2.8284z"></path>
                            <path clip-rule="evenodd" d="m12.7071 11.2929c-.3905-.3905-1.0237-.3905-1.4142 0s-.3905 1.0237 0 1.4142 1.0237.3905 1.4142 0 .3905-1.0237 0-1.4142zm-2.82842-1.41422c1.17162-1.17157 3.07102-1.17157 4.24262 0 1.1716 1.17162 1.1716 3.07102 0 4.24262s-3.071 1.1716-4.24262 0c-1.17157-1.1716-1.17157-3.071 0-4.24262z" fill-rule="evenodd"></path>
                        </g>
                    </svg>
                    <h1>Isotone CMS</h1>
                </div>
                <p class="subtitle">Your lightweight CMS is ready to go!</p>
                
                <div class="status">
                    <div class="status-item">
                        <span>PHP Version</span>
                        <span class="badge badge-success">{$phpVersion}</span>
                    </div>
                    <div class="status-item">
                        <span>Environment</span>
                        <span class="badge badge-warning">{$environment}</span>
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
        $baseUrl = $this->getBaseUrl($request);
        
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Installation - Isotone CMS</title>
            <meta name="description" content="Install and configure Isotone CMS on your server.">
            <meta name="robots" content="noindex, nofollow">
            <meta name="theme-color" content="#0A0E27">
            <!-- Modern favicon setup using PNG -->
            <link rel="icon" type="image/png" sizes="512x512" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="192x192" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/isotone/public/favicon.png">
            <link rel="apple-touch-icon" href="/isotone/public/favicon.png">
            <link rel="manifest" href="/isotone/public/site.webmanifest">
            <!-- Fallback for older browsers if ICO exists -->
            <link rel="shortcut icon" href="/isotone/public/favicon.ico">
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
            <title>Admin Panel - Isotone CMS</title>
            <meta name="description" content="Manage your Isotone CMS content and settings.">
            <meta name="robots" content="noindex, nofollow">
            <meta name="theme-color" content="#0A0E27">
            <!-- Modern favicon setup using PNG -->
            <link rel="icon" type="image/png" sizes="512x512" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="192x192" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/isotone/public/favicon.png">
            <link rel="apple-touch-icon" href="/isotone/public/favicon.png">
            <link rel="manifest" href="/isotone/public/site.webmanifest">
            <!-- Fallback for older browsers if ICO exists -->
            <link rel="shortcut icon" href="/isotone/public/favicon.ico">
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
        $baseUrl = env('APP_URL', '/isotone');
        
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Page Not Found | Isotone CMS</title>
            <meta name="description" content="The page you're looking for could not be found.">
            <meta name="robots" content="noindex, nofollow">
            <meta name="theme-color" content="#0A0E27">
            <!-- Modern favicon setup using PNG -->
            <link rel="icon" type="image/png" sizes="512x512" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="192x192" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="/isotone/public/favicon.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/isotone/public/favicon.png">
            <link rel="apple-touch-icon" href="/isotone/public/favicon.png">
            <link rel="manifest" href="/isotone/public/site.webmanifest">
            <!-- Fallback for older browsers if ICO exists -->
            <link rel="shortcut icon" href="/isotone/public/favicon.ico">
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
                    overflow: hidden;
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
                    overflow: hidden;
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
    
    private function handleError(\Exception $e): Response
    {
        $message = env('APP_DEBUG', false) ? $e->getMessage() : 'An error occurred';
        
        return new Response(
            '<h1>Error</h1><p>' . htmlspecialchars($message) . '</p>',
            500
        );
    }
}
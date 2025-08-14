<?php
/**
 * Default Landing Page for Isotone
 * Displayed when no theme is installed
 * 
 * @package Isotone
 */

// Get base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host . '/isotone';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Isotone - Modern Content Management</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Isotone is a lightweight, powerful PHP content management system designed for shared hosting. Built for developers, optimized for performance.">
    <meta name="keywords" content="CMS, PHP, content management, lightweight CMS, shared hosting">
    
    <!-- Isotone Glassmorphism CSS -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/iso-includes/css/isotone.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo $baseUrl; ?>/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $baseUrl; ?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $baseUrl; ?>/favicon-16x16.png">
    
    <style>
        /* Hero Section Specific Styles */
        .iso-hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .iso-hero-content {
            text-align: center;
            z-index: 10;
            padding: var(--space-2xl);
        }
        
        .iso-hero-title {
            font-size: clamp(2.5rem, 8vw, 5rem);
            margin-bottom: var(--space-lg);
        }
        
        .iso-hero-subtitle {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            color: var(--text-secondary);
            margin-bottom: var(--space-2xl);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Feature Grid */
        .iso-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-xl);
            padding: var(--space-3xl) var(--space-xl);
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .iso-feature-card {
            padding: var(--space-xl);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .iso-feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 217, 255, 0.2);
        }
        
        .iso-feature-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto var(--space-lg);
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-green) 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 0 20px rgba(0, 217, 255, 0.5));
        }
        
        .iso-feature-icon svg {
            width: 30px;
            height: 30px;
            stroke: var(--primary-dark);
        }
        
        /* CTA Section */
        .iso-cta {
            text-align: center;
            padding: var(--space-3xl) var(--space-xl);
        }
        
        .iso-btn-group {
            display: flex;
            gap: var(--space-lg);
            justify-content: center;
            flex-wrap: wrap;
            margin-top: var(--space-xl);
        }
        
        /* Animated Background Elements */
        .iso-bg-element {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 217, 255, 0.1) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
        }
        
        .iso-bg-element-1 {
            width: 400px;
            height: 400px;
            top: -200px;
            left: -200px;
            animation-delay: 0s;
        }
        
        .iso-bg-element-2 {
            width: 300px;
            height: 300px;
            bottom: -150px;
            right: -150px;
            animation-delay: 5s;
        }
        
        .iso-bg-element-3 {
            width: 250px;
            height: 250px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }
    </style>
</head>
<body>
    <!-- Animated Background Elements -->
    <div class="iso-bg-element iso-bg-element-1"></div>
    <div class="iso-bg-element iso-bg-element-2"></div>
    <div class="iso-bg-element iso-bg-element-3"></div>
    
    <!-- Hero Section -->
    <section class="iso-hero">
        <div class="iso-hero-content iso-animate-fadeInUp">
            <!-- Logo and Title -->
            <div class="iso-header">
                <img src="<?php echo $baseUrl; ?>/iso-includes/assets/logo.svg" alt="Isotone" class="iso-header-logo">
                <h1 class="iso-title iso-hero-title">Isotone</h1>
            </div>
            
            <p class="iso-hero-subtitle">
                Modern Content Management System<br>
                Lightweight. Powerful. Everywhere.
            </p>
            
            <div class="iso-btn-group">
                <a href="<?php echo $baseUrl; ?>/iso-admin/" class="iso-btn iso-btn-primary iso-btn-arrow">
                    Access Admin Panel
                </a>
                <a href="<?php echo $baseUrl; ?>/install/" class="iso-btn iso-btn-secondary">
                    Installation Guide
                </a>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="iso-features">
        <div class="iso-card iso-glass iso-feature-card">
            <div class="iso-feature-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h3 class="iso-title-sm">Lightning Fast</h3>
            <p class="iso-text-secondary">
                Built for speed with minimal overhead. Runs smoothly on shared hosting with just 128MB RAM.
            </p>
        </div>
        
        <div class="iso-card iso-glass iso-feature-card">
            <div class="iso-feature-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
            </div>
            <h3 class="iso-title-sm">Plugin System</h3>
            <p class="iso-text-secondary">
                WordPress-like hooks and filters. Extend functionality without modifying core files.
            </p>
        </div>
        
        <div class="iso-card iso-glass iso-feature-card">
            <div class="iso-feature-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                </svg>
            </div>
            <h3 class="iso-title-sm">No Migrations</h3>
            <p class="iso-text-secondary">
                RedBeanPHP ORM handles database schema automatically. Zero configuration needed.
            </p>
        </div>
        
        <div class="iso-card iso-glass iso-feature-card">
            <div class="iso-feature-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h3 class="iso-title-sm">Secure by Default</h3>
            <p class="iso-text-secondary">
                Built with security in mind. Prepared statements, CSRF protection, and secure sessions.
            </p>
        </div>
        
        <div class="iso-card iso-glass iso-feature-card">
            <div class="iso-feature-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
            </div>
            <h3 class="iso-title-sm">Theme System</h3>
            <p class="iso-text-secondary">
                Flexible theming with complete control over your site's appearance and functionality.
            </p>
        </div>
        
        <div class="iso-card iso-glass iso-feature-card">
            <div class="iso-feature-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
            </div>
            <h3 class="iso-title-sm">API Ready</h3>
            <p class="iso-text-secondary">
                RESTful API built-in for headless CMS capabilities and third-party integrations.
            </p>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="iso-cta">
        <div class="iso-container iso-container-md">
            <div class="iso-card iso-glass">
                <h2 class="iso-title-md">Ready to Get Started?</h2>
                <p class="iso-text-secondary">
                    Isotone is currently in development. Join us in building the future of content management.
                </p>
                <div class="iso-btn-group">
                    <a href="https://github.com/rizonesoft/isotone" target="_blank" class="iso-btn iso-btn-primary">
                        View on GitHub
                    </a>
                    <a href="<?php echo $baseUrl; ?>/docs/" class="iso-btn iso-btn-secondary">
                        Documentation
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="iso-footer">
        <div class="iso-container">
            <p class="iso-text-secondary" style="text-align: center; padding: var(--space-2xl) 0;">
                © <?php echo date('Y'); ?> Isotone. Open Source CMS. Built with ❤️ by the community.
            </p>
        </div>
    </footer>
</body>
</html>
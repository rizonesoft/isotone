<?php
/**
 * Default Landing Page for Isotone
 * Professional, clean design with proper spacing and hierarchy
 * 
 * @package Isotone
 */

// Get base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host . '/isotone';

// Check installation status
$isInstalled = file_exists(__DIR__ . '/../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isotone - Modern PHP Content Management System</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="A lightweight PHP content management system built for shared hosting. No build steps, no Node.js, just pure PHP that runs anywhere with 128MB RAM.">
    <meta name="keywords" content="Isotone, PHP content management, content platform, lightweight platform, shared hosting, RedBeanPHP, WordPress alternative">
    <meta name="author" content="Isotone">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Isotone - The Platform That Runs Anywhere">
    <meta property="og:description" content="Modern PHP content management for shared hosting. Zero configuration, instant setup.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $baseUrl; ?>">
    
    <!-- Isotone CSS -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/iso-includes/css/isotone.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/iso-includes/css/landing.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo $baseUrl; ?>/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $baseUrl; ?>/favicon-32x32.png">
    
    <!-- Custom styles for this landing page -->
    <style>
        /* 
         * Landing Page Layout
         * Uses .iso-landing instead of .iso-app to avoid conflicts with global layout.css
         * The .iso-app class uses flexbox centering for login/install pages
         * Landing page needs normal document flow for vertical sections
         */
        body.iso-landing {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 50%, var(--primary) 100%);
            background-attachment: fixed;
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            letter-spacing: 0.01em;
            min-height: 100vh;
        }
        
        /* Hero section for landing page */
        .iso-hero {
            min-height: 100vh;
            padding: var(--space-3xl) var(--space-xl);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Proper section spacing */
        .page-section {
            padding: 100px 20px;
            position: relative;
        }
        
        /* Add breathing room between sections */
        .section-spacer {
            height: 60px;
            background: transparent;
        }
        
        .feature-icon {
            width: 48px;
            height: 48px;
            stroke: currentColor;
            stroke-width: 1.5;
            fill: none;
            color: var(--accent);
            margin-bottom: var(--space-lg);
        }
        
        .stat-number {
            font-size: var(--text-4xl);
            font-weight: var(--font-weight-bold);
            color: var(--accent);
            display: block;
            margin-bottom: var(--space-xs);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: var(--text-sm);
            text-transform: uppercase;
            letter-spacing: var(--letter-spacing-widest);
        }
        
        /* Simple feature cards without glass effect */
        .feature-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-lg);
            padding: var(--space-2xl);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: var(--accent);
            transform: translateY(-4px);
        }
        
        /* Tech stack badges */
        .tech-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-sm) var(--space-lg);
            background: rgba(0, 217, 255, 0.1);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: var(--radius-full);
            color: var(--accent);
            font-size: var(--text-sm);
            font-weight: var(--font-weight-medium);
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Content containers */
        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        
        /* Progress bars */
        .progress-track {
            background: rgba(255, 255, 255, 0.05);
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            margin-top: var(--space-xs);
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--accent-green));
            border-radius: 3px;
            transition: width 1s ease;
        }
        
        /* Section backgrounds */
        .bg-dark {
            background: rgba(0, 0, 0, 0.3);
        }
        
        .bg-darker {
            background: rgba(0, 0, 0, 0.5);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-section {
                padding: 60px 20px;
            }
            
            .section-spacer {
                height: 40px;
            }
        }
    </style>
</head>
<body class="iso-landing">
    <!-- Fixed Background -->
    <div class="iso-background" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;">
        <div class="grid-bg"></div>
    </div>
    
    <!-- Hero Section - Clean and Focused -->
    <section class="iso-hero">
        <div class="content-wrapper" style="text-align: center;">
            <!-- Logo -->
            <img src="<?php echo $baseUrl; ?>/iso-includes/assets/logo.svg" 
                 alt="Isotone" 
                 class="iso-header-logo iso-animate-pulse" 
                 style="width: 80px; height: 80px; margin: 0 auto var(--space-xl);">
            
            <!-- Main Title -->
            <h1 class="iso-title" style="margin-bottom: var(--space-lg);">Isotone</h1>
            
            <!-- Tagline -->
            <p style="font-size: var(--text-2xl); color: var(--text-primary); margin-bottom: var(--space-md); font-weight: 300;">
                The Content Platform That Just Works
            </p>
            
            <!-- Description -->
            <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto var(--space-2xl); line-height: 1.7;">
                Built for real-world hosting. No build process, no Node.js, no DevOps complexity. 
                Upload, install, and start creating content in minutes.
            </p>
            
            <!-- CTA Buttons -->
            <div style="display: flex; gap: var(--space-lg); justify-content: center; flex-wrap: wrap; margin-bottom: var(--space-3xl);">
                <?php if ($isInstalled): ?>
                    <a href="<?php echo $baseUrl; ?>/iso-admin/" class="iso-btn iso-btn-primary">
                        Admin Panel
                        <svg style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $baseUrl; ?>/install/" class="iso-btn iso-btn-primary">
                        Get Started
                        <svg style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                <?php endif; ?>
                <a href="https://github.com/rizonesoft/isotone" target="_blank" class="iso-btn iso-btn-secondary">
                    <svg style="width: 20px; height: 20px; margin-right: 8px; vertical-align: middle;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
            </div>
            
            <!-- Key Stats -->
            <div style="display: flex; justify-content: center; gap: var(--space-3xl); flex-wrap: wrap; max-width: 700px; margin: 0 auto;">
                <div style="text-align: center; min-width: 120px;">
                    <span class="stat-number">128MB</span>
                    <span class="stat-label">Min RAM</span>
                </div>
                <div style="text-align: center; min-width: 120px;">
                    <span class="stat-number">Zero</span>
                    <span class="stat-label">Build Steps</span>
                </div>
                <div style="text-align: center; min-width: 120px;">
                    <span class="stat-number">PHP 8+</span>
                    <span class="stat-label">Modern</span>
                </div>
                <div style="text-align: center; min-width: 120px;">
                    <span class="stat-number">5 Min</span>
                    <span class="stat-label">Setup</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Spacer between sections -->
    <div class="section-spacer"></div>
    
    <!-- Core Features Section -->
    <section class="page-section bg-dark">
        <div class="content-wrapper">
            <h2 style="text-align: center; font-size: var(--text-3xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Why Isotone?
            </h2>
            <p style="text-align: center; color: var(--text-secondary); max-width: 600px; margin: 0 auto var(--space-3xl);">
                We built Isotone for the real world - where shared hosting is common and simplicity wins.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-xl);">
                <!-- Feature 1 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Instant Deployment</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">
                        Upload via FTP, visit /install, done. No command line, no build tools, no containerization needed.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Smart Database</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">
                        RedBeanPHP handles schema evolution automatically. No migrations, no SQL files, it just works.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Extensible</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">
                        WordPress-style hooks and filters make extending Isotone familiar and powerful.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Secure by Default</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">
                        Prepared statements, CSRF protection, and secure sessions built-in from day one.
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Shared Hosting Ready</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">
                        Runs perfectly on budget hosting. No VPS, no Docker, no special server requirements.
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Developer Friendly</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">
                        PSR-12 compliant, Composer autoloading, clean architecture. A joy to work with.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Spacer between sections -->
    <div class="section-spacer"></div>
    
    <!-- Tech Stack Section -->
    <section class="page-section">
        <div class="content-wrapper">
            <h2 style="text-align: center; font-size: var(--text-3xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Built With Modern PHP
            </h2>
            <p style="text-align: center; color: var(--text-secondary); max-width: 600px; margin: 0 auto var(--space-3xl);">
                Enterprise-grade components that work everywhere
            </p>
            
            <div style="display: flex; flex-wrap: wrap; gap: var(--space-md); justify-content: center;">
                <span class="tech-badge">PHP 8.0+</span>
                <span class="tech-badge">RedBeanPHP ORM</span>
                <span class="tech-badge">Composer</span>
                <span class="tech-badge">Symfony Routing</span>
                <span class="tech-badge">PSR-4 Autoloading</span>
                <span class="tech-badge">Tailwind CSS</span>
            </div>
        </div>
    </section>
    
    <!-- Spacer between sections -->
    <div class="section-spacer"></div>
    
    <!-- Quick Start Section -->
    <section class="page-section bg-dark">
        <div class="content-wrapper" style="max-width: 800px;">
            <h2 style="text-align: center; font-size: var(--text-3xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Get Started in Minutes
            </h2>
            <p style="text-align: center; color: var(--text-secondary); max-width: 600px; margin: 0 auto var(--space-3xl);">
                Three simple steps to your new platform
            </p>
            
            <div style="display: grid; gap: var(--space-xl);">
                <!-- Step 1 -->
                <div style="display: flex; gap: var(--space-lg); align-items: start;">
                    <div style="min-width: 40px; height: 40px; border-radius: 50%; background: var(--accent); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        1
                    </div>
                    <div style="flex: 1;">
                        <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Download</h3>
                        <p style="color: var(--text-secondary); margin-bottom: var(--space-md);">
                            Clone or download Isotone from GitHub
                        </p>
                        <code style="display: block; background: rgba(0, 0, 0, 0.5); padding: var(--space-md); border-radius: var(--radius-sm); font-family: monospace; color: var(--accent);">
                            git clone https://github.com/rizonesoft/isotone.git
                        </code>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div style="display: flex; gap: var(--space-lg); align-items: start;">
                    <div style="min-width: 40px; height: 40px; border-radius: 50%; background: var(--accent); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        2
                    </div>
                    <div style="flex: 1;">
                        <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Upload</h3>
                        <p style="color: var(--text-secondary); margin-bottom: var(--space-md);">
                            Upload files to your web server via FTP or file manager
                        </p>
                        <code style="display: block; background: rgba(0, 0, 0, 0.5); padding: var(--space-md); border-radius: var(--radius-sm); font-family: monospace; color: var(--accent);">
                            Upload to: /public_html/ or /htdocs/
                        </code>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div style="display: flex; gap: var(--space-lg); align-items: start;">
                    <div style="min-width: 40px; height: 40px; border-radius: 50%; background: var(--accent-green); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        3
                    </div>
                    <div style="flex: 1;">
                        <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Install</h3>
                        <p style="color: var(--text-secondary); margin-bottom: var(--space-md);">
                            Visit your site's /install URL and follow the wizard
                        </p>
                        <code style="display: block; background: rgba(0, 0, 0, 0.5); padding: var(--space-md); border-radius: var(--radius-sm); font-family: monospace; color: var(--accent);">
                            https://yoursite.com/install/
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Spacer between sections -->
    <div class="section-spacer"></div>
    
    <!-- Development Status Section -->
    <section class="page-section">
        <div class="content-wrapper" style="max-width: 600px;">
            <h2 style="text-align: center; font-size: var(--text-3xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Development Progress
            </h2>
            <p style="text-align: center; color: var(--text-secondary); margin-bottom: var(--space-3xl);">
                Isotone is actively being developed. Join us!
            </p>
            
            <div style="display: grid; gap: var(--space-xl);">
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-xs);">
                        <span style="color: var(--text-primary);">Core System</span>
                        <span style="color: var(--accent); font-weight: bold;">85%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: 85%;"></div>
                    </div>
                </div>
                
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-xs);">
                        <span style="color: var(--text-primary);">Admin Panel</span>
                        <span style="color: var(--accent); font-weight: bold;">40%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: 40%;"></div>
                    </div>
                </div>
                
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-xs);">
                        <span style="color: var(--text-primary);">Plugin System</span>
                        <span style="color: var(--accent); font-weight: bold;">30%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: 30%;"></div>
                    </div>
                </div>
                
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-xs);">
                        <span style="color: var(--text-primary);">Theme System</span>
                        <span style="color: var(--accent); font-weight: bold;">25%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: 25%;"></div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: var(--space-3xl);">
                <span class="iso-badge iso-badge-warning">Alpha Release - Not for Production</span>
            </div>
        </div>
    </section>
    
    <!-- Spacer between sections -->
    <div class="section-spacer"></div>
    
    <!-- CTA Section -->
    <section class="page-section bg-dark" style="padding: 120px 20px;">
        <div class="content-wrapper" style="text-align: center; max-width: 600px;">
            <h2 style="font-size: var(--text-3xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Ready to Try Isotone?
            </h2>
            <p style="color: var(--text-secondary); margin-bottom: var(--space-2xl); line-height: 1.7;">
                Join the community building a platform that respects simplicity and works everywhere.
            </p>
            
            <div style="display: flex; gap: var(--space-lg); justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo $baseUrl; ?>/install/" class="iso-btn iso-btn-primary">
                    Start Installation
                </a>
                <a href="https://github.com/rizonesoft/isotone" target="_blank" class="iso-btn iso-btn-secondary">
                    Star on GitHub
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-darker" style="padding: 60px 20px; margin-top: 80px; border-top: 1px solid rgba(255, 255, 255, 0.05);">
        <div class="content-wrapper">
            <div style="text-align: center;">
                <p style="color: var(--text-secondary); margin-bottom: var(--space-sm);">
                    © <?php echo date('Y'); ?> Isotone - Open Source Content Management
                </p>
                <p style="color: var(--text-secondary); font-size: var(--text-sm);">
                    Built with PHP • Powered by RedBeanPHP • Made for Everyone
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
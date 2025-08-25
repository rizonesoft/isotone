<?php
/**
 * Isotone Official Theme - Main Template
 * The official Isotone theme with enhanced glass morphism design
 * 
 * @package Isotone
 * @version 2.0.0
 */

// Get base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host . '/isotone';

// Check installation status
$isInstalled = file_exists(dirname(__DIR__, 3) . '/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isotone - The Platform That Runs Anywhere</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="A lightweight PHP content management system built for shared hosting. No build steps, no Node.js, just pure PHP that runs anywhere with 128MB RAM.">
    <meta name="keywords" content="Isotone, PHP content management, content platform, lightweight platform, shared hosting, RedBeanPHP, WordPress alternative">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Isotone - Modern PHP Content Management">
    <meta property="og:description" content="The content platform that just works. Built for real-world hosting.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $baseUrl; ?>">
    
    <!-- Isotone CSS - Contains glass morphism styles -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/iso-content/themes/quantum/assets/css/isotone.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/iso-content/themes/quantum/assets/css/landing.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo $baseUrl; ?>/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $baseUrl; ?>/favicon-32x32.png">
    
    <!-- Enhanced styles for the official theme -->
    <style>
        /* Custom properties for theme customization */
        :root {
            --header-height: 70px;
            --header-bg: rgba(10, 10, 20, 0.7);
            --glow-color: rgba(0, 217, 255, 0.6);
            --particle-color: rgba(0, 217, 255, 0.3);
        }
        
        /* Landing Page enhanced layout */
        body.iso-landing {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 50%, var(--primary) 100%);
            background-attachment: fixed;
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        /* Sticky Header with Glass Effect */
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--header-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .main-header.scrolled {
            background: rgba(10, 10, 20, 0.95);
            box-shadow: 0 4px 30px rgba(0, 217, 255, 0.1);
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 var(--space-xl);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        
        .header-logo:hover {
            transform: scale(1.05);
        }
        
        .header-logo img {
            width: 40px;
            height: 40px;
            filter: drop-shadow(0 0 10px var(--glow-color));
        }
        
        .header-logo span {
            font-size: var(--text-xl);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }
        
        /* Navigation Menu */
        .main-nav {
            display: flex;
            align-items: center;
            gap: var(--space-xl);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: var(--space-2xl);
        }
        
        .nav-menu a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-menu a:hover {
            color: var(--accent);
        }
        
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s ease;
        }
        
        .nav-menu a:hover::after {
            width: 100%;
        }
        
        /* GitHub Button in Header */
        .header-github-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.2), 
                transparent);
            transition: left 0.5s ease;
            z-index: 0;
        }
        
        .header-github-btn:hover::before {
            left: 100%;
        }
        
        .header-github-btn:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--accent) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 217, 255, 0.2);
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: var(--space-sm);
        }
        
        /* Enhanced Hero Section */
        .iso-hero {
            min-height: 100vh;
            padding: calc(var(--header-height) + var(--space-3xl)) var(--space-xl) var(--space-3xl);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Particles Background */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: radial-gradient(circle, rgba(0, 217, 255, 0.8) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            opacity: 0;
            filter: blur(0.5px);
            will-change: transform;
            transition: transform 0.3s ease-out, box-shadow 0.3s ease-out, filter 0.3s ease-out;
        }
        
        /* Different particle sizes and behaviors */
        .particle-small {
            width: 2px;
            height: 2px;
            animation: floatUp 25s infinite linear;
        }
        
        .particle-medium {
            width: 3px;
            height: 3px;
            animation: floatDiagonal 35s infinite linear;
        }
        
        .particle-large {
            width: 4px;
            height: 4px;
            animation: floatSwirl 45s infinite linear;
            filter: blur(0.8px);
        }
        
        .particle-glow {
            width: 6px;
            height: 6px;
            background: radial-gradient(circle, rgba(0, 217, 255, 1) 0%, rgba(0, 217, 255, 0.4) 40%, transparent 70%);
            animation: floatGlow 50s infinite linear;
            filter: blur(1px);
            box-shadow: 0 0 10px rgba(0, 217, 255, 0.6);
        }
        
        @keyframes floatUp {
            0% {
                transform: translateY(110vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 0.8;
            }
            90% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-10vh) translateX(10vw);
                opacity: 0;
            }
        }
        
        @keyframes floatDiagonal {
            0% {
                transform: translateY(110vh) translateX(-10vw);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-10vh) translateX(110vw);
                opacity: 0;
            }
        }
        
        @keyframes floatSwirl {
            0% {
                transform: translateY(110vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.7;
            }
            50% {
                transform: translateY(50vh) translateX(20vw) rotate(180deg);
            }
            90% {
                opacity: 0.7;
            }
            100% {
                transform: translateY(-10vh) translateX(-10vw) rotate(360deg);
                opacity: 0;
            }
        }
        
        @keyframes floatGlow {
            0% {
                transform: translateY(110vh) translateX(0) scale(0.5);
                opacity: 0;
            }
            10% {
                opacity: 0.4;
                transform: translateY(90vh) translateX(5vw) scale(1);
            }
            30% {
                transform: translateY(60vh) translateX(-10vw) scale(1.2);
            }
            50% {
                transform: translateY(40vh) translateX(15vw) scale(0.8);
                opacity: 0.6;
            }
            70% {
                transform: translateY(20vh) translateX(-5vw) scale(1.1);
            }
            90% {
                opacity: 0.4;
                transform: translateY(5vh) translateX(10vw) scale(0.9);
            }
            100% {
                transform: translateY(-10vh) translateX(0) scale(0.5);
                opacity: 0;
            }
        }
        
        /* Pulse animation for some particles */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.5);
                opacity: 0.4;
            }
        }
        
        .particle-pulse {
            width: 3px;
            height: 3px;
            animation: floatUp 30s infinite linear, pulse 3s infinite ease-in-out;
        }
        
        /* Hero Content Animations */
        .hero-content {
            position: relative;
            z-index: 1;
            animation: fadeInUp 1s ease;
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
        
        /* Enhanced Glass Cards */
        .feature-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: var(--space-2xl);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 217, 255, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 217, 255, 0.2);
            border-color: var(--accent);
        }
        
        .feature-card:hover::before {
            opacity: 1;
        }
        
        /* Feature Icons with Glow */
        .feature-icon {
            width: 48px;
            height: 48px;
            stroke: currentColor;
            stroke-width: 1.5;
            fill: none;
            color: var(--accent);
            margin-bottom: var(--space-lg);
            filter: drop-shadow(0 0 8px currentColor);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            filter: drop-shadow(0 0 15px currentColor);
        }
        
        /* Enhanced Buttons with Shimmer */
        .iso-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        /* Primary button - darker with accent border */
        .iso-btn-primary {
            background: rgba(0, 217, 255, 0.1);
            border: 1px solid var(--accent);
            color: var(--accent);
        }
        
        .iso-btn-primary:hover {
            background: rgba(0, 217, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.3);
        }
        
        /* Secondary button - glass effect */
        .iso-btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
        }
        
        .iso-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
        }
        
        /* Shimmer effect for all buttons */
        .iso-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.4), 
                transparent);
            transition: left 0.6s ease;
        }
        
        .iso-btn:hover::before {
            left: 100%;
        }
        
        /* Stats with Counter Animation */
        .stat-number {
            font-size: var(--text-4xl);
            font-weight: var(--font-weight-bold);
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-green) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
            margin-bottom: var(--space-xs);
            filter: drop-shadow(0 0 20px rgba(0, 217, 255, 0.5));
        }
        
        /* Tech Badges with Hover */
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
            transition: all 0.3s ease;
            cursor: default;
        }
        
        .tech-badge:hover {
            background: rgba(0, 217, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 217, 255, 0.3);
        }
        
        /* Progress Bars with Animation */
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--accent-green));
            border-radius: 3px;
            position: relative;
            overflow: hidden;
            animation: progressGlow 2s ease-in-out infinite;
        }
        
        @keyframes progressGlow {
            0%, 100% {
                box-shadow: 0 0 10px rgba(0, 217, 255, 0.5);
            }
            50% {
                box-shadow: 0 0 20px rgba(0, 217, 255, 0.8);
            }
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            to {
                left: 100%;
            }
        }
        
        /* Section Backgrounds */
        .bg-gradient {
            background: linear-gradient(135deg, rgba(0, 217, 255, 0.05) 0%, transparent 100%);
        }
        
        /* Footer with Social Links */
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-3xl) var(--space-xl);
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: var(--space-xl);
            margin-top: var(--space-xl);
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: var(--accent);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 217, 255, 0.3);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .main-nav.mobile-open .nav-menu {
                display: flex;
                position: absolute;
                top: var(--header-height);
                left: 0;
                right: 0;
                flex-direction: column;
                background: rgba(10, 10, 20, 0.98);
                padding: var(--space-xl);
                backdrop-filter: blur(20px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Content wrapper */
        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        
        /* Page sections */
        .page-section {
            padding: 100px 20px;
            position: relative;
        }
        
        .section-spacer {
            height: 60px;
            background: transparent;
        }
    </style>
</head>
<body class="iso-landing">
    <!-- Sticky Header -->
    <header class="main-header" id="main-header">
        <div class="header-container">
            <a href="<?php echo $baseUrl; ?>" class="header-logo">
                <img src="<?php echo $baseUrl; ?>/iso-includes/assets/logo.svg" alt="Isotone">
                <span>Isotone</span>
            </a>
            
            <nav class="main-nav" id="main-nav">
                <ul class="nav-menu">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#stack">Tech Stack</a></li>
                    <li><a href="#quickstart">Quick Start</a></li>
                </ul>
                
                <a href="https://github.com/rizonesoft/isotone" target="_blank" class="header-github-btn" style="display: inline-flex; align-items: center; padding: 8px 20px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 6px; color: var(--text-primary); text-decoration: none; font-size: 14px; transition: all 0.3s ease; position: relative; overflow: hidden;">
                    <svg style="width: 16px; height: 16px; margin-right: 8px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    <span style="position: relative; z-index: 1;">GitHub</span>
                </a>
                
                <button class="mobile-menu-toggle" onclick="document.getElementById('main-nav').classList.toggle('mobile-open')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18"/>
                    </svg>
                </button>
            </nav>
        </div>
    </header>
    
    <!-- Fixed Background -->
    <div class="iso-background" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;">
        <div class="grid-bg"></div>
    </div>
    
    <!-- Enhanced Hero Section -->
    <section class="iso-hero">
        <!-- Animated Particles - Only in Hero -->
        <div class="particles">
            <?php 
            $particleTypes = ['particle-small', 'particle-medium', 'particle-large', 'particle-glow', 'particle-pulse'];
            for($i = 0; $i < 40; $i++): 
                $type = $particleTypes[array_rand($particleTypes)];
                $left = rand(0, 100);
                $delay = rand(0, 50) / 10; // 0 to 5 seconds delay
                $duration = rand(25, 55); // 25 to 55 seconds duration
            ?>
                <div class="particle <?php echo $type; ?>" 
                     style="left: <?php echo $left; ?>%; 
                            animation-delay: <?php echo $delay; ?>s;
                            <?php if($type === 'particle-glow'): ?>
                                animation-duration: <?php echo $duration; ?>s;
                            <?php endif; ?>">
                </div>
            <?php endfor; ?>
        </div>
        <div class="hero-content content-wrapper" style="text-align: center;">
            <!-- Animated Logo -->
            <img src="<?php echo $baseUrl; ?>/iso-includes/assets/logo.svg" 
                 alt="Isotone" 
                 class="iso-header-logo" 
                 style="width: 100px; height: 100px; margin: 0 auto var(--space-xl); filter: drop-shadow(0 0 20px var(--glow-color)); opacity: 0; animation: fadeInUp 1s ease forwards;">
            
            <!-- Main Title with Gradient -->
            <h1 class="iso-title" style="margin-bottom: var(--space-lg); font-size: clamp(3rem, 8vw, 5rem); background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                Isotone
            </h1>
            
            <!-- Animated Tagline -->
            <p style="font-size: var(--text-2xl); color: var(--text-primary); margin-bottom: var(--space-md); font-weight: 300; opacity: 0; animation: fadeInUp 1s ease 0.3s forwards;">
                The Platform That Runs Anywhere
            </p>
            
            <!-- Description with Fade-in -->
            <p style="color: var(--text-secondary); max-width: 700px; margin: 0 auto var(--space-2xl); line-height: 1.8; font-size: var(--text-lg); opacity: 0; animation: fadeInUp 1s ease 0.5s forwards;">
                Built for the real world where shared hosting is common and simplicity wins. 
                Minimal build steps, no Node.js required for runtime, no DevOps complexity. PHP that works on any standard hosting.
            </p>
            
            <!-- CTA Buttons with Hover Effects -->
            <div style="display: flex; gap: var(--space-lg); justify-content: center; flex-wrap: wrap; margin-bottom: var(--space-3xl); opacity: 0; animation: fadeInUp 1s ease 0.7s forwards;">
                <?php if ($isInstalled): ?>
                    <a href="#progress" class="iso-btn iso-btn-primary iso-btn-lg">
                        <span style="position: relative; z-index: 1;">View Progress</span>
                        <svg style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle; position: relative; z-index: 1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $baseUrl; ?>/install/" class="iso-btn iso-btn-primary iso-btn-lg">
                        <span style="position: relative; z-index: 1;">Get Started Now</span>
                        <svg style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle; position: relative; z-index: 1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                <?php endif; ?>
                <a href="#features" class="iso-btn iso-btn-secondary iso-btn-lg">
                    <span style="position: relative; z-index: 1;">Explore Features</span>
                    <svg style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle; position: relative; z-index: 1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </a>
            </div>
            
            <!-- Animated Key Stats -->
            <div style="display: flex; justify-content: center; gap: var(--space-3xl); flex-wrap: wrap; max-width: 800px; margin: 0 auto; opacity: 0; animation: fadeInUp 1s ease 0.9s forwards;">
                <div style="text-align: center; min-width: 140px;">
                    <span class="stat-number">128MB</span>
                    <span class="stat-label">Minimum RAM</span>
                </div>
                <div style="text-align: center; min-width: 140px;">
                    <span class="stat-number">Zero</span>
                    <span class="stat-label">Build Steps</span>
                </div>
                <div style="text-align: center; min-width: 140px;">
                    <span class="stat-number">PHP 8+</span>
                    <span class="stat-label">Modern Stack</span>
                </div>
                <div style="text-align: center; min-width: 140px;">
                    <span class="stat-number">5 Min</span>
                    <span class="stat-label">Setup Time</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Section Spacer -->
    <div class="section-spacer"></div>
    
    <!-- Core Features Section -->
    <section id="features" class="page-section bg-gradient">
        <div class="content-wrapper">
            <h2 style="text-align: center; font-size: var(--text-4xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Why Choose Isotone?
            </h2>
            <p style="text-align: center; color: var(--text-secondary); max-width: 700px; margin: 0 auto var(--space-3xl); font-size: var(--text-lg);">
                We built Isotone for developers who value simplicity, performance, and compatibility
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: var(--space-2xl);">
                <!-- Feature 1 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-md); font-size: var(--text-xl);">Lightning Fast Setup</h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        Upload via FTP, visit /install, and you're done. No terminal commands, no build tools, no containerization needed.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-md); font-size: var(--text-xl);">Smart Database</h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        RedBeanPHP handles schema evolution automatically. No migrations, no SQL files, it adapts as your content grows.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-md); font-size: var(--text-xl);">Fully Extensible</h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        WordPress-style hooks and filters make extending Isotone familiar and powerful for developers.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-md); font-size: var(--text-xl);">Secure by Default</h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        Prepared statements, CSRF protection, and secure sessions built-in from day one. Security isn't an afterthought.
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-md); font-size: var(--text-xl);">Shared Hosting Ready</h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        Runs perfectly on budget hosting with just 128MB RAM. No VPS, Docker, or special server requirements.
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card">
                    <svg class="feature-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-md); font-size: var(--text-xl);">Developer Friendly</h3>
                    <p style="color: var(--text-secondary); line-height: 1.7;">
                        PSR-12 compliant, Composer autoloading, clean architecture. A joy to work with and extend.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Section Spacer -->
    <div class="section-spacer"></div>
    
    <!-- Tech Stack Section -->
    <section id="stack" class="page-section">
        <div class="content-wrapper">
            <h2 style="text-align: center; font-size: var(--text-4xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Built With Modern PHP
            </h2>
            <p style="text-align: center; color: var(--text-secondary); max-width: 700px; margin: 0 auto var(--space-3xl); font-size: var(--text-lg);">
                Enterprise-grade components that work everywhere, from shared hosting to cloud infrastructure
            </p>
            
            <div style="display: flex; flex-wrap: wrap; gap: var(--space-md); justify-content: center;">
                <span class="tech-badge">PHP 8.0+</span>
                <span class="tech-badge">RedBeanPHP ORM</span>
                <span class="tech-badge">Composer</span>
                <span class="tech-badge">Symfony Routing</span>
                <span class="tech-badge">PSR-4 Autoloading</span>
                <span class="tech-badge">Tailwind CSS</span>
                <span class="tech-badge">Alpine.js</span>
                <span class="tech-badge">Chart.js</span>
            </div>
        </div>
    </section>
    
    <!-- Section Spacer -->
    <div class="section-spacer"></div>
    
    <!-- Development Progress Section -->
    <section id="progress" class="page-section bg-gradient">
        <div class="content-wrapper" style="max-width: 700px;">
            <h2 style="text-align: center; font-size: var(--text-4xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Development Progress
            </h2>
            <p style="text-align: center; color: var(--text-secondary); margin-bottom: var(--space-3xl); font-size: var(--text-lg);">
                Isotone is actively being developed. Join us on this journey!
            </p>
            
            <div class="iso-glass" style="padding: var(--space-2xl); border-radius: var(--radius-lg);">
                <div style="display: grid; gap: var(--space-2xl);">
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                            <span style="color: var(--text-primary); font-weight: var(--font-weight-medium);">Core System</span>
                            <span style="color: var(--accent); font-weight: bold;">90%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: 90%;"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                            <span style="color: var(--text-primary); font-weight: var(--font-weight-medium);">Admin Panel</span>
                            <span style="color: var(--accent); font-weight: bold;">75%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: 75%;"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                            <span style="color: var(--text-primary); font-weight: var(--font-weight-medium);">Plugin System</span>
                            <span style="color: var(--accent); font-weight: bold;">60%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: 60%;"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                            <span style="color: var(--text-primary); font-weight: var(--font-weight-medium);">Theme System</span>
                            <span style="color: var(--accent); font-weight: bold;">85%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: 85%;"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                            <span style="color: var(--text-primary); font-weight: var(--font-weight-medium);">Documentation</span>
                            <span style="color: var(--accent); font-weight: bold;">45%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: 45%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: var(--space-3xl);">
                <span class="iso-badge iso-badge-warning">Alpha Release - Not for Production</span>
            </div>
        </div>
    </section>
    
    <!-- Section Spacer -->
    <div class="section-spacer"></div>
    
    <!-- Quick Start Section -->
    <section id="quickstart" class="page-section">
        <div class="content-wrapper" style="max-width: 900px;">
            <h2 style="text-align: center; font-size: var(--text-4xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Get Started in Minutes
            </h2>
            <p style="text-align: center; color: var(--text-secondary); max-width: 600px; margin: 0 auto var(--space-3xl); font-size: var(--text-lg);">
                Three simple steps to your new content platform
            </p>
            
            <div style="display: grid; gap: var(--space-2xl);">
                <!-- Step 1 -->
                <div class="iso-glass" style="display: flex; gap: var(--space-xl); align-items: start; padding: var(--space-xl); border-radius: var(--radius-lg);">
                    <div style="min-width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--accent) 0%, var(--accent-green) 100%); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: var(--text-xl);">
                        1
                    </div>
                    <div style="flex: 1;">
                        <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm); font-size: var(--text-xl);">Download Isotone</h3>
                        <p style="color: var(--text-secondary); margin-bottom: var(--space-md);">
                            Clone or download the latest version from GitHub
                        </p>
                        <code style="display: block; background: rgba(0, 0, 0, 0.3); padding: var(--space-md); border-radius: var(--radius-sm); font-family: monospace; color: var(--accent);">
                            git clone https://github.com/rizonesoft/isotone.git
                        </code>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="iso-glass" style="display: flex; gap: var(--space-xl); align-items: start; padding: var(--space-xl); border-radius: var(--radius-lg);">
                    <div style="min-width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--accent) 0%, var(--accent-green) 100%); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: var(--text-xl);">
                        2
                    </div>
                    <div style="flex: 1;">
                        <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm); font-size: var(--text-xl);">Upload to Server</h3>
                        <p style="color: var(--text-secondary); margin-bottom: var(--space-md);">
                            Upload files to your web server via FTP or file manager
                        </p>
                        <code style="display: block; background: rgba(0, 0, 0, 0.3); padding: var(--space-md); border-radius: var(--radius-sm); font-family: monospace; color: var(--accent);">
                            Upload to: /public_html/ or /htdocs/
                        </code>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="iso-glass" style="display: flex; gap: var(--space-xl); align-items: start; padding: var(--space-xl); border-radius: var(--radius-lg);">
                    <div style="min-width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-green) 0%, var(--accent) 100%); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: var(--text-xl);">
                        3
                    </div>
                    <div style="flex: 1;">
                        <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm); font-size: var(--text-xl);">Run Installation</h3>
                        <p style="color: var(--text-secondary); margin-bottom: var(--space-md);">
                            Visit your site's /install URL and follow the wizard
                        </p>
                        <code style="display: block; background: rgba(0, 0, 0, 0.3); padding: var(--space-md); border-radius: var(--radius-sm); font-family: monospace; color: var(--accent);">
                            https://yoursite.com/install/
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Section Spacer -->
    <div class="section-spacer"></div>
    
    <!-- CTA Section -->
    <section class="page-section bg-gradient" style="padding: 120px 20px;">
        <div class="content-wrapper" style="text-align: center; max-width: 700px;">
            <h2 style="font-size: var(--text-4xl); margin-bottom: var(--space-lg); color: var(--text-primary);">
                Ready to Experience Isotone?
            </h2>
            <p style="color: var(--text-secondary); margin-bottom: var(--space-2xl); line-height: 1.8; font-size: var(--text-lg);">
                Join the community building a content platform that respects simplicity and works everywhere.
            </p>
            
            <div style="display: flex; gap: var(--space-lg); justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo $baseUrl; ?>/install/" class="iso-btn iso-btn-primary iso-btn-lg">
                    <span style="position: relative; z-index: 1;">Start Installation</span>
                </a>
                <a href="https://github.com/rizonesoft/isotone" target="_blank" class="iso-btn iso-btn-secondary iso-btn-lg">
                    <span style="position: relative; z-index: 1;">Star on GitHub</span>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Enhanced Footer -->
    <footer style="background: rgba(0, 0, 0, 0.7); border-top: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="footer-content">
            <div style="text-align: center;">
                <img src="<?php echo $baseUrl; ?>/iso-includes/assets/logo.svg" 
                     alt="Isotone" 
                     style="width: 50px; height: 50px; margin-bottom: var(--space-lg); filter: drop-shadow(0 0 10px var(--glow-color));">
                
                <p style="color: var(--text-primary); font-size: var(--text-lg); margin-bottom: var(--space-sm);">
                    Isotone
                </p>
                
                <p style="color: var(--text-secondary); margin-bottom: var(--space-xl);">
                    © <?php echo date('Y'); ?> Isotone - Open Source Content Management
                </p>
                
                <div class="social-links">
                    <a href="https://github.com/rizonesoft/isotone" target="_blank" class="social-link" title="GitHub">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </a>
                    <a href="https://twitter.com/isotone" target="_blank" class="social-link" title="Twitter">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                    <a href="https://discord.gg/isotone" target="_blank" class="social-link" title="Discord">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028 14.09 14.09 0 001.226-1.994.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                        </svg>
                    </a>
                </div>
                
                <p style="color: var(--text-muted); font-size: var(--text-sm); margin-top: var(--space-xl);">
                    Built with PHP • Powered by RedBeanPHP • Made for Everyone
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('main-header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerHeight = document.getElementById('main-header').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight - 20;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Particle mouse interaction
        let mouseX = 0;
        let mouseY = 0;
        let isMouseMoving = false;
        
        document.addEventListener('mousemove', function(e) {
            mouseX = e.clientX;
            mouseY = e.clientY;
            isMouseMoving = true;
            
            // Apply repulsion effect to particles
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                const rect = particle.getBoundingClientRect();
                const particleX = rect.left + rect.width / 2;
                const particleY = rect.top + rect.height / 2;
                
                // Calculate distance from mouse
                const dx = particleX - mouseX;
                const dy = particleY - mouseY;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                // Apply repulsion within radius
                const maxDistance = 150;
                if (distance < maxDistance) {
                    const force = (1 - distance / maxDistance) * 50;
                    const angle = Math.atan2(dy, dx);
                    const moveX = Math.cos(angle) * force;
                    const moveY = Math.sin(angle) * force;
                    
                    // Apply transform
                    particle.style.transform = `translate(${moveX}px, ${moveY}px)`;
                    particle.style.transition = 'transform 0.3s ease-out';
                    
                    // Glow effect on nearby particles
                    if (distance < 80) {
                        particle.style.boxShadow = `0 0 ${20 - distance/4}px rgba(0, 217, 255, ${0.8 - distance/100})`;
                        particle.style.filter = `blur(0px) brightness(${1.5 - distance/160})`;
                    }
                } else {
                    // Reset transform when mouse is far
                    particle.style.transform = '';
                    particle.style.boxShadow = '';
                    particle.style.filter = '';
                }
            });
            
            // Reset flag after a delay
            clearTimeout(window.mouseStopTimer);
            window.mouseStopTimer = setTimeout(() => {
                isMouseMoving = false;
                // Reset all particles smoothly
                particles.forEach(particle => {
                    particle.style.transform = '';
                    particle.style.boxShadow = '';
                    particle.style.filter = '';
                    particle.style.transition = 'all 1s ease-out';
                });
            }, 100);
        });
        
        // Create ripple effect on click
        document.addEventListener('click', function(e) {
            // Skip if clicking on interactive elements
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            
            const particles = document.querySelectorAll('.particle');
            particles.forEach((particle, index) => {
                const rect = particle.getBoundingClientRect();
                const particleX = rect.left + rect.width / 2;
                const particleY = rect.top + rect.height / 2;
                
                const dx = particleX - e.clientX;
                const dy = particleY - e.clientY;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                // Create ripple push effect
                setTimeout(() => {
                    const force = Math.max(0, 300 - distance) / 3;
                    const angle = Math.atan2(dy, dx);
                    const moveX = Math.cos(angle) * force;
                    const moveY = Math.sin(angle) * force;
                    
                    particle.style.transform = `translate(${moveX}px, ${moveY}px) scale(${1 + force/100})`;
                    particle.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    
                    // Reset after animation
                    setTimeout(() => {
                        particle.style.transform = '';
                        particle.style.transition = 'transform 1s ease-out';
                    }, 600);
                }, index * 2); // Stagger the effect
            });
        });
        
        // Progress bar animation on scroll
        const observerOptions = {
            threshold: 0.3,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBars = entry.target.querySelectorAll('.progress-fill');
                    progressBars.forEach(bar => {
                        const width = bar.style.width;
                        bar.style.width = '0';
                        setTimeout(() => {
                            bar.style.width = width;
                            bar.style.transition = 'width 1.5s cubic-bezier(0.4, 0, 0.2, 1)';
                        }, 100);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('#progress .iso-glass').forEach(section => {
            observer.observe(section);
        });
        
        // Feature cards animation on scroll
        const featureObserver = new IntersectionObserver(function(entries) {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '0';
                        entry.target.style.transform = 'translateY(30px)';
                        entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                    }, index * 100);
                    featureObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.feature-card').forEach(card => {
            featureObserver.observe(card);
        });
    </script>
</body>
</html>
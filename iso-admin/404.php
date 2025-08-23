<?php
/**
 * Glitch-style 404 Error Page for Admin
 * 
 * @package Isotone
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = '404 - Page Not Found';
$hide_sidebar = true; // Don't show sidebar on error page
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Isotone Admin</title>
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="/isotone/iso-admin/css/tailwind.min.css">
    
    <style>
        /* Glitch effect for text */
        @keyframes glitch {
            0% {
                text-shadow: 
                    0.05em 0 0 rgba(255, 0, 0, 0.5),
                    -0.05em -0.025em 0 rgba(0, 255, 0, 0.5),
                    0.025em 0.05em 0 rgba(0, 0, 255, 0.5);
            }
            14% {
                text-shadow: 
                    0.05em 0 0 rgba(255, 0, 0, 0.5),
                    -0.05em -0.025em 0 rgba(0, 255, 0, 0.5),
                    0.025em 0.05em 0 rgba(0, 0, 255, 0.5);
            }
            15% {
                text-shadow: 
                    -0.05em -0.025em 0 rgba(255, 0, 0, 0.5),
                    0.025em 0.025em 0 rgba(0, 255, 0, 0.5),
                    -0.05em -0.05em 0 rgba(0, 0, 255, 0.5);
            }
            49% {
                text-shadow: 
                    -0.05em -0.025em 0 rgba(255, 0, 0, 0.5),
                    0.025em 0.025em 0 rgba(0, 255, 0, 0.5),
                    -0.05em -0.05em 0 rgba(0, 0, 255, 0.5);
            }
            50% {
                text-shadow: 
                    0.025em 0.05em 0 rgba(255, 0, 0, 0.5),
                    0.05em 0 0 rgba(0, 255, 0, 0.5),
                    0 -0.05em 0 rgba(0, 0, 255, 0.5);
            }
            99% {
                text-shadow: 
                    0.025em 0.05em 0 rgba(255, 0, 0, 0.5),
                    0.05em 0 0 rgba(0, 255, 0, 0.5),
                    0 -0.05em 0 rgba(0, 0, 255, 0.5);
            }
            100% {
                text-shadow: 
                    -0.025em 0 0 rgba(255, 0, 0, 0.5),
                    -0.025em -0.025em 0 rgba(0, 255, 0, 0.5),
                    -0.025em -0.05em 0 rgba(0, 0, 255, 0.5);
            }
        }

        /* Glitch effect with clip-path */
        @keyframes glitch-clip {
            0% {
                clip-path: polygon(0 2%, 100% 2%, 100% 5%, 0 5%);
                transform: translateX(0);
            }
            10% {
                clip-path: polygon(0 15%, 100% 15%, 100% 22%, 0 22%);
                transform: translateX(-2px);
            }
            20% {
                clip-path: polygon(0 35%, 100% 35%, 100% 45%, 0 45%);
                transform: translateX(2px);
            }
            30% {
                clip-path: polygon(0 50%, 100% 50%, 100% 58%, 0 58%);
                transform: translateX(-1px);
            }
            40% {
                clip-path: polygon(0 60%, 100% 60%, 100% 65%, 0 65%);
                transform: translateX(1px);
            }
            50% {
                clip-path: polygon(0 70%, 100% 70%, 100% 78%, 0 78%);
                transform: translateX(-2px);
            }
            60% {
                clip-path: polygon(0 80%, 100% 80%, 100% 85%, 0 85%);
                transform: translateX(2px);
            }
            70% {
                clip-path: polygon(0 90%, 100% 90%, 100% 95%, 0 95%);
                transform: translateX(-1px);
            }
            80% {
                clip-path: polygon(0 20%, 100% 20%, 100% 30%, 0 30%);
                transform: translateX(1px);
            }
            90% {
                clip-path: polygon(0 40%, 100% 40%, 100% 48%, 0 48%);
                transform: translateX(-2px);
            }
            100% {
                clip-path: polygon(0 2%, 100% 2%, 100% 5%, 0 5%);
                transform: translateX(0);
            }
        }

        .glitch {
            position: relative;
            animation: glitch 2.5s infinite;
        }

        .glitch::before,
        .glitch::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .glitch::before {
            animation: glitch-clip 0.5s infinite linear alternate-reverse;
            color: #00ffff;
            mix-blend-mode: screen;
            opacity: 0.5;
        }

        .glitch::after {
            animation: glitch-clip 0.5s infinite linear alternate-reverse;
            animation-delay: 0.1s;
            color: #ff00ff;
            mix-blend-mode: screen;
            opacity: 0.5;
        }

        /* System error glitch - more subtle */
        .system-error {
            position: relative;
            display: inline-block;
            animation: system-glitch 4s infinite;
        }

        @keyframes system-glitch {
            0%, 90%, 100% {
                text-shadow: none;
                transform: translate(0);
            }
            92% {
                text-shadow: 
                    -2px 0 #ff00ff,
                    2px 0 #00ffff;
                transform: translate(-1px, 0);
            }
            94% {
                text-shadow: 
                    2px 0 #ff00ff,
                    -2px 0 #00ffff;
                transform: translate(1px, 0);
            }
            96% {
                text-shadow: 
                    -1px 0 #ff00ff,
                    1px 0 #00ffff;
                transform: translate(0, 0);
            }
        }

        .system-error::before,
        .system-error::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
        }

        .system-error::before {
            animation: system-glitch-2 3s infinite;
            color: #ff00ff;
            z-index: -1;
        }

        .system-error::after {
            animation: system-glitch-3 3s infinite;
            color: #00ffff;
            z-index: -1;
        }

        @keyframes system-glitch-2 {
            0%, 94%, 100% { 
                opacity: 0;
                transform: translate(0);
            }
            95% {
                opacity: 0.5;
                transform: translate(-2px, 0);
                clip-path: polygon(0 0, 100% 0, 100% 45%, 0 45%);
            }
            96% {
                opacity: 0.5;
                transform: translate(2px, 0);
                clip-path: polygon(0 55%, 100% 55%, 100% 100%, 0 100%);
            }
        }

        @keyframes system-glitch-3 {
            0%, 94%, 100% { 
                opacity: 0;
                transform: translate(0);
            }
            95% {
                opacity: 0.5;
                transform: translate(2px, 0);
                clip-path: polygon(0 25%, 100% 25%, 100% 75%, 0 75%);
            }
            96% {
                opacity: 0.5;
                transform: translate(-2px, 0);
                clip-path: polygon(0 0, 100% 0, 100% 25%, 0 25%);
            }
        }

        /* Matrix rain effect */
        .matrix-rain {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .matrix-column {
            position: absolute;
            top: 0;
            font-family: 'Courier New', monospace;
            color: #00d3f2;
            animation: matrix-fall linear infinite;
            writing-mode: vertical-rl;
            text-orientation: upright;
            transform: translateY(-100vh);
            opacity: 0;
            animation-fill-mode: both;
        }
        
        /* Matrix glitch effect - instant color change */
        .matrix-column.matrix-glitching {
            color: #00ffcc !important;
            text-shadow: 0 0 15px #00ffcc !important;
        }
        
        /* Depth layers for matrix */
        .matrix-column.layer-back {
            font-size: 10px;
            opacity: 0.15;
            filter: blur(2px);
            text-shadow: 0 0 4px #00d3f2;
            animation-duration: 25s;
        }
        
        .matrix-column.layer-mid {
            font-size: 12px;
            opacity: 0.3;
            filter: blur(0.5px);
            text-shadow: 0 0 6px #00d3f2;
            animation-duration: 18s;
        }
        
        .matrix-column.layer-front {
            font-size: 16px;
            opacity: 0.6;
            filter: blur(0px);
            text-shadow: 0 0 10px #00d3f2;
            animation-duration: 12s;
        }
        
        @keyframes matrix-fall {
            0% {
                transform: translateY(-100vh);
                opacity: 0;
            }
            1% {
                opacity: 1;
            }
            100% {
                transform: translateY(100vh);
                opacity: 1;
            }
        }

        /* Scanlines effect */
        @keyframes scanlines {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 0 10px;
            }
        }

        .scanlines::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                to bottom,
                transparent 0%,
                rgba(255, 255, 255, 0.03) 50%,
                transparent 100%
            );
            background-size: 100% 10px;
            animation: scanlines 0.5s linear infinite;
            pointer-events: none;
            z-index: 2;
        }

        /* Flicker effect */
        @keyframes flicker {
            0% { opacity: 1; }
            4% { opacity: 0.9; }
            6% { opacity: 0.85; }
            8% { opacity: 0.95; }
            10% { opacity: 1; }
            11% { opacity: 0.9; }
            12% { opacity: 1; }
            14% { opacity: 0.95; }
            16% { opacity: 1; }
            86% { opacity: 1; }
            87% { opacity: 0.9; }
            88% { opacity: 1; }
            90% { opacity: 0.95; }
            92% { opacity: 0.9; }
            93% { opacity: 1; }
            94% { opacity: 0.9; }
            95% { opacity: 1; }
            100% { opacity: 1; }
        }

        .flicker {
            animation: flicker 5s linear infinite;
        }

        /* Card glassmorphism */
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 
                0 8px 32px 0 rgba(0, 211, 242, 0.15),
                inset 0 0 0 1px rgba(0, 211, 242, 0.1);
        }

        /* Distortion on hover */
        .distort-hover {
            transition: all 0.2s ease;
        }

        .distort-hover:hover {
            transform: scale(1.02) skew(1deg);
            filter: contrast(1.2) brightness(1.1);
        }

        /* RGB shift effect */
        @keyframes rgb-shift {
            0%, 100% {
                filter: hue-rotate(0deg);
            }
            50% {
                filter: hue-rotate(180deg);
            }
        }

        .rgb-shift {
            animation: rgb-shift 10s linear infinite;
        }

        /* Terminal cursor blink */
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        .cursor {
            animation: blink 1s step-end infinite;
        }

        /* Glitch button styles */
        .glitch-button {
            position: relative;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .glitch-button:hover {
            animation: glitch-button 0.3s infinite;
            text-shadow: 
                2px 2px 0 #ff00ff,
                -2px -2px 0 #00ffff;
        }

        @keyframes glitch-button {
            0%, 100% {
                transform: translate(0);
            }
            20% {
                transform: translate(-1px, 1px);
            }
            40% {
                transform: translate(1px, -1px);
            }
            60% {
                transform: translate(-1px, -1px);
            }
            80% {
                transform: translate(1px, 1px);
            }
        }

        .glitch-lines {
            pointer-events: none;
        }

        .glitch-line {
            position: absolute;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                #00d3f2 10%, 
                #00d3f2 90%, 
                transparent 100%);
            opacity: 0;
        }

        .glitch-line:nth-child(1) {
            top: 25%;
        }

        .glitch-line:nth-child(2) {
            top: 50%;
        }

        .glitch-line:nth-child(3) {
            top: 75%;
        }

        @keyframes scan-line {
            0%, 95% {
                opacity: 0;
                transform: translateY(0);
            }
            95.1%, 100% {
                opacity: 0.5;
                transform: translateY(0);
            }
        }

        .glitch-button:hover .glitch-line {
            animation: scan-line 0.5s linear infinite;
        }

        .glitch-button:hover .glitch-line:nth-child(1) {
            animation-delay: 0s;
        }

        .glitch-button:hover .glitch-line:nth-child(2) {
            animation-delay: 0.15s;
        }

        .glitch-button:hover .glitch-line:nth-child(3) {
            animation-delay: 0.3s;
        }

        /* Terminal-style button flicker on hover */
        .glitch-button:hover span {
            animation: terminal-flicker 0.15s infinite;
        }

        @keyframes terminal-flicker {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center relative overflow-hidden scanlines">
    
    <!-- Matrix Rain Background -->
    <div class="matrix-rain" id="matrixRain"></div>
    
    <!-- Main Content Card -->
    <div class="relative z-10 max-w-2xl w-full mx-8">
        <div class="glass-card rounded-2xl p-8 md:p-12 flicker">
            
            <!-- Glitch Icon -->
            <div class="mb-8 text-center">
                <div class="inline-block relative">
                    <svg class="w-32 h-32 mx-auto rgb-shift" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" 
                              stroke="url(#gradient)" 
                              d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" 
                              stroke="url(#gradient)" 
                              d="M12 9v4m0 4h.01" />
                        <defs>
                            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00D3F2;stop-opacity:1">
                                    <animate attributeName="stop-color" values="#00D3F2;#00FF88;#FF3366;#00D3F2" dur="3s" repeatCount="indefinite" />
                                </stop>
                                <stop offset="100%" style="stop-color:#FF3366;stop-opacity:1">
                                    <animate attributeName="stop-color" values="#FF3366;#00D3F2;#00FF88;#FF3366" dur="3s" repeatCount="indefinite" />
                                </stop>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
            
            <!-- 404 Glitch Text -->
            <h1 class="font-bold text-center mb-6 glitch" data-text="404" style="font-size: 10rem; line-height: 1;">
                404
            </h1>
            
            <!-- Error Message -->
            <div class="text-center mb-8">
                <h2 class="text-2xl md:text-3xl font-semibold mb-4 text-cyan-400">
                    <span class="font-mono system-error" data-text="[SYSTEM.ERROR]">[SYSTEM.ERROR]</span>
                </h2>
                
                <div class="font-mono text-sm md:text-base text-gray-400 space-y-1">
                    <p>
                        <span class="text-green-400">$</span> searching /isotone/iso-admin/
                        <span class="cursor">_</span>
                    </p>
                    <p class="text-red-400">
                        ERROR: Resource not found in current reality
                    </p>
                    <p class="text-yellow-400">
                        WARNING: Timeline divergence detected
                    </p>
                    <p class="text-cyan-400">
                        SUGGESTION: Attempting multiverse scan...
                    </p>
                </div>
            </div>
            
            <!-- Action Button -->
            <div class="flex justify-center">
                <a href="/isotone/iso-admin/dashboard.php" 
                   class="glitch-button relative inline-block px-8 py-4 font-mono text-lg font-bold text-cyan-400 uppercase tracking-wider group">
                    <span class="relative z-10">&lt;SYSTEM.RESTORE/&gt;</span>
                    <div class="absolute inset-0 border-2 border-cyan-400 opacity-50"></div>
                    <div class="absolute inset-0 border-2 border-cyan-400 opacity-50 animate-pulse"></div>
                    <div class="glitch-button-bg absolute inset-0 bg-cyan-400 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                    <div class="glitch-lines absolute inset-0 overflow-hidden">
                        <div class="glitch-line"></div>
                        <div class="glitch-line"></div>
                        <div class="glitch-line"></div>
                    </div>
                </a>
            </div>
            
            <!-- Glitch Stats -->
            <div class="mt-8 pt-8 border-t border-gray-700">
                <div class="font-mono text-xs text-gray-500 space-y-1">
                    <div class="flex justify-between">
                        <span>ERRNO:</span>
                        <span class="text-cyan-400">0x404</span>
                    </div>
                    <div class="flex justify-between">
                        <span>REALITY:</span>
                        <span class="text-yellow-400">UNSTABLE</span>
                    </div>
                    <div class="flex justify-between">
                        <span>QUANTUM.STATE:</span>
                        <span class="text-green-400">SUPERPOSITION</span>
                    </div>
                    <div class="flex justify-between">
                        <span>GLITCH.LEVEL:</span>
                        <span class="text-red-400">CRITICAL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Generate matrix rain
        function generateMatrixRain() {
            const container = document.getElementById('matrixRain');
            const characters = '01アイウエオカキクケコサシスセソタチツテト10110010101ERROR404SYSTEMFAILURE';
            const layers = ['layer-back', 'layer-mid', 'layer-front'];
            
            // Create multiple layers for depth
            layers.forEach((layer, layerIndex) => {
                const numberOfColumns = Math.floor(window.innerWidth / (45 - layerIndex * 10)); // Reduced density
                
                for (let i = 0; i < numberOfColumns; i++) {
                    setTimeout(() => {
                        const column = document.createElement('div');
                        column.className = `matrix-column ${layer}`;
                        
                        // Random positioning with slight overlap possibility
                        const position = (Math.random() * 100);
                        column.style.left = position + '%';
                        
                        // Override animation duration for more variety within layers
                        const baseDuration = layer === 'layer-back' ? 25 : layer === 'layer-mid' ? 18 : 12;
                        column.style.animationDuration = (baseDuration + Math.random() * 8 - 4) + 's';
                        
                        // Stagger the animation start
                        column.style.animationDelay = (Math.random() * 10) + 's';
                        
                        // Vary the length of text based on layer
                        const textLength = layer === 'layer-back' ? 60 : layer === 'layer-mid' ? 50 : 40;
                        
                        // Generate random characters
                        let text = '';
                        for (let j = 0; j < textLength; j++) {
                            text += characters[Math.floor(Math.random() * characters.length)] + '\n';
                        }
                        column.textContent = text;
                        
                        container.appendChild(column);
                    }, (layerIndex * 300) + (i * 30)); // Stagger by layer then by column
                }
            });
        }
        
        // Initialize matrix rain after a small delay
        setTimeout(generateMatrixRain, 100);
        
        // Add random glitch effects
        setInterval(() => {
            const glitchElements = document.querySelectorAll('.glitch');
            glitchElements.forEach(el => {
                if (Math.random() > 0.95) {
                    el.style.transform = `translateX(${Math.random() * 4 - 2}px)`;
                    setTimeout(() => {
                        el.style.transform = 'translateX(0)';
                    }, 50);
                }
            });
            
            // Matrix glitch - occasional random column brightening
            if (Math.random() > 0.8) { // 20% chance every cycle
                const matrixColumns = document.querySelectorAll('.matrix-column');
                if (matrixColumns.length > 0) {
                    const randomIndex = Math.floor(Math.random() * matrixColumns.length);
                    const col = matrixColumns[randomIndex];
                    col.classList.add('matrix-glitching');
                    
                    setTimeout(() => {
                        col.classList.remove('matrix-glitching');
                    }, 100);
                }
            }
        }, 300); // Check every 300ms
        
        // Console easter eggs
        console.log('%c⚠ SYSTEM.BREACH.DETECTED', 'font-size: 20px; color: #ff0000; font-weight: bold; text-shadow: 0 0 10px #ff0000;');
        console.log('%c> Initializing recovery protocol...', 'color: #00ff00; font-family: monospace;');
        console.log('%c> ERROR 404: Reality not found', 'color: #00ffff; font-family: monospace;');
        console.log('%c> Suggestion: Try dashboard.php or users.php', 'color: #ffff00; font-family: monospace;');
    </script>
</body>
</html>
<?php
/**
 * Universal Error Page Handler
 * Handles multiple HTTP error codes with glitch-style design
 * Allows theme overrides for custom error pages
 * 
 * Usage: error.php?code=404
 * 
 * @package Isotone
 * @since 0.3.2
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get error code from various sources
// 1. Check query parameter (for direct access)
// 2. Check Apache's REDIRECT_STATUS (when used as ErrorDocument)
// 3. Check HTTP response code
// 4. Default to 404
$error_code = 404; // Default

if (isset($_GET['code'])) {
    $error_code = intval($_GET['code']);
} elseif (isset($_SERVER['REDIRECT_STATUS'])) {
    $error_code = intval($_SERVER['REDIRECT_STATUS']);
} elseif (http_response_code() !== false) {
    $error_code = http_response_code();
}

// Check if we're in a frontend context (not admin)
$is_admin = strpos($_SERVER['REQUEST_URI'], '/iso-admin/') !== false;

if (!$is_admin) {
    // Load config to get active theme
    $config_file = dirname(__DIR__, 2) . '/config.php';
    if (file_exists($config_file)) {
        require_once $config_file;
    }
    
    // Check for theme-specific error page
    if (defined('ACTIVE_THEME')) {
        $theme_error_files = [
            dirname(__DIR__, 2) . '/iso-themes/' . ACTIVE_THEME . '/' . $error_code . '.php',
            dirname(__DIR__, 2) . '/iso-themes/' . ACTIVE_THEME . '/error-' . $error_code . '.php',
            dirname(__DIR__, 2) . '/iso-themes/' . ACTIVE_THEME . '/errors/' . $error_code . '.php',
        ];
        
        // Also check for a generic error.php that handles all errors
        $theme_generic_error = dirname(__DIR__, 2) . '/iso-themes/' . ACTIVE_THEME . '/error.php';
        
        // Try specific error files first
        foreach ($theme_error_files as $theme_file) {
            if (file_exists($theme_file)) {
                // Set error code for theme to use
                $GLOBALS['error_code'] = $error_code;
                $_GET['error_code'] = $error_code;
                
                // Include theme's custom error page
                include $theme_file;
                exit;
            }
        }
        
        // Try generic error handler
        if (file_exists($theme_generic_error)) {
            // Set error code for theme to use
            $GLOBALS['error_code'] = $error_code;
            $_GET['error_code'] = $error_code;
            
            // Include theme's custom error page
            include $theme_generic_error;
            exit;
        }
    }
}

// Define error messages and details - All using multiverse/quantum reality theme
$error_configs = [
    400 => [
        'title' => '400 - Bad Request',
        'system_error' => '[REALITY.MALFORMED]',
        'messages' => [
            'ERROR: Request exists in impossible quantum state',
            'WARNING: Data paradox preventing materialization',
            'SUGGESTION: Recalibrating dimensional parameters...'
        ],
        'errno' => '0x400',
        'status' => 'PARADOX',
        'quantum' => 'COLLAPSED',
        'glitch' => 'DIMENSIONAL'
    ],
    401 => [
        'title' => '401 - Unauthorized',
        'system_error' => '[QUANTUM.LOCKED]',
        'messages' => [
            'ERROR: Quantum signature not recognized',
            'WARNING: Dimensional access key missing',
            'SUGGESTION: Synchronizing alternate credentials...'
        ],
        'errno' => '0x401',
        'status' => 'UNVERIFIED',
        'quantum' => 'ENCRYPTED',
        'glitch' => 'IDENTITY'
    ],
    403 => [
        'title' => '403 - Forbidden',
        'system_error' => '[DIMENSION.RESTRICTED]',
        'messages' => [
            'ERROR: Access forbidden across all timelines',
            'WARNING: Reality barrier preventing entry',
            'SUGGESTION: Seeking permission from higher dimension...'
        ],
        'errno' => '0x403',
        'status' => 'FORBIDDEN',
        'quantum' => 'SHIELDED',
        'glitch' => 'BARRIER'
    ],
    404 => [
        'title' => '404 - Page Not Found',
        'system_error' => '[MULTIVERSE.ERROR]',
        'messages' => [
            'ERROR: Resource not found in current reality',
            'WARNING: Timeline divergence detected',
            'SUGGESTION: Attempting multiverse scan...'
        ],
        'errno' => '0x404',
        'status' => 'MISSING',
        'quantum' => 'SUPERPOSITION',
        'glitch' => 'CRITICAL'
    ],
    405 => [
        'title' => '405 - Method Not Allowed',
        'system_error' => '[REALITY.INCOMPATIBLE]',
        'messages' => [
            'ERROR: Method exists in parallel universe only',
            'WARNING: Dimensional protocol mismatch',
            'SUGGESTION: Adjusting quantum methodology...'
        ],
        'errno' => '0x405',
        'status' => 'MISALIGNED',
        'quantum' => 'PHASE-SHIFTED',
        'glitch' => 'PROTOCOL'
    ],
    408 => [
        'title' => '408 - Request Timeout',
        'system_error' => '[TIME.PARADOX]',
        'messages' => [
            'ERROR: Request caught in temporal loop',
            'WARNING: Time dilation exceeding parameters',
            'SUGGESTION: Resynchronizing with base timeline...'
        ],
        'errno' => '0x408',
        'status' => 'LOOPING',
        'quantum' => 'TIME-LOCKED',
        'glitch' => 'TEMPORAL'
    ],
    500 => [
        'title' => '500 - Internal Server Error',
        'system_error' => '[REALITY.COLLAPSE]',
        'messages' => [
            'ERROR: Reality matrix experiencing cascade failure',
            'WARNING: Multiple timelines converging dangerously',
            'SUGGESTION: Activating quantum stabilizers...'
        ],
        'errno' => '0x500',
        'status' => 'COLLAPSING',
        'quantum' => 'UNSTABLE',
        'glitch' => 'CATASTROPHIC'
    ],
    502 => [
        'title' => '502 - Bad Gateway',
        'system_error' => '[PORTAL.MALFUNCTION]',
        'messages' => [
            'ERROR: Interdimensional gateway corrupted',
            'WARNING: Portal destination unreachable',
            'SUGGESTION: Calculating alternate wormhole route...'
        ],
        'errno' => '0x502',
        'status' => 'DISRUPTED',
        'quantum' => 'ENTANGLED',
        'glitch' => 'GATEWAY'
    ],
    503 => [
        'title' => '503 - Service Unavailable',
        'system_error' => '[DIMENSION.OFFLINE]',
        'messages' => [
            'ERROR: Service exists in inaccessible dimension',
            'WARNING: Reality undergoing maintenance',
            'SUGGESTION: Checking neighboring timelines...'
        ],
        'errno' => '0x503',
        'status' => 'PHASED-OUT',
        'quantum' => 'MAINTENANCE',
        'glitch' => 'DIMENSIONAL'
    ],
    504 => [
        'title' => '504 - Gateway Timeout',
        'system_error' => '[WORMHOLE.TIMEOUT]',
        'messages' => [
            'ERROR: Portal response exceeding light-speed delay',
            'WARNING: Quantum tunnel destabilizing',
            'SUGGESTION: Boosting tachyon transmission power...'
        ],
        'errno' => '0x504',
        'status' => 'DELAYED',
        'quantum' => 'DESYNCHRONIZED',
        'glitch' => 'LATENCY'
    ]
];

// Get config for current error code, default to 404 if not found
$config = isset($error_configs[$error_code]) ? $error_configs[$error_code] : $error_configs[404];

// Set the proper HTTP response code
http_response_code($error_code);

// Load hooks system if available
$hooks_file = dirname(__DIR__, 2) . '/iso-core/hooks.php';
if (file_exists($hooks_file)) {
    require_once $hooks_file;
}

// Allow themes/plugins to modify error config
if (function_exists('apply_filters')) {
    $config = apply_filters('iso_error_config', $config, $error_code);
    
    // Allow themes/plugins to handle error display
    $custom_handled = apply_filters('iso_handle_error', false, $error_code, $config);
    if ($custom_handled === true) {
        exit; // Theme/plugin handled the error display
    }
}

$page_title = $config['title'];
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
        /* Glitch effect for text - triggered by class */
        @keyframes glitch {
            0% {
                text-shadow: none;
                transform: none;
                filter: none;
            }
            10% {
                text-shadow: 
                    0.08em 0 0 rgba(255, 0, 0, 0.9),
                    -0.05em -0.04em 0 rgba(0, 255, 255, 0.9),
                    0.025em 0.05em 0 rgba(255, 255, 0, 0.9);
                transform: skew(-2deg, 0.5deg) scale(1.05) translateX(-3px);
                filter: blur(1px) contrast(3) brightness(1.5);
            }
            20% {
                text-shadow: 
                    0.05em 0 0 rgba(255, 0, 0, 0.7),
                    -0.03em -0.04em 0 rgba(0, 255, 255, 0.7),
                    0.025em 0.05em 0 rgba(255, 255, 0, 0.7);
                transform: skew(-0.5deg, 0.2deg) scale(1.01);
                filter: blur(0.3px) contrast(1.5) brightness(1.2);
            }
            30% {
                text-shadow: 
                    -0.08em -0.025em 0 rgba(255, 0, 255, 0.9),
                    0.05em 0.025em 0 rgba(0, 255, 0, 0.9),
                    -0.08em -0.05em 0 rgba(0, 255, 255, 0.9);
                transform: skew(3deg, -0.5deg) scale(0.95) translateX(5px);
                filter: blur(1.5px) saturate(3) hue-rotate(180deg);
            }
            40% {
                text-shadow: 
                    -0.05em -0.025em 0 rgba(255, 0, 255, 0.7),
                    0.025em 0.025em 0 rgba(0, 255, 0, 0.7),
                    -0.05em -0.05em 0 rgba(0, 255, 255, 0.7);
                transform: skew(0.8deg, -0.1deg) scale(0.99) translateX(-2px);
                filter: blur(0.5px) saturate(2) hue-rotate(90deg);
            }
            50% {
                text-shadow: 
                    0.1em 0.05em 0 rgba(255, 0, 0, 0.9),
                    0.08em 0 0 rgba(0, 255, 255, 0.9),
                    0 -0.08em 0 rgba(255, 0, 255, 0.9);
                transform: skew(-2.5deg, 1deg) scale(1.08) translateY(-2px);
                filter: blur(2px) contrast(5) brightness(0.5);
            }
            60% {
                text-shadow: 
                    0.025em 0.05em 0 rgba(255, 0, 0, 0.7),
                    0.05em 0 0 rgba(0, 255, 255, 0.7),
                    0 -0.05em 0 rgba(255, 0, 255, 0.7);
                transform: skew(-0.3deg, 0.5deg) scale(1.02) translateY(1px);
                filter: blur(0.2px) contrast(1.8) brightness(0.9);
            }
            70% {
                text-shadow: 
                    -0.08em 0 0 rgba(0, 255, 255, 0.9),
                    -0.05em -0.025em 0 rgba(255, 0, 255, 0.9),
                    -0.025em -0.08em 0 rgba(255, 255, 0, 0.9);
                transform: skew(2deg, -1deg) scale(0.92) translateX(-4px) translateY(3px);
                filter: blur(1.2px) saturate(4) contrast(2) hue-rotate(-90deg);
            }
            80% {
                text-shadow: 
                    -0.025em 0 0 rgba(0, 255, 255, 0.7),
                    -0.025em -0.025em 0 rgba(255, 0, 255, 0.7),
                    -0.025em -0.05em 0 rgba(255, 255, 0, 0.7);
                transform: skew(0.5deg, -0.3deg) scale(0.98) translateX(1px);
                filter: blur(0.4px) saturate(1.5) contrast(1.3);
            }
            90%, 100% {
                text-shadow: none;
                transform: none;
                filter: none;
            }
        }

        /* Glitch effect with clip-path - for glitch animation */
        @keyframes glitch-clip {
            0% {
                clip-path: none;
                transform: translateX(0);
                opacity: 0;
                filter: none;
            }
            10% {
                opacity: 0.8;
                clip-path: polygon(0 10%, 100% 10%, 100% 25%, 0 25%);
                transform: translateX(-3px) skewY(0.5deg) scaleX(1.05);
                filter: blur(0.8px) contrast(2);
            }
            25% {
                clip-path: polygon(0 35%, 100% 35%, 100% 50%, 0 50%);
                transform: translateX(3px) skewY(-0.3deg) scaleY(0.95);
                filter: blur(0.6px) brightness(1.5) saturate(2);
            }
            40% {
                clip-path: polygon(0 60%, 100% 60%, 100% 75%, 0 75%);
                transform: translateX(-2px) skewX(0.8deg) scale(1.03);
                filter: blur(0.4px) contrast(1.8) hue-rotate(180deg);
            }
            55% {
                clip-path: polygon(0 20%, 100% 20%, 100% 40%, 0 40%);
                transform: translateX(4px) skewX(-1deg) scaleX(0.94);
                filter: blur(1px) brightness(1.2) saturate(2.5);
            }
            70% {
                clip-path: polygon(0 80%, 100% 80%, 100% 95%, 0 95%);
                transform: translateX(2px) skewX(-0.5deg) scaleX(0.97);
                filter: blur(0.5px) brightness(0.8) saturate(3);
            }
            90%, 100% {
                opacity: 0;
                clip-path: none;
                transform: translateX(0);
                filter: none;
            }
        }

        .glitch {
            position: relative;
            will-change: transform, filter;
        }

        /* Glitch active state - triggered randomly via JS */
        .glitch.glitching {
            animation: glitch 0.4s linear;
        }

        .glitch::before,
        .glitch::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            will-change: transform, filter, opacity;
            opacity: 0;
        }

        .glitch.glitching::before {
            animation: glitch-clip 0.4s linear;
            color: #00ffff;
            mix-blend-mode: screen;
        }

        .glitch.glitching::after {
            animation: glitch-clip 0.4s linear;
            animation-delay: 0.02s;
            color: #ff00ff;
            mix-blend-mode: screen;
        }

        /* Additional distortion wave effect */
        @keyframes distortion-wave {
            0% {
                filter: none;
            }
            20% {
                filter: url(#wavy);
            }
            50% {
                filter: url(#turbulence);
            }
            80% {
                filter: url(#wavy);
            }
            100% {
                filter: none;
            }
        }
        
        .glitch.glitching {
            animation: glitch 0.4s linear, distortion-wave 0.4s linear;
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
            position: relative;
            overflow: visible;
        }

        /* Bottom center glow effect */
        .glass-card::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(0, 211, 242, 0.8) 20%,
                rgba(0, 211, 242, 0.8) 50%,
                rgba(0, 211, 242, 0.8) 80%,
                transparent 100%);
            opacity: 0.8;
            transition: all 0.3s ease;
            filter: blur(0.5px);
        }

        .glass-card::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 40%;
            height: 6px;
            background: radial-gradient(ellipse at center, 
                rgba(0, 211, 242, 0.4) 0%, 
                transparent 70%);
            opacity: 0.5;
            transition: all 0.3s ease;
            filter: blur(6px);
        }

        /* Hover effect for glow */
        .glass-card:hover::before {
            width: 80%;
            opacity: 1;
        }

        .glass-card:hover::after {
            width: 60%;
            opacity: 0.7;
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
    
    <!-- SVG Filters for Distortion -->
    <svg style="position: absolute; width: 0; height: 0;">
        <defs>
            <filter id="wavy">
                <feTurbulence type="turbulence" baseFrequency="0.02" numOctaves="5" result="turbulence" seed="1" />
                <feDisplacementMap in2="turbulence" in="SourceGraphic" scale="30" xChannelSelector="R" yChannelSelector="G" />
            </filter>
            <filter id="turbulence">
                <feTurbulence type="fractalNoise" baseFrequency="0.01 0.1" numOctaves="2" result="turbulence" seed="5" />
                <feDisplacementMap in="SourceGraphic" in2="turbulence" scale="20" xChannelSelector="G" yChannelSelector="B" />
                <feColorMatrix type="matrix" values="1 0 0 0 0.2
                                                      0 1 0 0 -0.1
                                                      0 0 1 0 0.1
                                                      0 0 0 1 0" />
            </filter>
        </defs>
    </svg>
    
    <!-- Main Content Card -->
    <div class="relative z-10 max-w-2xl w-full mx-8">
        <div class="glass-card rounded-2xl p-8 md:p-12 flicker">
            
            <!-- Glitch Icon -->
            <div class="mb-8 text-center">
                <div class="inline-block relative">
                    <svg class="w-32 h-32 mx-auto rgb-shift" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" 
                              stroke="url(#gradient)" 
                              d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
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
            
            <!-- Error Code Glitch Text -->
            <h1 class="font-bold text-center mb-6 glitch" data-text="<?php echo $error_code; ?>" style="font-size: 10rem; line-height: 1;">
                <?php echo $error_code; ?>
            </h1>
            
            <!-- Error Message -->
            <div class="text-center mb-8">
                <h2 class="text-2xl md:text-3xl font-semibold mb-4 text-cyan-400">
                    <span class="font-mono system-error" data-text="<?php echo $config['system_error']; ?>"><?php echo $config['system_error']; ?></span>
                </h2>
                
                <div class="font-mono text-sm md:text-base text-gray-400 space-y-1">
                    <p>
                        <span class="text-green-400">$</span> analyzing /isotone/
                        <span class="cursor">_</span>
                    </p>
                    <p class="text-red-400">
                        <?php echo $config['messages'][0]; ?>
                    </p>
                    <p class="text-yellow-400">
                        <?php echo $config['messages'][1]; ?>
                    </p>
                    <p class="text-cyan-400">
                        <?php echo $config['messages'][2]; ?>
                    </p>
                </div>
            </div>
            
            <!-- Action Button -->
            <div class="flex justify-center">
                <a href="<?php echo $error_code === 401 ? '/isotone/iso-admin/login.php' : '/isotone/iso-admin/dashboard.php'; ?>" 
                   class="glitch-button relative inline-block px-8 py-4 font-mono text-lg font-bold text-cyan-400 uppercase tracking-wider group">
                    <span class="relative z-10"><?php echo $error_code === 401 ? '&lt;SYSTEM.LOGIN/&gt;' : '&lt;SYSTEM.RESTORE/&gt;'; ?></span>
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
                        <span class="text-cyan-400"><?php echo $config['errno']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>STATUS:</span>
                        <span class="text-yellow-400"><?php echo $config['status']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>QUANTUM.STATE:</span>
                        <span class="text-green-400"><?php echo $config['quantum']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>GLITCH.LEVEL:</span>
                        <span class="text-red-400"><?php echo $config['glitch']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Store all timeouts and intervals for cleanup
        const activeTimers = {
            timeouts: [],
            intervals: [],
            glitchTimeout: null
        };
        
        // Cleanup function to prevent memory leaks
        function cleanup() {
            activeTimers.timeouts.forEach(t => clearTimeout(t));
            activeTimers.intervals.forEach(i => clearInterval(i));
            if (activeTimers.glitchTimeout) clearTimeout(activeTimers.glitchTimeout);
            activeTimers.timeouts = [];
            activeTimers.intervals = [];
            activeTimers.glitchTimeout = null;
        }
        
        // Clean up when page unloads or user navigates away
        window.addEventListener('beforeunload', cleanup);
        window.addEventListener('pagehide', cleanup);
        
        // Generate matrix rain
        function generateMatrixRain() {
            const container = document.getElementById('matrixRain');
            if (!container) return; // Safety check
            
            const characters = '01アイウエオカキクケコサシスセソタチツテト10110010101ERROR404SYSTEMFAILURE';
            const layers = ['layer-back', 'layer-mid', 'layer-front'];
            
            // Track used positions to avoid overlap
            const usedPositions = [];
            
            // Create a single column
            function createColumn(layer, layerIndex) {
                const column = document.createElement('div');
                column.className = `matrix-column ${layer}`;
                
                // Find a position that's not too close to existing columns
                let position;
                let attempts = 0;
                do {
                    position = Math.random() * 100;
                    attempts++;
                    // Check if position is at least 2% away from other columns
                    var tooClose = usedPositions.some(pos => Math.abs(pos - position) < 2);
                } while (tooClose && attempts < 50);
                
                // Track position temporarily (will be cleared after column is gone)
                usedPositions.push(position);
                column.style.left = position + '%';
                
                // Override animation duration for more variety within layers
                // Base durations are slow, with variation that only makes them slower
                const baseDuration = layer === 'layer-back' ? 30 : layer === 'layer-mid' ? 25 : 20;
                // Add 0-15 seconds of additional slowness (no negative values to prevent fast columns)
                const randomVariation = Math.random() * 15;
                const duration = baseDuration + randomVariation;
                column.style.animationDuration = duration + 's';
                
                // Stagger the animation start
                const delay = Math.random() * 10;
                column.style.animationDelay = delay + 's';
                
                // Vary the length of text based on layer
                const textLength = layer === 'layer-back' ? 60 : layer === 'layer-mid' ? 50 : 40;
                
                // Generate random characters
                let text = '';
                for (let j = 0; j < textLength; j++) {
                    text += characters[Math.floor(Math.random() * characters.length)] + '\n';
                }
                column.textContent = text;
                
                container.appendChild(column);
                
                // Remove column after animation and clear position tracking
                const totalTime = (duration + delay) * 1000;
                const removeTimer = setTimeout(() => {
                    if (column.parentNode) {
                        column.parentNode.removeChild(column);
                    }
                    // Clear position from tracking
                    const posIndex = usedPositions.indexOf(position);
                    if (posIndex > -1) {
                        usedPositions.splice(posIndex, 1);
                    }
                    
                    // Create a new column to replace this one
                    createColumn(layer, layerIndex);
                }, totalTime + 1000); // Add 1 second buffer
                
                activeTimers.timeouts.push(removeTimer);
            }
            
            // Create multiple layers for depth
            layers.forEach((layer, layerIndex) => {
                const numberOfColumns = Math.floor(window.innerWidth / (80 - layerIndex * 15)); // Much less density
                
                for (let i = 0; i < numberOfColumns; i++) {
                    const timer = setTimeout(() => {
                        createColumn(layer, layerIndex);
                    }, (layerIndex * 300) + (i * 30)); // Stagger by layer then by column
                    
                    activeTimers.timeouts.push(timer);
                }
            });
        }
        
        // Initialize matrix rain after a small delay
        const initTimer = setTimeout(generateMatrixRain, 100);
        activeTimers.timeouts.push(initTimer);
        
        // Random glitch trigger - use setInterval instead of recursive setTimeout
        let glitchInterval = null;
        
        function startGlitchEffect() {
            const glitchElement = document.querySelector('.glitch');
            if (!glitchElement) return;
            
            function doGlitch() {
                if (!glitchElement.classList.contains('glitching')) {
                    glitchElement.classList.add('glitching');
                    
                    // Remove class after animation completes
                    activeTimers.glitchTimeout = setTimeout(() => {
                        glitchElement.classList.remove('glitching');
                    }, 400);
                }
            }
            
            // Random glitch every 2-8 seconds
            glitchInterval = setInterval(() => {
                if (Math.random() > 0.5) { // 50% chance each cycle
                    doGlitch();
                }
            }, 4000); // Check every 4 seconds
            
            activeTimers.intervals.push(glitchInterval);
        }
        
        // Start glitch effect after initial delay
        const glitchStartTimer = setTimeout(startGlitchEffect, 2000);
        activeTimers.timeouts.push(glitchStartTimer);
        
        // Cache DOM queries for micro-glitches
        let glitchElementsCache = null;
        let matrixColumnsCache = null;
        
        // Update cache periodically
        function updateCache() {
            glitchElementsCache = document.querySelectorAll('.glitch');
            matrixColumnsCache = document.querySelectorAll('.matrix-column');
        }
        
        // Initial cache
        const cacheTimer = setTimeout(updateCache, 500);
        activeTimers.timeouts.push(cacheTimer);
        
        // Update cache every 5 seconds
        const cacheInterval = setInterval(updateCache, 5000);
        activeTimers.intervals.push(cacheInterval);
        
        // Additional micro-glitches - optimized
        const microGlitchInterval = setInterval(() => {
            // Use cached elements
            if (glitchElementsCache) {
                glitchElementsCache.forEach(el => {
                    // Only do micro-glitch if not in main glitch
                    if (!el.classList.contains('glitching') && Math.random() > 0.97) {
                        el.style.transform = `translateX(${Math.random() * 4 - 2}px)`;
                        const resetTimer = setTimeout(() => {
                            el.style.transform = 'translateX(0)';
                        }, 50);
                        activeTimers.timeouts.push(resetTimer);
                    }
                });
            }
            
            // Matrix glitch - occasional random column brightening
            if (matrixColumnsCache && Math.random() > 0.8) { // 20% chance every cycle
                if (matrixColumnsCache.length > 0) {
                    const randomIndex = Math.floor(Math.random() * matrixColumnsCache.length);
                    const col = matrixColumnsCache[randomIndex];
                    if (col) {
                        col.classList.add('matrix-glitching');
                        
                        const resetTimer = setTimeout(() => {
                            col.classList.remove('matrix-glitching');
                        }, 100);
                        activeTimers.timeouts.push(resetTimer);
                    }
                }
            }
        }, 300); // Check every 300ms
        
        activeTimers.intervals.push(microGlitchInterval);
        
        // Console easter eggs
        console.log('%c⚠ SYSTEM.BREACH.DETECTED', 'font-size: 20px; color: #ff0000; font-weight: bold; text-shadow: 0 0 10px #ff0000;');
        console.log('%c> Initializing recovery protocol...', 'color: #00ff00; font-family: monospace;');
        console.log('%c> ERROR <?php echo $error_code; ?>: <?php echo $config['title']; ?>', 'color: #00ffff; font-family: monospace;');
        console.log('%c> <?php echo $config['messages'][2]; ?>', 'color: #ffff00; font-family: monospace;');
    </script>
</body>
</html>
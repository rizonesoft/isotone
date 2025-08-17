<?php
declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        // First check if there's a corresponding config constant
        // Map common env keys to config constants
        $configMap = [
            'DB_HOST' => 'DB_HOST',
            'DB_DATABASE' => 'DB_NAME',
            'DB_NAME' => 'DB_NAME',
            'DB_USERNAME' => 'DB_USER',
            'DB_USER' => 'DB_USER',
            'DB_PASSWORD' => 'DB_PASSWORD',
            'DB_PORT' => 'DB_PORT',
            'APP_ENV' => 'ENVIRONMENT',
            'APP_DEBUG' => 'DEBUG_MODE',
            'APP_URL' => 'SITE_URL',
        ];
        
        if (isset($configMap[$key]) && defined($configMap[$key])) {
            return constant($configMap[$key]);
        }
        
        // Fallback to environment variables (for backward compatibility)
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $config = [];
        
        if (empty($config)) {
            $configPath = ISOTONE_ROOT . '/config';
            if (is_dir($configPath)) {
                foreach (glob($configPath . '/*.php') as $file) {
                    $name = basename($file, '.php');
                    $config[$name] = require $file;
                }
            }
        }
        
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/public' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        // Legacy support - redirects to iso-runtime
        return ISOTONE_ROOT . '/iso-runtime' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('content_path')) {
    function content_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-content' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('runtime_path')) {
    function runtime_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-runtime' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('logs_path')) {
    function logs_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-runtime/logs' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('cache_path')) {
    function cache_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-runtime/cache' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('temp_path')) {
    function temp_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-runtime/temp' . ($path ? '/' . ltrim($path, '/') : '');
    }
}
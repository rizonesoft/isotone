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

if (!function_exists('logs_path')) {
    function logs_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-content/logs' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('cache_path')) {
    function cache_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-content/cache' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('temp_path')) {
    function temp_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-content/temp' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('uploads_path')) {
    function uploads_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-content/uploads' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('sessions_path')) {
    function sessions_path(string $path = ''): string
    {
        return ISOTONE_ROOT . '/iso-content/sessions' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

// ============================================================================
// Carbon Date Helpers
// ============================================================================

use Carbon\Carbon;

if (!function_exists('iso_date')) {
    /**
     * Create a Carbon date instance
     * 
     * @param mixed $date Date string, timestamp, or null for now
     * @param string|null $format Optional format to return
     * @return Carbon|string
     */
    function iso_date($date = null, ?string $format = null)
    {
        $carbon = $date ? Carbon::parse($date) : Carbon::now();
        return $format ? $carbon->format($format) : $carbon;
    }
}

if (!function_exists('iso_human_date')) {
    /**
     * Get human-readable date difference
     * Examples: "2 hours ago", "in 3 days", "yesterday"
     * 
     * @param mixed $date Date to convert
     * @return string
     */
    function iso_human_date($date): string
    {
        return Carbon::parse($date)->diffForHumans();
    }
}

if (!function_exists('iso_localized_date')) {
    /**
     * Get localized date string
     * 
     * @param mixed $date Date to format
     * @param string $format Format string (default: 'LLLL' - full date and time)
     * @param string|null $locale Locale to use (null for site default)
     * @return string
     */
    function iso_localized_date($date, string $format = 'LLLL', ?string $locale = null): string
    {
        if (!$locale) {
            // Get from site settings or default to 'en'
            $locale = 'en'; // TODO: get from site settings
        }
        
        return Carbon::parse($date)->locale($locale)->isoFormat($format);
    }
}

if (!function_exists('iso_timezone')) {
    /**
     * Convert date to specific timezone
     * 
     * @param mixed $date Date to convert
     * @param string $timezone Target timezone
     * @param string|null $format Optional format
     * @return Carbon|string
     */
    function iso_timezone($date, string $timezone, ?string $format = null)
    {
        $carbon = Carbon::parse($date)->setTimezone($timezone);
        return $format ? $carbon->format($format) : $carbon;
    }
}

if (!function_exists('iso_date_range')) {
    /**
     * Format a date range
     * 
     * @param mixed $start Start date
     * @param mixed $end End date
     * @param string $format Date format
     * @return string
     */
    function iso_date_range($start, $end, string $format = 'M j, Y'): string
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        
        if ($startDate->isSameDay($endDate)) {
            return $startDate->format($format);
        } elseif ($startDate->isSameMonth($endDate)) {
            return $startDate->format('M j') . '-' . $endDate->format('j, Y');
        } elseif ($startDate->isSameYear($endDate)) {
            return $startDate->format('M j') . ' - ' . $endDate->format('M j, Y');
        } else {
            return $startDate->format($format) . ' - ' . $endDate->format($format);
        }
    }
}

if (!function_exists('iso_age')) {
    /**
     * Calculate age from date
     * 
     * @param mixed $date Birth date
     * @return int Age in years
     */
    function iso_age($date): int
    {
        return Carbon::parse($date)->age;
    }
}

if (!function_exists('iso_working_days')) {
    /**
     * Calculate working days between two dates
     * 
     * @param mixed $start Start date
     * @param mixed $end End date
     * @return int Number of working days
     */
    function iso_working_days($start, $end): int
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        
        $days = 0;
        while ($startDate->lte($endDate)) {
            if (!$startDate->isWeekend()) {
                $days++;
            }
            $startDate->addDay();
        }
        
        return $days;
    }
}

if (!function_exists('iso_error')) {
    /**
     * Display error page or redirect to error handler
     * 
     * @param int $code HTTP error code (400, 401, 403, 404, 405, 408, 500, 502, 503, 504)
     * @param bool $redirect Whether to redirect or include directly
     * @return void
     */
    function iso_error(int $code = 404, bool $redirect = true): void
    {
        // Valid error codes
        $valid_codes = [400, 401, 403, 404, 405, 408, 500, 502, 503, 504];
        
        // Default to 404 if invalid code
        if (!in_array($code, $valid_codes)) {
            $code = 404;
        }
        
        if ($redirect) {
            // Redirect to error page
            header("Location: /isotone/server/error.php?code={$code}");
            exit;
        } else {
            // Set the error code in GET for the error page
            $_GET['code'] = $code;
            
            // Include error page directly
            require_once dirname(__DIR__) . '/server/error.php';
            exit;
        }
    }
}

if (!function_exists('iso_abort')) {
    /**
     * Abort execution with error page
     * Alias for iso_error with direct include
     * 
     * @param int $code HTTP error code
     * @return void
     */
    function iso_abort(int $code = 404): void
    {
        iso_error($code, false);
    }
}

if (!function_exists('iso_is_development_mode')) {
    /**
     * Check if development mode is enabled
     * Development mode is enabled when the iso-development directory exists
     *
     * @return bool True if development mode is enabled
     */
    function iso_is_development_mode(): bool
    {
        return is_dir(dirname(__DIR__) . '/iso-development');
    }
}
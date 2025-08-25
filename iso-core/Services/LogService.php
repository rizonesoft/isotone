<?php
/**
 * Log Service
 * 
 * Centralized logging service using Monolog
 * Provides logging capabilities throughout Isotone
 * 
 * @package Isotone
 * @subpackage Services
 */

namespace Isotone\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class LogService
{
    private static ?Logger $instance = null;
    private static string $logPath;
    
    /**
     * Get the logger instance
     */
    public static function get(): Logger
    {
        if (self::$instance === null) {
            self::init();
        }
        
        return self::$instance;
    }
    
    /**
     * Initialize the logger
     */
    private static function init(): void
    {
        self::$logPath = dirname(__DIR__, 2) . '/iso-content/logs';
        
        // Ensure log directory exists
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        self::$instance = new Logger('isotone');
        
        // Custom formatter without stack traces for cleaner logs
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context%\n",
            "Y-m-d H:i:s",
            false,
            true
        );
        
        // Error log - only errors and above
        $errorHandler = new StreamHandler(
            self::$logPath . '/error.log',
            Logger::ERROR
        );
        $errorHandler->setFormatter($formatter);
        self::$instance->pushHandler($errorHandler);
        
        // Daily rotating log - info and above
        $dailyHandler = new RotatingFileHandler(
            self::$logPath . '/isotone.log',
            30, // Keep 30 days
            Logger::INFO
        );
        $dailyHandler->setFormatter($formatter);
        self::$instance->pushHandler($dailyHandler);
        
        // Security log - for authentication and security events
        // We'll create a separate logger for security to avoid filter issues
        // This is handled in the security() method instead
    }
    
    /**
     * Log an info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::get()->info($message, $context);
    }
    
    /**
     * Log a warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::get()->warning($message, $context);
    }
    
    /**
     * Log an error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::get()->error($message, $context);
    }
    
    /**
     * Log a critical message
     */
    public static function critical(string $message, array $context = []): void
    {
        self::get()->critical($message, $context);
    }
    
    /**
     * Log a debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        self::get()->debug($message, $context);
    }
    
    /**
     * Log a security event
     */
    public static function security(string $message, array $context = []): void
    {
        // Write directly to security log
        $securityLog = self::$logPath . '/security.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logLine = "[$timestamp] isotone.SECURITY: $message $contextStr\n";
        
        file_put_contents($securityLog, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log a database query (for debugging)
     */
    public static function query(string $sql, array $params = []): void
    {
        if (defined('ISO_DEBUG') && ISO_DEBUG) {
            self::debug('Database Query', [
                'sql' => $sql,
                'params' => $params
            ]);
        }
    }
    
    /**
     * Get the log file path
     */
    public static function getLogPath(): string
    {
        return self::$logPath;
    }
    
    /**
     * Clear old log files
     */
    public static function cleanOldLogs(int $daysToKeep = 30): void
    {
        $files = glob(self::$logPath . '/*.log*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $daysToKeep) {
                    unlink($file);
                }
            }
        }
        
        self::info('Cleaned old log files', ['days_kept' => $daysToKeep]);
    }
}
<?php

/**
 * Simple autoloader for VOsaka HTTP library
 * This allows testing without running composer install
 */

spl_autoload_register(function ($class) {
    // Define the base directories for autoloading
    $baseDirs = [
        __DIR__ . '/src/',
        __DIR__ . '/vendor/venndev/v-osaka/src/',
        __DIR__ . '/vendor/psr/http-message/src/',
    ];

    // Convert namespace separators to directory separators
    $relativeClass = str_replace('\\', '/', $class);

    // Try each base directory
    foreach ($baseDirs as $baseDir) {
        $file = $baseDir . $relativeClass . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Special handling for PSR classes that might be in different locations
    $psrMappings = [
        'Psr\\Http\\Message\\' => __DIR__ . '/vendor/psr/http-message/src/',
        'venndev\\vosaka\\' => __DIR__ . '/vendor/venndev/v-osaka/src/venndev/vosaka/',
        'vosaka\\http\\' => __DIR__ . '/src/vosaka/http/',
    ];

    foreach ($psrMappings as $prefix => $baseDir) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Load composer autoloader if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

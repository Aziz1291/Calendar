<?php
/**
 * Simple PSR-4 autoloader for the Calendar namespace
 */
spl_autoload_register(function ($class) {
    $prefix = 'Calendar\\';
    $base_dir = __DIR__ . '/src/Calendar/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Not a Calendar class
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

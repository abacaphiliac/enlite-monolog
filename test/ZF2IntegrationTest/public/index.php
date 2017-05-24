<?php
{
    chdir(dirname(__DIR__));

    // Decline static file requests back to the PHP built-in web-server
    if (PHP_SAPI === 'cli-server') {
        $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (__FILE__ !== $path && is_file($path)) {
            return false;
        }
        
        unset($path);
    }
    
    require __DIR__ . '/../vendor/autoload.php';

    // Retrieve configuration
    $config = require __DIR__ . '/../config/application.config.php';
    
    \Zend\Mvc\Application::init($config)->run();
}

<?php

spl_autoload_register(function ($className) {
    $prefix = 'AthosFeed\\';
    if (strpos($className, $prefix) !== 0) {
        return;
    }

    $path = __DIR__ . '/' . str_replace('\\', '/', substr($className, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

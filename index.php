<?php

/**
 * Laravel - Root Router for PHP Built-in Server
 *
 * Serves static files from public/ with proper MIME types
 * and routes all other requests through Laravel.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
$publicPath = __DIR__ . '/public' . $uri;

// Serve static files from public directory with proper MIME types
if ($uri !== '/' && is_file($publicPath)) {
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
    ];
    
    $ext = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    
    readfile($publicPath);
    return;
}

// Route all other requests through Laravel
require __DIR__ . '/public/index.php';

<?php
/**
 * PHP 内置服务器路由文件
 * 使用方式: php -S localhost:8080 router.php
 */

// 获取请求URI，处理可能的null值
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH);
$uri = $parsedPath ? urldecode($parsedPath) : '/';

// 静态文件扩展名列表
$staticExtensions = ['html', 'htm', 'css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot'];
$fileExtension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

// 如果是静态文件，直接返回
if (in_array($fileExtension, $staticExtensions)) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath) && is_file($filePath)) {
        return false; // 让 PHP 内置服务器处理
    } else {
        // 文件不存在，返回404
        http_response_code(404);
        echo "File not found: " . $uri;
        return true;
    }
}

// API 请求通过 index.php 处理
require_once __DIR__ . '/index.php';


<?php
// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 设置 CORS 头部（在应用运行前）
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, token, Token');
header('Access-Control-Max-Age: 86400');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// ===== 特殊处理：api/admin 目录下的独立 PHP 接口文件 =====
// 检查是否是 /api/admin/* 的请求
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

if (preg_match('#^/api/admin/(.+)$#', $requestPath, $matches)) {
    $adminPath = $matches[1];
    // 如果没有 .php 后缀，自动添加
    if (!str_ends_with($adminPath, '.php')) {
        $adminPath .= '.php';
    }
    
    $phpFile = __DIR__ . '/api/admin/' . $adminPath;
    
    // 如果文件存在，直接执行
    if (file_exists($phpFile) && is_file($phpFile)) {
        // 直接 require，这些文件在全局命名空间下
        require $phpFile;
        exit();
    }
}

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);

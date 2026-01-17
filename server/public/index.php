<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// ============ CORS 跨域处理（在所有逻辑之前） ============
// 获取请求来源
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// 只要有 Origin 就设置跨域头
if ($origin) {
    header("Access-Control-Allow-Origin: {$origin}");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Token, token, X-Token, X-CSRF-TOKEN, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, Cache-Control, Pragma");
    header("Access-Control-Expose-Headers: Authorization, Token, Content-Disposition");
    header("Access-Control-Max-Age: 86400");
}

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}
// ============ CORS 处理结束 ============

// ============ 静态资源保护（PHP 内置服务器） ============
// 如果请求的是静态文件且文件存在，直接返回
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if ($requestUri && preg_match('/\.(jpg|jpeg|png|gif|webp|css|js|ico|svg|woff|woff2|ttf|eot)$/i', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath) && is_file($filePath)) {
        // 设置正确的 Content-Type
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'css' => 'text/css',
            'js' => 'application/javascript', 'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml', 'woff' => 'font/woff', 'woff2' => 'font/woff2',
            'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject'
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($filePath);
        exit;
    }
}

// 引入自动加载文件
require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new \think\App())->http;

$response = $http->run();

$response->send();

$http->end($response);

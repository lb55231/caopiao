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

// ===== 特殊处理：api 目录下的独立 PHP 接口文件 =====
// 检查是否是 /api/* 的请求
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

if (preg_match('#^/api/(.+)$#', $requestPath, $matches)) {
    $apiPath = $matches[1];
    
    // 如果没有 .php 后缀，自动添加
    if (!str_ends_with($apiPath, '.php')) {
        // 将路径转换为文件名（文件名使用下划线命名）
        // 1. 斜杠 / 转为下划线 _
        // 2. 连字符 - 转为下划线 _
        // 3. 移除路径末尾的数字ID（通常是 /数字 格式）
        // 4. 移除路径末尾的 undefined、null 等无效参数
        // 例如：admin/lottery/type/update/137 -> admin/lottery_type_update.php
        // 例如：admin/member/change_balance/undefined -> admin/member_change_balance.php
        
        // 移除路径末尾的无效参数（数字ID、undefined、null）
        $apiPath = preg_replace('#/(undefined|null|\d+)$#', '', $apiPath);
        
        // 按斜杠分割路径
        $parts = explode('/', $apiPath);
        
        // 如果是 admin 目录，第一部分是 admin，后面的部分合并并转换
        if (count($parts) > 1 && $parts[0] === 'admin') {
            // admin 目录下的文件：admin/xxx/yyy -> admin/xxx_yyy.php
            $firstPart = array_shift($parts); // 取出 'admin'
            $restPath = implode('/', $parts); // 剩余路径
            $restPath = str_replace(['/', '-'], '_', $restPath); // 转换为下划线
            $apiPath = $firstPart . '/' . $restPath . '.php';
        } else {
            // 其他文件：upload/image -> upload_image.php
            $apiPath = str_replace(['/', '-'], '_', $apiPath) . '.php';
        }
    }
    
    $phpFile = __DIR__ . '/api/' . $apiPath;
    
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

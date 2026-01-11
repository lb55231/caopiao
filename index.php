<?php
/**
 * 统一入口 - 8000端口
 * 根据URL路径分发到：API服务、后台管理、前台用户端
 */

// 获取请求URI
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// 1. API请求 - 转到API服务
if (strpos($requestPath, '/api/') === 0) {
    $_SERVER['SCRIPT_NAME'] = '/api/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/server/laoxiangmu/public/index.php';
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/server/laoxiangmu/public';
    
    chdir(__DIR__ . '/server/laoxiangmu/public');
    require __DIR__ . '/server/laoxiangmu/public/index.php';
    exit;
}

// 2. 后台管理请求 - 转到admin目录
// 后台路径：/Admincenter, /admin, /Kjapi, /Home 等
if (
    strpos($requestPath, '/Admincenter') === 0 ||
    strpos($requestPath, '/admin') === 0 ||
    strpos($requestPath, '/Kjapi') === 0 ||
    strpos($requestPath, '/Home') === 0 ||
    strpos($requestPath, '/Public') === 0 && strpos($requestUri, 'admin') !== false
) {
    $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/admin/index.php';
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/admin';
    
    chdir(__DIR__ . '/admin');
    require __DIR__ . '/admin/index.php';
    exit;
}

// 3. 前台用户端请求 - 转到wap.com目录
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/wap.com/index.php';
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/wap.com';

chdir(__DIR__ . '/wap.com');
require __DIR__ . '/wap.com/index.php';

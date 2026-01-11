<?php
/**
 * ThinkPHP 入口文件
 * 
 * 用于替代 public/index.php，使用ThinkPHP的MVC架构
 * 
 * 使用方式：
 * 1. 启动内置服务器：php think run -p 8000
 * 2. 或配置Nginx/Apache指向此文件
 */

namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);


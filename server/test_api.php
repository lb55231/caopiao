<?php
/**
 * ThinkPHP接口测试脚本
 * 
 * 使用方法：
 * cd server
 * php test_api.php
 */

// API基础地址
$baseUrl = 'http://127.0.0.1:8000/api';

// 测试结果
$results = [];

/**
 * 发送HTTP请求
 */
function request($method, $url, $data = [], $token = '')
{
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

/**
 * 测试接口
 */
function test($name, $method, $url, $data = [], $token = '')
{
    global $baseUrl, $results;
    
    echo "\n测试：{$name}\n";
    echo "请求：{$method} {$url}\n";
    
    $response = request($method, $baseUrl . $url, $data, $token);
    
    $success = $response['code'] === 200 && isset($response['body']['code']);
    $results[] = [
        'name' => $name,
        'success' => $success,
        'response' => $response
    ];
    
    if ($success) {
        echo "✅ 成功：" . json_encode($response['body'], JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "❌ 失败：HTTP {$response['code']}\n";
        if (isset($response['body'])) {
            echo json_encode($response['body'], JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    
    return $response['body'] ?? null;
}

echo "==================== ThinkPHP API 测试 ====================\n";

// 1. 测试系统配置（公开接口）
test('获取系统配置', 'GET', '/system/config');

// 2. 测试彩票列表（公开接口）
test('获取彩票列表', 'GET', '/lottery/list');

// 3. 测试用户注册
$registerData = [
    'username' => 'test_' . time(),
    'password' => '123456'
];
$registerResult = test('用户注册', 'POST', '/user/register', $registerData);

// 4. 测试用户登录
$loginData = [
    'username' => $registerData['username'],
    'password' => $registerData['password']
];
$loginResult = test('用户登录', 'POST', '/user/login', $loginData);

// 获取token
$token = $loginResult['data']['token'] ?? '';

if ($token) {
    echo "\n========== 以下为需要认证的接口测试 ==========\n";
    
    // 5. 测试获取用户信息
    test('获取用户信息', 'GET', '/user/info', [], $token);
    
    // 6. 测试更新用户资料
    $profileData = [
        'realname' => '测试用户',
        'tel' => '13800138000',
        'qq' => '123456789'
    ];
    test('更新用户资料', 'POST', '/user/profile', $profileData, $token);
    
    // 7. 测试获取账变记录
    test('获取账变记录', 'GET', '/user/account_records?page=1&page_size=10', [], $token);
    
    // 8. 测试银行卡列表
    test('获取银行卡列表', 'GET', '/user/banklist', [], $token);
    
    // 9. 测试投注记录
    test('获取投注记录', 'GET', '/game/records?page=1&page_size=10', [], $token);
    
} else {
    echo "\n❌ 未获取到token，跳过认证接口测试\n";
}

// 输出测试结果统计
echo "\n==================== 测试结果统计 ====================\n";
$total = count($results);
$success = count(array_filter($results, function($r) { return $r['success']; }));
$failed = $total - $success;

echo "总计：{$total} 个接口\n";
echo "✅ 成功：{$success} 个\n";
echo "❌ 失败：{$failed} 个\n";

if ($failed > 0) {
    echo "\n失败的接口：\n";
    foreach ($results as $result) {
        if (!$result['success']) {
            echo "- {$result['name']}\n";
        }
    }
}

echo "\n==================== 测试完成 ====================\n";


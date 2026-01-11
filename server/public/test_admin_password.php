<?php
/**
 * 测试管理员密码加密
 */
require_once __DIR__ . '/common/Database.php';
require_once __DIR__ . '/common/Encrypt.php';

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 查询管理员信息
    $stmt = $pdo->prepare("SELECT id, username, password, safecode FROM {$prefix}adminmember LIMIT 5");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== 数据库中的管理员信息 ===" . PHP_EOL . PHP_EOL;
    
    foreach ($admins as $admin) {
        echo "ID: " . $admin['id'] . PHP_EOL;
        echo "用户名: " . $admin['username'] . PHP_EOL;
        echo "密码(存储): " . $admin['password'] . PHP_EOL;
        echo "密码长度: " . strlen($admin['password']) . PHP_EOL;
        echo "安全码: " . $admin['safecode'] . PHP_EOL;
        echo str_repeat('-', 50) . PHP_EOL;
    }
    
    echo PHP_EOL . "=== 测试密码加密 ===" . PHP_EOL . PHP_EOL;
    
    $testPassword = 'admin123';
    
    // MD5加密
    $md5 = md5($testPassword);
    echo "MD5('$testPassword'): $md5" . PHP_EOL;
    echo "MD5长度: " . strlen($md5) . PHP_EOL . PHP_EOL;
    
    // encrypt加密（默认key）
    $encrypted = Encrypt::encrypt($testPassword);
    echo "Encrypt('$testPassword'): $encrypted" . PHP_EOL;
    echo "Encrypt长度: " . strlen($encrypted) . PHP_EOL . PHP_EOL;
    
    // 测试验证
    if (!empty($admins)) {
        $firstAdmin = $admins[0];
        echo "=== 验证第一个管理员密码 ===" . PHP_EOL;
        echo "用户名: " . $firstAdmin['username'] . PHP_EOL;
        echo "存储的密码: " . $firstAdmin['password'] . PHP_EOL . PHP_EOL;
        
        // 尝试MD5
        $isMD5 = ($md5 === $firstAdmin['password']);
        echo "MD5验证: " . ($isMD5 ? '✓ 通过' : '✗ 失败') . PHP_EOL;
        
        // 尝试Encrypt
        $isEncrypt = Encrypt::verifyPassword($testPassword, $firstAdmin['password']);
        echo "Encrypt验证: " . ($isEncrypt ? '✓ 通过' : '✗ 失败') . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . PHP_EOL;
}


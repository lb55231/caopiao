<?php
/**
 * 测试解密管理员密码
 */
require_once __DIR__ . '/common/Database.php';
require_once __DIR__ . '/common/Encrypt.php';

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 查询第一个管理员
    $stmt = $pdo->prepare("SELECT id, username, password, safecode FROM {$prefix}adminmember WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "用户名: " . $admin['username'] . PHP_EOL;
        echo "存储的密码: " . $admin['password'] . PHP_EOL;
        echo "安全码: " . $admin['safecode'] . PHP_EOL . PHP_EOL;
        
        // 尝试解密
        echo "=== 尝试解密 ===" . PHP_EOL;
        $decrypted = Encrypt::decrypt($admin['password']);
        echo "解密结果: " . $decrypted . PHP_EOL . PHP_EOL;
        
        // 测试常见密码
        $testPasswords = ['admin', 'admin123', '123456', '888888', 'admin888', 'password'];
        
        echo "=== 测试常见密码 ===" . PHP_EOL;
        foreach ($testPasswords as $pass) {
            $encrypted = Encrypt::encrypt($pass);
            $match = ($encrypted === $admin['password']);
            echo "$pass => " . ($match ? '✓ 匹配' : '✗ 不匹配') . " (加密: $encrypted)" . PHP_EOL;
        }
    } else {
        echo "未找到admin用户" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . PHP_EOL;
}


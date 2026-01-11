<?php
/**
 * 更新测试管理员的安全码为整数
 */
require_once __DIR__ . '/common/Database.php';

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 更新safecode为整数
    $updateStmt = $pdo->prepare("
        UPDATE {$prefix}adminmember 
        SET safecode = 123456
        WHERE username = 'testadmin'
    ");
    $updateStmt->execute();
    
    echo "安全码已更新为整数 123456" . PHP_EOL;
    
    // 验证
    $checkStmt = $pdo->prepare("SELECT username, safecode FROM {$prefix}adminmember WHERE username = 'testadmin'");
    $checkStmt->execute();
    $admin = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "用户名: " . $admin['username'] . PHP_EOL;
    echo "安全码: " . $admin['safecode'] . " (类型: " . gettype($admin['safecode']) . ")" . PHP_EOL;
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . PHP_EOL;
}


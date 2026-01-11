<?php
/**
 * 创建测试管理员账户
 */
require_once __DIR__ . '/common/Database.php';
require_once __DIR__ . '/common/Encrypt.php';

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $username = 'testadmin';
    $password = 'admin123';
    $safecode = '123456';
    
    // 使用encrypt加密密码
    $encryptedPassword = Encrypt::encrypt($password);
    
    echo "创建测试管理员账户" . PHP_EOL;
    echo "用户名: $username" . PHP_EOL;
    echo "密码: $password" . PHP_EOL;
    echo "安全码: $safecode" . PHP_EOL;
    echo "加密后的密码: $encryptedPassword" . PHP_EOL . PHP_EOL;
    
    // 检查是否已存在
    $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}adminmember WHERE username = :username");
    $checkStmt->execute([':username' => $username]);
    
    if ($checkStmt->fetch()) {
        echo "用户已存在，更新密码..." . PHP_EOL;
        $updateStmt = $pdo->prepare("
            UPDATE {$prefix}adminmember 
            SET password = :password, safecode = :safecode 
            WHERE username = :username
        ");
        $updateStmt->execute([
            ':password' => $encryptedPassword,
            ':safecode' => $safecode,
            ':username' => $username
        ]);
        echo "密码更新成功！" . PHP_EOL;
    } else {
        echo "创建新用户..." . PHP_EOL;
        $insertStmt = $pdo->prepare("
            INSERT INTO {$prefix}adminmember 
            (username, email, password, safecode, groupid, islock, logintime, loginip) 
            VALUES 
            (:username, '', :password, :safecode, 1, 0, 0, '')
        ");
        $insertStmt->execute([
            ':username' => $username,
            ':password' => $encryptedPassword,
            ':safecode' => $safecode
        ]);
        echo "用户创建成功！" . PHP_EOL;
    }
    
    echo PHP_EOL . "现在可以使用以下信息登录：" . PHP_EOL;
    echo "用户名: $username" . PHP_EOL;
    echo "密码: $password" . PHP_EOL;
    echo "安全码: $safecode" . PHP_EOL;
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . PHP_EOL;
}


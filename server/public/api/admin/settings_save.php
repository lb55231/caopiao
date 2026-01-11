<?php
/**
 * 系统设置 - 保存配置
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Database::error('请使用POST请求', 405);
}

// 验证Token
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

$jwt = new Jwt();
$adminInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$adminInfo) {
    Database::error('Token无效或已过期', 401);
}

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input)) {
    Database::error('请提供配置数据');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 保存每个配置项
    foreach ($input as $name => $value) {
        // 检查配置是否存在
        $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}setting WHERE name = :name");
        $checkStmt->execute([':name' => $name]);
        
        if ($checkStmt->fetch()) {
            // 更新
            $updateStmt = $pdo->prepare("UPDATE {$prefix}setting SET value = :value WHERE name = :name");
            $updateStmt->execute([
                ':value' => $value,
                ':name' => $name
            ]);
        } else {
            // 插入
            $insertStmt = $pdo->prepare("INSERT INTO {$prefix}setting (name, value) VALUES (:name, :value)");
            $insertStmt->execute([
                ':name' => $name,
                ':value' => $value
            ]);
        }
    }
    
    Database::success('保存成功');
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


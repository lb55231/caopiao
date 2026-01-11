<?php
/**
 * 开奖管理 - 删除开奖记录
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

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

// 获取参数
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? '';

if (empty($id)) {
    Database::error('缺少ID参数');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查是否已结算
    $checkStmt = $pdo->prepare("SELECT isdraw, expect, name FROM {$prefix}kaijiang WHERE id = :id");
    $checkStmt->execute([':id' => $id]);
    $record = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        Database::error('记录不存在');
    }
    
    if ($record['isdraw'] == 1) {
        Database::error('该期已结算，不能删除。请先处理相关投注记录');
    }
    
    // 删除记录
    $stmt = $pdo->prepare("DELETE FROM {$prefix}kaijiang WHERE id = :id");
    $result = $stmt->execute([':id' => $id]);
    
    if ($result) {
        Database::success('删除成功');
    } else {
        Database::error('删除失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


<?php
/**
 * 彩种管理 - 删除接口
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// 只接受DELETE请求
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    Database::error('请使用DELETE请求', 405);
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

// 获取DELETE数据
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['id'])) {
    Database::error('缺少ID参数');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查彩种是否存在
    $checkStmt = $pdo->prepare("SELECT id, name FROM {$prefix}caipiao WHERE id = :id");
    $checkStmt->execute([':id' => $input['id']]);
    $lottery = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lottery) {
        Database::error('彩种不存在或已删除');
    }
    
    // 删除彩种
    $stmt = $pdo->prepare("DELETE FROM {$prefix}caipiao WHERE id = :id");
    $result = $stmt->execute([':id' => $input['id']]);
    
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


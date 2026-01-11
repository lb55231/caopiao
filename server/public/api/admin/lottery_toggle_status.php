<?php
/**
 * 彩种管理 - 切换状态接口
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

if (empty($input['id']) || empty($input['field'])) {
    Database::error('缺少必要参数');
}

// 验证字段名
$allowedFields = ['isopen', 'iswh'];
if (!in_array($input['field'], $allowedFields)) {
    Database::error('非法操作');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $field = $input['field'];
    
    // 查询当前状态
    $checkStmt = $pdo->prepare("SELECT id, {$field} FROM {$prefix}caipiao WHERE id = :id");
    $checkStmt->execute([':id' => $input['id']]);
    $lottery = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lottery) {
        Database::error('彩种不存在');
    }
    
    // 切换状态
    $newStatus = $lottery[$field] == 1 ? 0 : 1;
    
    $stmt = $pdo->prepare("UPDATE {$prefix}caipiao SET {$field} = :status WHERE id = :id");
    $result = $stmt->execute([
        ':status' => $newStatus,
        ':id' => $input['id']
    ]);
    
    if ($result) {
        Database::success('操作成功', ['newStatus' => $newStatus]);
    } else {
        Database::error('操作失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


<?php
/**
 * 彩种管理 - 保存排序接口
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

if (empty($input['orders']) || !is_array($input['orders'])) {
    Database::error('缺少排序数据');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $pdo->beginTransaction();
    
    foreach ($input['orders'] as $id => $order) {
        $stmt = $pdo->prepare("
            UPDATE {$prefix}caipiao 
            SET listorder = :listorder 
            WHERE id = :id
        ");
        $stmt->execute([
            ':listorder' => (int)$order,
            ':id' => (int)$id
        ]);
    }
    
    $pdo->commit();
    
    Database::success('排序保存成功');
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    Database::error('操作失败：' . $e->getMessage());
}


<?php
/**
 * 充值记录 - 删除接口
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    Database::error('请使用DELETE请求', 405);
}

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

$jwt = new Jwt();
$adminInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$adminInfo) {
    Database::error('Token无效或已过期', 401);
}

$id = $_GET['id'] ?? '';
if (empty($id)) {
    Database::error('缺少ID参数');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $stmt = $pdo->prepare("DELETE FROM {$prefix}recharge WHERE id = :id");
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


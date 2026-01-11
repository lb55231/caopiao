<?php
/**
 * 用户设置默认银行卡
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Database::error('请使用POST请求', 405);
}

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

$jwt = new Jwt();
$userInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$userInfo) {
    Database::error('Token无效或已过期', 401);
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['id'])) {
    Database::error('缺少ID参数');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查银行卡是否属于当前用户
    $checkStmt = $pdo->prepare("SELECT * FROM {$prefix}banklist WHERE id = :id AND uid = :uid AND state = 1");
    $checkStmt->execute([':id' => $input['id'], ':uid' => $userInfo['id']]);
    $bank = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bank) {
        Database::error('银行卡不存在');
    }
    
    $pdo->beginTransaction();
    
    // 取消其他卡的默认状态
    $pdo->prepare("UPDATE {$prefix}banklist SET isdefault = 0 WHERE uid = :uid")
        ->execute([':uid' => $userInfo['id']]);
    
    // 设置为默认
    $pdo->prepare("UPDATE {$prefix}banklist SET isdefault = 1 WHERE id = :id")
        ->execute([':id' => $input['id']]);
    
    $pdo->commit();
    
    Database::success('设置成功');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    Database::error('操作失败：' . $e->getMessage());
}


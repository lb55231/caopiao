<?php
/**
 * 用户删除银行卡
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
$userInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$userInfo) {
    Database::error('Token无效或已过期', 401);
}

$id = $_GET['id'] ?? '';
if (empty($id)) {
    Database::error('缺少ID参数');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查银行卡是否属于当前用户
    $checkStmt = $pdo->prepare("SELECT * FROM {$prefix}banklist WHERE id = :id AND uid = :uid");
    $checkStmt->execute([':id' => $id, ':uid' => $userInfo['id']]);
    $bank = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bank) {
        Database::error('银行卡不存在');
    }
    
    // 删除银行卡(软删除)
    $deleteStmt = $pdo->prepare("UPDATE {$prefix}banklist SET state = 0, deleted_at = :time WHERE id = :id");
    $result = $deleteStmt->execute([':id' => $id, ':time' => time()]);
    
    if ($result) {
        // 如果删除的是默认卡，将第一张卡设为默认
        if ($bank['isdefault'] == 1) {
            $pdo->prepare("UPDATE {$prefix}banklist SET isdefault = 1 WHERE uid = :uid AND state = 1 ORDER BY id ASC LIMIT 1")
                ->execute([':uid' => $userInfo['id']]);
        }
        
        Database::success('删除成功');
    } else {
        Database::error('删除失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


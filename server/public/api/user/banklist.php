<?php
/**
 * 用户获取银行卡列表
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Database::error('请使用GET请求', 405);
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

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $stmt = $pdo->prepare("
        SELECT id, bankname, accountname, banknumber, bankbranch, bankaddress, isdefault, state
        FROM {$prefix}banklist
        WHERE uid = :uid AND state = 1
        ORDER BY isdefault DESC, id DESC
    ");
    $stmt->execute([':uid' => $userInfo['id']]);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Database::success('查询成功', ['list' => $list]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


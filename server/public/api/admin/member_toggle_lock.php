<?php
/**
 * 切换会员锁定状态
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    Database::error('请使用PUT请求', 405);
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

// 从URL获取ID
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
preg_match('/\/admin\/member\/toggle_lock\/(\d+)/', $path, $matches);
$id = $matches[1] ?? 0;

if (empty($id)) {
    Database::error('缺少ID参数');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 获取当前状态
    $stmt = $pdo->prepare("SELECT islock FROM {$prefix}member WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        Database::error('会员不存在');
    }
    
    $newStatus = $member['islock'] == 0 ? 1 : 0;
    
    $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET islock = :islock WHERE id = :id");
    $result = $updateStmt->execute([':islock' => $newStatus, ':id' => $id]);
    
    if ($result) {
        Database::success('更新成功');
    } else {
        Database::error('更新失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


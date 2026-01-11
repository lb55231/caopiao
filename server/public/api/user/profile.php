<?php
/**
 * 获取用户资料
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
    
    $stmt = $pdo->prepare("SELECT userbankname as realname, phone, email, qq FROM {$prefix}member WHERE id = :uid");
    $stmt->execute([':uid' => $userInfo['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        Database::error('用户不存在');
    }
    
    // 添加wechat字段（如果没有可以留空）
    $user['wechat'] = '';
    
    Database::success('获取成功', $user);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


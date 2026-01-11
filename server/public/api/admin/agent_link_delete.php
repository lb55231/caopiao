<?php
/**
 * 删除代理注册链接
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

// 从URL获取ID
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
preg_match('/\/admin\/agent\/link\/delete\/(\d+)/', $path, $matches);
$id = $matches[1] ?? 0;

if (empty($id)) {
    Database::error('缺少ID参数');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 清空邀请码
    $stmt = $pdo->prepare("UPDATE {$prefix}member SET invite_code = '' WHERE id = :id");
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


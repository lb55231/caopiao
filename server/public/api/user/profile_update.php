<?php
/**
 * 更新用户资料
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
$userInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$userInfo) {
    Database::error('Token无效或已过期', 401);
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $stmt = $pdo->prepare("
        UPDATE {$prefix}member 
        SET userbankname = :realname,
            phone = :phone,
            email = :email,
            qq = :qq
        WHERE id = :uid
    ");
    
    $result = $stmt->execute([
        ':realname' => trim($input['realname'] ?? ''),
        ':phone' => trim($input['phone'] ?? ''),
        ':email' => trim($input['email'] ?? ''),
        ':qq' => trim($input['qq'] ?? ''),
        ':uid' => $userInfo['id']
    ]);
    
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


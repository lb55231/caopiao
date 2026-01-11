<?php
/**
 * 修改密码
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

if (empty($input['oldPassword']) || empty($input['newPassword']) || empty($input['type'])) {
    Database::error('请填写完整信息');
}

$oldPassword = trim($input['oldPassword']);
$newPassword = trim($input['newPassword']);
$type = trim($input['type']); // login 或 trade

// 根据类型验证密码格式
if ($type === 'login') {
    // 登录密码：6-20位
    if (strlen($newPassword) < 6 || strlen($newPassword) > 20) {
        Database::error('新密码长度为6-20位');
    }
} elseif ($type === 'trade') {
    // 支付密码：6位数字
    if (!preg_match('/^\d{6}$/', $newPassword)) {
        Database::error('支付密码必须为6位数字');
    }
} else {
    Database::error('密码类型错误');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 根据类型选择字段
    $passwordField = $type === 'login' ? 'password' : 'tradepassword';
    
    // 获取用户信息
    $stmt = $pdo->prepare("SELECT {$passwordField} FROM {$prefix}member WHERE id = :uid");
    $stmt->execute([':uid' => $userInfo['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        Database::error('用户不存在');
    }
    
    // 验证旧密码
    if (md5($oldPassword) !== $user[$passwordField]) {
        Database::error('旧密码错误');
    }
    
    // 更新密码
    $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET {$passwordField} = :password WHERE id = :uid");
    $result = $updateStmt->execute([
        ':password' => md5($newPassword),
        ':uid' => $userInfo['id']
    ]);
    
    if ($result) {
        $message = $type === 'login' ? '登录密码修改成功' : '支付密码修改成功';
        Database::success($message);
    } else {
        Database::error('密码修改失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


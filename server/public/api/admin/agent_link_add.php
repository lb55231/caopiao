<?php
/**
 * 添加代理注册链接
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
$adminInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$adminInfo) {
    Database::error('Token无效或已过期', 401);
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['username'])) {
    Database::error('请输入代理账号');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查会员是否存在
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE username = :username");
    $stmt->execute([':username' => $input['username']]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        Database::error('会员不存在');
    }
    
    // 生成邀请码
    if (empty($input['invite_code'])) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        $inviteCode = $code;
    } else {
        $inviteCode = strtoupper($input['invite_code']);
    }
    
    // 检查邀请码是否已存在
    $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}member WHERE invite_code = :code");
    $checkStmt->execute([':code' => $inviteCode]);
    if ($checkStmt->fetch()) {
        Database::error('邀请码已存在');
    }
    
    // 更新会员信息，设置为代理并添加邀请码
    $updateStmt = $pdo->prepare("
        UPDATE {$prefix}member 
        SET proxy = 1, invite_code = :code 
        WHERE username = :username
    ");
    $result = $updateStmt->execute([
        ':code' => $inviteCode,
        ':username' => $input['username']
    ]);
    
    if ($result) {
        Database::success('生成成功', [
            'invite_code' => $inviteCode
        ]);
    } else {
        Database::error('生成失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


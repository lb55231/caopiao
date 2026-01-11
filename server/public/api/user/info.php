<?php
/**
 * 获取用户信息API
 */

// 引入JWT工具类
require_once __DIR__ . '/../../common/Jwt.php';

// 获取Token
$token = $_SERVER['HTTP_TOKEN'] ?? '';

if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

// 验证Token
$jwt = new Jwt();
$userInfo = $jwt->verifyToken($token);

if (!$userInfo) {
    Database::error('Token无效或已过期', 401);
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $uid = $userInfo['id'];
    
    // 查询用户详细信息
    $stmt = $pdo->prepare("
        SELECT 
            id,
            username,
            nickname,
            email,
            phone,
            balance,
            point,
            xima,
            fandian,
            groupid,
            islock,
            status_order,
            status_withdraw,
            parentid,
            regtime
        FROM {$prefix}member 
        WHERE id = :uid
    ");
    
    $stmt->execute([':uid' => $uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        Database::error('用户不存在', 404);
    }
    
    // 格式化返回数据
    $userInfo = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'nickname' => $user['nickname'] ?? '',
        'email' => $user['email'] ?? '',
        'mobile' => $user['phone'] ?? '',  // 数据库字段是phone，但返回时用mobile
        'balance' => number_format($user['balance'], 2, '.', ''),
        'point' => number_format($user['point'], 2, '.', ''),
        'xima' => number_format($user['xima'], 2, '.', ''),
        'fandian' => $user['fandian'] ?? '0.00',
        'groupid' => (int)$user['groupid'],
        'islock' => (int)$user['islock'],
        'status_order' => (int)$user['status_order'],
        'status_withdraw' => (int)$user['status_withdraw'],
        'parentid' => (int)$user['parentid'],
        'regtime' => date('Y-m-d H:i:s', $user['regtime'])
    ];
    
    Database::success('获取成功', $userInfo);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}



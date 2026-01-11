<?php
/**
 * 更新会员信息
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
preg_match('/\/admin\/member\/update\/(\d+)/', $path, $matches);
$id = $matches[1] ?? 0;

if (empty($id)) {
    Database::error('缺少ID参数');
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 构建更新SQL
    $updateFields = [
        'groupid = :groupid',
        'proxy = :proxy',
        'fandian = :fandian',
        'userbankname = :userbankname',
        'qq = :qq',
        'tel = :tel',
        'email = :email',
        'invite_code = :invite_code',
        'xinyu = :xinyu',
        'zijin_num = :zijin_num',
        'status_order = :status_order',
        'status_withdraw = :status_withdraw',
        'withdraw_deny_message = :withdraw_deny_message',
        'isnb = :isnb',
        'updatetime = :updatetime'
    ];
    
    $params = [
        ':groupid' => intval($input['groupid'] ?? 0),
        ':proxy' => intval($input['proxy'] ?? 0),
        ':fandian' => floatval($input['fandian'] ?? 0),
        ':userbankname' => $input['userbankname'] ?? '',
        ':qq' => $input['qq'] ?? '',
        ':tel' => $input['tel'] ?? '',
        ':email' => $input['email'] ?? '',
        ':invite_code' => $input['invite_code'] ?? '',
        ':xinyu' => intval($input['xinyu'] ?? 0),
        ':zijin_num' => intval($input['zijin_num'] ?? 0),
        ':status_order' => intval($input['status_order'] ?? 1),
        ':status_withdraw' => intval($input['status_withdraw'] ?? 1),
        ':withdraw_deny_message' => $input['withdraw_deny_message'] ?? '',
        ':isnb' => intval($input['isnb'] ?? 0),
        ':updatetime' => time(),
        ':id' => $id
    ];
    
    // 如果提供了密码，则更新密码
    if (!empty($input['password'])) {
        $updateFields[] = 'password = :password';
        $params[':password'] = md5($input['password']);
    }
    
    // 如果提供了资金密码，则更新资金密码
    if (!empty($input['tradepassword'])) {
        $updateFields[] = 'tradepassword = :tradepassword';
        $params[':tradepassword'] = md5($input['tradepassword']);
    }
    
    $sql = "UPDATE {$prefix}member SET " . implode(', ', $updateFields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($params);
    
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


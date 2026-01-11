<?php
/**
 * 用户添加银行卡
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

if (empty($input['bankname']) || empty($input['accountname']) || empty($input['banknumber']) || empty($input['password'])) {
    Database::error('请填写完整信息');
}

$bankname = trim($input['bankname']);
$accountname = trim($input['accountname']);
$banknumber = trim($input['banknumber']);
$bankbranch = trim($input['bankbranch'] ?? '');
$bankaddress = trim($input['bankaddress'] ?? '');
$bankcode = trim($input['bankcode'] ?? $bankname); // bankcode 默认为 bankname
$password = trim($input['password']);

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 获取用户信息
    $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :uid");
    $userStmt->execute([':uid' => $userInfo['id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        Database::error('用户不存在');
    }
    
    // 验证支付密码
    if (md5($password) !== $user['tradepassword']) {
        Database::error('支付密码错误');
    }
    
    // 检查是否已存在该银行卡
    $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}banklist WHERE uid = :uid AND banknumber = :banknumber");
    $checkStmt->execute([':uid' => $userInfo['id'], ':banknumber' => $banknumber]);
    if ($checkStmt->fetch()) {
        Database::error('该银行卡已绑定');
    }
    
    // 检查银行卡数量限制(最多3张)
    $countStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM {$prefix}banklist WHERE uid = :uid AND state = 1");
    $countStmt->execute([':uid' => $userInfo['id']]);
    $count = $countStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    if ($count >= 3) {
        Database::error('最多只能绑定3张银行卡');
    }
    
    // 如果是第一张卡，设为默认
    $isDefault = ($count == 0) ? 1 : 0;
    
    // 如果有真实姓名要求，检查是否匹配
    if ($user['userbankname'] && $user['userbankname'] != $accountname) {
        Database::error('持卡人姓名与之前绑定姓名不一致');
    }
    
    // 如果用户没有绑定真实姓名，更新用户表
    if (!$user['userbankname']) {
        $updateUserStmt = $pdo->prepare("UPDATE {$prefix}member SET userbankname = :name WHERE id = :uid");
        $updateUserStmt->execute([':name' => $accountname, ':uid' => $userInfo['id']]);
    }
    
    // 插入银行卡
    $insertStmt = $pdo->prepare("
        INSERT INTO {$prefix}banklist 
        (uid, username, bankname, bankcode, accountname, banknumber, bankbranch, bankaddress, 
         isdefault, state, date, created_at)
        VALUES 
        (:uid, :username, :bankname, :bankcode, :accountname, :banknumber, :bankbranch, :bankaddress,
         :isdefault, 1, NOW(), :created_at)
    ");
    
    $result = $insertStmt->execute([
        ':uid' => $userInfo['id'],
        ':username' => $user['username'],
        ':bankname' => $bankname,
        ':bankcode' => $bankcode,
        ':accountname' => $accountname,
        ':banknumber' => $banknumber,
        ':bankbranch' => $bankbranch,
        ':bankaddress' => $bankaddress,
        ':isdefault' => $isDefault,
        ':created_at' => time()
    ]);
    
    if ($result) {
        Database::success('银行卡绑定成功');
    } else {
        Database::error('绑定失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


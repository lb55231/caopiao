<?php
/**
 * 会员余额变动（加减款）
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

if (empty($input['uid']) || empty($input['amount']) || empty($input['type'])) {
    Database::error('参数不完整');
}

$uid = intval($input['uid']);
$amount = floatval($input['amount']);
$type = $input['type']; // 'add' 或 'sub'
$remark = trim($input['remark'] ?? '管理员操作');

if ($amount <= 0) {
    Database::error('金额必须大于0');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $pdo->beginTransaction();
    
    // 获取用户信息
    $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :id FOR UPDATE");
    $userStmt->execute([':id' => $uid]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $pdo->rollBack();
        Database::error('会员不存在');
    }
    
    $oldBalance = floatval($user['balance']);
    
    // 计算新余额
    if ($type === 'add') {
        $newBalance = $oldBalance + $amount;
        $changeAmount = $amount;
        $typename = '人工加款';
    } else {
        if ($oldBalance < $amount) {
            $pdo->rollBack();
            Database::error('余额不足');
        }
        $newBalance = $oldBalance - $amount;
        $changeAmount = -$amount;
        $typename = '人工减款';
    }
    
    // 更新会员余额
    $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = :balance WHERE id = :id");
    $updateStmt->execute([':balance' => $newBalance, ':id' => $uid]);
    
    // 记录账变
    $trano = 'ADM' . date('ymdHis') . rand(1000, 9999);
    $insertStmt = $pdo->prepare("
        INSERT INTO {$prefix}fuddetail 
        (trano, uid, username, type, typename, amount, amountbefor, amountafter, oddtime, remark, status_show, ticket_income_report)
        VALUES 
        (:trano, :uid, :username, :type, :typename, :amount, :amountbefor, :amountafter, :oddtime, :remark, 1, 1)
    ");
    
    $insertStmt->execute([
        ':trano' => $trano,
        ':uid' => $uid,
        ':username' => $user['username'],
        ':type' => $type === 'add' ? 'admin_add' : 'admin_sub',
        ':typename' => $typename,
        ':amount' => $changeAmount,
        ':amountbefor' => $oldBalance,
        ':amountafter' => $newBalance,
        ':oddtime' => time(),
        ':remark' => $remark
    ]);
    
    $pdo->commit();
    
    Database::success('操作成功', [
        'old_balance' => $oldBalance,
        'new_balance' => $newBalance
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    Database::error('操作失败：' . $e->getMessage());
}


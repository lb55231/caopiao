<?php
/**
 * 用户提交提现申请
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

if (empty($input['amount']) || empty($input['bank_id']) || empty($input['password'])) {
    Database::error('请填写完整信息');
}

$amount = floatval($input['amount']);
$bankId = intval($input['bank_id']);
$password = trim($input['password']);

if ($amount <= 0) {
    Database::error('提现金额必须大于0');
}

if ($amount < 100) {
    Database::error('最低提现金额为100元');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $pdo->beginTransaction();
    
    // 获取用户信息
    $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :uid FOR UPDATE");
    $userStmt->execute([':uid' => $userInfo['id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $pdo->rollBack();
        Database::error('用户不存在');
    }
    
    // 验证支付密码
    if (md5($password) !== $user['tradepassword']) {
        $pdo->rollBack();
        Database::error('支付密码错误');
    }
    
    // 检查洗码余额（必须为0才能提现）
    $xima = floatval($user['xima'] ?? 0);
    if ($xima > 0) {
        $pdo->rollBack();
        Database::error('打码不足，洗码余额为0时可以提款。当前洗码余额：' . $xima);
    }
    
    // 检查余额
    if (floatval($user['balance']) < $amount) {
        $pdo->rollBack();
        Database::error('余额不足');
    }
    
    // 获取银行卡信息
    $bankStmt = $pdo->prepare("SELECT * FROM {$prefix}banklist WHERE id = :id AND uid = :uid AND state = 1");
    $bankStmt->execute([':id' => $bankId, ':uid' => $userInfo['id']]);
    $bank = $bankStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bank) {
        $pdo->rollBack();
        Database::error('银行卡不存在');
    }
    
    // 计算手续费(1%)
    $fee = max($amount * 0.01, 1);
    $actualAmount = $amount - $fee;
    
    // 扣除用户余额
    $oldMoney = floatval($user['balance']);
    $newMoney = $oldMoney - $amount;
    
    $updateUserStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = :money WHERE id = :uid");
    $updateUserStmt->execute([
        ':money' => $newMoney,
        ':uid' => $userInfo['id']
    ]);
    
    // 生成订单号
    $trano = 'W' . date('ymdHis') . rand(1000, 9999);
    
    // 插入提现记录
    $insertStmt = $pdo->prepare("
        INSERT INTO {$prefix}withdraw 
        (uid, username, trano, amount, actualamount, fee, oldaccountmoney, newaccountmoney,
         accountname, bankname, bankbranch, banknumber, state, oddtime, ticket_income_report)
        VALUES 
        (:uid, :username, :trano, :amount, :actualamount, :fee, :oldmoney, :newmoney,
         :accountname, :bankname, :bankbranch, :banknumber, 0, :oddtime, 1)
    ");
    
    $result = $insertStmt->execute([
        ':uid' => $userInfo['id'],
        ':username' => $user['username'],
        ':trano' => $trano,
        ':amount' => $amount,
        ':actualamount' => $actualAmount,
        ':fee' => $fee,
        ':oldmoney' => $oldMoney,
        ':newmoney' => $newMoney,
        ':accountname' => $bank['accountname'],
        ':bankname' => $bank['bankname'],
        ':bankbranch' => $bank['bankbranch'] ?: '',
        ':banknumber' => $bank['banknumber'],
        ':oddtime' => time()
    ]);
    
    if ($result) {
        $pdo->commit();
        
        Database::success('提现申请已提交，请等待审核', [
            'id' => $pdo->lastInsertId(),
            'trano' => $trano,
            'amount' => $amount,
            'fee' => $fee,
            'actualAmount' => $actualAmount
        ]);
    } else {
        $pdo->rollBack();
        Database::error('提交失败');
    }
    
} catch (PDOException $e) {
    $pdo->rollBack();
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    Database::error('操作失败：' . $e->getMessage());
}


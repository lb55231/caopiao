<?php
/**
 * 用户提交充值申请
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

if (empty($input['amount']) || empty($input['paytype'])) {
    Database::error('请填写完整信息');
}

$amount = floatval($input['amount']);
$paytype = trim($input['paytype']);
$userpayname = trim($input['userpayname'] ?? ''); // 付款人姓名

if ($amount <= 0) {
    Database::error('充值金额必须大于0');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 获取支付方式配置
    $payStmt = $pdo->prepare("SELECT * FROM {$prefix}payset WHERE paytype = :paytype AND state = 1");
    $payStmt->execute([':paytype' => $paytype]);
    $paySet = $payStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paySet) {
        Database::error('支付方式不存在');
    }
    
    // 检查金额范围
    if ($amount < floatval($paySet['minmoney'])) {
        Database::error('充值金额不能小于' . $paySet['minmoney'] . '元');
    }
    
    if (floatval($paySet['maxmoney']) > 0 && $amount > floatval($paySet['maxmoney'])) {
        Database::error('充值金额不能大于' . $paySet['maxmoney'] . '元');
    }
    
    // 获取用户信息
    $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :uid");
    $userStmt->execute([':uid' => $userInfo['id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        Database::error('用户不存在');
    }
    
    // 生成订单号
    $trano = 'R' . date('ymdHis') . rand(1000, 9999);
    
    // 插入充值记录
    $insertStmt = $pdo->prepare("
        INSERT INTO {$prefix}recharge 
        (uid, username, paytype, paytypetitle, trano, threetrano, amount, fee, actualamount, actualfee,
         oldaccountmoney, newaccountmoney, remark, ticket_income_report, payname, fuyanma, 
         isauto, sdtype, state, oddtime, stateadmin)
        VALUES 
        (:uid, :username, :paytype, :paytypetitle, :trano, :threetrano, :amount, :fee, :actualamount, :actualfee,
         :oldmoney, :newmoney, :remark, :ticket, :payname, :fuyanma, 
         :isauto, :sdtype, :state, :oddtime, :stateadmin)
    ");
    
    $result = $insertStmt->execute([
        ':uid' => $userInfo['id'],
        ':username' => $user['username'],
        ':paytype' => $paytype,
        ':paytypetitle' => $paySet['paytypetitle'],
        ':trano' => $trano,
        ':threetrano' => '', // 第三方订单号（线下支付为空）
        ':amount' => $amount,
        ':fee' => 0,
        ':actualamount' => $amount,
        ':actualfee' => 0,
        ':oldmoney' => floatval($user['balance']),
        ':newmoney' => floatval($user['balance']),
        ':remark' => '',
        ':ticket' => 1,
        ':payname' => $userpayname,
        ':fuyanma' => 0,
        ':isauto' => 2,
        ':sdtype' => 0,
        ':state' => 0,
        ':oddtime' => time(),
        ':stateadmin' => ''
    ]);
    
    if ($result) {
        $rechargeId = $pdo->lastInsertId();
        
        // 解析configs
        $configs = [];
        if (!empty($paySet['configs'])) {
            $configs = unserialize($paySet['configs']);
        }
        
        Database::success('充值申请已提交，请等待审核', [
            'id' => $rechargeId,
            'trano' => $trano,
            'amount' => $amount,
            'paytype' => $paytype,
            'paytypetitle' => $paySet['paytypetitle'],
            'bankname' => $configs['bankname'] ?? '',
            'bankcode' => $configs['bankcode'] ?? '',
            'isewm' => $configs['isewm'] ?? '-1',
            'ewmurl' => $configs['ewmurl'] ?? '',
            'configs' => $configs
        ]);
    } else {
        Database::error('提交失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


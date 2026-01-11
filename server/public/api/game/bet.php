<?php
/**
 * 投注提交API
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
$tokenData = $jwt->verifyToken($token);

if (!$tokenData) {
    Database::error('Token无效或已过期', 401);
}

// 从token中获取用户ID
$userId = $tokenData['id'];

// 获取数据库连接
$pdo = Database::getInstance();
$prefix = Database::getPrefix();

// 获取请求数据
$input = json_decode(file_get_contents('php://input'), true);

// 必需参数
$cpname = $input['cpname'] ?? '';  // 彩票代码
$period = $input['period'] ?? '';  // 期号
$bets = $input['bets'] ?? [];      // 投注列表

// 参数验证
if (!$userId) {
    Database::error('用户未登录');
}

if (!$cpname || !$period) {
    Database::error('参数错误');
}

if (empty($bets)) {
    Database::error('请选择投注内容');
}

try {
    // 开始事务
    $pdo->beginTransaction();
    
    // 查询用户信息
    $stmt = $pdo->prepare("SELECT id, username, balance, xima FROM {$prefix}member WHERE id = :userId FOR UPDATE");
    $stmt->execute([':userId' => $userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $pdo->rollBack();
        Database::error('用户不存在');
    }
    
    // 计算总金额
    $totalAmount = 0;
    foreach ($bets as $bet) {
        // 使用前端传来的price，如果没有则默认2元
        $price = $bet['price'] ?? 2;
        $amount = ($bet['count'] ?? 0) * ($bet['multiple'] ?? 1) * $price;
        $totalAmount += $amount;
    }
    
    // 检查余额
    if ($user['balance'] < $totalAmount) {
        $pdo->rollBack();
        Database::error('余额不足');
    }
    
    // 扣除余额
    $newBalance = $user['balance'] - $totalAmount;
    $stmt = $pdo->prepare("UPDATE {$prefix}member SET balance = :balance WHERE id = :userId");
    $stmt->execute([
        ':balance' => $newBalance,
        ':userId' => $userId
    ]);
    
    // 处理洗码余额（投注时减少洗码）
    $oldXima = floatval($user['xima'] ?? 0);
    $newXima = $oldXima;
    if ($oldXima > 0) {
        // 计算本次投注可以消耗的洗码金额
        $ximaAmount = min($totalAmount, $oldXima);
        $newXima = $oldXima - $ximaAmount;
        
        // 更新洗码余额
        $stmt = $pdo->prepare("UPDATE {$prefix}member SET xima = :xima WHERE id = :userId");
        $stmt->execute([
            ':xima' => $newXima,
            ':userId' => $userId
        ]);
        
        // 记录洗码账变
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}fuddetail (
                uid, username, type, typename, amount, 
                amountbefor, amountafter, remark, oddtime, 
                trano, expect, status_show, ticket_income_report
            ) VALUES (
                :uid, :username, :type, :typename, :amount,
                :amountbefor, :amountafter, :remark, :oddtime,
                :trano, :expect, :status_show, :ticket_income_report
            )
        ");
        
        $stmt->execute([
            ':uid' => $userId,
            ':username' => $user['username'],
            ':type' => 'xima',
            ':typename' => '洗码',
            ':amount' => $ximaAmount,
            ':amountbefor' => $oldXima,
            ':amountafter' => $newXima,
            ':remark' => "投注消耗洗码，彩种：{$cpname}，期号：{$period}",
            ':oddtime' => time(),
            ':trano' => 'XM' . date('ymdHis') . rand(10, 99),  // XM + 12位时间 + 2位随机 = 16位
            ':expect' => $period,
            ':status_show' => 1,
            ':ticket_income_report' => 1
        ]);
    }
    
    // 插入投注记录
    $orderNo = date('YmdHis') . rand(1000, 9999);
    $insertedIds = [];
    
    foreach ($bets as $bet) {
        $price = $bet['price'] ?? 2;
        $betAmount = ($bet['count'] ?? 0) * ($bet['multiple'] ?? 1) * $price;
        $trano = substr(md5(uniqid()), 0, 15) . time();
        
        // 计算玩法赔率（简化版，实际应从玩法配置获取）
        $odds = 2.0; // 默认赔率
        $okamount = $betAmount * $odds;
        
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}touzhu (
                uid, username, cpname, cptitle, expect,
                playid, playtitle, tzcode, itemcount, beishu,
                amount, oddtime, isdraw, source, trano,
                amountbefor, amountafter, okamount, okcount,
                typeid, yjf, mode, repoint, repointamout,
                Chase, stopChase, opencode, is_show
            ) VALUES (
                :uid, :username, :cpname, :cptitle, :expect,
                :playid, :playtitle, :tzcode, :itemcount, :beishu,
                :amount, :oddtime, :isdraw, :source, :trano,
                :amountbefor, :amountafter, :okamount, :okcount,
                :typeid, :yjf, :mode, :repoint, :repointamout,
                :Chase, :stopChase, :opencode, :is_show
            )
        ");
        
        $result = $stmt->execute([
            ':uid' => $userId,
            ':username' => $user['username'],
            ':cpname' => $cpname,
            ':cptitle' => $input['cptitle'] ?? 'K3',
            ':expect' => $period,
            ':playid' => $bet['playId'] ?? '',
            ':playtitle' => $bet['playName'] ?? '',
            ':tzcode' => $bet['numbersText'] ?? '',
            ':itemcount' => $bet['count'] ?? 0,
            ':beishu' => $bet['multiple'] ?? 1,
            ':amount' => $betAmount,
            ':oddtime' => time(),
            ':isdraw' => 0,
            ':source' => 'mobile',
            ':trano' => $trano,
            ':amountbefor' => $user['balance'],
            ':amountafter' => $newBalance,
            ':okamount' => $okamount,
            ':okcount' => 0,
            ':typeid' => 'k3',
            ':yjf' => '1',
            ':mode' => (string)$odds,
            ':repoint' => 0.00,
            ':repointamout' => 0.00,
            ':Chase' => 0,
            ':stopChase' => 0,
            ':opencode' => '',
            ':is_show' => '1'
        ]);
        
        if ($result) {
            $insertedIds[] = $pdo->lastInsertId();
        }
    }
    
    // 提交事务
    $pdo->commit();
    
    Database::success('投注成功', [
        'orderNo' => $orderNo,
        'totalAmount' => $totalAmount,
        'newBalance' => $newBalance,
        'recordIds' => $insertedIds
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    Database::error('投注失败：' . $e->getMessage(), 500);
}


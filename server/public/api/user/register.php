<?php
/**
 * 用户注册接口
 * 完全按照原wap.com项目的注册逻辑
 */

// 获取数据库连接
$pdo = Database::getInstance();
$prefix = Database::getPrefix();

// 获取请求参数
$input = json_decode(file_get_contents('php://input'), true);

// 只获取表单中实际存在的5个字段
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');
$cpassword = trim($input['cpassword'] ?? '');  // 确认密码
$tradepassword = trim($input['tradepassword'] ?? '');  // 提款密码
$reccode = trim($input['reccode'] ?? '');  // 邀请码/推荐码

// ==================== 参数验证 ====================

// 1. 验证用户名（2-12个字符，字母数字或中文）
$_paten = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
$username_len = function_exists('mb_strlen') ? mb_strlen($username, 'utf-8') : strlen($username);
if (!$username || preg_match($_paten, $username) || $username_len < 2 || $username_len > 12) {
    Database::error('用户名为2-12字母与数字或中文的字符!');
}

// 2. 验证密码（6-16位）
if (!$password || !preg_match("/^[\w\W]{6,16}$/", $password)) {
    Database::error('请输入6-16位的密码');
}

// 3. 验证确认密码
if (!$cpassword || !preg_match("/^[\w\W]{6,16}$/", $cpassword)) {
    Database::error('请输入6-16位的重复密码');
}

// 4. 两次密码是否一致
if ($cpassword != $password) {
    Database::error('两次密码输入不一致');
}

// 5. 验证提款密码（4-16位）
if (!$tradepassword || !preg_match("/^[\w\W]{4,16}$/", $tradepassword)) {
    Database::error('请输入4-16位的提款密码');
}

// ==================== 邀请码验证 ====================
// 获取系统设置：是否必须填写邀请码
$needInviteStmt = $pdo->query("SELECT value FROM {$prefix}setting WHERE name = 'needinvitecode'");
$needInviteRow = $needInviteStmt->fetch(PDO::FETCH_ASSOC);
$needInviteCode = intval($needInviteRow['value'] ?? 0);

$parentid = 0;

// 如果开启了"必须邀请码"
if ($needInviteCode == 1) {
    // 必须填写邀请码
    if (empty($reccode)) {
        Database::error('请填写邀请码');
    }
    
    // 邀请码必须是数字
    if (!is_numeric($reccode)) {
        Database::error('邀请码格式错误');
    }
    
    try {
        // 查找邀请码对应的用户
        $stmt = $pdo->prepare("SELECT id, proxy FROM {$prefix}member WHERE invite_code = :reccode LIMIT 1");
        $stmt->execute([':reccode' => $reccode]);
        $parent_user = $stmt->fetch();
        
        if (empty($parent_user)) {
            Database::error('邀请码不存在');
        }
        
        if ($parent_user['proxy'] != 1) {
            Database::error('推荐码无效');
        }
        
        $parentid = $parent_user['id'];
    } catch (PDOException $e) {
        Database::error('推荐码验证失败：' . $e->getMessage(), 500);
    }
} else {
    // 不强制邀请码，按原逻辑（可选填写）
    if (!empty($reccode)) {
        // 只有当邀请码是数字时才验证
        if (is_numeric($reccode)) {
            try {
                // 步骤1：通过 invite_code 查找上级用户
                $stmt = $pdo->prepare("SELECT id, proxy FROM {$prefix}member WHERE invite_code = :reccode LIMIT 1");
                $stmt->execute([':reccode' => $reccode]);
                $parent_user = $stmt->fetch();
                
                if (!empty($parent_user)) {
                    // 步骤2：验证找到的用户是否是代理
                    if ($parent_user['proxy'] != 1) {
                        Database::error('推荐码无效');  // 不是代理，拒绝
                    }
                    // 步骤3：邀请码转化为用户ID
                    $parentid = $parent_user['id'];
                } else {
                    // 邀请码不存在，设置为0（不报错，允许注册）
                    $parentid = 0;
                }
            } catch (PDOException $e) {
                Database::error('推荐码验证失败：' . $e->getMessage(), 500);
            }
        } else {
            // 邀请码不是数字，忽略（不报错）
            $parentid = 0;
        }
    }
}

// ==================== 检查用户名是否存在 ====================
try {
    $stmt = $pdo->prepare("SELECT id FROM {$prefix}member WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    
    if ($stmt->fetch()) {
        Database::error('用户名已被注册');
    }
} catch (PDOException $e) {
    Database::error('用户名验证失败：' . $e->getMessage(), 500);
}

// ==================== 准备注册数据 ====================
try {
    $regTime = time();
    $regIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    // 生成邀请码（8位）
    $inviteCode = substr(md5($username . $regTime), 0, 8);
    
    $insertData = [
        'username' => $username,
        'password' => md5($password),  // MD5加密
        'tradepassword' => md5($tradepassword),  // 提款密码也MD5加密
        'nickname' => $username,
        'groupid' => 2,  // 默认普通会员组
        'parentid' => $parentid,  // 上级ID
        'proxy' => 0,  // 默认不是代理
        'invite_code' => $inviteCode,  // 邀请码
        'isnb' => 0,  // 不是内部账号
        'email' => '',
        'phone' => '',  // 没有手机号输入框，默认空
        'userbankname' => '',
        'sex' => 1,  // 默认男性
        'balance' => 0,
        'point' => 0,
        'xima' => 0,
        'fandian' => 0,
        'tel' => '',  // 没有电话输入框，默认空
        'qq' => '',  // 没有QQ输入框，默认空
        'loginip' => $regIp,
        'regtime' => $regTime,
        'regip' => $regIp,
        'iparea' => '',
        'source' => 'mobile版注册',  // 注册来源
        'logintime' => $regTime,
        'loginsource' => 'mobile',
        'onlinetime' => 0,
        'updatetime' => $regTime,
        'islock' => 0,
        'zijin_num' => '0',
        'mlogin_num' => '0',
        'mzijin_num' => '0',
        'xinyu' => 100,
        'status_order' => 1,
        'status_withdraw' => 1,
        'withdraw_deny_message' => ''
    ];

    // 构建 SQL
    $fields = array_keys($insertData);
    $placeholders = array_map(function($field) { return ":$field"; }, $fields);
    
    $sql = "INSERT INTO {$prefix}member (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    $insertStmt = $pdo->prepare($sql);
    
    // 绑定参数
    foreach ($insertData as $key => $value) {
        $insertStmt->bindValue(":$key", $value);
    }
    
    $insertStmt->execute();
    
    $newUserId = $pdo->lastInsertId();
    
    // ==================== 注册送金额 ====================
    // 获取系统设置的注册送金额
    $bonusStmt = $pdo->query("SELECT value FROM {$prefix}setting WHERE name = 'registerbonus'");
    $bonusRow = $bonusStmt->fetch(PDO::FETCH_ASSOC);
    $registerBonus = floatval($bonusRow['value'] ?? 0);
    
    if ($registerBonus > 0) {
        // 更新用户余额
        $updateBalanceStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = balance + :bonus WHERE id = :uid");
        $updateBalanceStmt->execute([
            ':bonus' => $registerBonus,
            ':uid' => $newUserId
        ]);
        
        // 记录余额账变（添加到资金明细表）
        $trano = date('YmdHis') . rand(100, 999);  // 生成交易单号
        $insertDetailStmt = $pdo->prepare("
            INSERT INTO {$prefix}fuddetail 
            (trano, uid, username, type, typename, amount, amountbefor, amountafter, oddtime, remark, expect, status_show, ticket_income_report) 
            VALUES 
            (:trano, :uid, :username, :type, :typename, :amount, :amountbefor, :amountafter, :oddtime, :remark, :expect, :status_show, :ticket_income_report)
        ");
        $insertDetailStmt->execute([
            ':trano' => $trano,
            ':uid' => $newUserId,
            ':username' => $username,
            ':type' => 'register',  // 类型标识
            ':typename' => '注册赠送',  // 类型名称
            ':amount' => $registerBonus,
            ':amountbefor' => 0,  // 注册前余额为0
            ':amountafter' => $registerBonus,  // 注册后余额
            ':oddtime' => time(),
            ':remark' => '新用户注册赠送',
            ':expect' => '',
            ':status_show' => 1,
            ':ticket_income_report' => 0  // 不计入彩票收入报表
        ]);
        
        // ==================== 注册送金额需要增加洗码（1:5比例）====================
        // 计算洗码金额：注册送金额 × 5
        $ximaAmount = $registerBonus * 5;
        
        // 更新用户洗码余额
        $updateXimaStmt = $pdo->prepare("UPDATE {$prefix}member SET xima = xima + :xima WHERE id = :uid");
        $updateXimaStmt->execute([
            ':xima' => $ximaAmount,
            ':uid' => $newUserId
        ]);
        
        // 记录洗码账变
        $ximaTrano = 'XMREG' . date('ymdHis') . rand(10, 99);
        $insertXimaStmt = $pdo->prepare("
            INSERT INTO {$prefix}fuddetail 
            (trano, uid, username, type, typename, amount, amountbefor, amountafter, oddtime, remark, expect, status_show, ticket_income_report) 
            VALUES 
            (:trano, :uid, :username, :type, :typename, :amount, :amountbefor, :amountafter, :oddtime, :remark, :expect, :status_show, :ticket_income_report)
        ");
        $insertXimaStmt->execute([
            ':trano' => $ximaTrano,
            ':uid' => $newUserId,
            ':username' => $username,
            ':type' => 'xima',
            ':typename' => '洗码',
            ':amount' => $ximaAmount,
            ':amountbefor' => 0,
            ':amountafter' => $ximaAmount,
            ':oddtime' => time(),
            ':remark' => "注册赠送增加洗码额度（1:5比例，赠送{$registerBonus}元需打码{$ximaAmount}元）",
            ':expect' => '',
            ':status_show' => 1,
            ':ticket_income_report' => 1
        ]);
    }
    
    // ==================== 生成登录token ====================
    require_once __DIR__ . '/../../common/Jwt.php';
    
    $jwt = new Jwt();
    $token = $jwt->createToken([
        'userId' => $newUserId,
        'username' => $username
    ]);
    
    // ==================== 返回成功响应（与原项目一致）====================
    Database::success('注册成功', [
        'regisok' => 1,
        'id' => $newUserId,
        'username' => $username,
        'invite_code' => $inviteCode,
        'sign' => true,
        'bonus' => $registerBonus,  // 返回赠送金额
        'token' => $token  // 返回token，前端可以直接登录
    ]);

} catch (PDOException $e) {
    Database::error('注册失败：' . $e->getMessage(), 500);
}

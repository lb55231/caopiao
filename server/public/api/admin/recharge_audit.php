<?php
/**
 * 充值记录 - 审核接口
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

if (empty($input['id'])) {
    Database::error('缺少ID参数');
}

if (!isset($input['state']) || !in_array($input['state'], [1, -1])) {
    Database::error('状态参数错误');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $pdo->beginTransaction();
    
    // 获取记录
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}recharge WHERE id = :id FOR UPDATE");
    $stmt->execute([':id' => $input['id']]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        $pdo->rollBack();
        Database::error('记录不存在');
    }
    
    if ($record['state'] != 0) {
        $pdo->rollBack();
        Database::error('该记录已审核，无法重复操作');
    }
    
    $newState = intval($input['state']);
    $remark = isset($input['remark']) ? trim($input['remark']) : '';
    
    // 更新充值记录状态
    $updateStmt = $pdo->prepare("
        UPDATE {$prefix}recharge 
        SET state = :state,
            stateadmin = :admin,
            remark = :remark
        WHERE id = :id
    ");
    $updateStmt->execute([
        ':state' => $newState,
        ':admin' => $adminInfo['username'] ?? 'admin',
        ':remark' => $remark ?: $record['remark'],
        ':id' => $input['id']
    ]);
    
    // 如果审核通过，增加用户余额
    if ($newState == 1) {
        $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :uid");
        $userStmt->execute([':uid' => $record['uid']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $oldMoney = floatval($user['balance']);
            $newMoney = $oldMoney + floatval($record['amount']);
            
            $updateUserStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = :money WHERE id = :uid");
            $updateUserStmt->execute([
                ':money' => $newMoney,
                ':uid' => $record['uid']
            ]);
            
            // 更新充值记录的账变信息
            $updateAccountStmt = $pdo->prepare("
                UPDATE {$prefix}recharge 
                SET oldaccountmoney = :old,
                    newaccountmoney = :new
                WHERE id = :id
            ");
            $updateAccountStmt->execute([
                ':old' => $oldMoney,
                ':new' => $newMoney,
                ':id' => $input['id']
            ]);
            
            // 处理洗码余额（充值时增加）
            // 获取打码量设置
            $settingStmt = $pdo->prepare("SELECT value FROM {$prefix}setting WHERE name = 'damaliang'");
            $settingStmt->execute();
            $damaliang = floatval($settingStmt->fetchColumn() ?: 0);
            
            if ($damaliang > 0) {
                // 计算洗码金额：充值金额 × 打码量%
                $ximaAmount = ($record['amount'] * $damaliang / 100);
                $ximaAmount = round($ximaAmount, 2);
                
                $oldXima = floatval($user['xima'] ?? 0);
                $newXima = $oldXima + $ximaAmount;
                
                // 更新用户洗码余额
                $updateXimaStmt = $pdo->prepare("UPDATE {$prefix}member SET xima = :xima WHERE id = :uid");
                $updateXimaStmt->execute([
                    ':xima' => $newXima,
                    ':uid' => $record['uid']
                ]);
                
                // 记录洗码账变
                $fudStmt = $pdo->prepare("
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
                
                $fudStmt->execute([
                    ':uid' => $record['uid'],
                    ':username' => $user['username'],
                    ':type' => 'xima',
                    ':typename' => '洗码',
                    ':amount' => $ximaAmount,
                    ':amountbefor' => $oldXima,
                    ':amountafter' => $newXima,
                    ':remark' => "充值增加洗码额度（打码量{$damaliang}%）",
                    ':oddtime' => time(),
                    ':trano' => $record['trano'],
                    ':expect' => '',
                    ':status_show' => 1,
                    ':ticket_income_report' => 1
                ]);
            }
        }
    }
    
    $pdo->commit();
    
    Database::success('审核成功');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    Database::error('操作失败：' . $e->getMessage());
}


<?php
/**
 * 提现记录 - 审核接口
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

if (!isset($input['state']) || !in_array($input['state'], [1, 2])) {
    Database::error('状态参数错误');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $pdo->beginTransaction();
    
    // 获取记录
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}withdraw WHERE id = :id FOR UPDATE");
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
    
    // 更新提现记录状态
    $updateStmt = $pdo->prepare("
        UPDATE {$prefix}withdraw 
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
    
    // 如果退回（state=2），需要退还用户余额
    if ($newState == 2) {
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


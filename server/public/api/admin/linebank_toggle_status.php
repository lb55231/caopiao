<?php
/**
 * 存款方式配置 - 切换状态接口
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Database::error('请使用POST请求', 405);
}

// 验证Token
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

$jwt = new Jwt();
$adminInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$adminInfo) {
    Database::error('Token无效或已过期', 401);
}

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['id'])) {
    Database::error('缺少ID参数');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 获取当前状态
    $stmt = $pdo->prepare("SELECT state FROM {$prefix}linebanklist WHERE id = :id");
    $stmt->execute([':id' => $input['id']]);
    $bank = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bank) {
        Database::error('该存款方式不存在');
    }
    
    // 切换状态
    $newState = $bank['state'] == 1 ? 0 : 1;
    $updateStmt = $pdo->prepare("UPDATE {$prefix}linebanklist SET state = :state WHERE id = :id");
    $result = $updateStmt->execute([
        ':state' => $newState,
        ':id' => $input['id']
    ]);
    
    if ($result) {
        Database::success('状态更新成功', ['state' => $newState]);
    } else {
        Database::error('状态更新失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


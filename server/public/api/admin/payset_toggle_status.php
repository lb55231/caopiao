<?php
/**
 * 存款方式配置 - 切换状态接口
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

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $stmt = $pdo->prepare("SELECT state FROM {$prefix}payset WHERE id = :id");
    $stmt->execute([':id' => $input['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        Database::error('该存款方式不存在');
    }
    
    $newState = $row['state'] == 1 ? -1 : 1;
    $updateStmt = $pdo->prepare("UPDATE {$prefix}payset SET state = :state WHERE id = :id");
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


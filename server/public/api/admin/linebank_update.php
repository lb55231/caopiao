<?php
/**
 * 存款方式配置 - 更新接口
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// 只接受PUT请求
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    Database::error('请使用PUT请求', 405);
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

// 获取PUT数据
$input = json_decode(file_get_contents('php://input'), true);

// 验证必填字段
if (empty($input['id']) || empty($input['bankname']) || empty($input['accountname']) || empty($input['banknumber'])) {
    Database::error('请填写完整信息');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查是否存在
    $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}linebanklist WHERE id = :id");
    $checkStmt->execute([':id' => $input['id']]);
    if (!$checkStmt->fetch()) {
        Database::error('该存款方式不存在');
    }
    
    // 更新数据
    $stmt = $pdo->prepare("
        UPDATE {$prefix}linebanklist 
        SET bankname = :bankname,
            accountname = :accountname,
            banknumber = :banknumber,
            banklogo = :banklogo,
            listorder = :listorder,
            state = :state
        WHERE id = :id
    ");
    
    $result = $stmt->execute([
        ':id' => $input['id'],
        ':bankname' => trim($input['bankname']),
        ':accountname' => trim($input['accountname']),
        ':banknumber' => trim($input['banknumber']),
        ':banklogo' => trim($input['banklogo'] ?? ''),
        ':listorder' => intval($input['listorder'] ?? 0),
        ':state' => intval($input['state'] ?? 1)
    ]);
    
    if ($result) {
        Database::success('更新成功');
    } else {
        Database::error('更新失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


<?php
/**
 * 提款银行 - 添加接口
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

// 验证必填字段
if (empty($input['bankcode']) || empty($input['bankname'])) {
    Database::error('请填写银行代码和银行名称');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查银行代码是否已存在
    $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}sysbank WHERE bankcode = :bankcode");
    $checkStmt->execute([':bankcode' => $input['bankcode']]);
    if ($checkStmt->fetch()) {
        Database::error('该银行代码已存在');
    }
    
    // 插入数据
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}sysbank 
        (bankcode, bankname, banklogo, state, listorder, imgbg)
        VALUES 
        (:bankcode, :bankname, :banklogo, :state, :listorder, :imgbg)
    ");
    
    $result = $stmt->execute([
        ':bankcode' => $input['bankcode'],
        ':bankname' => $input['bankname'],
        ':banklogo' => $input['banklogo'] ?? '',
        ':state' => isset($input['state']) ? intval($input['state']) : 1,
        ':listorder' => isset($input['listorder']) ? intval($input['listorder']) : 0,
        ':imgbg' => $input['imgbg'] ?? ''
    ]);
    
    if ($result) {
        Database::success('添加成功', ['id' => $pdo->lastInsertId()]);
    } else {
        Database::error('添加失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


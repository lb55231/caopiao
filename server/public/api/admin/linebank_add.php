<?php
/**
 * 存款方式配置 - 添加接口
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
if (empty($input['bankname']) || empty($input['accountname']) || empty($input['banknumber'])) {
    Database::error('请填写完整的银行信息');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 插入数据
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}linebanklist 
        (bankname, accountname, banknumber, banklogo, listorder, state)
        VALUES 
        (:bankname, :accountname, :banknumber, :banklogo, :listorder, :state)
    ");
    
    $result = $stmt->execute([
        ':bankname' => trim($input['bankname']),
        ':accountname' => trim($input['accountname']),
        ':banknumber' => trim($input['banknumber']),
        ':banklogo' => trim($input['banklogo'] ?? ''),
        ':listorder' => 0, // 默认值
        ':state' => 1 // 默认启用
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


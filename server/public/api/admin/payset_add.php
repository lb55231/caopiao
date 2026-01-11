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
if (empty($input['paytype']) || empty($input['paytypetitle'])) {
    Database::error('请填写标识和名称');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 处理configs字段（序列化）
    $configs = '';
    if (isset($input['configs']) && is_array($input['configs'])) {
        $configs = serialize($input['configs']);
    }
    
    // 插入数据
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}payset 
        (paytype, paytypetitle, ftitle, minmoney, maxmoney, remark, configs, isonline, listorder, state)
        VALUES 
        (:paytype, :paytypetitle, :ftitle, :minmoney, :maxmoney, :remark, :configs, :isonline, :listorder, :state)
    ");
    
    $result = $stmt->execute([
        ':paytype' => trim($input['paytype']),
        ':paytypetitle' => trim($input['paytypetitle']),
        ':ftitle' => trim($input['ftitle'] ?? ''),
        ':minmoney' => floatval($input['minmoney'] ?? 0),
        ':maxmoney' => floatval($input['maxmoney'] ?? 0),
        ':remark' => trim($input['remark'] ?? ''),
        ':configs' => $configs,
        ':isonline' => intval($input['isonline'] ?? -1),
        ':listorder' => intval($input['listorder'] ?? 0),
        ':state' => intval($input['state'] ?? 1)
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


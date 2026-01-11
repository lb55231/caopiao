<?php
/**
 * 存款方式配置 - 更新接口
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    Database::error('请使用PUT请求', 405);
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

if (empty($input['id']) || empty($input['paytype']) || empty($input['paytypetitle'])) {
    Database::error('请填写完整信息');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $configs = '';
    if (isset($input['configs']) && is_array($input['configs'])) {
        $configs = serialize($input['configs']);
    }
    
    $stmt = $pdo->prepare("
        UPDATE {$prefix}payset 
        SET paytype = :paytype,
            paytypetitle = :paytypetitle,
            ftitle = :ftitle,
            minmoney = :minmoney,
            maxmoney = :maxmoney,
            remark = :remark,
            configs = :configs,
            isonline = :isonline,
            listorder = :listorder,
            state = :state
        WHERE id = :id
    ");
    
    $result = $stmt->execute([
        ':id' => $input['id'],
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
        Database::success('更新成功');
    } else {
        Database::error('更新失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


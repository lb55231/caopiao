<?php
/**
 * 更新会员组
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

// 从URL获取ID
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
preg_match('/\/admin\/membergroup\/update\/(\d+)/', $path, $matches);
$id = $matches[1] ?? 0;

if (empty($id)) {
    Database::error('缺少ID参数');
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $stmt = $pdo->prepare("
        UPDATE {$prefix}membergroup SET
            groupname = :groupname,
            level = :level,
            isagent = :isagent,
            isdefautreg = :isdefautreg,
            groupstatus = :groupstatus,
            listorder = :listorder,
            jjje = :jjje,
            lowest = :lowest,
            highest = :highest,
            fandian = :fandian
        WHERE groupid = :groupid
    ");
    
    $result = $stmt->execute([
        ':groupname' => $input['groupname'],
        ':level' => intval($input['level'] ?? 0),
        ':isagent' => intval($input['isagent'] ?? 0),
        ':isdefautreg' => intval($input['isdefautreg'] ?? 0),
        ':groupstatus' => intval($input['groupstatus'] ?? 1),
        ':listorder' => intval($input['listorder'] ?? 0),
        ':jjje' => floatval($input['jjje'] ?? 0),
        ':lowest' => intval($input['lowest'] ?? 10),
        ':highest' => intval($input['highest'] ?? 50000),
        ':fandian' => $input['fandian'] ?? '0',
        ':groupid' => $id
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


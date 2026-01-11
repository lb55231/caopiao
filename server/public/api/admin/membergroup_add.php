<?php
/**
 * 添加会员组
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

if (empty($input['groupname'])) {
    Database::error('请输入会员组名称');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}membergroup 
        (groupname, level, isagent, isdefautreg, groupstatus, listorder, jjje, lowest, highest, fandian, addtime)
        VALUES 
        (:groupname, :level, :isagent, :isdefautreg, :groupstatus, :listorder, :jjje, :lowest, :highest, :fandian, :addtime)
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
        ':addtime' => time()
    ]);
    
    if ($result) {
        Database::success('添加成功');
    } else {
        Database::error('添加失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


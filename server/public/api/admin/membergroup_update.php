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

// 兼容前端：如果传的是 fandian，转换为 fanshui
if (isset($input['fandian']) && !isset($input['fanshui'])) {
    $input['fanshui'] = $input['fandian'];
    unset($input['fandian']);
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 构建更新字段（只更新实际存在的字段）
    $updateFields = [];
    $updateData = [':groupid' => $id];
    
    // 定义字段映射（前端字段 => 数据库字段）
    $fieldMap = [
        'groupname' => 'groupname',
        'level' => 'level',
        'isagent' => 'isagent',
        'isdefautreg' => 'isdefautreg',
        'groupstatus' => 'groupstatus',
        'listorder' => 'listorder',
        'jjje' => 'jjje',
        'lowest' => 'lowest',
        'highest' => 'highest',
        'fanshui' => 'fanshui'  // 注意：数据库中是 fanshui，不是 fandian
    ];
    
    foreach ($fieldMap as $inputKey => $dbField) {
        if (isset($input[$inputKey])) {
            $updateFields[] = "$dbField = :$dbField";
            
            // 根据字段类型处理值
            switch ($dbField) {
                case 'jjje':
                    $updateData[":$dbField"] = floatval($input[$inputKey]);
                    break;
                case 'level':
                case 'isagent':
                case 'isdefautreg':
                case 'groupstatus':
                case 'listorder':
                case 'lowest':
                case 'highest':
                    $updateData[":$dbField"] = intval($input[$inputKey]);
                    break;
                default:
                    $updateData[":$dbField"] = $input[$inputKey];
            }
        }
    }
    
    if (empty($updateFields)) {
        Database::error('没有要更新的数据');
    }
    
    $sql = "UPDATE {$prefix}membergroup SET " . implode(', ', $updateFields) . " WHERE groupid = :groupid";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($updateData);
    
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


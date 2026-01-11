<?php
/**
 * 切换会员组状态
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
preg_match('/\/admin\/membergroup\/toggle_status\/(\d+)/', $path, $matches);
$id = $matches[1] ?? 0;

if (empty($id)) {
    Database::error('缺少ID参数');
}

$input = json_decode(file_get_contents('php://input'), true);
$status = intval($input['status'] ?? 1);

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $stmt = $pdo->prepare("UPDATE {$prefix}membergroup SET groupstatus = :status WHERE groupid = :groupid");
    $result = $stmt->execute([':status' => $status, ':groupid' => $id]);
    
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


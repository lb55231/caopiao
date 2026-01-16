<?php
/**
 * 删除活动接口
 */
// 引入必要的类文件
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// Database 和 Jwt 类都是全局命名空间的，不需要 use 语句

header('Content-Type: application/json; charset=utf-8');

// 验证Token
$token = $_SERVER['HTTP_TOKEN'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (empty($token)) {
    $token = $_REQUEST['token'] ?? '';
}
if (empty($token)) {
    echo json_encode(['code' => 401, 'msg' => '未提供认证Token', 'data' => null]);
    exit;
}

try {
    $jwt = new Jwt();
    $payload = $jwt->verifyToken($token);
    if (!$payload || !isset($payload['id'])) {
        echo json_encode(['code' => 401, 'msg' => 'Token无效或已过期', 'data' => null]);
        exit;
    }
    // 验证是否为管理员
    if (!isset($payload['type']) || $payload['type'] !== 'admin') {
        echo json_encode(['code' => 403, 'msg' => '权限不足', 'data' => null]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['code' => 401, 'msg' => 'Token验证失败: ' . $e->getMessage(), 'data' => null]);
    exit;
}

// 获取ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['code' => 400, 'msg' => '缺少活动ID', 'data' => null]);
    exit;
}

// 连接数据库
$db = Database::getInstance();
$prefix = 'caopiao_';

try {
    $sql = "DELETE FROM {$prefix}news WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    
    echo json_encode([
        'code' => 200,
        'msg' => '删除成功',
        'data' => null
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'code' => 500,
        'msg' => '删除失败: ' . $e->getMessage(),
        'data' => null
    ]);
}

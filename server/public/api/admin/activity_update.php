<?php
/**
 * 更新活动接口
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

// 获取请求数据（支持 PUT 和 POST）
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

// 如果JSON解析失败，尝试从 POST 获取
if (!$data) {
    $data = $_POST;
}

if (!$data) {
    echo json_encode(['code' => 400, 'msg' => '请求数据格式错误', 'data' => null]);
    exit;
}

// 连接数据库
$db = Database::getInstance();
$prefix = 'caipiao_';

try {
    $updateFields = [];
    $params = [];
    
    if (isset($data['title'])) {
        $updateFields[] = "title = ?";
        $params[] = $data['title'];
    }
    
    if (isset($data['content'])) {
        $updateFields[] = "content = ?";
        $params[] = $data['content'];
    }
    
    if (isset($data['catid'])) {
        $updateFields[] = "catid = ?";
        $params[] = $data['catid'];
    }
    
    if (isset($data['status'])) {
        $updateFields[] = "status = ?";
        $params[] = $data['status'];
    }
    
    if (empty($updateFields)) {
        echo json_encode(['code' => 400, 'msg' => '没有需要更新的字段', 'data' => null]);
        exit;
    }
    
    $params[] = $id;
    $sql = "UPDATE {$prefix}news SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode([
        'code' => 200,
        'msg' => '更新成功',
        'data' => null
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'code' => 500,
        'msg' => '更新失败: ' . $e->getMessage(),
        'data' => null
    ]);
}

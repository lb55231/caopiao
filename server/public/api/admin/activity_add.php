<?php
/**
 * 添加活动接口
 */
// 引入必要的类文件
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// Database 和 Jwt 类都是全局命名空间的，不需要 use 语句

header('Content-Type: application/json; charset=utf-8');

// 验证Token
$token = $_SERVER['HTTP_TOKEN'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
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

// 获取POST数据
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

if (!$data) {
    echo json_encode(['code' => 400, 'msg' => '请求数据格式错误', 'data' => null]);
    exit;
}

// 验证必填字段
$required = ['title', 'content'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['code' => 400, 'msg' => "缺少必填字段: {$field}", 'data' => null]);
        exit;
    }
}

// 连接数据库
$db = Database::getInstance();
$prefix = 'caipiao_';

try {
    $sql = "INSERT INTO {$prefix}news (title, content, catid, status, oddtime) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $data['title'],
        $data['content'],
        $data['catid'] ?? 41, // 默认活动分类
        $data['status'] ?? 1,
        time()
    ]);
    
    echo json_encode([
        'code' => 200,
        'msg' => '添加成功',
        'data' => ['id' => $db->lastInsertId()]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'code' => 500,
        'msg' => '添加失败: ' . $e->getMessage(),
        'data' => null
    ]);
}

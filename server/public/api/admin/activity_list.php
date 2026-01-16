<?php
/**
 * 活动列表接口
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

// 连接数据库
$db = Database::getInstance();
$prefix = 'caipiao_';

try {
    // 获取分页参数
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $pageSize = isset($_GET['pageSize']) ? max(1, intval($_GET['pageSize'])) : 20;
    $offset = ($page - 1) * $pageSize;
    
    // 获取筛选参数
    $catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
    $status = isset($_GET['status']) ? intval($_GET['status']) : -1; // -1=全部, 0=禁用, 1=启用
    
    // 构建查询条件
    $where = "1=1";
    $params = [];
    
    if ($catid > 0) {
        $where .= " AND catid = ?";
        $params[] = $catid;
    }
    
    if ($status >= 0) {
        $where .= " AND status = ?";
        $params[] = $status;
    }
    
    // 查询总数
    $countSql = "SELECT COUNT(*) as total FROM {$prefix}news WHERE {$where}";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 查询列表
    $sql = "SELECT * FROM {$prefix}news WHERE {$where} ORDER BY id DESC LIMIT {$offset}, {$pageSize}";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'code' => 200,
        'msg' => '获取成功',
        'data' => [
            'list' => $list,
            'pagination' => [
                'total' => intval($total),
                'page' => $page,
                'pageSize' => $pageSize,
                'totalPages' => ceil($total / $pageSize)
            ]
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'code' => 500,
        'msg' => '查询失败: ' . $e->getMessage(),
        'data' => null
    ]);
}

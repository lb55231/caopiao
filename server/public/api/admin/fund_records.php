<?php
/**
 * 获取账变记录
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Database::error('请使用GET请求', 405);
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

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $page = intval($_GET['page'] ?? 1);
    $pageSize = intval($_GET['page_size'] ?? 20);
    $username = trim($_GET['username'] ?? '');
    $type = trim($_GET['type'] ?? '');
    $startTime = intval($_GET['start_time'] ?? 0);
    $endTime = intval($_GET['end_time'] ?? 0);
    
    $offset = ($page - 1) * $pageSize;
    
    $where = [];
    $params = [];
    
    if (!empty($username)) {
        $where[] = "username LIKE :username";
        $params[':username'] = "%{$username}%";
    }
    
    if (!empty($type)) {
        $where[] = "type = :type";
        $params[':type'] = $type;
    }
    
    if ($startTime > 0) {
        $where[] = "oddtime >= :start_time";
        $params[':start_time'] = $startTime;
    }
    
    if ($endTime > 0) {
        $where[] = "oddtime <= :end_time";
        $params[':end_time'] = $endTime;
    }
    
    $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // 获取总数
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}fuddetail {$whereSQL}");
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 获取列表
    $stmt = $pdo->prepare("
        SELECT * FROM {$prefix}fuddetail 
        {$whereSQL}
        ORDER BY id DESC
        LIMIT {$offset}, {$pageSize}
    ");
    $stmt->execute($params);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Database::success('获取成功', [
        'list' => $list,
        'total' => intval($total)
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


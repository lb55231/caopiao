<?php
/**
 * 获取登录日志
 * 注意：由于数据库中可能没有loginlog表，这里使用member表的登录信息
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
    $ip = trim($_GET['ip'] ?? '');
    $startTime = intval($_GET['start_time'] ?? 0);
    $endTime = intval($_GET['end_time'] ?? 0);
    
    $offset = ($page - 1) * $pageSize;
    
    $where = [];
    $params = [];
    
    if (!empty($username)) {
        $where[] = "username LIKE :username";
        $params[':username'] = "%{$username}%";
    }
    
    if (!empty($ip)) {
        $where[] = "loginip LIKE :ip";
        $params[':ip'] = "%{$ip}%";
    }
    
    if ($startTime > 0) {
        $where[] = "logintime >= :start_time";
        $params[':start_time'] = $startTime;
    }
    
    if ($endTime > 0) {
        $where[] = "logintime <= :end_time";
        $params[':end_time'] = $endTime;
    }
    
    $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // 获取总数
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}member {$whereSQL}");
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 获取列表
    $stmt = $pdo->prepare("
        SELECT 
            id,
            username,
            loginip,
            iparea,
            loginsource,
            logintime,
            '' as device,
            1 as status
        FROM {$prefix}member 
        {$whereSQL}
        ORDER BY logintime DESC
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


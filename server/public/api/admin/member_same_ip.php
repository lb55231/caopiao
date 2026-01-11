<?php
/**
 * 同IP会员检测
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
    $ip = trim($_GET['ip'] ?? '');
    $minCount = intval($_GET['min_count'] ?? 2);
    
    $offset = ($page - 1) * $pageSize;
    
    $where = '';
    if (!empty($ip)) {
        $where = "WHERE regip = '{$ip}'";
    }
    
    // 获取同IP会员数据
    $stmt = $pdo->query("
        SELECT 
            regip as ip,
            COUNT(*) as member_count,
            MIN(regtime) as first_reg_time,
            MAX(regtime) as last_reg_time
        FROM {$prefix}member
        {$where}
        GROUP BY regip
        HAVING member_count >= {$minCount}
        ORDER BY member_count DESC, first_reg_time DESC
        LIMIT {$offset}, {$pageSize}
    ");
    
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取每个IP的会员列表
    foreach ($list as &$item) {
        $memberStmt = $pdo->prepare("
            SELECT id, username, balance, regtime 
            FROM {$prefix}member 
            WHERE regip = :ip
            ORDER BY regtime DESC
        ");
        $memberStmt->execute([':ip' => $item['ip']]);
        $item['members'] = $memberStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取总数
    $countStmt = $pdo->query("
        SELECT COUNT(*) as total FROM (
            SELECT regip
            FROM {$prefix}member
            {$where}
            GROUP BY regip
            HAVING COUNT(*) >= {$minCount}
        ) as t
    ");
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    Database::success('获取成功', [
        'list' => $list,
        'total' => intval($total)
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


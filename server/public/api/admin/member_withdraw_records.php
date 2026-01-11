<?php
/**
 * 获取会员提现记录
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
    
    $uid = intval($_GET['uid'] ?? 0);
    $page = intval($_GET['page'] ?? 1);
    $pageSize = intval($_GET['page_size'] ?? 20);
    
    if (empty($uid)) {
        Database::error('缺少会员ID');
    }
    
    $offset = ($page - 1) * $pageSize;
    
    // 获取总数和总金额
    $countStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN state = 1 THEN amount ELSE 0 END), 0) as totalAmount
        FROM {$prefix}withdraw 
        WHERE uid = :uid
    ");
    $countStmt->execute([':uid' => $uid]);
    $stats = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    // 获取列表
    $stmt = $pdo->prepare("
        SELECT * FROM {$prefix}withdraw 
        WHERE uid = :uid
        ORDER BY id DESC
        LIMIT {$offset}, {$pageSize}
    ");
    $stmt->execute([':uid' => $uid]);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Database::success('获取成功', [
        'list' => $list,
        'total' => intval($stats['total']),
        'totalAmount' => floatval($stats['totalAmount'])
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


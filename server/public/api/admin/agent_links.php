<?php
/**
 * 获取代理注册链接列表
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
    $offset = ($page - 1) * $pageSize;
    
    // 获取总数
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM {$prefix}member WHERE proxy = 1 AND invite_code != ''");
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 获取列表，包括注册人数统计
    $stmt = $pdo->query("
        SELECT 
            m.id,
            m.username,
            m.invite_code,
            m.regtime as created_at,
            (SELECT COUNT(*) FROM {$prefix}member WHERE parentid = m.id) as reg_count
        FROM {$prefix}member m
        WHERE m.proxy = 1 AND m.invite_code != ''
        ORDER BY m.id DESC
        LIMIT {$offset}, {$pageSize}
    ");
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


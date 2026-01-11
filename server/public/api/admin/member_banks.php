<?php
/**
 * 获取会员银行卡信息
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
    $bankname = trim($_GET['bankname'] ?? '');
    $accountname = trim($_GET['accountname'] ?? '');
    
    $offset = ($page - 1) * $pageSize;
    
    $where = ["state = 1"]; // 只显示有效的银行卡
    $params = [];
    
    if (!empty($username)) {
        $where[] = "username LIKE :username";
        $params[':username'] = "%{$username}%";
    }
    
    if (!empty($bankname)) {
        $where[] = "bankname LIKE :bankname";
        $params[':bankname'] = "%{$bankname}%";
    }
    
    if (!empty($accountname)) {
        $where[] = "accountname LIKE :accountname";
        $params[':accountname'] = "%{$accountname}%";
    }
    
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
    
    // 获取总数
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}banklist {$whereSQL}");
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 获取列表
    $stmt = $pdo->prepare("
        SELECT * FROM {$prefix}banklist 
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


<?php
/**
 * 提现记录 - 列表接口
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

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$pageSize = isset($_GET['page_size']) ? intval($_GET['page_size']) : 10;
$state = isset($_GET['state']) && $_GET['state'] !== '' ? intval($_GET['state']) : null;
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : null;
$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$trano = isset($_GET['trano']) ? trim($_GET['trano']) : '';
$sDate = isset($_GET['sDate']) ? trim($_GET['sDate']) : '';
$eDate = isset($_GET['eDate']) ? trim($_GET['eDate']) : '';
$sAmout = isset($_GET['sAmout']) ? floatval($_GET['sAmout']) : null;
$eAmout = isset($_GET['eAmout']) ? floatval($_GET['eAmout']) : null;

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $where = [];
    $params = [];
    
    if ($state !== null) {
        $where[] = "state = :state";
        $params[':state'] = $state;
    }
    
    if ($uid) {
        $where[] = "uid = :uid";
        $params[':uid'] = $uid;
    }
    
    if ($username) {
        $where[] = "username = :username";
        $params[':username'] = $username;
    }
    
    if ($trano) {
        $where[] = "trano = :trano";
        $params[':trano'] = $trano;
    }
    
    if ($sDate) {
        $where[] = "oddtime >= :sDate";
        $params[':sDate'] = strtotime($sDate);
    }
    
    if ($eDate) {
        $where[] = "oddtime <= :eDate";
        $params[':eDate'] = strtotime($eDate) + 86400;
    }
    
    if ($sAmout !== null) {
        $where[] = "amount >= :sAmout";
        $params[':sAmout'] = $sAmout;
    }
    
    if ($eAmout !== null) {
        $where[] = "amount <= :eAmout";
        $params[':eAmout'] = $eAmout;
    }
    
    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // 统计
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}withdraw {$whereSQL}");
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 列表
    $offset = ($page - 1) * $pageSize;
    $stmt = $pdo->prepare("
        SELECT * 
        FROM {$prefix}withdraw 
        {$whereSQL}
        ORDER BY id DESC 
        LIMIT :offset, :pageSize
    ");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
    
    $stmt->execute();
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 统计数据
    $statsStmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN state = 1 THEN amount ELSE 0 END) as total_success_amount,
            COUNT(CASE WHEN state = 1 THEN 1 END) as total_success_count
        FROM {$prefix}withdraw
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    Database::success('查询成功', [
        'list' => $list,
        'total' => intval($total),
        'page' => $page,
        'page_size' => $pageSize,
        'stats' => $stats
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


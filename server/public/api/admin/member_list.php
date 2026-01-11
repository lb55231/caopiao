<?php
/**
 * 获取会员列表
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
    $userbankname = trim($_GET['userbankname'] ?? '');
    $nickname = trim($_GET['nickname'] ?? '');
    $phone = trim($_GET['phone'] ?? '');
    $qq = trim($_GET['qq'] ?? '');
    $loginip = trim($_GET['loginip'] ?? '');
    $groupid = trim($_GET['groupid'] ?? '');
    $proxy = $_GET['proxy'] ?? '';
    $isnb = $_GET['isnb'] ?? '';
    $islock = $_GET['islock'] ?? '';
    $isonline = intval($_GET['isonline'] ?? 0);
    $ordertype = intval($_GET['ordertype'] ?? 0);
    $sDate = trim($_GET['sDate'] ?? '');
    $eDate = trim($_GET['eDate'] ?? '');
    $sAmount = trim($_GET['sAmount'] ?? '');
    $eAmount = trim($_GET['eAmount'] ?? '');
    $parentid = intval($_GET['parentid'] ?? 0);
    
    $offset = ($page - 1) * $pageSize;
    
    $where = [];
    $params = [];
    
    if (!empty($username)) {
        $where[] = "m.username LIKE :username";
        $params[':username'] = "%{$username}%";
    }
    
    if (!empty($userbankname)) {
        $where[] = "m.userbankname LIKE :userbankname";
        $params[':userbankname'] = "%{$userbankname}%";
    }
    
    if (!empty($nickname)) {
        $where[] = "m.nickname LIKE :nickname";
        $params[':nickname'] = "%{$nickname}%";
    }
    
    if (!empty($phone)) {
        $where[] = "m.phone LIKE :phone";
        $params[':phone'] = "%{$phone}%";
    }
    
    if (!empty($qq)) {
        $where[] = "m.qq LIKE :qq";
        $params[':qq'] = "%{$qq}%";
    }
    
    if (!empty($loginip)) {
        $where[] = "m.loginip LIKE :loginip";
        $params[':loginip'] = "%{$loginip}%";
    }
    
    if ($groupid !== '') {
        $where[] = "m.groupid = :groupid";
        $params[':groupid'] = $groupid;
    }
    
    if ($proxy !== '') {
        $where[] = "m.proxy = :proxy";
        $params[':proxy'] = $proxy;
    }
    
    if ($isnb !== '') {
        $where[] = "m.isnb = :isnb";
        $params[':isnb'] = $isnb;
    }
    
    if ($islock !== '') {
        $where[] = "m.islock = :islock";
        $params[':islock'] = $islock;
    }
    
    if ($isonline == 1) {
        $where[] = "(UNIX_TIMESTAMP() - m.onlinetime) < 600"; // 10分钟内在线
    }
    
    if (!empty($sDate)) {
        $sTimestamp = strtotime($sDate);
        $where[] = "m.regtime >= :sDate";
        $params[':sDate'] = $sTimestamp;
    }
    
    if (!empty($eDate)) {
        $eTimestamp = strtotime($eDate . ' 23:59:59');
        $where[] = "m.regtime <= :eDate";
        $params[':eDate'] = $eTimestamp;
    }
    
    if (!empty($sAmount)) {
        $where[] = "m.balance >= :sAmount";
        $params[':sAmount'] = $sAmount;
    }
    
    if (!empty($eAmount)) {
        $where[] = "m.balance <= :eAmount";
        $params[':eAmount'] = $eAmount;
    }
    
    if ($parentid > 0) {
        $where[] = "m.parentid = :parentid";
        $params[':parentid'] = $parentid;
    }
    
    $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // 排序
    $orderSQL = 'ORDER BY m.id DESC';
    switch ($ordertype) {
        case 1: $orderSQL = 'ORDER BY m.regtime ASC'; break;
        case 2: $orderSQL = 'ORDER BY m.fandian DESC'; break;
        case 3: $orderSQL = 'ORDER BY m.fandian ASC'; break;
        case 4: $orderSQL = 'ORDER BY m.balance DESC'; break;
        case 5: $orderSQL = 'ORDER BY m.balance ASC'; break;
        case 6: $orderSQL = 'ORDER BY m.point DESC'; break;
        case 7: $orderSQL = 'ORDER BY m.point ASC'; break;
        case 8: $orderSQL = 'ORDER BY m.xima DESC'; break;
        case 9: $orderSQL = 'ORDER BY m.xima ASC'; break;
        case 16: $orderSQL = 'ORDER BY m.logintime DESC'; break;
        case 17: $orderSQL = 'ORDER BY m.logintime ASC'; break;
        case 18: $orderSQL = 'ORDER BY m.onlinetime DESC'; break;
        case 19: $orderSQL = 'ORDER BY m.onlinetime ASC'; break;
    }
    
    // 获取总数
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}member m {$whereSQL}");
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 获取列表，包括统计数据
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            p.username as parent_username,
            IF((UNIX_TIMESTAMP() - m.onlinetime) < 600, 1, 0) as isonline,
            (SELECT COALESCE(SUM(amount), 0) FROM {$prefix}recharge WHERE uid = m.id AND state = 1) as total_recharge,
            (SELECT COALESCE(SUM(amount), 0) FROM {$prefix}withdraw WHERE uid = m.id AND state = 1) as total_withdraw,
            0 as total_win
        FROM {$prefix}member m
        LEFT JOIN {$prefix}member p ON m.parentid = p.id
        {$whereSQL}
        {$orderSQL}
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


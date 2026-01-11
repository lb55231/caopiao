<?php
/**
 * 投注记录API
 */

// 引入JWT工具类
require_once __DIR__ . '/../../common/Jwt.php';

// 获取Token
$token = $_SERVER['HTTP_TOKEN'] ?? '';

if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

// 验证Token
$jwt = new Jwt();
$tokenData = $jwt->verifyToken($token);

if (!$tokenData) {
    Database::error('Token无效或已过期', 401);
}

// 从token中获取用户ID
$userId = $tokenData['id'];

// 获取数据库连接
$pdo = Database::getInstance();
$prefix = Database::getPrefix();

// 获取请求参数
$cpname = $_GET['cpname'] ?? '';  // 彩票代码
$atime = $_GET['atime'] ?? '1';    // 时间范围: 1-今天 2-昨天 3-七天
$a_item = $_GET['a_item'] ?? '1';  // 状态: 1-全部 2-已中奖 3-未中奖 4-待开奖
$page = intval($_GET['page'] ?? 1);
$pageSize = intval($_GET['pageSize'] ?? 10);

try {
    // 构建查询条件
    $where = ['uid = :uid'];
    $params = [':uid' => $userId];
    
    // 彩票筛选
    if (!empty($cpname) && $cpname != '0') {
        $where[] = 'cpname = :cpname';
        $params[':cpname'] = $cpname;
    }
    
    // 时间范围筛选
    switch ($atime) {
        case '1': // 今天
            $startTime = strtotime(date('Y-m-d 00:00:00'));
            $endTime = time();
            break;
        case '2': // 昨天
            $startTime = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
            $endTime = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
            break;
        case '3': // 七天
            $startTime = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days')));
            $endTime = time();
            break;
        default:
            $startTime = null;
            $endTime = null;
    }
    
    if ($startTime && $endTime) {
        $where[] = 'oddtime >= :startTime AND oddtime <= :endTime';
        $params[':startTime'] = $startTime;
        $params[':endTime'] = $endTime;
    }
    
    // 状态筛选
    switch ($a_item) {
        case '2': // 已中奖
            $where[] = 'isdraw = 1';
            break;
        case '3': // 未中奖
            $where[] = 'isdraw = -1';
            break;
        case '4': // 待开奖
            $where[] = 'isdraw = 0';
            break;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // 查询总数
    $countSql = "SELECT COUNT(*) as total FROM {$prefix}touzhu WHERE {$whereClause}";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 查询列表
    $offset = ($page - 1) * $pageSize;
    $sql = "
        SELECT 
            id,
            cptitle,
            cpname,
            expect,
            playtitle,
            tzcode,
            amount,
            opencode,
            okamount,
            isdraw,
            oddtime
        FROM {$prefix}touzhu 
        WHERE {$whereClause}
        ORDER BY oddtime DESC
        LIMIT {$offset}, {$pageSize}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 查询今日概况
    $todayStart = strtotime(date('Y-m-d 00:00:00'));
    $todayEnd = strtotime(date('Y-m-d 23:59:59'));
    
    $summaryStmt = $pdo->prepare("
        SELECT 
            SUM(amount) as total_bet,
            SUM(CASE WHEN isdraw = 1 THEN okamount ELSE 0 END) as total_win
        FROM {$prefix}touzhu 
        WHERE uid = :uid AND oddtime >= :start AND oddtime <= :end
    ");
    $summaryStmt->execute([
        ':uid' => $userId,
        ':start' => $todayStart,
        ':end' => $todayEnd
    ]);
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);
    
    $totalBet = floatval($summary['total_bet'] ?? 0);
    $totalWin = floatval($summary['total_win'] ?? 0);
    $profit = $totalWin - $totalBet;
    
    // 格式化记录数据
    foreach ($records as &$record) {
        $record['id'] = (int)$record['id'];
        $record['amount'] = number_format($record['amount'], 2, '.', '');
        $record['okamount'] = number_format($record['okamount'], 2, '.', '');
        $record['isdraw'] = (int)$record['isdraw'];
        $record['oddtime'] = (int)$record['oddtime'];
        $record['time_formatted'] = date('m-d H:i:s', $record['oddtime']);
        
        // 状态文本
        switch ($record['isdraw']) {
            case 0:
                $record['status_text'] = '未开奖';
                break;
            case 1:
                $record['status_text'] = '已中奖';
                break;
            case -1:
                $record['status_text'] = '未中奖';
                break;
            case -2:
                $record['status_text'] = '已撤单';
                break;
            default:
                $record['status_text'] = '未知';
        }
    }
    
    // 返回数据
    Database::success('获取成功', [
        'records' => $records,
        'summary' => [
            'total_bet' => number_format($totalBet, 2, '.', ''),
            'total_win' => number_format($totalWin, 2, '.', ''),
            'profit' => number_format($profit, 2, '.', '')
        ],
        'pagination' => [
            'page' => $page,
            'pageSize' => $pageSize,
            'total' => (int)$totalCount,
            'totalPages' => ceil($totalCount / $pageSize)
        ]
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


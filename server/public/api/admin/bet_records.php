<?php
/**
 * 游戏记录 - 获取投注记录列表
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// 验证Token
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
    
    // 获取查询参数
    $cpname = $_GET['cpname'] ?? '';
    $username = $_GET['username'] ?? '';
    $expect = $_GET['expect'] ?? '';
    $qihao = $_GET['qihao'] ?? ''; // 期号
    $trano = $_GET['trano'] ?? ''; // 订单号
    $isnb = $_GET['isnb'] ?? ''; // 内外盘: 1=内盘, 0=外盘, 999=全部
    $status = $_GET['status'] ?? ''; // 0=待开奖, 1=已中奖, 2=未中奖, 999=全部
    $sDate = $_GET['sDate'] ?? '';
    $eDate = $_GET['eDate'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $pageSize = min(100, max(10, intval($_GET['page_size'] ?? 20)));
    
    // 构建查询条件
    $where = '1=1';
    $params = [];
    
    // 注: isnb字段在当前表中不存在，如需要可添加
    
    if ($cpname) {
        $where .= ' AND t.cpname = :cpname';
        $params[':cpname'] = $cpname;
    }
    
    if ($username) {
        $where .= ' AND t.username LIKE :username';
        $params[':username'] = '%' . $username . '%';
    }
    
    if ($expect || $qihao) {
        $expectSearch = $expect ?: $qihao;
        $where .= ' AND t.expect LIKE :expect';
        $params[':expect'] = '%' . $expectSearch . '%';
    }
    
    if ($trano) {
        $where .= ' AND t.trano LIKE :trano';
        $params[':trano'] = '%' . $trano . '%';
    }
    
    // 状态筛选 (使用 isdraw 字段，而不是 status)
    if ($status !== '' && $status != '999') {
        $where .= ' AND t.isdraw = :isdraw';
        $params[':isdraw'] = $status;
    }
    
    if ($sDate) {
        $where .= ' AND FROM_UNIXTIME(t.addtime) >= :start_date';
        $params[':start_date'] = $sDate . ' 00:00:00';
    }
    
    if ($eDate) {
        $where .= ' AND FROM_UNIXTIME(t.addtime) <= :end_date';
        $params[':end_date'] = $eDate . ' 23:59:59';
    }
    
    // 查询总数
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}touzhu t WHERE {$where}");
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 查询列表
    $offset = ($page - 1) * $pageSize;
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.uid,
            t.username,
            t.cpname,
            t.cptitle,
            t.expect,
            t.playid,
            t.playtitle,
            t.tzcode,
            t.opencode,
            t.trano,
            t.amount,
            t.okamount,
            t.isdraw,
            t.oddtime,
            t.source
        FROM {$prefix}touzhu t
        WHERE {$where}
        ORDER BY t.id DESC
        LIMIT {$offset}, {$pageSize}
    ");
    $stmt->execute($params);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['uid'] = (int)$item['uid'];
        $item['playid'] = (int)$item['playid'];
        $item['amount'] = (float)$item['amount'];
        $item['okamount'] = (float)$item['okamount'];
        $item['isdraw'] = (int)$item['isdraw'];
        $item['oddtime'] = (int)$item['oddtime'];
        $item['oddtime_str'] = $item['oddtime'] > 0 ? date('Y-m-d H:i:s', $item['oddtime']) : '';
        
        // 状态文本
        $statusMap = [
            0 => '待开奖',
            1 => '已中奖',
            2 => '未中奖'
        ];
        $item['status_text'] = $statusMap[$item['isdraw']] ?? '未知';
        
        // 来源文本
        $sourceMap = [
            'pc' => 'PC端',
            'wap' => '手机端',
            'app' => 'APP'
        ];
        $item['source_text'] = $sourceMap[$item['source']] ?? $item['source'];
    }
    
    // 统计数据
    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_count,
            SUM(amount) as total_amount,
            SUM(CASE WHEN isdraw = 1 THEN okamount ELSE 0 END) as total_award,
            SUM(CASE WHEN isdraw = 0 THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN isdraw = 1 THEN 1 ELSE 0 END) as win_count,
            SUM(CASE WHEN isdraw = 2 THEN 1 ELSE 0 END) as lose_count
        FROM {$prefix}touzhu t
        WHERE {$where}
    ");
    $statsStmt->execute($params);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    Database::success('获取成功', [
        'list' => $list,
        'total' => (int)$total,
        'page' => $page,
        'page_size' => $pageSize,
        'total_pages' => ceil($total / $pageSize),
        'stats' => [
            'total_count' => (int)$stats['total_count'],
            'total_amount' => (float)$stats['total_amount'],
            'total_award' => (float)$stats['total_award'],
            'pending_count' => (int)$stats['pending_count'],
            'win_count' => (int)$stats['win_count'],
            'lose_count' => (int)$stats['lose_count'],
            'profit' => (float)$stats['total_amount'] - (float)$stats['total_award']
        ]
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


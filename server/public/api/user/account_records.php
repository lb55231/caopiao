<?php
/**
 * 账变记录API
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
$type = $_GET['type'] ?? '';       // 摘要类型
$atime = $_GET['atime'] ?? '3';    // 时间范围: 1-今天 2-昨天 3-七天
$page = intval($_GET['page'] ?? 1);
$pageSize = intval($_GET['pageSize'] ?? 10);

try {
    // 构建查询条件
    $where = ['uid = :uid'];
    $params = [':uid' => $userId];
    
    // 排除洗码类型的账变记录
    $where[] = "type != 'xima'";
    
    // 类型筛选
    if (!empty($type) && $type != '0') {
        $where[] = 'type = :type';
        $params[':type'] = $type;
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
    
    $whereClause = implode(' AND ', $where);
    
    // 查询总数
    $countSql = "SELECT COUNT(*) as total FROM {$prefix}fuddetail WHERE {$whereClause}";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 查询列表
    $offset = ($page - 1) * $pageSize;
    $sql = "
        SELECT 
            id,
            type,
            typename,
            amount,
            amountbefor,
            amountafter,
            remark,
            oddtime
        FROM {$prefix}fuddetail 
        WHERE {$whereClause}
        ORDER BY oddtime DESC
        LIMIT {$offset}, {$pageSize}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 格式化记录数据
    foreach ($records as &$record) {
        $record['id'] = (int)$record['id'];
        $record['oddtime'] = (int)$record['oddtime'];
        $record['time_formatted'] = date('m-d H:i:s', $record['oddtime']);
        
        // 根据前后金额判断是收入还是支出
        $amountBefor = floatval($record['amountbefor']);
        $amountAfter = floatval($record['amountafter']);
        $amount = floatval($record['amount']);
        
        if ($amountBefor > $amountAfter) {
            // 支出
            $record['amount_formatted'] = '-' . number_format($amount, 2, '.', '');
            $record['amount_type'] = 'out';
        } else {
            // 收入
            $record['amount_formatted'] = '+' . number_format($amount, 2, '.', '');
            $record['amount_type'] = 'in';
        }
        
        $record['amountafter'] = number_format($amountAfter, 2, '.', '');
        
        // 处理备注中的替换（和原项目一样）
        $remark = $record['remark'];
        $remark = str_replace('和值大', '普货', $remark);
        $remark = str_replace('和值小', '精品', $remark);
        $remark = str_replace('和值单', '一件', $remark);
        $remark = str_replace('和值双', '多件', $remark);
        $record['remark'] = $remark;
    }
    
    // 返回数据
    Database::success('获取成功', [
        'records' => $records,
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


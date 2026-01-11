<?php
/**
 * 开奖管理 - 获取开奖记录列表
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
    $expect = $_GET['expect'] ?? '';
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $pageSize = min(100, max(10, intval($_GET['page_size'] ?? 20)));
    
    // 构建查询条件
    $where = '1=1';
    $params = [];
    
    if ($cpname) {
        $where .= ' AND k.name = :cpname';
        $params[':cpname'] = $cpname;
    }
    
    if ($expect) {
        $where .= ' AND k.expect LIKE :expect';
        $params[':expect'] = '%' . $expect . '%';
    }
    
    if ($startDate) {
        $where .= ' AND FROM_UNIXTIME(k.opentime) >= :start_date';
        $params[':start_date'] = $startDate . ' 00:00:00';
    }
    
    if ($endDate) {
        $where .= ' AND FROM_UNIXTIME(k.opentime) <= :end_date';
        $params[':end_date'] = $endDate . ' 23:59:59';
    }
    
    // 查询总数
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}kaijiang k WHERE {$where}");
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 查询列表
    $offset = ($page - 1) * $pageSize;
    $stmt = $pdo->prepare("
        SELECT 
            k.id,
            k.name as cpname,
            k.title as cptitle,
            k.expect,
            k.opencode,
            k.opentime,
            k.drawtime,
            k.isdraw,
            k.source,
            k.addtime
        FROM {$prefix}kaijiang k
        WHERE {$where}
        ORDER BY k.id DESC
        LIMIT {$offset}, {$pageSize}
    ");
    $stmt->execute($params);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['isdraw'] = (int)$item['isdraw'];
        $item['opentime'] = (int)$item['opentime'];
        $item['drawtime'] = (int)$item['drawtime'];
        $item['addtime'] = (int)$item['addtime'];
        $item['opentime_str'] = $item['opentime'] > 0 ? date('Y-m-d H:i:s', $item['opentime']) : '';
        $item['addtime_str'] = $item['addtime'] > 0 ? date('Y-m-d H:i:s', $item['addtime']) : '';
    }
    
    Database::success('获取成功', [
        'list' => $list,
        'total' => (int)$total,
        'page' => $page,
        'page_size' => $pageSize,
        'total_pages' => ceil($total / $pageSize)
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


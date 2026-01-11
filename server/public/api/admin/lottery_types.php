<?php
/**
 * 彩种管理 - 列表接口
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
    $typeid = $_GET['typeid'] ?? '';
    
    // 构建查询条件
    $where = '1=1';
    $params = [];
    
    if ($typeid) {
        $where .= ' AND typeid = :typeid';
        $params[':typeid'] = $typeid;
    }
    
    // 查询彩种列表
    $orderBy = $typeid ? 'listorder ASC, id DESC' : 'allsort ASC, id DESC';
    
    $stmt = $pdo->prepare("
        SELECT 
            id, typeid, title, name, ftime, qishu, ftitle, logo,
            issys, isopen, iswh, listorder, allsort,
            closetime1, closetime2, expecttime
        FROM {$prefix}caipiao
        WHERE {$where}
        ORDER BY {$orderBy}
    ");
    $stmt->execute($params);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['qishu'] = (int)$item['qishu'];
        $item['issys'] = (int)$item['issys'];
        $item['isopen'] = (int)$item['isopen'];
        $item['iswh'] = (int)$item['iswh'];
        $item['listorder'] = (int)$item['listorder'];
        $item['allsort'] = (int)$item['allsort'];
    }
    
    Database::success('获取成功', [
        'list' => $list,
        'total' => count($list)
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


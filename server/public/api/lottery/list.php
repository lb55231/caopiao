<?php
/**
 * 获取彩票列表API
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../common/Database.php';

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();

    // 获取所有启用的彩票（按排序）
    $stmt = $pdo->query("
        SELECT 
            id, 
            typeid, 
            name, 
            title, 
            ftitle,
            logo,
            issys, 
            isopen, 
            iswh,
            listorder,
            qishu,
            expecttime,
            closetime1,
            closetime2
        FROM {$prefix}caipiao 
        WHERE isopen = 1
        ORDER BY listorder ASC, id ASC
    ");
    
    $lotteryList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 如果logo为空，使用默认值
    foreach ($lotteryList as &$lottery) {
        if (empty($lottery['logo'])) {
            $lottery['logo'] = '/images/lottery/' . $lottery['typeid'] . '.png';
        }
    }
    
    Database::success('获取成功', $lotteryList);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}

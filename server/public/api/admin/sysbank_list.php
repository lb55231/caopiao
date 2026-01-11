<?php
/**
 * 提款银行 - 列表接口
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
    $page = max(1, intval($_GET['page'] ?? 1));
    $pageSize = min(100, max(10, intval($_GET['page_size'] ?? 20)));
    
    // 查询总数
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM {$prefix}sysbank");
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 查询列表（按排序）
    $offset = ($page - 1) * $pageSize;
    $stmt = $pdo->query("
        SELECT 
            id, bankcode, bankname, banklogo, state, listorder, imgbg
        FROM {$prefix}sysbank
        ORDER BY listorder ASC, id ASC
        LIMIT {$offset}, {$pageSize}
    ");
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['state'] = (int)$item['state'];
        $item['listorder'] = (int)$item['listorder'];
        $item['state_text'] = $item['state'] == 1 ? '启用' : '禁用';
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


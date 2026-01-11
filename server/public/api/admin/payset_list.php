<?php
/**
 * 存款方式配置 - 列表接口
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
    
    // 查询所有数据（按排序）
    $stmt = $pdo->query("
        SELECT 
            id, paytype, paytypetitle, ftitle, minmoney, maxmoney, 
            remark, configs, isonline, listorder, state
        FROM {$prefix}payset
        ORDER BY listorder ASC, id ASC
    ");
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['isonline'] = (int)$item['isonline'];
        $item['listorder'] = (int)$item['listorder'];
        $item['state'] = (int)$item['state'];
        $item['minmoney'] = (float)$item['minmoney'];
        $item['maxmoney'] = (float)$item['maxmoney'];
        
        // 解析configs字段
        if (!empty($item['configs'])) {
            $configs = @unserialize($item['configs']);
            $item['configs_array'] = $configs ?: [];
        } else {
            $item['configs_array'] = [];
        }
        
        $item['isonline_text'] = $item['isonline'] == 1 ? '是' : '否';
        $item['state_text'] = $item['state'] == 1 ? '启用' : '禁用';
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


<?php
/**
 * 系统配置 - 公开接口（不需要登录）
 * 用于前台和登录页面获取基本配置
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
    
    // 获取公开的配置项
    $stmt = $pdo->query("SELECT name, value FROM {$prefix}setting WHERE name IN ('webtitle', 'weblogo', 'keywords', 'description', 'copyright', 'icp', 'serviceqq', 'servicecode')");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 转换为键值对
    $config = [
        'webtitle' => '彩票系统',
        'weblogo' => '',
        'keywords' => '',
        'description' => '',
        'copyright' => '',
        'icp' => '',
        'serviceqq' => '',
        'servicecode' => ''
    ];
    
    foreach ($settings as $item) {
        $config[$item['name']] = $item['value'];
    }
    
    Database::success('获取成功', $config);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


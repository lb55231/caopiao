<?php
/**
 * 系统设置 - 获取配置
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
    
    // 获取所有设置
    $stmt = $pdo->query("SELECT name, value FROM {$prefix}setting");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 转换为键值对
    $config = [];
    foreach ($settings as $item) {
        $config[$item['name']] = $item['value'];
    }
    
    // 确保必要的配置项存在
    $defaults = [
        'webtitle' => '彩票系统',
        'weblogo' => '',
        'keywords' => '',
        'description' => '',
        'copyright' => '',
        'icp' => '',
        'serviceqq' => '',
        'servicecode' => '',
        'registerbonus' => '0',
        'needinvitecode' => '0',
        'damaliang' => '0'
    ];
    
    foreach ($defaults as $key => $value) {
        if (!isset($config[$key])) {
            $config[$key] = $value;
        }
    }
    
    Database::success('获取成功', $config);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


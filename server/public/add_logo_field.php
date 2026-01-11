<?php
/**
 * 为 caipiao_caipiao 表添加 logo 字段
 */
require_once 'common/Database.php';

try {
    $pdo = Database::getInstance();
    
    // 检查是否已存在 logo 字段
    $stmt = $pdo->query("SHOW COLUMNS FROM caipiao_caipiao LIKE 'logo'");
    if ($stmt->rowCount() > 0) {
        echo "logo 字段已存在\n";
        exit;
    }
    
    // 添加 logo 字段
    $sql = "ALTER TABLE caipiao_caipiao ADD COLUMN logo VARCHAR(255) DEFAULT '' COMMENT '彩票图标' AFTER title";
    $pdo->exec($sql);
    
    echo "✅ 成功添加 logo 字段\n";
    
    // 为现有数据添加默认logo
    $updateSql = "UPDATE caipiao_caipiao SET logo = CONCAT('/images/lottery/', typeid, '.png') WHERE logo = '' OR logo IS NULL";
    $pdo->exec($updateSql);
    
    echo "✅ 已为现有数据设置默认logo\n";
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}


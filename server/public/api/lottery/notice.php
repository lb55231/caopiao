<?php
/**
 * 获取最新公告
 */

// 获取数据库连接
$pdo = Database::getInstance();
$prefix = Database::getPrefix();

try {
    // 查询最新的公告
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            oddtime
        FROM {$prefix}gonggao 
        WHERE user_id = 0 
        ORDER BY id DESC 
        LIMIT 1
    ");
    
    $stmt->execute();
    $notice = $stmt->fetch();
    
    if ($notice) {
        Database::success('获取成功', $notice);
    } else {
        Database::success('获取成功', [
            'id' => 0,
            'title' => '欢迎光临！祝您游戏愉快！',
            'oddtime' => time()
        ]);
    }
    
} catch (PDOException $e) {
    Database::error('查询失败：' . $e->getMessage(), 500);
}


<?php
require_once 'common/Database.php';

$pdo = Database::getInstance();
$stmt = $pdo->query('SHOW COLUMNS FROM caipiao_caipiao');

echo "caipiao_caipiao 表结构：\n\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%-20s %-30s %s\n", $row['Field'], $row['Type'], $row['Null']);
}

// 检查是否有 logo 或 image 字段
$stmt = $pdo->query('SHOW COLUMNS FROM caipiao_caipiao LIKE "%logo%" OR Field LIKE "%image%" OR Field LIKE "%icon%"');
$hasImageField = $stmt->rowCount() > 0;

echo "\n是否有图片字段: " . ($hasImageField ? "是" : "否") . "\n";


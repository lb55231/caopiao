<?php
require_once __DIR__ . '/common/Database.php';

$pdo = Database::getInstance();
$prefix = Database::getPrefix();

echo "检查今天的开奖数据:\n";
echo "==================\n\n";

$stmt = $pdo->query("SELECT expect, opencode FROM {$prefix}kaijiang WHERE name='hebk3' AND expect LIKE '20260109%' ORDER BY expect DESC LIMIT 20");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "最近20条记录:\n";
foreach($results as $row) {
    $codes = explode(',', $row['opencode']);
    $sum = array_sum($codes);
    $display = ($sum > 10 ? '普货' : '精品') . ' ' . ($sum % 2 === 0 ? '一件' : '多件');
    echo "  期号: {$row['expect']}, 开奖: {$row['opencode']}, {$display}\n";
}

echo "\n==================\n";
echo "当前时间: " . date('Y-m-d H:i:s') . "\n";
echo "当前时间戳: " . time() . "\n";


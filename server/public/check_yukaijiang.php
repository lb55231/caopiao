<?php
/**
 * 检查预开奖数据
 */
require_once __DIR__ . '/common/Database.php';

$pdo = Database::getInstance();
$prefix = Database::getPrefix();

// 查询最近的预开奖记录
$stmt = $pdo->query("SELECT * FROM {$prefix}yukaijiang WHERE hid = 0 ORDER BY id DESC LIMIT 20");
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>预开奖表数据（最近20条）</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>彩种</th><th>期号</th><th>开奖号码</th><th>开奖时间</th><th>操作员</th><th>hid</th></tr>";

foreach ($list as $row) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['expect']}</td>";
    echo "<td><strong style='color:red;'>{$row['opencode']}</strong></td>";
    echo "<td>" . date('Y-m-d H:i:s', $row['opentime']) . "</td>";
    echo "<td>{$row['stateadmin']}</td>";
    echo "<td>{$row['hid']}</td>";
    echo "</tr>";
}

echo "</table>";

// 查询最近的开奖记录
echo "<br><h2>开奖表数据（最近20条）</h2>";
$stmt2 = $pdo->query("SELECT * FROM {$prefix}kaijiang ORDER BY id DESC LIMIT 20");
$list2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>彩种</th><th>期号</th><th>开奖号码</th><th>来源</th><th>开奖时间</th></tr>";

foreach ($list2 as $row) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['expect']}</td>";
    echo "<td><strong style='color:blue;'>{$row['opencode']}</strong></td>";
    echo "<td>{$row['source']}</td>";
    echo "<td>" . date('Y-m-d H:i:s', $row['opentime']) . "</td>";
    echo "</tr>";
}

echo "</table>";

// 对比分析
echo "<br><h2>分析</h2>";
echo "<p>查看预开奖表中的期号和开奖号码是否与开奖表中的一致</p>";
echo "<p style='color:red;'>如果预开奖表有数据，但开奖表中对应期号的号码不一致，说明自动开奖没有读取预开奖数据</p>";

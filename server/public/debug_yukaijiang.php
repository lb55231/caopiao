<?php
/**
 * 预开奖调试脚本
 * 帮助诊断预开奖功能问题
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/common/Database.php';

$pdo = Database::getInstance();
$prefix = Database::getPrefix();

// 获取参数
$expect = $_GET['expect'] ?? '20260116365';
$name = $_GET['name'] ?? 'haizhu';

echo "<h1>预开奖调试工具</h1>";
echo "<p>期号：<strong>{$expect}</strong> | 彩种：<strong>{$name}</strong></p>";
echo "<hr>";

// 1. 检查预开奖表
echo "<h2>1. 预开奖表（yukaijiang）</h2>";
$stmt1 = $pdo->prepare("SELECT * FROM {$prefix}yukaijiang WHERE name = :name AND expect = :expect AND hid = 0");
$stmt1->execute([':name' => $name, ':expect' => $expect]);
$ykjData = $stmt1->fetch(PDO::FETCH_ASSOC);

if ($ykjData) {
    echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;'>";
    echo "<strong>✓ 找到预开奖数据：</strong><br>";
    echo "ID: {$ykjData['id']}<br>";
    echo "开奖号码: <span style='color:red;font-size:20px;'>{$ykjData['opencode']}</span><br>";
    echo "开奖时间: " . date('Y-m-d H:i:s', $ykjData['opentime']) . "<br>";
    echo "操作员: {$ykjData['stateadmin']}<br>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;'>";
    echo "<strong>✗ 未找到预开奖数据</strong><br>";
    echo "可能原因：预开奖还没有保存成功";
    echo "</div>";
}

echo "<br>";

// 2. 检查开奖表
echo "<h2>2. 开奖表（kaijiang）</h2>";
$stmt2 = $pdo->prepare("SELECT * FROM {$prefix}kaijiang WHERE name = :name AND expect = :expect");
$stmt2->execute([':name' => $name, ':expect' => $expect]);
$kjData = $stmt2->fetch(PDO::FETCH_ASSOC);

if ($kjData) {
    echo "<div style='background:#fff3cd;padding:10px;border:1px solid #ffeeba;'>";
    echo "<strong>⚠ 该期号已经存在开奖记录：</strong><br>";
    echo "ID: {$kjData['id']}<br>";
    echo "开奖号码: <span style='color:blue;font-size:20px;'>{$kjData['opencode']}</span><br>";
    echo "开奖时间: " . date('Y-m-d H:i:s', $kjData['opentime']) . "<br>";
    echo "来源: {$kjData['source']}<br>";
    echo "是否派奖: " . ($kjData['isdraw'] == 0 ? '未派奖' : '已派奖') . "<br>";
    echo "<br>";
    echo "<strong style='color:red;'>问题分析：</strong><br>";
    echo "该期号已经在开奖表中存在，自动开奖程序会跳过已存在的期号。<br>";
    echo "预开奖功能只对<strong>未来的期号</strong>有效。<br>";
    echo "<br>";
    echo "<strong>解决方法：</strong><br>";
    echo "1. 如果要修改这一期的开奖号码，需要先删除开奖表中的记录<br>";
    echo "2. 或者设置下一期的预开奖号码<br>";
    echo "</div>";
} else {
    echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;'>";
    echo "<strong>✓ 开奖表中不存在该期号</strong><br>";
    echo "预开奖功能应该可以正常工作";
    echo "</div>";
}

echo "<br>";

// 3. 对比分析
echo "<h2>3. 对比分析</h2>";
if ($ykjData && $kjData) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr><th>数据来源</th><th>开奖号码</th><th>开奖时间</th></tr>";
    echo "<tr>";
    echo "<td>预开奖表</td>";
    echo "<td style='color:red;font-size:18px;'>{$ykjData['opencode']}</td>";
    echo "<td>" . date('Y-m-d H:i:s', $ykjData['opentime']) . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>开奖表</td>";
    echo "<td style='color:blue;font-size:18px;'>{$kjData['opencode']}</td>";
    echo "<td>" . date('Y-m-d H:i:s', $kjData['opentime']) . "</td>";
    echo "</tr>";
    echo "</table>";
    
    if ($ykjData['opencode'] != $kjData['opencode']) {
        echo "<p style='color:red;'><strong>号码不一致！</strong> 说明预开奖数据没有被使用。</p>";
    } else {
        echo "<p style='color:green;'><strong>号码一致。</strong> 预开奖功能正常工作。</p>";
    }
}

echo "<br>";

// 4. 操作建议
echo "<h2>4. 操作建议</h2>";
echo "<div style='background:#e7f3ff;padding:15px;border:1px solid #bee5eb;'>";

if ($kjData) {
    echo "<h3>如果要修改已开奖的期号：</h3>";
    echo "<ol>";
    echo "<li><strong>删除开奖记录：</strong> 在后台管理的【开奖管理】中删除期号 {$expect} 的记录</li>";
    echo "<li><strong>重新设置预开奖：</strong> 设置期号 {$expect} 的预开奖号码为 5,5,5</li>";
    echo "<li><strong>触发自动开奖：</strong> 等待系统自动开奖，或手动运行开奖脚本</li>";
    echo "</ol>";
    echo "<br>";
    echo "<h3>或者直接修改开奖记录：</h3>";
    echo "<p>执行以下 SQL 命令：</p>";
    echo "<pre style='background:#f8f9fa;padding:10px;border:1px solid #dee2e6;'>";
    echo "UPDATE {$prefix}kaijiang SET opencode = '5,5,5' WHERE name = '{$name}' AND expect = '{$expect}';\n";
    echo "</pre>";
    
    // 提供删除链接
    echo "<br>";
    echo "<a href='?action=delete&expect={$expect}&name={$name}' onclick='return confirm(\"确定要删除期号 {$expect} 的开奖记录吗？\")' style='background:#dc3545;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;'>删除该期开奖记录</a>";
} else {
    echo "<p style='color:green;'>✓ 该期号还没有开奖，预开奖功能应该可以正常工作</p>";
    echo "<p>请确保：</p>";
    echo "<ol>";
    echo "<li>预开奖数据已经保存（见上面第1部分）</li>";
    echo "<li>自动开奖脚本正在运行</li>";
    echo "<li>该期号的开奖时间未到</li>";
    echo "</ol>";
}

echo "</div>";

// 处理删除操作
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['expect']) && isset($_GET['name'])) {
    $delExpect = $_GET['expect'];
    $delName = $_GET['name'];
    
    $delStmt = $pdo->prepare("DELETE FROM {$prefix}kaijiang WHERE name = :name AND expect = :expect");
    $result = $delStmt->execute([':name' => $delName, ':expect' => $delExpect]);
    
    if ($result) {
        echo "<script>alert('删除成功！请刷新页面查看最新状态。'); location.href='?expect={$delExpect}&name={$delName}';</script>";
    } else {
        echo "<script>alert('删除失败！');</script>";
    }
}

echo "<hr>";
echo "<p><a href='?'>返回</a> | <a href='check_yukaijiang.php'>查看所有预开奖数据</a></p>";
?>

<?php
/**
 * 模拟开奖脚本 - 生成今天的开奖数据（用于测试）
 */
require_once __DIR__ . '/common/Database.php';

$pdo = Database::getInstance();
$prefix = Database::getPrefix();

$cpname = $_GET['cpname'] ?? 'hebk3';
$date = $_GET['date'] ?? date('Ymd');

// 查询彩票配置
$stmt = $pdo->prepare("SELECT * FROM {$prefix}caipiao WHERE name = :cpname");
$stmt->execute([':cpname' => $cpname]);
$cpinfo = $stmt->fetch();

if (!$cpinfo) {
    die("彩票不存在！");
}

$expecttime = intval($cpinfo['expecttime']); // 分钟
$_expecttime = $expecttime * 60; // 秒
$closetime1 = $cpinfo['closetime1'];
$closetime2 = $cpinfo['closetime2'];

$_t1 = strtotime($date . ' ' . $closetime1);
$_t2 = strtotime($date . ' ' . $closetime2);
$totalcount = floor(abs($_t2 - $_t1) / $_expecttime);
$_length = $totalcount >= 1000 ? 4 : 3;

echo "<h2>模拟开奖 - {$cpinfo['title']}</h2>";
echo "日期: {$date}<br>";
echo "总期数: {$totalcount}<br>";
echo "每期: {$expecttime}分钟<br><br>";

// 计算当前应该到第几期了
$now = time();
$currentPeriod = 1;

if (strtotime($date) == strtotime(date('Y-m-d'))) {
    // 今天，计算当前期号
    if ($now > $_t1) {
        $currentPeriod = min($totalcount, floor(($now - $_t1) / $_expecttime) + 1);
    }
} else if (strtotime($date) < strtotime(date('Y-m-d'))) {
    // 历史日期，生成全天
    $currentPeriod = $totalcount;
}

echo "需要生成: 第1期 到 第{$currentPeriod}期<br><br>";

$generated = 0;
$exists = 0;

for ($i = 1; $i <= $currentPeriod; $i++) {
    $expect = $date . str_pad($i, $_length, '0', STR_PAD_LEFT);
    
    // 检查是否已存在
    $stmt = $pdo->prepare("SELECT id FROM {$prefix}kaijiang WHERE name = :name AND expect = :expect");
    $stmt->execute([':name' => $cpname, ':expect' => $expect]);
    
    if ($stmt->fetch()) {
        $exists++;
        continue;
    }
    
    // 生成随机开奖号码（K3: 3个骰子）
    $dice1 = rand(1, 6);
    $dice2 = rand(1, 6);
    $dice3 = rand(1, 6);
    $opencode = "{$dice1},{$dice2},{$dice3}";
    
    // 计算开奖时间
    $opentime = $_t1 + ($i * $_expecttime);
    
    // 插入开奖数据
    try {
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}kaijiang 
            (name, title, expect, opencode, sourcecode, remarks, opentime, isdraw, addtime, source, drawtime) 
            VALUES 
            (:name, :title, :expect, :opencode, :sourcecode, :remarks, :opentime, :isdraw, :addtime, :source, :drawtime)
        ");
        
        $stmt->execute([
            ':name' => $cpname,
            ':title' => $cpinfo['title'],
            ':expect' => $expect,
            ':opencode' => $opencode,
            ':sourcecode' => '',
            ':remarks' => '',
            ':opentime' => $opentime,
            ':isdraw' => 0,
            ':addtime' => time(),
            ':source' => '模拟开奖',
            ':drawtime' => 0
        ]);
        
        $generated++;
        echo "✓ 期号: {$expect} | 号码: {$opencode} | 时间: " . date('Y-m-d H:i:s', $opentime) . "<br>";
        
    } catch (Exception $e) {
        echo "✗ 期号: {$expect} 失败: " . $e->getMessage() . "<br>";
    }
}

echo "<br><hr>";
echo "<h3>统计</h3>";
echo "新生成: {$generated} 期<br>";
echo "已存在: {$exists} 期<br>";
echo "总计: " . ($generated + $exists) . " 期<br>";
echo "<br>";
echo "<a href='test_period.php?cpname={$cpname}'>查看期号信息</a> | ";
echo "<a href='?cpname={$cpname}&date={$date}'>刷新</a>";
?>


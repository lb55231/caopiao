<?php
/**
 * 测试期号计算 - 显示详细信息
 */
require_once __DIR__ . '/common/Database.php';

$pdo = Database::getInstance();
$prefix = Database::getPrefix();
$cpname = $_GET['cpname'] ?? 'hebk3';

// 查询配置
$stmt = $pdo->prepare("SELECT * FROM {$prefix}caipiao WHERE name = :cpname");
$stmt->execute([':cpname' => $cpname]);
$cpinfo = $stmt->fetch();

echo "<h2>彩票配置</h2>";
echo "<pre>";
print_r($cpinfo);
echo "</pre>";

// 计算期号
$nowtime = time();
$cjnowtime = intval($cpinfo['ftime']);
$expecttime = intval($cpinfo['expecttime']);
$_expecttime = $expecttime * 60;

$closetime1 = $cpinfo['closetime1'];
$closetime2 = $cpinfo['closetime2'];
$_t1 = strtotime(date('Y-m-d') . ' ' . $closetime1);
$_t2 = strtotime(date('Y-m-d') . ' ' . $closetime2);
$totalcount = floor(abs($_t2 - $_t1) / $_expecttime);

echo "<h2>计算参数</h2>";
echo "当前时间: " . date('Y-m-d H:i:s', $nowtime) . " (timestamp: $nowtime)<br>";
echo "开始时间: " . date('Y-m-d H:i:s', $_t1) . " (timestamp: $_t1)<br>";
echo "结束时间: " . date('Y-m-d H:i:s', $_t2) . " (timestamp: $_t2)<br>";
echo "每期时长: {$expecttime}分钟 ({$_expecttime}秒)<br>";
echo "总期数: {$totalcount}<br>";
echo "封盘提前: {$cjnowtime}秒<br>";

$_t = time();

if ($_t < $_t1) {
    $actNo = $totalcount;
    $status = "还未到开始时间";
} else if ($_t > $_t2) {
    $actNo = 1;
    $status = "已过结束时间，等待明天";
} else {
    $actNo_t = ($_t - $_t1) / $_expecttime;
    $actNo = floor($actNo_t) + 1;
    $status = "正常营业中";
}

$_length = $totalcount >= 1000 ? 4 : 3;
$currentPeriod = date('Ymd') . str_pad($actNo, $_length, '0', STR_PAD_LEFT);

echo "<h2>当前期号</h2>";
echo "状态: {$status}<br>";
echo "当前期数: {$actNo}<br>";
echo "当前期号: {$currentPeriod}<br>";

// 计算开始和结束时间
$startTime = $_t1 + ($actNo - 1) * $_expecttime;
$endTime = $_t1 + $actNo * $_expecttime;

echo "开始时间: " . date('Y-m-d H:i:s', $startTime) . "<br>";
echo "结束时间: " . date('Y-m-d H:i:s', $endTime) . "<br>";

// 计算倒计时
$remainTime = $endTime - $nowtime - $cjnowtime - 15;
echo "<h2>倒计时</h2>";
echo "剩余秒数: {$remainTime}秒<br>";
echo "倒计时: " . gmdate('i:s', max(0, $remainTime)) . "<br>";

// 上一期
$prevPeriod = ($actNo == 1) 
    ? date('Ymd', strtotime('-1 day')) . str_pad($totalcount, $_length, '0', STR_PAD_LEFT)
    : date('Ymd') . str_pad($actNo - 1, $_length, '0', STR_PAD_LEFT);

echo "<h2>上一期</h2>";
echo "上期期号: {$prevPeriod}<br>";

// 查询开奖
$stmt = $pdo->prepare("SELECT expect, opencode, opentime FROM {$prefix}kaijiang WHERE name = :cpname AND expect = :expect");
$stmt->execute([':cpname' => $cpname, ':expect' => $prevPeriod]);
$lastResult = $stmt->fetch();

if ($lastResult) {
    echo "开奖号码: " . $lastResult['opencode'] . "<br>";
    echo "开奖时间: " . date('Y-m-d H:i:s', $lastResult['opentime']) . "<br>";
} else {
    echo "<span style='color:red;'>未找到开奖数据</span><br>";
    
    // 查询最近一条
    $stmt = $pdo->prepare("SELECT expect, opencode, opentime FROM {$prefix}kaijiang WHERE name = :cpname ORDER BY id DESC LIMIT 1");
    $stmt->execute([':cpname' => $cpname]);
    $latestResult = $stmt->fetch();
    
    if ($latestResult) {
        echo "<br><strong>最近开奖:</strong><br>";
        echo "期号: " . $latestResult['expect'] . "<br>";
        echo "号码: " . $latestResult['opencode'] . "<br>";
        echo "时间: " . date('Y-m-d H:i:s', $latestResult['opentime']) . "<br>";
    }
}

// 测试连续几期
echo "<h2>连续期号测试</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>期数</th><th>期号</th><th>开始时间</th><th>结束时间</th></tr>";
for ($i = max(1, $actNo - 2); $i <= min($totalcount, $actNo + 2); $i++) {
    $period = date('Ymd') . str_pad($i, $_length, '0', STR_PAD_LEFT);
    $start = date('H:i:s', $_t1 + ($i - 1) * $_expecttime);
    $end = date('H:i:s', $_t1 + $i * $_expecttime);
    $highlight = ($i == $actNo) ? ' style="background:#ffffcc;"' : '';
    echo "<tr{$highlight}><td>{$i}</td><td>{$period}</td><td>{$start}</td><td>{$end}</td></tr>";
}
echo "</table>";
?>


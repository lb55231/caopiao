<?php
/**
 * 生成缺失的开奖数据
 * 用于补充数据库中缺少的期号开奖数据
 */
require_once __DIR__ . '/common/Database.php';

$pdo = Database::getInstance();
$prefix = Database::getPrefix();

// 获取彩票配置
$stmt = $pdo->query("SELECT * FROM {$prefix}caipiao WHERE name='hebk3'");
$cpinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cpinfo) {
    die("彩票hebk3不存在\n");
}

echo "彩票信息: {$cpinfo['title']}\n";
echo "开盘时间: {$cpinfo['closetime1']}\n";
echo "封盘时间: {$cpinfo['closetime2']}\n";
echo "每期时长: {$cpinfo['expecttime']}分钟\n";
echo "==================\n\n";

// 获取最新的开奖记录
$stmt = $pdo->query("SELECT * FROM {$prefix}kaijiang WHERE name='hebk3' ORDER BY id DESC LIMIT 1");
$lastKaijiang = $stmt->fetch(PDO::FETCH_ASSOC);

if ($lastKaijiang) {
    echo "最新开奖期号: {$lastKaijiang['expect']}\n";
    echo "最新开奖号码: {$lastKaijiang['opencode']}\n";
    echo "最新开奖时间: " . date('Y-m-d H:i:s', $lastKaijiang['opentime']) . "\n\n";
} else {
    echo "数据库中没有开奖记录\n\n";
}

// 计算需要补充的期号
$expecttime = intval($cpinfo['expecttime']); // 每期时长（分钟）
$_expecttime = $expecttime * 60; // 转为秒

$closetime1 = $cpinfo['closetime1'];
$closetime2 = $cpinfo['closetime2'];

// 计算总期数
$_t1 = strtotime(date('Y-m-d ') . $closetime1);
$_t2 = strtotime(date('Y-m-d ') . $closetime2);
$totalcount = floor(abs($_t2 - $_t1) / $_expecttime);
$_length = $totalcount >= 1000 ? 4 : 3;

echo "每天总期数: {$totalcount}\n";
echo "期号长度: {$_length}\n\n";

// 生成最近3天的所有期号开奖数据
$generated = 0;
$skipped = 0;

for ($day = 2; $day >= 0; $day--) {
    $date = date('Ymd', strtotime("-{$day} days"));
    echo "生成 {$date} 的开奖数据...\n";
    
    for ($i = 1; $i <= $totalcount; $i++) {
        $expect = $date . str_pad($i, $_length, '0', STR_PAD_LEFT);
        
        // 检查是否已存在
        $stmt = $pdo->prepare("SELECT id FROM {$prefix}kaijiang WHERE name='hebk3' AND expect=:expect");
        $stmt->execute([':expect' => $expect]);
        
        if ($stmt->fetch()) {
            $skipped++;
            continue;
        }
        
        // 生成随机开奖号码 (1-6的三个数字)
        $code1 = rand(1, 6);
        $code2 = rand(1, 6);
        $code3 = rand(1, 6);
        $opencode = "{$code1},{$code2},{$code3}";
        
        // 计算开奖时间
        $opentime = strtotime($date . ' ' . $closetime1) + ($i - 1) * $_expecttime;
        
        // 如果是未来的时间，跳过
        if ($opentime > time()) {
            continue;
        }
        
        // 插入数据
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}kaijiang 
            (addtime, name, title, opencode, sourcecode, remarks, source, expect, opentime, isdraw, drawtime) 
            VALUES 
            (:addtime, :name, :title, :opencode, :sourcecode, :remarks, :source, :expect, :opentime, :isdraw, :drawtime)
        ");
        
        $result = $stmt->execute([
            ':addtime' => time(),
            ':name' => 'hebk3',
            ':title' => $cpinfo['title'],
            ':opencode' => $opencode,
            ':sourcecode' => '',
            ':remarks' => '',
            ':source' => '系统生成',
            ':expect' => $expect,
            ':opentime' => $opentime,
            ':isdraw' => 0,
            ':drawtime' => $opentime
        ]);
        
        if ($result) {
            $generated++;
            echo "  ✓ {$expect}: {$opencode}\n";
        }
    }
}

echo "\n==================\n";
echo "生成完成！\n";
echo "新增: {$generated} 条\n";
echo "跳过: {$skipped} 条\n";

// 显示最新5条
echo "\n最新5条开奖记录：\n";
$stmt = $pdo->query("SELECT expect, opencode FROM {$prefix}kaijiang WHERE name='hebk3' ORDER BY id DESC LIMIT 5");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $codes = explode(',', $row['opencode']);
    $sum = array_sum($codes);
    echo "  期号: {$row['expect']}, 开奖: {$row['opencode']}, 和值: {$sum}\n";
}


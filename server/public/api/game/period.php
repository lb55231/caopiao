<?php
/**
 * 获取彩票期号API
 * 完全按照原项目 wap.com/app/Api/Controller/LotteryController.class.php 的 lotterytimes 方法实现
 */

$cpname = $_GET['cpname'] ?? '';

if (empty($cpname)) {
    Database::error('缺少彩票名称参数', 400);
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 1. 查询彩票配置（对应原项目的 M('caipiao')->where(["name"=>$shortName])->cache(300)->find()）
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}caipiao WHERE name = :name AND isopen = 1");
    $stmt->execute([':name' => $cpname]);
    $cpinfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cpinfo) {
        Database::error('彩种不存在或已关闭', 404);
    }
    
    // 2. 计算期号（完全按照 hebk3.class.php 的 drawtimes 方法）
    $nowtime = time();
    $cjnowtime = intval($cpinfo['ftime']); // 封盘提前时间（秒）
    $expecttime = intval($cpinfo['expecttime']); // 每期时长（分钟）
    $_expecttime = $expecttime * 60; // 转为秒
    
    // 计算总期数
    $closetime1 = $cpinfo['closetime1']; // 例如: 00:00:00
    $closetime2 = $cpinfo['closetime2']; // 例如: 23:55:00
    $_t1 = strtotime(date('Y-m-d ') . $closetime1);
    $_t2 = strtotime(date('Y-m-d ') . $closetime2);
    $totalcount = floor(abs($_t2 - $_t1) / $_expecttime);
    $_length = $totalcount >= 1000 ? 4 : 3;
    $jgtime = $expecttime * 60;
    
    $_t = time();
    
    // 计算当前期号（原逻辑）
    if ($_t < $_t1) {
        $actNo_t = $totalcount;
    } else {
        $actNo_t = (time() - strtotime(date('Y-m-d ') . $closetime1) + $cjnowtime) / $_expecttime;
    }
    $actNo_t = floor($actNo_t);
    $actNo = is_numeric($actNo_t) ? ($actNo_t == $totalcount ? 1 : $actNo_t + 1) : ceil($actNo_t);
    
    // 当前期信息
    $nowdraws = [
        'expect' => date('Ymd') . str_pad($actNo, $_length, 0, STR_PAD_LEFT),
        'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + ($actNo - 1) * $_expecttime),
        'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + $actNo * $_expecttime),
    ];
    
    // 上期信息
    if (intval($actNo) == 1) {
        // 第一期，上期是昨天最后一期
        $nowdraws = [
            'expect' => date('Ymd') . str_pad($actNo, $_length, 0, STR_PAD_LEFT),
            'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1)),
            'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + $_expecttime),
        ];
        $preqihao = str_pad($totalcount, $_length, 0, STR_PAD_LEFT);
        $predraws = [
            'expect' => date('Ymd', strtotime($nowdraws['end']) - 86400) . $preqihao,
            'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) - $jgtime),
            'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1)),
        ];
    } else {
        // 普通期
        $preqihao = str_pad($actNo - 1, $_length, 0, STR_PAD_LEFT);
        $predraws = [
            'expect' => date('Ymd', strtotime($nowdraws['end'])) . $preqihao,
            'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + ($actNo - 2) * ($expecttime * 60)),
            'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + ($actNo - 1) * ($expecttime * 60)),
        ];
    }
    
    // 处理最后一期或已过结束时间
    if ($actNo >= $totalcount) {
        if ($_t < $_t1) {
            // 凌晨时段（还未到开始时间）
            $nowdraws = [
                'expect' => date('Ymd') . str_pad($totalcount, $_length, 0, STR_PAD_LEFT),
                'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1)),
                'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + $_expecttime),
            ];
            $preqihao = str_pad($totalcount, $_length, 0, STR_PAD_LEFT);
            $predraws = [
                'expect' => date('Ymd', strtotime($nowdraws['end']) - 86400) . $preqihao,
                'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) - $jgtime),
                'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1)),
            ];
        } else {
            // 已过结束时间，显示明天第一期
            $nowdraws = [
                'expect' => date('Ymd', time() + 86400) . str_pad(1, $_length, 0, STR_PAD_LEFT),
                'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime2)),
                'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + $_expecttime + 86400),
            ];
            $preqihao = str_pad($totalcount, $_length, 0, STR_PAD_LEFT);
            $predraws = [
                'expect' => date('Ymd', strtotime($nowdraws['end']) - 86400) . $preqihao,
                'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime2) - $jgtime),
                'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime2)),
            ];
        }
    }
    
    // 计算剩余时间（原逻辑：提前 ftime + 15秒封盘）
    $remainTime = strtotime($nowdraws['end']) - $nowtime - $cjnowtime - 15;
    
    // 3. 查询上期开奖结果
    $stmt = $pdo->prepare("
        SELECT expect, opencode, opentime 
        FROM {$prefix}kaijiang 
        WHERE name = :name 
          AND expect = :expect
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([
        ':name' => $cpname,
        ':expect' => $predraws['expect']
    ]);
    $lastResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 如果没有找到上期开奖，查询最近一条
    if (!$lastResult) {
        $stmt = $pdo->prepare("
            SELECT expect, opencode, opentime 
            FROM {$prefix}kaijiang 
            WHERE name = :name 
              AND opencode IS NOT NULL 
              AND opencode != ''
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute([':name' => $cpname]);
        $lastResult = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 4. 返回数据（完全按照原项目格式）
    $result = [
        'lastFullExpect'  => $predraws['expect'],
        'lastExpect'      => substr($predraws['expect'], -$_length),
        'currFullExpect'  => $nowdraws['expect'],
        'currExpect'      => substr($nowdraws['expect'], -$_length),
        'remainTime'      => max(0, $remainTime), // 剩余秒数
        'openRemainTime'  => $cjnowtime, // 封盘提前时间
        'shortname'       => $cpinfo['title'],
        'status'          => $cpinfo['isopen'],
        // 额外信息（给前端使用）
        'nowdraws'        => $nowdraws,
        'predraws'        => $predraws,
        'lastResult'      => [
            'expect'   => $lastResult['expect'] ?? $predraws['expect'],
            'opencode' => $lastResult['opencode'] ?? '',
            'opentime' => $lastResult['opentime'] ?? 0,
        ],
    ];
    
    Database::success('获取成功', $result);
    
} catch (PDOException $e) {
    Database::error('查询失败：' . $e->getMessage(), 500);
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage(), 500);
}

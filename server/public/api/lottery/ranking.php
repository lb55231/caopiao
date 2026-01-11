<?php
/**
 * 收益榜API
 * 显示近7天中奖用户收益排行
 */

// 获取数据库连接
$pdo = Database::getInstance();
$prefix = Database::getPrefix();

try {
    // 检查投注表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}touzhu'");
    $tableExists = $stmt->fetch();
    
    $list = [];
    
    if ($tableExists) {
        // 计算7天前的时间
        $time = time() - (1 * 24 * 60 * 60 * 7);
        $day = date("Y-m-d", $time);
        $StartTime = strtotime($day . ' 00:00:00');
        $EndTime = time();
        
        // 查询近7天的中奖记录，按用户分组统计
        $stmt = $pdo->prepare("
            SELECT 
                MAX(cptitle) as k3name,
                username,
                SUM(okamount) as okamount
            FROM {$prefix}touzhu 
            WHERE oddtime >= :starttime 
              AND oddtime <= :endtime 
              AND isdraw = 1 
              AND cpname IN ('f5k3', 'hebk3')
            GROUP BY uid, username
            ORDER BY okamount DESC 
            LIMIT 50
        ");
        
        $stmt->execute([
            ':starttime' => $StartTime,
            ':endtime' => $EndTime
        ]);
        
        $list = $stmt->fetchAll();
    }
    
    // 处理数据：隐藏部分用户名
    foreach ($list as $key => $item) {
        // 隐藏用户名中间部分
        $username = $item['username'];
        $len = mb_strlen($username, 'utf-8');
        if ($len > 3) {
            $list[$key]['username'] = mb_substr($username, 0, 1, 'utf-8') . '***' . mb_substr($username, -1, 1, 'utf-8');
        } else {
            $list[$key]['username'] = $username[0] . '***';
        }
        
        // 格式化金额
        $list[$key]['okamount'] = number_format($item['okamount'], 2, '.', '');
    }
    
    // 如果数据不足，生成一些随机数据（与原项目逻辑一致）
    if (count($list) < 30) {
        $list = array_merge($list, generateRandomRanking(30 - count($list)));
    }
    
    Database::success('获取成功', $list);
    
} catch (PDOException $e) {
    Database::error('查询失败：' . $e->getMessage(), 500);
}

/**
 * 生成随机排行数据（与原项目randking函数逻辑一致）
 */
function generateRandomRanking($count) {
    $result = [];
    
    $k3names = ['重庆时时彩', '福彩快3', '北京PK10', '幸运28', '广东11选5'];
    
    for ($i = 0; $i < $count; $i++) {
        $numCount = rand(4, 6);
        $username = '';
        for ($j = 0; $j < $numCount; $j++) {
            $username .= chr(rand(97, 122)); // 随机小写字母
        }
        $username = substr_replace($username, '***', -3, 3);
        
        $result[] = [
            'username' => $username,
            'k3name' => $k3names[rand(0, count($k3names) - 1)],
            'okamount' => number_format(rand(100, 99999) + rand(0, 99) / 100, 2, '.', '')
        ];
    }
    
    return $result;
}


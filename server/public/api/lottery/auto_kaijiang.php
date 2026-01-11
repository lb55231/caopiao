<?php
/**
 * 自动开奖API
 * 用于补充缺失的期号开奖数据
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../common/Database.php';

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();

    // 获取所有系统彩票（issys=1且启用isopen=1）
    $stmt = $pdo->query("SELECT * FROM {$prefix}caipiao WHERE issys=1 AND isopen=1 ORDER BY listorder ASC");
    $lotteryList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($lotteryList)) {
        Database::error('没有可用的系统彩票');
    }

    $totalGenerated = 0;
    $totalSkipped = 0;
    $allDetails = [];

    // 遍历每个系统彩票
    foreach ($lotteryList as $cpinfo) {
        $cpname = $cpinfo['name'];
        $cptitle = $cpinfo['title'];
        $typeid = $cpinfo['typeid'];
        
        // 计算期号参数
        $expecttime = intval($cpinfo['expecttime']); // 每期时长（分钟）
        $_expecttime = $expecttime * 60; // 转为秒
        $closetime1 = $cpinfo['closetime1'];
        $closetime2 = $cpinfo['closetime2'];

        // 计算总期数
        $_t1 = strtotime(date('Y-m-d ') . $closetime1);
        $_t2 = strtotime(date('Y-m-d ') . $closetime2);
        $totalcount = floor(abs($_t2 - $_t1) / $_expecttime);
        $_length = $totalcount >= 1000 ? 4 : 3;

        $generated = 0;
        $skipped = 0;

        // 只生成今天的缺失数据
        $date = date('Ymd');
        
        for ($i = 1; $i <= $totalcount; $i++) {
            $expect = $date . str_pad($i, $_length, '0', STR_PAD_LEFT);
            
            // 计算开奖时间
            $opentime = strtotime($date . ' ' . $closetime1) + ($i - 1) * $_expecttime;
            
            // 如果是未来的时间，跳过
            if ($opentime > time()) {
                continue;
            }
            
            // 检查是否已存在
            $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}kaijiang WHERE name=:name AND expect=:expect");
            $checkStmt->execute([':name' => $cpname, ':expect' => $expect]);
            
            if ($checkStmt->fetch()) {
                $skipped++;
                continue;
            }
            
            // 根据彩票类型生成开奖号码
            $opencode = generateOpenCode($typeid, $cpname);
            
            // 插入数据
            $insertStmt = $pdo->prepare("
                INSERT INTO {$prefix}kaijiang 
                (addtime, name, title, opencode, sourcecode, remarks, source, expect, opentime, isdraw, drawtime) 
                VALUES 
                (:addtime, :name, :title, :opencode, :sourcecode, :remarks, :source, :expect, :opentime, :isdraw, :drawtime)
            ");
            
            $result = $insertStmt->execute([
                ':addtime' => time(),
                ':name' => $cpname,
                ':title' => $cptitle,
                ':opencode' => $opencode,
                ':sourcecode' => '',
                ':remarks' => '',
                ':source' => '系统自动',
                ':expect' => $expect,
                ':opentime' => $opentime,
                ':isdraw' => 0,
                ':drawtime' => $opentime
            ]);
            
            if ($result) {
                $generated++;
            }
        }
        
        $totalGenerated += $generated;
        $totalSkipped += $skipped;
        
        if ($generated > 0) {
            $allDetails[] = [
                'cpname' => $cpname,
                'title' => $cptitle,
                'generated' => $generated,
                'skipped' => $skipped
            ];
        }
    }

    // 获取最新开奖结果（每个彩票的最新5条）
    $latestResults = [];
    foreach ($lotteryList as $cpinfo) {
        $cpname = $cpinfo['name'];
        $stmt = $pdo->prepare("SELECT name, title, expect, opencode FROM {$prefix}kaijiang WHERE name = :name ORDER BY id DESC LIMIT 5");
        $stmt->execute([':name' => $cpname]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($results)) {
            $latestResults[$cpname] = [
                'title' => $cpinfo['title'],
                'results' => $results
            ];
        }
    }

    Database::success('自动开奖完成', [
        'total_generated' => $totalGenerated,
        'total_skipped' => $totalSkipped,
        'lottery_count' => count($lotteryList),
        'details' => $allDetails,
        'latest_results' => $latestResults,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}

/**
 * 根据彩票类型生成开奖号码
 */
function generateOpenCode($typeid, $cpname) {
    switch ($typeid) {
        case 'k3': // 快3类彩票
            $code1 = rand(1, 6);
            $code2 = rand(1, 6);
            $code3 = rand(1, 6);
            return "{$code1},{$code2},{$code3}";
            
        case 'ssc': // 时时彩类彩票
            $codes = [];
            for ($i = 0; $i < 5; $i++) {
                $codes[] = rand(0, 9);
            }
            return implode(',', $codes);
            
        case 'fc3d': // 福彩3D
            $code1 = rand(0, 9);
            $code2 = rand(0, 9);
            $code3 = rand(0, 9);
            return "{$code1},{$code2},{$code3}";
            
        case 'pk10': // PK10
            $numbers = range(1, 10);
            shuffle($numbers);
            return implode(',', $numbers);
            
        case '11x5': // 11选5
            $numbers = range(1, 11);
            shuffle($numbers);
            $selected = array_slice($numbers, 0, 5);
            sort($selected);
            return implode(',', $selected);
            
        default:
            // 默认生成3个1-6的号码
            return rand(1, 6) . ',' . rand(1, 6) . ',' . rand(1, 6);
    }
}


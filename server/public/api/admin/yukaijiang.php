<?php
/**
 * 设置开奖（预开奖管理）- 获取待开奖期号列表
 * 完全按照老项目 CaipiaoController::yukaijiang 实现
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// 验证Token
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

$jwt = new Jwt();
$adminInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$adminInfo) {
    Database::error('Token无效或已过期', 401);
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 获取所有开启的彩种
    $stmt = $pdo->query("SELECT * FROM {$prefix}caipiao WHERE isopen = 1 ORDER BY typeid DESC");
    $cplist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 过滤掉某些彩种（和老项目一致）
    $my_list = [];
    foreach ($cplist as $v) {
        if (!in_array($v['name'], ['lhc', 'fc3d', 'pl3', 'jxk3'])) {
            $my_list[] = $v;
        }
    }
    
    //获取请求的彩种
    $name = $_GET['name'] ?? '';
    if (!$name && count($my_list) > 0) {
        $name = $my_list[0]['name'];
    }
    
    // 查找当前彩种信息
    $cpinfo = null;
    foreach ($cplist as $v) {
        if ($v['name'] === $name) {
            $cpinfo = $v;
            break;
        }
    }
    
    if (!$cpinfo) {
        Database::error('彩种不存在');
    }
    
    $typeid = $cpinfo['typeid'];
    $expecttime = $cpinfo['expecttime'];
    $_expecttime = $expecttime * 60;
    $totalcount = floor(abs(strtotime($cpinfo['closetime2']) - strtotime($cpinfo['closetime1'])) / $_expecttime);
    $_length = $totalcount >= 1000 ? 4 : 3;
    $jgtime = $expecttime * 60;
    $_t = time();
    $_t1 = strtotime(date('Y-m-d ') . $cpinfo['closetime1']);
    
    // 计算当前期号
    if ($_t < $_t1) {
        $actNo_t = $totalcount;
    } else {
        $actNo_t = ($_t - strtotime(date('Y-m-d ') . $cpinfo['closetime1']) + intval($cpinfo['ftime'])) / $_expecttime;
    }
    $actNo_t = floor($actNo_t);
    $actNo = is_numeric($actNo_t) ? ($actNo_t == $totalcount ? 1 : $actNo_t + 1) : ceil($actNo_t);
    
    // 生成待开奖期号列表（未来20期）
    $openlist = [];
    
    if ($cpinfo['issys'] == 1) {
        // 系统彩
        if ($actNo > $totalcount) {
            if ($_t > strtotime($cpinfo['closetime2'])) {
                // 跨天到明天
                for ($j = 20; $j >= 1; $j--) {
                    $rand_keys = returnrankey($cpinfo['typeid']);
                    if ($cpinfo['typeid'] == 'k3' || $cpinfo['typeid'] == 'keno') sort($rand_keys);
                    $opentime = date('Y-m-d H:i:s', strtotime($cpinfo['closetime1']) + $j * $jgtime + 86400);
                    $expect = str_pad($j, $_length, 0, STR_PAD_LEFT);
                    $openlist[] = [
                        'expect' => date('Ymd') . $expect,
                        'opencode' => implode(',', $rand_keys),
                        'opentime' => $opentime,
                        'cptitle' => $cpinfo['title'],
                        'name' => $cpinfo['name']
                    ];
                }
            } else {
                for ($j = 20; $j >= 1; $j--) {
                    $rand_keys = returnrankey($cpinfo['typeid']);
                    if ($cpinfo['typeid'] == 'k3' || $cpinfo['typeid'] == 'keno') sort($rand_keys);
                    $opentime = date('Y-m-d H:i:s', strtotime($cpinfo['closetime1']) + $j * $jgtime);
                    $expect = str_pad($j, $_length, 0, STR_PAD_LEFT);
                    $openlist[] = [
                        'expect' => date('Ymd') . $expect,
                        'opencode' => implode(',', $rand_keys),
                        'opentime' => $opentime,
                        'cptitle' => $cpinfo['title'],
                        'name' => $cpinfo['name']
                    ];
                }
            }
        } else {
            if ($actNo + 19 <= $totalcount) {
                for ($j = $actNo + 19; $j >= $actNo; $j--) {
                    $rand_keys = returnrankey($cpinfo['typeid']);
                    if ($cpinfo['typeid'] == 'k3' || $cpinfo['typeid'] == 'keno') sort($rand_keys);
                    $opentime = date('Y-m-d H:i:s', strtotime($cpinfo['closetime2']) - ($totalcount - $j) * $jgtime);
                    $expect = str_pad($j, $_length, 0, STR_PAD_LEFT);
                    $openlist[] = [
                        'expect' => date('Ymd') . $expect,
                        'opencode' => implode(',', $rand_keys),
                        'opentime' => $opentime,
                        'cptitle' => $cpinfo['title'],
                        'name' => $cpinfo['name']
                    ];
                }
            } else {
                for ($j = $totalcount; $j >= $actNo; $j--) {
                    $rand_keys = returnrankey($cpinfo['typeid']);
                    if ($cpinfo['typeid'] == 'k3' || $cpinfo['typeid'] == 'keno') sort($rand_keys);
                    $opentime = date('Y-m-d H:i:s', strtotime($cpinfo['closetime2']) - ($totalcount - $j) * $jgtime);
                    $expect = str_pad($j, $_length, 0, STR_PAD_LEFT);
                    $openlist[] = [
                        'expect' => date('Ymd') . $expect,
                        'opencode' => implode(',', $rand_keys),
                        'opentime' => $opentime,
                        'cptitle' => $cpinfo['title'],
                        'name' => $cpinfo['name']
                    ];
                }
            }
        }
    }
    
    // 查询预开奖表，标记已保存的期号
    $yukaijiangStmt = $pdo->prepare("SELECT * FROM {$prefix}yukaijiang WHERE name = :name AND hid = 0");
    $yukaijiangStmt->execute([':name' => $name]);
    $yukaijiangList = $yukaijiangStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $yukaijiangMap = [];
    foreach ($yukaijiangList as $ykj) {
        $yukaijiangMap[$ykj['expect']] = $ykj;
    }
    
    foreach ($openlist as $k => $v) {
        $v['isbc'] = 0;
        $v['stateadmin'] = '';
        if (isset($yukaijiangMap[$v['expect']])) {
            $v['opencode'] = $yukaijiangMap[$v['expect']]['opencode'];
            $v['isbc'] = 1;
            $v['stateadmin'] = $yukaijiangMap[$v['expect']]['stateadmin'] ?? '';
        }
        $openlist[$k] = $v;
    }
    
    // 按期号排序
    usort($openlist, function($a, $b) {
        return strcmp($a['expect'], $b['expect']);
    });
    
    Database::success('获取成功', [
        'cplist' => $my_list,
        'openlist' => $openlist,
        'typeid' => $typeid,
        'cpname' => $name
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}

// 辅助函数：生成随机号码
function returnrankey($typeid) {
    $rand_keys = [];
    switch ($typeid) {
        case 'k3':
            $rand_keys = explode(',', rand_keys('3', '123456'));
            break;
        case 'ssc':
            $rand_keys = explode(',', rand_keys('5', '0123456789'));
            break;
        case 'x5':
            $rand_keys = explode(',', rand_keys_x('5', '01,02,03,04,05,06,07,08,09,10,11'));
            break;
        case 'pk10':
            $rand_keys = explode(',', rand_keys_x('10', '01,02,03,04,05,06,07,08,09,10'));
            break;
        case 'keno':
            $num = '';
            for ($i = 1; $i <= 80; $i++) {
                if ($i < 10) $i = '0' . $i;
                $num .= $i . ',';
            }
            $num = substr($num, 0, -1);
            $rand_keys = explode(',', rand_keys_x('20', $num));
            break;
        case 'dpc':
            $rand_keys = explode(',', rand_keys('3', '0123456789'));
            break;
        case 'lhc':
            $rand_keys = explode(',', rand_keys_x('7', '01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49'));
            break;
    }
    return $rand_keys;
}

function rand_keys($count, $chars) {
    $result = [];
    for ($i = 0; $i < $count; $i++) {
        $result[] = $chars[rand(0, strlen($chars) - 1)];
    }
    return implode(',', $result);
}

function rand_keys_x($count, $nums) {
    $arr = explode(',', $nums);
    shuffle($arr);
    return implode(',', array_slice($arr, 0, $count));
}


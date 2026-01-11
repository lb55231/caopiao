<?php
namespace app\api\service;

/**
 * 游戏服务类
 */
class GameService
{
    /**
     * 获取期号信息（完全按照原逻辑）
     */
    public function getPeriod($cpname)
    {
        $db = \think\facade\Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        // 查询彩票配置
        $cpinfo = $db->table($prefix . 'caipiao')
            ->where('name', $cpname)
            ->where('isopen', 1)
            ->find();
        
        if (!$cpinfo) {
            throw new \Exception('彩种不存在或已关闭');
        }
        
        // 计算期号
        $nowtime = time();
        $cjnowtime = intval($cpinfo['ftime']);
        $expecttime = intval($cpinfo['expecttime']);
        $_expecttime = $expecttime * 60;
        
        $closetime1 = $cpinfo['closetime1'];
        $closetime2 = $cpinfo['closetime2'];
        $_t1 = strtotime(date('Y-m-d ') . $closetime1);
        $_t2 = strtotime(date('Y-m-d ') . $closetime2);
        $totalcount = floor(abs($_t2 - $_t1) / $_expecttime);
        $_length = $totalcount >= 1000 ? 4 : 3;
        $jgtime = $expecttime * 60;
        
        $_t = time();
        
        // 计算当前期号
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
            $preqihao = str_pad($actNo - 1, $_length, 0, STR_PAD_LEFT);
            $predraws = [
                'expect' => date('Ymd', strtotime($nowdraws['end'])) . $preqihao,
                'start'  => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + ($actNo - 2) * ($expecttime * 60)),
                'end'    => date('Y-m-d H:i:s', strtotime(date('Y-m-d ') . $closetime1) + ($actNo - 1) * ($expecttime * 60)),
            ];
        }
        
        // 处理最后一期
        if ($actNo >= $totalcount) {
            if ($_t < $_t1) {
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
        
        // 计算剩余时间
        $remainTime = strtotime($nowdraws['end']) - $nowtime - $cjnowtime - 15;
        
        // 查询上期开奖结果
        $lastResult = $db->table($prefix . 'kaijiang')
            ->where('name', $cpname)
            ->where('expect', $predraws['expect'])
            ->order('id', 'desc')
            ->find();
        
        if (!$lastResult) {
            $lastResult = $db->table($prefix . 'kaijiang')
                ->where('name', $cpname)
                ->where('opencode', '<>', '')
                ->whereNotNull('opencode')
                ->order('id', 'desc')
                ->find();
        }
        
        return [
            'lastFullExpect'  => $predraws['expect'],
            'lastExpect'      => substr($predraws['expect'], -$_length),
            'currFullExpect'  => $nowdraws['expect'],
            'currExpect'      => substr($nowdraws['expect'], -$_length),
            'remainTime'      => max(0, $remainTime),
            'openRemainTime'  => $cjnowtime,
            'shortname'       => $cpinfo['title'],
            'status'          => $cpinfo['isopen'],
            'nowdraws'        => $nowdraws,
            'predraws'        => $predraws,
            'lastResult'      => [
                'expect'   => $lastResult['expect'] ?? $predraws['expect'],
                'opencode' => $lastResult['opencode'] ?? '',
                'opentime' => $lastResult['opentime'] ?? 0,
            ],
        ];
    }
    
    /**
     * 获取玩法配置
     */
    public function getPlays()
    {
        // K3游戏的玩法配置
        return [
            'k3hzzx' => [
                'id' => 'k3hzzx',
                'name' => '和值',
                'description' => '投注三个号码之和',
                'minOdds' => 1.96,
                'maxOdds' => 215.0,
                'minBet' => 2,
                'numbers' => range(3, 18),
                'odds' => [
                    '3' => 215.0, '4' => 107.5, '5' => 53.75, '6' => 35.83,
                    '7' => 26.88, '8' => 23.32, '9' => 21.5, '10' => 19.35,
                    '11' => 19.35, '12' => 21.5, '13' => 23.32, '14' => 26.88,
                    '15' => 35.83, '16' => 53.75, '17' => 107.5, '18' => 215.0
                ]
            ],
            'k3hzbig' => [
                'id' => 'k3hzbig',
                'name' => '大',
                'description' => '和值11-17',
                'odds' => 1.96,
                'minBet' => 2
            ],
            'k3hzsmall' => [
                'id' => 'k3hzsmall',
                'name' => '小',
                'description' => '和值4-10',
                'odds' => 1.96,
                'minBet' => 2
            ],
            'k3hzodd' => [
                'id' => 'k3hzodd',
                'name' => '单',
                'description' => '和值为单数',
                'odds' => 1.96,
                'minBet' => 2
            ],
            'k3hzeven' => [
                'id' => 'k3hzeven',
                'name' => '双',
                'description' => '和值为双数',
                'odds' => 1.96,
                'minBet' => 2
            ],
        ];
    }
    
    /**
     * 投注（简化版，保留核心逻辑）
     */
    public function placeBet($userId, $cpname, $period, $bets)
    {
        $db = \think\facade\Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        // 验证用户
        $user = $db->table($prefix . 'member')->where('id', $userId)->find();
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 验证彩票
        $cpinfo = $db->table($prefix . 'caipiao')
            ->where('name', $cpname)
            ->where('isopen', 1)
            ->find();
        
        if (!$cpinfo) {
            throw new \Exception('彩种不存在或已关闭');
        }
        
        // 计算总金额
        $totalAmount = 0;
        foreach ($bets as $bet) {
            $totalAmount += floatval($bet['amount'] ?? 0);
        }
        
        if ($totalAmount <= 0) {
            throw new \Exception('投注金额必须大于0');
        }
        
        if ($user['balance'] < $totalAmount) {
            throw new \Exception('余额不足');
        }
        
        // 开始事务
        $db->startTrans();
        
        try {
            // 扣除余额
            $db->table($prefix . 'member')
                ->where('id', $userId)
                ->dec('balance', $totalAmount)
                ->update();
            
            // 插入投注记录
            foreach ($bets as $bet) {
                $db->table($prefix . 'touzhu')->insert([
                    'uid' => $userId,
                    'username' => $user['username'],
                    'cpname' => $cpname,
                    'cptitle' => $cpinfo['title'],
                    'expect' => $period,
                    'playid' => $bet['playid'] ?? '',
                    'tzcode' => $bet['code'] ?? '',
                    'amount' => floatval($bet['amount'] ?? 0),
                    'mode' => floatval($bet['odds'] ?? 1.96),
                    'isdraw' => 0,
                    'oddtime' => time(),
                ]);
            }
            
            $db->commit();
            
            return [
                'total_amount' => $totalAmount,
                'bet_count' => count($bets),
                'balance' => $user['balance'] - $totalAmount
            ];
            
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}

<?php
namespace app\api\service;

use app\api\model\Lottery;
use app\api\model\LotteryPeriod;
use app\api\model\LotteryResult;

/**
 * 彩票服务类
 */
class LotteryService
{
    /**
     * 获取彩票列表
     */
    public function getLotteryList()
    {
        // 直接使用数据库查询，与旧逻辑保持一致
        $db = \think\facade\Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        $list = $db->table($prefix . 'caipiao')
            ->where('isopen', 1)
            ->order('listorder', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();
        
        // 处理logo路径
        foreach ($list as &$lottery) {
            if (empty($lottery['logo'])) {
                $lottery['logo'] = '/images/lottery/' . $lottery['typeid'] . '.png';
            }
        }
        
        return $list;
    }
    
    /**
     * 获取彩票详情
     */
    public function getLotteryDetail($name)
    {
        $lottery = Lottery::where('cpname', $name)->find();
        
        if (!$lottery) {
            throw new \Exception('彩票不存在');
        }
        
        return [
            'id' => $lottery->id,
            'name' => $lottery->cpname,
            'typeid' => $lottery->typeid,
            'logo' => $this->generateLogoPath($lottery->typeid, $lottery->logo),
            'timelong' => $lottery->timelong,
            'closetime1' => $lottery->closetime1,
            'closetime2' => $lottery->closetime2,
            'state' => $lottery->state
        ];
    }
    
    /**
     * 获取当前期数
     */
    public function getCurrentPeriod($cpid)
    {
        $period = LotteryPeriod::where('cpid', $cpid)
            ->where('kjtime', '>', time())
            ->order('kjtime', 'asc')
            ->find();
        
        if (!$period) {
            throw new \Exception('暂无可投注期数');
        }
        
        return [
            'id' => $period->id,
            'qishu' => $period->qishu,
            'kjtime' => $period->kjtime,
            'cpid' => $period->cpid,
            'cpname' => $period->cpname,
            'typeid' => $period->typeid
        ];
    }
    
    /**
     * 获取历史开奖
     */
    public function getHistory($cpid, $limit = 10)
    {
        $list = LotteryResult::where('cpid', $cpid)
            ->order('opentime', 'desc')
            ->limit($limit)
            ->select();
        
        $result = [];
        foreach ($list as $item) {
            $result[] = [
                'id' => $item->id,
                'expect' => $item->expect,
                'opencode' => $item->opencode,
                'opentime' => $item->opentime,
                'cpname' => $item->cpname
            ];
        }
        
        return $result;
    }
    
    /**
     * 获取最新公告
     */
    public function getNotice()
    {
        $db = \think\facade\Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        $notice = $db->table($prefix . 'gonggao')
            ->where('user_id', 0)
            ->order('id', 'desc')
            ->find();
        
        if ($notice) {
            return [
                'id' => $notice['id'],
                'title' => $notice['title'],
                'oddtime' => $notice['oddtime']
            ];
        }
        
        // 默认公告
        return [
            'id' => 0,
            'title' => '欢迎光临！祝您游戏愉快！',
            'oddtime' => time()
        ];
    }
    
    /**
     * 获取收益排行榜（近7天）
     */
    public function getRanking()
    {
        $db = \think\facade\Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        // 计算7天前的时间
        $startTime = strtotime(date('Y-m-d', time() - 7 * 24 * 60 * 60) . ' 00:00:00');
        $endTime = time();
        
        // 查询近7天的中奖记录
        $list = $db->table($prefix . 'touzhu')
            ->field('MAX(cptitle) as k3name, username, SUM(okamount) as okamount')
            ->where('oddtime', '>=', $startTime)
            ->where('oddtime', '<=', $endTime)
            ->where('isdraw', 1)
            ->whereIn('cpname', ['f5k3', 'hebk3'])
            ->group('uid, username')
            ->order('okamount', 'desc')
            ->limit(50)
            ->select()
            ->toArray();
        
        // 处理数据：隐藏部分用户名
        foreach ($list as $key => $item) {
            $username = $item['username'];
            $len = mb_strlen($username, 'utf-8');
            if ($len > 3) {
                $list[$key]['username'] = mb_substr($username, 0, 1, 'utf-8') . '***' . mb_substr($username, -1, 1, 'utf-8');
            } else {
                $list[$key]['username'] = $username[0] . '***';
            }
            $list[$key]['okamount'] = number_format($item['okamount'], 2, '.', '');
        }
        
        // 如果数据不足，生成一些随机数据
        if (count($list) < 30) {
            $list = array_merge($list, $this->generateRandomRanking(30 - count($list)));
        }
        
        return $list;
    }
    
    /**
     * 生成随机排行数据
     */
    private function generateRandomRanking($count)
    {
        $result = [];
        $k3names = ['重庆时时彩', '福彩快3', '北京PK10', '幸运28', '广东11选5'];
        
        for ($i = 0; $i < $count; $i++) {
            $numCount = rand(4, 6);
            $username = '';
            for ($j = 0; $j < $numCount; $j++) {
                $username .= chr(rand(97, 122));
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
    
    /**
     * 自动开奖（补充缺失的开奖数据）
     */
    public function autoKaijiang()
    {
        $db = \think\facade\Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        // 获取所有系统彩票（issys=1且启用isopen=1）
        $lotteryList = $db->table($prefix . 'caipiao')
            ->where('issys', 1)
            ->where('isopen', 1)
            ->order('listorder', 'asc')
            ->select()
            ->toArray();
        
        if (empty($lotteryList)) {
            throw new \Exception('没有可用的系统彩票');
        }
        
        $totalGenerated = 0;
        $totalSkipped = 0;
        $allDetails = [];
        
        // 遍历每个系统彩票
        foreach ($lotteryList as $cpinfo) {
            $cpname = $cpinfo['name'];
            $cptitle = $cpinfo['title'];
            $typeid = $cpinfo['typeid'];
            
            $expecttime = intval($cpinfo['expecttime']);
            $_expecttime = $expecttime * 60;
            $closetime1 = $cpinfo['closetime1'];
            $closetime2 = $cpinfo['closetime2'];
            
            $_t1 = strtotime(date('Y-m-d ') . $closetime1);
            $_t2 = strtotime(date('Y-m-d ') . $closetime2);
            $totalcount = floor(abs($_t2 - $_t1) / $_expecttime);
            $_length = $totalcount >= 1000 ? 4 : 3;
            
            $generated = 0;
            $skipped = 0;
            $date = date('Ymd');
            
            for ($i = 1; $i <= $totalcount; $i++) {
                $expect = $date . str_pad($i, $_length, '0', STR_PAD_LEFT);
                $opentime = strtotime($date . ' ' . $closetime1) + ($i - 1) * $_expecttime;
                
                if ($opentime > time()) {
                    continue;
                }
                
                // 检查是否已存在
                $exists = $db->table($prefix . 'kaijiang')
                    ->where('name', $cpname)
                    ->where('expect', $expect)
                    ->find();
                
                if ($exists) {
                    $skipped++;
                    continue;
                }
                
                // 先查询预设的开奖号码
                $ykjData = $db->table($prefix . 'yukaijiang')
                    ->where('name', $cpname)
                    ->where('expect', $expect)
                    ->where('hid', 0)
                    ->find();
                
                if ($ykjData && !empty($ykjData['opencode'])) {
                    $opencode = $ykjData['opencode'];
                } else {
                    $opencode = $this->generateOpenCode($typeid, $cpname);
                }
                
                // 插入数据
                $result = $db->table($prefix . 'kaijiang')->insert([
                    'addtime' => time(),
                    'name' => $cpname,
                    'title' => $cptitle,
                    'opencode' => $opencode,
                    'sourcecode' => '',
                    'remarks' => '',
                    'source' => '系统自动',
                    'expect' => $expect,
                    'opentime' => $opentime,
                    'isdraw' => 0,
                    'drawtime' => $opentime
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
        
        // 获取最新开奖结果
        $latestResults = [];
        foreach ($lotteryList as $cpinfo) {
            $cpname = $cpinfo['name'];
            $results = $db->table($prefix . 'kaijiang')
                ->where('name', $cpname)
                ->field('name, title, expect, opencode')
                ->order('id', 'desc')
                ->limit(5)
                ->select()
                ->toArray();
            
            if (!empty($results)) {
                $latestResults[$cpname] = [
                    'title' => $cpinfo['title'],
                    'results' => $results
                ];
            }
        }
        
        return [
            'total_generated' => $totalGenerated,
            'total_skipped' => $totalSkipped,
            'lottery_count' => count($lotteryList),
            'details' => $allDetails,
            'latest_results' => $latestResults,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 自动结算
     */
    public function settlement()
    {
        $settlementService = new \app\api\service\SettlementService();
        return $settlementService->settlement();
    }
    
    /**
     * 根据彩票类型生成开奖号码
     */
    private function generateOpenCode($typeid, $cpname)
    {
        switch ($typeid) {
            case 'k3':
                return rand(1, 6) . ',' . rand(1, 6) . ',' . rand(1, 6);
            
            case 'ssc':
                $codes = [];
                for ($i = 0; $i < 5; $i++) {
                    $codes[] = rand(0, 9);
                }
                return implode(',', $codes);
            
            case 'fc3d':
                return rand(0, 9) . ',' . rand(0, 9) . ',' . rand(0, 9);
            
            case 'pk10':
                $numbers = range(1, 10);
                shuffle($numbers);
                return implode(',', $numbers);
            
            case '11x5':
                $numbers = range(1, 11);
                shuffle($numbers);
                $selected = array_slice($numbers, 0, 5);
                sort($selected);
                return implode(',', $selected);
            
            default:
                return rand(1, 6) . ',' . rand(1, 6) . ',' . rand(1, 6);
        }
    }
    
    /**
     * 生成logo路径
     */
    private function generateLogoPath($typeid, $logo)
    {
        if (!empty($logo)) {
            return $logo;
        }
        
        // 默认图标路径
        $iconMap = [
            1 => '/uploads/lottery/k3.png',
            2 => '/uploads/lottery/ssc.png',
            3 => '/uploads/lottery/11x5.png',
            4 => '/uploads/lottery/pk10.png',
            5 => '/uploads/lottery/lhc.png',
        ];
        
        return $iconMap[$typeid] ?? '/uploads/lottery/default.png';
    }
}


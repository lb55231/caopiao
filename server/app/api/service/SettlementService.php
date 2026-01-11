<?php
namespace app\api\service;

/**
 * 结算服务类
 * 包含彩票结算的所有逻辑
 */
class SettlementService
{
    /**
     * 执行自动结算
     */
    public function settlement()
    {
        $db = \think\facade\Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        // 获取所有未结算的期号（有开奖结果但投注未结算）
        $periods = $db->query("
            SELECT DISTINCT t.cpname, t.expect 
            FROM {$prefix}touzhu t
            INNER JOIN {$prefix}kaijiang k ON t.cpname = k.name AND t.expect = k.expect
            WHERE t.isdraw = 0 AND k.opencode IS NOT NULL AND k.opencode != ''
            LIMIT 10
        ");
        
        $settledCount = 0;
        $settledPeriods = [];
        
        foreach ($periods as $period) {
            $cpname = $period['cpname'];
            $expect = $period['expect'];
            
            // 获取该期开奖号码
            $kjResult = $db->table($prefix . 'kaijiang')
                ->where('name', $cpname)
                ->where('expect', $expect)
                ->find();
            
            if (!$kjResult || empty($kjResult['opencode'])) {
                continue;
            }
            
            $opencode = $kjResult['opencode'];
            $opencodes = explode(',', $opencode);
            
            // 获取该期所有未结算的投注
            $bets = $db->table($prefix . 'touzhu')
                ->where('cpname', $cpname)
                ->where('expect', $expect)
                ->where('isdraw', 0)
                ->select()
                ->toArray();
            
            // 开始事务
            $db->startTrans();
            
            try {
                foreach ($bets as $bet) {
                    $isWin = $this->checkWin($cpname, $bet['playid'], $bet['tzcode'], $opencodes, $opencode);
                    
                    if ($isWin) {
                        // 中奖
                        $rate = floatval($bet['mode']);
                        $winAmount = floatval($bet['amount']) * $rate;
                        
                        // 更新投注记录
                        $db->table($prefix . 'touzhu')
                            ->where('id', $bet['id'])
                            ->update([
                                'isdraw' => 1,
                                'okamount' => $winAmount,
                                'okcount' => 1,
                                'opencode' => $opencode
                            ]);
                        
                        // 更新用户余额
                        $user = $db->table($prefix . 'member')
                            ->where('id', $bet['uid'])
                            ->find();
                        
                        if ($user) {
                            $oldBalance = floatval($user['balance']);
                            $newBalance = $oldBalance + $winAmount;
                            
                            $db->table($prefix . 'member')
                                ->where('id', $bet['uid'])
                                ->update(['balance' => $newBalance]);
                            
                            // 插入账变记录
                            $trano = date('YmdHis') . rand(100, 999);
                            $remarkMap = ['大' => '普货', '小' => '精品', '单' => '一件', '双' => '多件'];
                            $displayCode = $remarkMap[$bet['tzcode']] ?? $bet['tzcode'];
                            
                            $db->table($prefix . 'fuddetail')->insert([
                                'uid' => $bet['uid'],
                                'username' => $bet['username'],
                                'type' => 'prize',
                                'typename' => '中奖',
                                'amount' => $winAmount,
                                'amountbefor' => $oldBalance,
                                'amountafter' => $newBalance,
                                'remark' => "期号{$expect} {$displayCode} 中奖",
                                'oddtime' => time(),
                                'trano' => $trano
                            ]);
                        }
                    } else {
                        // 未中奖
                        $db->table($prefix . 'touzhu')
                            ->where('id', $bet['id'])
                            ->update([
                                'isdraw' => -1,
                                'okamount' => 0,
                                'okcount' => 0,
                                'opencode' => $opencode
                            ]);
                    }
                }
                
                $db->commit();
                
                $settledCount += count($bets);
                $settledPeriods[] = [
                    'cpname' => $cpname,
                    'expect' => $expect,
                    'count' => count($bets),
                    'opencode' => $opencode
                ];
                
            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }
        }
        
        return [
            'settled_count' => $settledCount,
            'periods' => $settledPeriods
        ];
    }
    
    /**
     * 判断是否中奖
     */
    private function checkWin($cpname, $playid, $tzcode, $opencodes, $opencode)
    {
        $lotteryType = $this->getLotteryType($cpname);
        
        switch ($lotteryType) {
            case 'k3':
                return $this->checkK3Win($playid, $tzcode, $opencodes);
            case 'ssc':
                return $this->checkSSCWin($playid, $tzcode, $opencodes);
            case 'fc3d':
                return $this->check3DWin($playid, $tzcode, $opencodes);
            case 'pk10':
                return $this->checkPK10Win($playid, $tzcode, $opencodes);
            case '11x5':
                return $this->check11x5Win($playid, $tzcode, $opencodes);
            default:
                return $this->checkK3Win($playid, $tzcode, $opencodes);
        }
    }
    
    /**
     * 判断彩票类型
     */
    private function getLotteryType($cpname)
    {
        if (strpos($cpname, 'k3') !== false) return 'k3';
        if (strpos($cpname, 'ssc') !== false) return 'ssc';
        if (strpos($cpname, '3d') !== false || strpos($cpname, 'fc3d') !== false) return 'fc3d';
        if (strpos($cpname, 'pk10') !== false) return 'pk10';
        if (strpos($cpname, '11x5') !== false) return '11x5';
        return 'k3';
    }
    
    /**
     * K3类彩票中奖判断
     */
    private function checkK3Win($playid, $tzcode, $opencodes)
    {
        $sum = array_sum($opencodes);
        
        switch ($playid) {
            case 'k3hzbig':
                return ($sum > 10 && $tzcode == '大');
            case 'k3hzsmall':
                return ($sum <= 10 && $tzcode == '小');
            case 'k3hzodd':
                return ($sum % 2 != 0 && $tzcode == '单');
            case 'k3hzeven':
                return ($sum % 2 == 0 && $tzcode == '双');
            default:
                return false;
        }
    }
    
    /**
     * 时时彩类彩票中奖判断
     */
    private function checkSSCWin($playid, $tzcode, $opencodes)
    {
        $sum = array_sum($opencodes);
        
        if (strpos($playid, 'big') !== false) return $sum > 22;
        if (strpos($playid, 'small') !== false) return $sum <= 22;
        if (strpos($playid, 'odd') !== false) return $sum % 2 != 0;
        if (strpos($playid, 'even') !== false) return $sum % 2 == 0;
        
        return false;
    }
    
    /**
     * 福彩3D类彩票中奖判断
     */
    private function check3DWin($playid, $tzcode, $opencodes)
    {
        $sum = array_sum($opencodes);
        
        if (strpos($playid, 'big') !== false) return $sum > 13;
        if (strpos($playid, 'small') !== false) return $sum <= 13;
        if (strpos($playid, 'odd') !== false) return $sum % 2 != 0;
        if (strpos($playid, 'even') !== false) return $sum % 2 == 0;
        
        return false;
    }
    
    /**
     * PK10类彩票中奖判断
     */
    private function checkPK10Win($playid, $tzcode, $opencodes)
    {
        if (empty($opencodes)) return false;
        
        $first = intval($opencodes[0]);
        $second = isset($opencodes[1]) ? intval($opencodes[1]) : 0;
        $sum = $first + $second;
        
        if (strpos($playid, 'big') !== false) return $sum > 11;
        if (strpos($playid, 'small') !== false) return $sum <= 11;
        if (strpos($playid, 'odd') !== false) return $sum % 2 != 0;
        if (strpos($playid, 'even') !== false) return $sum % 2 == 0;
        
        return false;
    }
    
    /**
     * 11选5类彩票中奖判断
     */
    private function check11x5Win($playid, $tzcode, $opencodes)
    {
        if (empty($opencodes)) return false;
        
        $sum = array_sum($opencodes);
        
        if (strpos($playid, 'big') !== false) return $sum > 28;
        if (strpos($playid, 'small') !== false) return $sum <= 28;
        if (strpos($playid, 'odd') !== false) return $sum % 2 != 0;
        if (strpos($playid, 'even') !== false) return $sum % 2 == 0;
        
        return false;
    }
}

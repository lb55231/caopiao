<?php
namespace app\api\service;

use app\api\model\Bet;
use app\api\model\Member;
use app\api\model\FundDetail;
use app\api\model\LotteryPeriod;
use think\facade\Db;

/**
 * 投注服务类
 */
class BetService
{
    /**
     * 提交投注
     */
    public function submitBet($userId, $data)
    {
        // 获取用户信息
        $user = Member::find($userId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 检查余额
        $totalAmount = floatval($data['amount']) * intval($data['beishu']);
        if ($user->balance < $totalAmount) {
            throw new \Exception('余额不足');
        }
        
        // 检查期数
        $period = LotteryPeriod::find($data['period_id']);
        if (!$period) {
            throw new \Exception('期数不存在');
        }
        
        if ($period->kjtime <= time()) {
            throw new \Exception('本期已封盘');
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 扣除余额
            $oldBalance = $user->balance;
            $user->balance = $user->balance - $totalAmount;
            $user->save();
            
            // 生成订单号
            $trano = date('ymdHis') . rand(100000, 999999);
            
            // 创建投注记录
            $bet = Bet::create([
                'trano' => $trano,
                'uid' => $user->id,
                'username' => $user->username,
                'typeid' => $data['typeid'],
                'cpid' => $data['cpid'],
                'cpname' => $data['cpname'],
                'expect' => $period->qishu,
                'wanfa' => $data['wanfa'],
                'betcode' => $data['betcode'],
                'beishu' => $data['beishu'],
                'amount' => $totalAmount,
                'okamount' => 0,
                'odds' => $data['odds'] ?? 0,
                'isdraw' => 0,
                'oddtime' => time(),
                'opentime' => 0,
                'isnb' => 0
            ]);
            
            // 记录账变
            FundDetail::create([
                'trano' => $trano,
                'uid' => $user->id,
                'username' => $user->username,
                'type' => 'bet',
                'typename' => '投注',
                'amount' => -$totalAmount,
                'amountbefor' => $oldBalance,
                'amountafter' => $user->balance,
                'oddtime' => time(),
                'remark' => $data['cpname'] . ' ' . $period->qishu . '期投注',
                'expect' => $period->qishu,
                'status_show' => 1,
                'ticket_income_report' => 0
            ]);
            
            // 扣除洗码余额
            $oldXima = $user->xima;
            if ($oldXima > 0) {
                $ximaDeduct = min($totalAmount, $oldXima);
                $user->xima = $oldXima - $ximaDeduct;
                $user->save();
                
                // 记录洗码账变
                FundDetail::create([
                    'trano' => 'XM' . date('ymdHis') . rand(10, 99),
                    'uid' => $user->id,
                    'username' => $user->username,
                    'type' => 'xima',
                    'typename' => '洗码',
                    'amount' => -$ximaDeduct,
                    'amountbefor' => $oldXima,
                    'amountafter' => $user->xima,
                    'oddtime' => time(),
                    'remark' => "投注扣除洗码余额{$ximaDeduct}元",
                    'expect' => $period->qishu,
                    'status_show' => 1,
                    'ticket_income_report' => 0
                ]);
            }
            
            Db::commit();
            
            return [
                'id' => $bet->id,
                'trano' => $trano,
                'expect' => $period->qishu,
                'amount' => $totalAmount
            ];
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 批量提交投注
     */
    public function submitBatchBet($userId, $data)
    {
        // 获取用户信息
        $db = Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        $user = $db->table($prefix . 'member')->where('id', $userId)->find();
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 检查参数
        if (empty($data['bets']) || !is_array($data['bets'])) {
            throw new \Exception('投注数据格式错误');
        }
        
        $cpname = $data['cpname'];
        $cptitle = $data['cptitle'] ?? '';
        $period = $data['period'];
        $bets = $data['bets'];
        
        // 计算总金额
        $totalAmount = 0;
        foreach ($bets as $bet) {
            $totalAmount += floatval($bet['price']);
        }
        
        // 检查余额
        if ($user['balance'] < $totalAmount) {
            throw new \Exception('余额不足');
        }
        
        // 检查洗码余额
        if ($user['xima'] < $totalAmount) {
            throw new \Exception('洗码余额不足，请先充值');
        }
        
        // 检查期号是否还能投注
        $nowTime = time();
        $lottery = $db->table($prefix . 'caipiao')->where('name', $cpname)->find();
        if (!$lottery) {
            throw new \Exception('彩种不存在');
        }
        
        // 开启事务
        Db::startTrans();
        try {
            $insertData = [];
            $oldBalance = $user['balance'];
            $newBalance = $oldBalance - $totalAmount;
            
            // 生成订单数据
            foreach ($bets as $bet) {
                $trano = date('His') . time() . rand(1000000, 9999999);
                $price = floatval($bet['price']);
                
                $insertData[] = [
                    'isdraw' => -1,
                    'trano' => $trano,
                    'yjf' => 1,
                    'typeid' => $lottery['typeid'] ?? 'k3',
                    'playid' => $bet['playId'],
                    'playtitle' => $bet['playName'],
                    'cptitle' => $cptitle,
                    'cpname' => $cpname,
                    'expect' => $period,
                    'uid' => $userId,
                    'username' => $user['username'],
                    'itemcount' => intval($bet['count']),
                    'beishu' => intval($bet['multiple']),
                    'tzcode' => $bet['numbersText'],
                    'repoint' => 0,
                    'repointamout' => 0,
                    'mode' => 2,
                    'amount' => $price,
                    'amountbefor' => $oldBalance,
                    'amountafter' => $newBalance,
                    'okamount' => 0,
                    'okcount' => 0,
                    'Chase' => 0,
                    'stopChase' => 0,
                    'oddtime' => $nowTime,
                    'opencode' => '',
                    'source' => 'mobile',
                    'is_show' => 1,
                ];
                
                // 每条记录后更新余额基准
                $oldBalance = $newBalance;
            }
            
            // 批量插入投注记录
            $db->table($prefix . 'touzhu')->insertAll($insertData);
            
            // 更新用户余额
            $db->table($prefix . 'member')
                ->where('id', $userId)
                ->update([
                    'balance' => $newBalance,
                    'xima' => $user['xima'] - $totalAmount
                ]);
            
            // 记录账变
            $db->table($prefix . 'fuddetail')->insert([
                'trano' => 'BET' . date('ymdHis') . rand(1000, 9999),
                'uid' => $userId,
                'username' => $user['username'],
                'type' => 'bet',
                'typename' => '投注',
                'amount' => -$totalAmount,
                'amountbefor' => $user['balance'],
                'amountafter' => $newBalance,
                'oddtime' => $nowTime,
                'remark' => "{$cptitle} {$period}期投注",
                'expect' => $period,
                'status_show' => 1,
                'ticket_income_report' => 0
            ]);
            
            // 记录洗码账变
            $db->table($prefix . 'fuddetail')->insert([
                'trano' => 'XM' . date('ymdHis') . rand(10, 99),
                'uid' => $userId,
                'username' => $user['username'],
                'type' => 'xima',
                'typename' => '洗码',
                'amount' => -$totalAmount,
                'amountbefor' => $user['xima'],
                'amountafter' => $user['xima'] - $totalAmount,
                'oddtime' => $nowTime,
                'remark' => "投注扣除洗码余额{$totalAmount}元",
                'expect' => $period,
                'status_show' => 1,
                'ticket_income_report' => 0
            ]);
            
            Db::commit();
            
            return [
                'success' => true,
                'total_amount' => $totalAmount,
                'bet_count' => count($bets),
                'balance' => $newBalance
            ];
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 获取投注记录
     */
    public function getBetList($userId, $page = 1, $pageSize = 20)
    {
        $list = Bet::where('uid', $userId)
            ->order('oddtime', 'desc')
            ->page($page, $pageSize)
            ->select();
        
        $total = Bet::where('uid', $userId)->count();
        
        $result = [];
        foreach ($list as $item) {
            $result[] = [
                'id' => $item->id,
                'trano' => $item->trano,
                'cpname' => $item->cpname,
                'expect' => $item->expect,
                'wanfa' => $item->wanfa,
                'betcode' => $item->betcode,
                'beishu' => $item->beishu,
                'amount' => $item->amount,
                'okamount' => $item->okamount,
                'isdraw' => $item->isdraw,
                'oddtime' => $item->oddtime
            ];
        }
        
        return [
            'list' => $result,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ];
    }
}


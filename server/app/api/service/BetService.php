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


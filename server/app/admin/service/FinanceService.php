<?php
namespace app\admin\service;

use app\api\model\Recharge;
use app\api\model\Withdraw;
use app\api\model\Member;
use app\api\model\FundDetail;
use app\api\model\Setting;
use think\facade\Db;

/**
 * 后台充值提现管理服务类
 */
class FinanceService
{
    /**
     * 获取充值列表
     */
    public function getRechargeList($params)
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;
        
        $query = Recharge::alias('r')
            ->leftJoin('member m', 'r.uid = m.id')
            ->field('r.*, m.balance');
        
        // 筛选条件
        if (!empty($params['username'])) {
            $query->where('r.username', 'like', '%' . $params['username'] . '%');
        }
        if (isset($params['state']) && $params['state'] !== '') {
            $query->where('r.state', $params['state']);
        }
        if (!empty($params['sDate'])) {
            $query->where('r.oddtime', '>=', strtotime($params['sDate']));
        }
        if (!empty($params['eDate'])) {
            $query->where('r.oddtime', '<=', strtotime($params['eDate'] . ' 23:59:59'));
        }
        
        $list = $query->order('r.oddtime', 'desc')
            ->page($page, $pageSize)
            ->select();
        
        $total = Recharge::where(function($q) use ($params) {
            if (!empty($params['username'])) {
                $q->where('username', 'like', '%' . $params['username'] . '%');
            }
            if (isset($params['state']) && $params['state'] !== '') {
                $q->where('state', $params['state']);
            }
        })->count();
        
        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ];
    }
    
    /**
     * 审核充值
     */
    public function auditRecharge($id, $state)
    {
        $recharge = Recharge::find($id);
        if (!$recharge) {
            throw new \Exception('充值记录不存在');
        }
        
        if ($recharge->state != 0) {
            throw new \Exception('该充值已处理');
        }
        
        Db::startTrans();
        try {
            // 更新充值状态
            $recharge->state = $state;
            $recharge->checktime = time();
            $recharge->save();
            
            // 如果审核通过，增加余额
            if ($state == 1) {
                $member = Member::find($recharge->uid);
                if ($member) {
                    $oldBalance = $member->balance;
                    $member->balance = $member->balance + $recharge->amount;
                    $member->save();
                    
                    // 记录账变
                    FundDetail::create([
                        'trano' => $recharge->trano,
                        'uid' => $member->id,
                        'username' => $member->username,
                        'type' => 'recharge',
                        'typename' => '充值',
                        'amount' => $recharge->amount,
                        'amountbefor' => $oldBalance,
                        'amountafter' => $member->balance,
                        'oddtime' => time(),
                        'remark' => '充值成功',
                        'expect' => '',
                        'status_show' => 1,
                        'ticket_income_report' => 0
                    ]);
                    
                    // 增加洗码余额
                    $damaliang = floatval(Setting::getConfigValue('damaliang', 100));
                    if ($damaliang > 0) {
                        $ximaAmount = $recharge->amount * ($damaliang / 100);
                        $oldXima = $member->xima;
                        $member->xima = $member->xima + $ximaAmount;
                        $member->save();
                        
                        // 记录洗码账变
                        FundDetail::create([
                            'trano' => 'XM' . date('ymdHis') . rand(10, 99),
                            'uid' => $member->id,
                            'username' => $member->username,
                            'type' => 'xima',
                            'typename' => '洗码',
                            'amount' => $ximaAmount,
                            'amountbefor' => $oldXima,
                            'amountafter' => $member->xima,
                            'oddtime' => time(),
                            'remark' => "充值增加洗码额度（打码量{$damaliang}%）",
                            'expect' => '',
                            'status_show' => 1,
                            'ticket_income_report' => 0
                        ]);
                    }
                }
            }
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 获取提现列表
     */
    public function getWithdrawList($params)
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;
        
        $query = Withdraw::alias('w')
            ->leftJoin('member m', 'w.uid = m.id')
            ->field('w.*, m.balance');
        
        // 筛选条件
        if (!empty($params['username'])) {
            $query->where('w.username', 'like', '%' . $params['username'] . '%');
        }
        if (isset($params['state']) && $params['state'] !== '') {
            $query->where('w.state', $params['state']);
        }
        if (!empty($params['sDate'])) {
            $query->where('w.oddtime', '>=', strtotime($params['sDate']));
        }
        if (!empty($params['eDate'])) {
            $query->where('w.oddtime', '<=', strtotime($params['eDate'] . ' 23:59:59'));
        }
        
        $list = $query->order('w.oddtime', 'desc')
            ->page($page, $pageSize)
            ->select();
        
        $total = Withdraw::where(function($q) use ($params) {
            if (!empty($params['username'])) {
                $q->where('username', 'like', '%' . $params['username'] . '%');
            }
            if (isset($params['state']) && $params['state'] !== '') {
                $q->where('state', $params['state']);
            }
        })->count();
        
        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ];
    }
    
    /**
     * 审核提现
     */
    public function auditWithdraw($id, $state)
    {
        $withdraw = Withdraw::find($id);
        if (!$withdraw) {
            throw new \Exception('提现记录不存在');
        }
        
        if ($withdraw->state != 0) {
            throw new \Exception('该提现已处理');
        }
        
        Db::startTrans();
        try {
            // 更新提现状态
            $withdraw->state = $state;
            $withdraw->checktime = time();
            $withdraw->save();
            
            // 如果退回，返还余额
            if ($state == 2) {
                $member = Member::find($withdraw->uid);
                if ($member) {
                    $oldBalance = $member->balance;
                    $member->balance = $member->balance + $withdraw->amount;
                    $member->save();
                    
                    // 记录账变
                    FundDetail::create([
                        'trano' => 'RT' . date('ymdHis') . rand(1000, 9999),
                        'uid' => $member->id,
                        'username' => $member->username,
                        'type' => 'withdraw_return',
                        'typename' => '提现退回',
                        'amount' => $withdraw->amount,
                        'amountbefor' => $oldBalance,
                        'amountafter' => $member->balance,
                        'oddtime' => time(),
                        'remark' => '提现审核未通过，退回金额',
                        'expect' => '',
                        'status_show' => 1,
                        'ticket_income_report' => 0
                    ]);
                }
            }
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
}


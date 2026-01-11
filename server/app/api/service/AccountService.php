<?php
namespace app\api\service;

use app\api\model\Recharge;
use app\api\model\Withdraw;
use app\api\model\BankCard;
use app\api\model\Member;
use app\api\model\FundDetail;
use app\api\model\Setting;
use think\facade\Db;

/**
 * 账户服务类
 */
class AccountService
{
    /**
     * 提交充值申请
     */
    public function submitRecharge($userId, $data)
    {
        $user = Member::find($userId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 生成订单号
        $trano = 'CZ' . date('ymdHis') . rand(1000, 9999);
        
        // 创建充值记录
        $recharge = Recharge::create([
            'trano' => $trano,
            'uid' => $user->id,
            'username' => $user->username,
            'paytype' => $data['paytype'],
            'paytypetitle' => $data['paytypetitle'] ?? '',
            'amount' => $data['amount'],
            'userpayname' => $data['userpayname'] ?? '',
            'bankname' => $data['bankname'] ?? '',
            'bankcode' => $data['bankcode'] ?? '',
            'state' => 0, // 待审核
            'oddtime' => time(),
            'checktime' => 0
        ]);
        
        return [
            'id' => $recharge->id,
            'trano' => $trano,
            'amount' => $data['amount']
        ];
    }
    
    /**
     * 提交提现申请
     */
    public function submitWithdraw($userId, $data)
    {
        $user = Member::find($userId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        $amount = floatval($data['amount']);
        
        // 检查余额
        if ($user->balance < $amount) {
            throw new \Exception('余额不足');
        }
        
        // 检查洗码余额
        if ($user->xima > 0) {
            throw new \Exception('打码不足，洗码余额为0时可以提款。当前洗码余额：' . $user->xima);
        }
        
        // 验证支付密码
        if (empty($data['password'])) {
            throw new \Exception('请输入支付密码');
        }
        
        if (!$user->checkTradePassword($data['password'])) {
            throw new \Exception('支付密码错误');
        }
        
        // 获取银行卡信息
        $bankCard = BankCard::find($data['bank_id']);
        if (!$bankCard || $bankCard->uid != $userId) {
            throw new \Exception('银行卡不存在');
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 扣除余额
            $oldBalance = $user->balance;
            $user->balance = $user->balance - $amount;
            $user->save();
            
            // 生成订单号
            $trano = 'TX' . date('ymdHis') . rand(1000, 9999);
            
            // 创建提现记录
            $withdraw = Withdraw::create([
                'trano' => $trano,
                'uid' => $user->id,
                'username' => $user->username,
                'amount' => $amount,
                'bankname' => $bankCard->bankname,
                'bankcode' => $bankCard->bankcode,
                'userbankname' => $bankCard->userbankname,
                'state' => 0, // 待审核
                'oddtime' => time(),
                'checktime' => 0,
                'remark' => ''
            ]);
            
            // 记录账变
            FundDetail::create([
                'trano' => $trano,
                'uid' => $user->id,
                'username' => $user->username,
                'type' => 'withdraw',
                'typename' => '提现',
                'amount' => -$amount,
                'amountbefor' => $oldBalance,
                'amountafter' => $user->balance,
                'oddtime' => time(),
                'remark' => '提现申请',
                'expect' => '',
                'status_show' => 1,
                'ticket_income_report' => 0
            ]);
            
            Db::commit();
            
            return [
                'id' => $withdraw->id,
                'trano' => $trano,
                'amount' => $amount
            ];
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 获取银行卡列表
     */
    public function getBankList($userId)
    {
        $list = BankCard::where('uid', $userId)
            ->order('isdefault', 'desc')
            ->order('addtime', 'desc')
            ->select();
        
        $result = [];
        foreach ($list as $item) {
            $result[] = [
                'id' => $item->id,
                'bankname' => $item->bankname,
                'bankcode' => $item->bankcode,
                'userbankname' => $item->userbankname,
                'isdefault' => $item->isdefault
            ];
        }
        
        return $result;
    }
    
    /**
     * 添加银行卡
     */
    public function addBank($userId, $data)
    {
        $user = Member::find($userId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 验证支付密码
        if (empty($data['password'])) {
            throw new \Exception('请输入支付密码');
        }
        
        if (!$user->checkTradePassword($data['password'])) {
            throw new \Exception('支付密码错误');
        }
        
        // 检查是否已有银行卡
        $count = BankCard::where('uid', $userId)->count();
        $isDefault = $count == 0 ? 1 : 0;
        
        // 创建银行卡
        $bankCard = BankCard::create([
            'uid' => $user->id,
            'username' => $user->username,
            'bankname' => $data['bankname'],
            'bankcode' => $data['bankcode'],
            'userbankname' => $data['userbankname'],
            'isdefault' => $isDefault,
            'addtime' => time()
        ]);
        
        // 如果用户没有真实姓名，更新
        if (empty($user->userbankname)) {
            $user->userbankname = $data['userbankname'];
            $user->save();
        }
        
        return [
            'id' => $bankCard->id
        ];
    }
    
    /**
     * 删除银行卡
     */
    public function deleteBank($userId, $bankId)
    {
        $bankCard = BankCard::where('id', $bankId)
            ->where('uid', $userId)
            ->find();
        
        if (!$bankCard) {
            throw new \Exception('银行卡不存在');
        }
        
        $bankCard->delete();
        
        return true;
    }
    
    /**
     * 设置默认银行卡
     */
    public function setDefaultBank($userId, $bankId)
    {
        // 取消所有默认
        BankCard::where('uid', $userId)->update(['isdefault' => 0]);
        
        // 设置新默认
        $bankCard = BankCard::where('id', $bankId)
            ->where('uid', $userId)
            ->find();
        
        if (!$bankCard) {
            throw new \Exception('银行卡不存在');
        }
        
        $bankCard->isdefault = 1;
        $bankCard->save();
        
        return true;
    }
    
    /**
     * 获取账变记录
     */
    public function getFundRecords($userId, $page = 1, $pageSize = 20)
    {
        $list = FundDetail::where('uid', $userId)
            ->where('type', '<>', 'xima') // 排除洗码记录
            ->order('oddtime', 'desc')
            ->page($page, $pageSize)
            ->select();
        
        $total = FundDetail::where('uid', $userId)
            ->where('type', '<>', 'xima')
            ->count();
        
        $result = [];
        foreach ($list as $item) {
            $result[] = [
                'id' => $item->id,
                'typename' => $item->typename,
                'amount' => $item->amount,
                'amountafter' => $item->amountafter,
                'oddtime' => $item->oddtime,
                'remark' => $item->remark
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


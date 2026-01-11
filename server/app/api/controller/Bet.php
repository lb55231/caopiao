<?php
namespace app\api\controller;

use think\facade\Request;
use think\facade\Db;
use app\api\model\Bet as BetModel;
use app\api\model\User;
use app\api\model\LotteryType;
use app\api\model\LotteryResult;
use app\api\model\LotteryPlay;
use app\api\model\AccountLog;

/**
 * 投注控制器
 */
class Bet
{
    /**
     * 提交投注
     */
    public function submit()
    {
        $userId = Request::instance()->userId;
        $typeCode = Request::post('type_code');
        $playId = Request::post('play_id');
        $betContent = Request::post('bet_content');
        $betAmount = Request::post('bet_amount');
        
        // 参数验证
        if (!$typeCode || !$playId || !$betContent || !$betAmount) {
            return json(['code' => 400, 'msg' => '参数不完整', 'data' => null]);
        }
        
        if ($betAmount < 2) {
            return json(['code' => 400, 'msg' => '投注金额不能小于2元', 'data' => null]);
        }
        
        // 获取彩种信息
        $lotteryType = LotteryType::getByCode($typeCode);
        if (!$lotteryType || $lotteryType->status != 1) {
            return json(['code' => 400, 'msg' => '彩种不存在或已停售', 'data' => null]);
        }
        
        // 获取玩法信息
        $play = LotteryPlay::find($playId);
        if (!$play || $play->status != 1) {
            return json(['code' => 400, 'msg' => '玩法不存在或已停售', 'data' => null]);
        }
        
        // 检查投注金额范围
        if ($betAmount < $play->min_amount || $betAmount > $play->max_amount) {
            return json(['code' => 400, 'msg' => '投注金额超出范围', 'data' => null]);
        }
        
        // 获取当前期号
        $currentPeriod = LotteryResult::getCurrentPeriod($lotteryType->id);
        if (!$currentPeriod) {
            return json(['code' => 400, 'msg' => '暂无可投注期号', 'data' => null]);
        }
        
        // 检查是否封盘
        if (time() >= $currentPeriod->open_time - 30) {
            return json(['code' => 400, 'msg' => '已封盘，无法投注', 'data' => null]);
        }
        
        // 获取用户信息
        $user = User::find($userId);
        if (!$user || $user->status != 1) {
            return json(['code' => 400, 'msg' => '用户状态异常', 'data' => null]);
        }
        
        // 检查余额
        if ($user->balance < $betAmount) {
            return json(['code' => 400, 'msg' => '余额不足', 'data' => null]);
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 扣除余额
            $beforeBalance = $user->balance;
            $user->balance = $user->balance - $betAmount;
            $user->save();
            
            // 生成订单
            $orderNo = BetModel::generateOrderNo();
            $bet = BetModel::create([
                'order_no'        => $orderNo,
                'user_id'         => $userId,
                'lottery_type_id' => $lotteryType->id,
                'period'          => $currentPeriod->period,
                'play_id'         => $playId,
                'play_name'       => $play->play_name,
                'bet_content'     => $betContent,
                'bet_amount'      => $betAmount,
                'odds'            => $play->odds,
                'win_amount'      => 0,
                'status'          => 0,
                'ip'              => Request::ip(),
            ]);
            
            // 记录账变
            AccountLog::addLog(
                $userId,
                AccountLog::TYPE_BET,
                -$betAmount,
                $beforeBalance,
                $user->balance,
                $orderNo,
                '投注扣款'
            );
            
            Db::commit();
            
            return json([
                'code' => 200,
                'msg' => '投注成功',
                'data' => [
                    'order_no' => $orderNo,
                    'balance' => $user->balance
                ]
            ]);
            
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 500, 'msg' => '投注失败：' . $e->getMessage(), 'data' => null]);
        }
    }
    
    /**
     * 获取投注记录
     */
    public function list()
    {
        $userId = Request::instance()->userId;
        $page = Request::param('page', 1);
        $limit = Request::param('limit', 20);
        
        $list = BetModel::getUserBets($userId, $page, $limit);
        $total = BetModel::where('user_id', $userId)->count();
        
        return json([
            'code' => 200,
            'msg' => 'success',
            'data' => [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }
    
    /**
     * 获取投注详情
     */
    public function detail()
    {
        $userId = Request::instance()->userId;
        $orderNo = Request::param('order_no');
        
        if (!$orderNo) {
            return json(['code' => 400, 'msg' => '缺少参数', 'data' => null]);
        }
        
        $bet = BetModel::where('order_no', $orderNo)
            ->where('user_id', $userId)
            ->find();
        
        if (!$bet) {
            return json(['code' => 404, 'msg' => '订单不存在', 'data' => null]);
        }
        
        return json([
            'code' => 200,
            'msg' => 'success',
            'data' => $bet
        ]);
    }
}


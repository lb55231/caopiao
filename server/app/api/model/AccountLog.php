<?php
namespace app\api\model;

use think\Model;

/**
 * 账变记录模型
 */
class AccountLog extends Model
{
    protected $name = 'transrecord';
    
    protected $schema = [
        'id'             => 'int',
        'user_id'        => 'int',
        'type'           => 'string',
        'amount'         => 'float',
        'before_balance' => 'float',
        'after_balance'  => 'float',
        'order_no'       => 'string',
        'remark'         => 'string',
        'create_time'    => 'int',
    ];
    
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = false;
    
    // 类型常量
    const TYPE_RECHARGE = 'recharge';      // 充值
    const TYPE_WITHDRAW = 'withdraw';      // 提现
    const TYPE_BET = 'bet';                // 投注
    const TYPE_WIN = 'win';                // 中奖
    const TYPE_REFUND = 'refund';          // 退款
    const TYPE_COMMISSION = 'commission';  // 佣金
    
    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * 添加账变记录
     */
    public static function addLog($userId, $type, $amount, $beforeBalance, $afterBalance, $orderNo = '', $remark = '')
    {
        return self::create([
            'user_id'        => $userId,
            'type'           => $type,
            'amount'         => $amount,
            'before_balance' => $beforeBalance,
            'after_balance'  => $afterBalance,
            'order_no'       => $orderNo,
            'remark'         => $remark,
        ]);
    }
    
    /**
     * 获取用户账变记录
     */
    public static function getUserLogs($userId, $page = 1, $limit = 20)
    {
        return self::where('user_id', $userId)
            ->order('create_time', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
    }
}


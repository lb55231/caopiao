<?php
namespace app\api\model;

use think\Model;

/**
 * 充值订单模型
 */
class RechargeOrder extends Model
{
    protected $name = 'recharge';
    
    protected $schema = [
        'id'             => 'int',
        'order_no'       => 'string',
        'user_id'        => 'int',
        'amount'         => 'float',
        'actual_amount'  => 'float',
        'pay_type'       => 'string',
        'pay_info'       => 'string',
        'status'         => 'int',
        'remark'         => 'string',
        'audit_time'     => 'int',
        'audit_user_id'  => 'int',
        'create_time'    => 'int',
        'update_time'    => 'int',
    ];
    
    protected $autoWriteTimestamp = true;
    
    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


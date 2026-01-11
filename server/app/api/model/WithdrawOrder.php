<?php
namespace app\api\model;

use think\Model;

/**
 * 提现订单模型
 */
class WithdrawOrder extends Model
{
    protected $name = 'withdraw';
    
    protected $schema = [
        'id'             => 'int',
        'order_no'       => 'string',
        'user_id'        => 'int',
        'amount'         => 'float',
        'actual_amount'  => 'float',
        'fee'            => 'float',
        'bank_name'      => 'string',
        'bank_account'   => 'string',
        'bank_owner'     => 'string',
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


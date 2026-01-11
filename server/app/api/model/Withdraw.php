<?php
namespace app\api\model;

use think\Model;

/**
 * 提现订单模型
 */
class Withdraw extends Model
{
    // 设置表名
    protected $name = 'withdraw';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'          => 'int',
        'trano'       => 'string',
        'uid'         => 'int',
        'username'    => 'string',
        'amount'      => 'float',
        'bankname'    => 'string',
        'bankcode'    => 'string',
        'userbankname'=> 'string',
        'state'       => 'int',
        'oddtime'     => 'int',
        'checktime'   => 'int',
        'remark'      => 'string',
    ];
    
    /**
     * 关联会员
     */
    public function member()
    {
        return $this->belongsTo('Member', 'uid');
    }
}


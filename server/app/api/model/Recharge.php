<?php
namespace app\api\model;

use think\Model;

/**
 * 充值订单模型
 */
class Recharge extends Model
{
    // 设置表名
    protected $name = 'recharge';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'          => 'int',
        'trano'       => 'string',
        'uid'         => 'int',
        'username'    => 'string',
        'paytype'     => 'string',
        'paytypetitle'=> 'string',
        'amount'      => 'float',
        'userpayname' => 'string',
        'bankname'    => 'string',
        'bankcode'    => 'string',
        'state'       => 'int',
        'oddtime'     => 'int',
        'checktime'   => 'int',
    ];
    
    /**
     * 关联会员
     */
    public function member()
    {
        return $this->belongsTo('Member', 'uid');
    }
}


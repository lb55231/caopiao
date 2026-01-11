<?php
namespace app\api\model;

use think\Model;

/**
 * 账变记录模型
 */
class FundDetail extends Model
{
    // 设置表名
    protected $name = 'fuddetail';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'           => 'int',
        'trano'        => 'string',
        'uid'          => 'int',
        'username'     => 'string',
        'type'         => 'string',
        'typename'     => 'string',
        'amount'       => 'float',
        'amountbefor'  => 'float',
        'amountafter'  => 'float',
        'oddtime'      => 'int',
        'remark'       => 'string',
        'expect'       => 'string',
        'status_show'  => 'int',
        'ticket_income_report' => 'int',
    ];
    
    /**
     * 关联会员
     */
    public function member()
    {
        return $this->belongsTo('Member', 'uid');
    }
}


<?php
namespace app\api\model;

use think\Model;

/**
 * 银行卡模型
 */
class BankCard extends Model
{
    // 设置表名
    protected $name = 'banklist';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'          => 'int',
        'uid'         => 'int',
        'username'    => 'string',
        'bankname'    => 'string',
        'bankcode'    => 'string',
        'userbankname'=> 'string',
        'isdefault'   => 'int',
        'addtime'     => 'int',
    ];
    
    /**
     * 关联会员
     */
    public function member()
    {
        return $this->belongsTo('Member', 'uid');
    }
}


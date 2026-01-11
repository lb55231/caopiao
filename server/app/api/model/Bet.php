<?php
namespace app\api\model;

use think\Model;

/**
 * 投注模型
 */
class Bet extends Model
{
    // 设置表名
    protected $name = 'touzhu';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'        => 'int',
        'trano'     => 'string',
        'uid'       => 'int',
        'username'  => 'string',
        'typeid'    => 'int',
        'cpid'      => 'int',
        'cpname'    => 'string',
        'expect'    => 'string',
        'wanfa'     => 'string',
        'betcode'   => 'string',
        'beishu'    => 'int',
        'amount'    => 'float',
        'okamount'  => 'float',
        'odds'      => 'float',
        'isdraw'    => 'int',
        'oddtime'   => 'int',
        'opentime'  => 'int',
        'isnb'      => 'int',
    ];
    
    /**
     * 关联会员
     */
    public function member()
    {
        return $this->belongsTo('Member', 'uid');
    }
    
    /**
     * 关联彩票
     */
    public function lottery()
    {
        return $this->belongsTo('Lottery', 'cpid');
    }
}

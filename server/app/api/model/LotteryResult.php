<?php
namespace app\api\model;

use think\Model;

/**
 * 开奖结果模型
 */
class LotteryResult extends Model
{
    // 设置表名
    protected $name = 'kaijiang';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'      => 'int',
        'typeid'  => 'int',
        'cpid'    => 'int',
        'cpname'  => 'string',
        'expect'  => 'string',
        'opencode'=> 'string',
        'opentime'=> 'int',
        'addtime' => 'int',
    ];
}

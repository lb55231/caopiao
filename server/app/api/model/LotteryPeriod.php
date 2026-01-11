<?php
namespace app\api\model;

use think\Model;

/**
 * 彩票期数模型
 */
class LotteryPeriod extends Model
{
    // 设置表名
    protected $name = 'qihao';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'       => 'int',
        'typeid'   => 'int',
        'cpid'     => 'int',
        'cpname'   => 'string',
        'qishu'    => 'string',
        'kjtime'   => 'int',
        'oddtime'  => 'int',
        'addtime'  => 'int',
    ];
}


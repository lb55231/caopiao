<?php
namespace app\api\model;

use think\Model;

/**
 * 彩票模型
 */
class Lottery extends Model
{
    // 设置表名
    protected $name = 'caipiao';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    protected $schema = [
        'id'         => 'int',
        'cpname'     => 'string',
        'typeid'     => 'int',
        'logo'       => 'string',
        'timelong'   => 'int',
        'closetime1' => 'string',
        'closetime2' => 'string',
        'state'      => 'int',
        'addtime'    => 'int',
        'listorder'  => 'int',
    ];
}


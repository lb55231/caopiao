<?php
namespace app\api\model;

use think\Model;

/**
 * 彩种模型
 */
class LotteryType extends Model
{
    protected $name = 'caipiao';
    
    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'type_code'     => 'string',
        'type_name'     => 'string',
        'type_name_en'  => 'string',
        'icon'          => 'string',
        'description'   => 'string',
        'interval_time' => 'int',
        'total_periods' => 'int',
        'start_time'    => 'string',
        'end_time'      => 'string',
        'status'        => 'int',
        'sort'          => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    
    /**
     * 获取启用的彩种列表
     */
    public static function getEnabledList()
    {
        return self::where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }
    
    /**
     * 根据代码获取彩种信息
     */
    public static function getByCode($code)
    {
        return self::where('type_code', $code)->find();
    }
}


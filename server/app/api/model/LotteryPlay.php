<?php
namespace app\api\model;

use think\Model;

/**
 * 彩种玩法模型
 */
class LotteryPlay extends Model
{
    protected $name = 'wanfa';
    
    protected $schema = [
        'id'              => 'int',
        'lottery_type_id' => 'int',
        'play_code'       => 'string',
        'play_name'       => 'string',
        'parent_id'       => 'int',
        'odds'            => 'float',
        'min_amount'      => 'float',
        'max_amount'      => 'float',
        'description'     => 'string',
        'status'          => 'int',
        'sort'            => 'int',
        'create_time'     => 'int',
        'update_time'     => 'int',
    ];
    
    protected $autoWriteTimestamp = true;
    
    /**
     * 关联彩种
     */
    public function lotteryType()
    {
        return $this->belongsTo(LotteryType::class, 'lottery_type_id');
    }
}


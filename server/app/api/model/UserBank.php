<?php
namespace app\api\model;

use think\Model;

/**
 * 用户银行卡模型
 */
class UserBank extends Model
{
    protected $name = 'banklist';
    
    protected $schema = [
        'id'           => 'int',
        'user_id'      => 'int',
        'bank_name'    => 'string',
        'bank_account' => 'string',
        'bank_owner'   => 'string',
        'bank_branch'  => 'string',
        'is_default'   => 'int',
        'status'       => 'int',
        'create_time'  => 'int',
        'update_time'  => 'int',
    ];
    
    protected $autoWriteTimestamp = true;
    
    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


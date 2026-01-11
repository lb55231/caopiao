<?php
namespace app\api\model;

use think\Model;

/**
 * 用户模型
 */
class User extends Model
{
    protected $name = 'member';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'username'        => 'string',
        'password'        => 'string',
        'nickname'        => 'string',
        'avatar'          => 'string',
        'mobile'          => 'string',
        'email'           => 'string',
        'balance'         => 'decimal',
        'status'          => 'int',
        'register_ip'     => 'string',
        'register_time'   => 'int',
        'last_login_ip'   => 'string',
        'last_login_time' => 'int',
    ];
    
    // 隐藏字段
    protected $hidden = ['password'];
    
    // 追加字段
    protected $append = [];
}


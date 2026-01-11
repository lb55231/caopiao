<?php
namespace app\admin\model;

use think\Model;

/**
 * 管理员模型
 */
class Admin extends Model
{
    // 设置表名
    protected $name = 'admin';
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    // 隐藏字段
    protected $hidden = ['password'];
    
    protected $schema = [
        'id'       => 'int',
        'username' => 'string',
        'password' => 'string',
        'realname' => 'string',
        'role'     => 'int',
        'status'   => 'int',
        'addtime'  => 'int',
    ];
    
    /**
     * 检查密码
     */
    public function checkPassword($password)
    {
        return md5($password) === $this->password;
    }
}


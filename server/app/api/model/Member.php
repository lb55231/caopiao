<?php
namespace app\api\model;

use think\Model;

/**
 * 会员模型
 */
class Member extends Model
{
    // 设置表名
    protected $name = 'member';
    
    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'username'      => 'string',
        'password'      => 'string',
        'tradepassword' => 'string',
        'balance'       => 'float',
        'xima'          => 'float',
        'point'         => 'float',
        'groupid'       => 'int',
        'userbankname'  => 'string',
        'tel'           => 'string',
        'qq'            => 'string',
        'email'         => 'string',
        'islock'        => 'int',
        'onlinetime'    => 'int',
        'logintime'     => 'int',
        'loginip'       => 'string',
        'regip'         => 'string',
        'regtime'       => 'int',
        'proxy'         => 'int',
        'fandian'       => 'float',
        'parentid'      => 'int',
        'invite_code'   => 'string',
        'isnb'          => 'int',
        'xinyu'         => 'int',
        'zijin_num'     => 'int',
        'status_order'  => 'int',
        'status_withdraw' => 'int',
        'withdraw_deny_message' => 'string',
    ];
    
    // 隐藏字段
    protected $hidden = ['password', 'tradepassword'];
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    /**
     * 关联会员组
     */
    public function group()
    {
        return $this->belongsTo('MemberGroup', 'groupid');
    }
    
    /**
     * 检查密码
     */
    public function checkPassword($password)
    {
        return md5($password) === $this->password;
    }
    
    /**
     * 检查支付密码
     */
    public function checkTradePassword($password)
    {
        return md5($password) === $this->tradepassword;
    }
}


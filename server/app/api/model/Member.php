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
    
    // 不限制字段，让数据库自动处理
    protected $schema = [];
    
    // 关闭严格字段检查
    protected $strict = false;
    
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


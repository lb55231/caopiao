<?php
namespace app\api\service;

use app\api\model\Member;
use app\api\model\Setting;
use app\api\model\FundDetail;
use app\common\service\JwtService;
use think\facade\Db;

/**
 * 用户服务类
 */
class UserService
{
    /**
     * 用户登录
     */
    public function login($username, $password)
    {
        // 查询用户
        $user = Member::where('username', $username)->find();
        
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 验证密码
        if (!$user->checkPassword($password)) {
            throw new \Exception('密码错误');
        }
        
        // 检查锁定状态
        if ($user->islock == 1) {
            throw new \Exception('账号已被锁定');
        }
        
        // 生成token
        $token = JwtService::create($user->id, [
            'username' => $user->username
        ]);
        
        // 更新登录信息
        $user->logintime = time();
        $user->loginip = request()->ip();
        $user->onlinetime = time(); // 设置在线时间
        $user->save();
        
        return [
            'token' => $token,
            'userInfo' => [
                'id' => $user->id,
                'username' => $user->username,
                'balance' => $user->balance,
                'xima' => $user->xima,
                'userbankname' => $user->userbankname,
                'tel' => $user->tel,
                'qq' => $user->qq,
                'email' => $user->email
            ]
        ];
    }
    
    /**
     * 用户注册
     */
    public function register($username, $password, $inviteCode = '')
    {
        // 检查用户名是否存在
        $exist = Member::where('username', $username)->find();
        if ($exist) {
            throw new \Exception('用户名已存在');
        }
        
        // 检查是否需要邀请码
        $needInviteCode = Setting::getValue('needinvitecode', 0);
        if ($needInviteCode == 1) {
            if (empty($inviteCode)) {
                throw new \Exception('请输入邀请码');
            }
            
            // 验证邀请码
            $inviter = Member::where('invite_code', $inviteCode)->find();
            if (!$inviter) {
                throw new \Exception('邀请码不正确');
            }
            $parentid = $inviter->id;
        } else {
            $parentid = 0;
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 创建用户
            $user = Member::create([
                'username' => $username,
                'password' => md5($password),
                'tradepassword' => md5('123456'), // 默认支付密码
                'balance' => 0,
                'xima' => 0,
                'point' => 0,
                'groupid' => 1,
                'regip' => request()->ip(),
                'regtime' => time(),
                'logintime' => time(),
                'loginip' => request()->ip(),
                'onlinetime' => time(),
                'islock' => 0,
                'parentid' => $parentid,
                'proxy' => 0,
                'fandian' => 0,
            ]);
            
            // 注册送金额
            $registerBonus = floatval(Setting::getValue('registerbonus', 0));
            if ($registerBonus > 0) {
                // 增加余额
                $user->balance = $registerBonus;
                $user->save();
                
                // 记录账变
                FundDetail::create([
                    'trano' => 'REG' . date('ymdHis') . rand(1000, 9999),
                    'uid' => $user->id,
                    'username' => $user->username,
                    'type' => 'register',
                    'typename' => '注册赠送',
                    'amount' => $registerBonus,
                    'amountbefor' => 0,
                    'amountafter' => $registerBonus,
                    'oddtime' => time(),
                    'remark' => '注册赠送' . $registerBonus . '元',
                    'expect' => '',
                    'status_show' => 1,
                    'ticket_income_report' => 0
                ]);
                
                // 增加洗码余额（1:5比例）
                $ximaAmount = $registerBonus * 5;
                $user->xima = $ximaAmount;
                $user->save();
                
                // 记录洗码账变
                FundDetail::create([
                    'trano' => 'XM' . date('ymdHis') . rand(10, 99),
                    'uid' => $user->id,
                    'username' => $user->username,
                    'type' => 'xima',
                    'typename' => '洗码',
                    'amount' => $ximaAmount,
                    'amountbefor' => 0,
                    'amountafter' => $ximaAmount,
                    'oddtime' => time(),
                    'remark' => "注册赠送增加洗码额度（1:5比例，赠送{$registerBonus}元需打码{$ximaAmount}元）",
                    'expect' => '',
                    'status_show' => 1,
                    'ticket_income_report' => 0
                ]);
            }
            
            Db::commit();
            
            // 生成token自动登录
            $token = JwtService::create($user->id, [
                'username' => $user->username
            ]);
            
            return [
                'token' => $token,
                'userInfo' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'balance' => $user->balance,
                    'xima' => $user->xima
                ]
            ];
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 获取用户信息
     */
    public function getUserInfo($userId)
    {
        $user = Member::find($userId);
        
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        return [
            'id' => $user->id,
            'username' => $user->username,
            'balance' => $user->balance,
            'xima' => $user->xima,
            'point' => $user->point,
            'userbankname' => $user->userbankname ?? '',
            'tel' => $user->tel ?? '',
            'qq' => $user->qq ?? '',
            'email' => $user->email ?? '',
            'wechat' => '' // 数据库没有这个字段
        ];
    }
    
    /**
     * 更新用户资料
     */
    public function updateProfile($userId, $data)
    {
        $user = Member::find($userId);
        
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 映射字段
        if (isset($data['realname'])) {
            $user->userbankname = $data['realname'];
        }
        if (isset($data['tel'])) {
            $user->tel = $data['tel'];
        }
        if (isset($data['qq'])) {
            $user->qq = $data['qq'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        // wechat字段不存在，忽略
        
        $user->save();
        
        return true;
    }
    
    /**
     * 修改密码
     */
    public function changePassword($userId, $type, $oldPassword, $newPassword)
    {
        $user = Member::find($userId);
        
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        if ($type === 'login') {
            // 修改登录密码
            if (!$user->checkPassword($oldPassword)) {
                throw new \Exception('旧密码错误');
            }
            
            // 验证新密码格式（6-20位字母数字）
            if (!preg_match('/^[a-zA-Z0-9]{6,20}$/', $newPassword)) {
                throw new \Exception('新密码必须是6-20位字母或数字');
            }
            
            $user->password = md5($newPassword);
            
        } else if ($type === 'trade') {
            // 修改支付密码
            if (!$user->checkTradePassword($oldPassword)) {
                throw new \Exception('旧密码错误');
            }
            
            // 验证新密码格式（6位数字）
            if (!preg_match('/^\d{6}$/', $newPassword)) {
                throw new \Exception('支付密码必须是6位数字');
            }
            
            $user->tradepassword = md5($newPassword);
            
        } else {
            throw new \Exception('密码类型错误');
        }
        
        $user->save();
        
        return true;
    }
}


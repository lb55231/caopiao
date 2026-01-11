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
        $db = Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        // 查询用户
        $user = $db->table($prefix . 'member')
            ->where('username', $username)
            ->find();
        
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 验证密码 (MD5)
        if (md5($password) !== $user['password']) {
            throw new \Exception('密码错误');
        }
        
        // 检查锁定状态
        if (isset($user['islock']) && $user['islock'] == 1) {
            throw new \Exception('账号已被锁定');
        }
        
        // 生成token (使用Jwt类)
        require_once app()->getRootPath() . 'public/common/Jwt.php';
        $jwt = new \Jwt();
        $token = $jwt->createToken([
            'id' => $user['id'],
            'username' => $user['username']
        ]);
        
        // 更新登录信息
        $db->table($prefix . 'member')
            ->where('id', $user['id'])
            ->update([
                'logintime' => time(),
                'loginip' => request()->ip()
            ]);
        
        return [
            'token' => $token,
            'userInfo' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'balance' => $user['balance'] ?? 0,
                'xima' => $user['xima'] ?? 0,
                'userbankname' => $user['userbankname'] ?? '',
                'tel' => $user['tel'] ?? '',
                'qq' => $user['qq'] ?? '',
                'email' => $user['email'] ?? ''
            ]
        ];
    }
    
    /**
     * 用户注册
     */
    public function register($username, $password, $inviteCode = '', $tradepassword = '123456')
    {
        // 检查用户名是否存在
        $exist = Member::where('username', $username)->find();
        if ($exist) {
            throw new \Exception('用户名已存在');
        }
        
        // reccode 可以是推荐人ID或邀请码
        $parentid = 0;
        if (!empty($inviteCode)) {
            // 先按邀请码查找，如果找不到再按ID查找
            $inviter = Member::where('invite_code', $inviteCode)->find();
            
            // 如果按邀请码找不到，且输入的是数字，则按用户ID查找
            if (!$inviter && is_numeric($inviteCode)) {
                $inviter = Member::where('id', $inviteCode)->find();
            }
            
            if (!$inviter) {
                throw new \Exception('推荐码验证失败');
            }
            
            // 检查是否是代理
            if ($inviter->proxy != 1) {
                throw new \Exception('推荐码无效');
            }
            
            $parentid = $inviter->id;
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 使用原始SQL插入，确保所有字段都有值
            $db = Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            $userId = $db->table($prefix . 'member')->insertGetId([
                'username' => $username,
                'password' => md5($password),
                'tradepassword' => md5($tradepassword), // 支付密码
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
                'isnb' => 0, // 0=正常会员, 1=内部会员
                'nickname' => '',
                'email' => '',
                'phone' => '',
                'userbankname' => '',
                'sex' => 1,
                'tel' => '',
                'qq' => '',
                'source' => 'mobile',
                'loginsource' => 'mobile',
                'updatetime' => time(),
                'zijin_num' => '0',
                'mlogin_num' => '0',
                'mzijin_num' => '0',
                'xinyu' => 100,
                'status_order' => 1,
                'status_withdraw' => 1,
                'withdraw_deny_message' => '',
                'invite_code' => '',
                'iparea' => '',
                'jinjijilu' => 1,
            ]);
            
            // 获取用户对象
            $user = Member::find($userId);
            
            // 注册送金额
            $registerBonus = floatval(Setting::getConfigValue('registerbonus', 0));
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
            'realname' => $user->userbankname ?? '',  // 前端用 realname
            'userbankname' => $user->userbankname ?? '',
            'phone' => $user->phone ?? '',
            'tel' => $user->tel ?? '',
            'qq' => $user->qq ?? '',
            'email' => $user->email ?? '',
            'groupid' => $user->groupid ?? 1,
            'wechat' => '' // 数据库没有这个字段
        ];
    }
    
    /**
     * 获取用户资料
     */
    public function getProfile($userId)
    {
        $db = Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        $user = $db->table($prefix . 'member')->where('id', $userId)->find();
        
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'balance' => $user['balance'],
            'xima' => $user['xima'],
            'point' => $user['point'],
            'realname' => $user['userbankname'] ?? '',  // 前端用 realname，数据库用 userbankname
            'userbankname' => $user['userbankname'] ?? '',
            'phone' => $user['phone'] ?? '',
            'tel' => $user['tel'] ?? '',
            'qq' => $user['qq'] ?? '',
            'email' => $user['email'] ?? '',
            'groupid' => $user['groupid'] ?? 1,
        ];
    }
    
    /**
     * 更新用户资料
     */
    public function updateProfile($userId, $data)
    {
        // 使用原生SQL更新，确保数据保存
        $db = Db::connect();
        $prefix = config('database.connections.mysql.prefix');
        
        $updateData = [];
        
        // 映射字段
        if (isset($data['realname'])) {
            $updateData['userbankname'] = $data['realname'];
        }
        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }
        if (isset($data['tel'])) {
            $updateData['tel'] = $data['tel'];
        }
        if (isset($data['qq'])) {
            $updateData['qq'] = $data['qq'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        
        if (empty($updateData)) {
            return true;
        }
        
        // 更新数据库
        $result = $db->table($prefix . 'member')
            ->where('id', $userId)
            ->update($updateData);
        
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


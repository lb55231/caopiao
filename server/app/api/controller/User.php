<?php
namespace app\api\controller;

use app\common\controller\BaseController;
use app\api\service\UserService;

/**
 * 用户控制器
 */
class User extends BaseController
{
    protected $userService;
    
    public function initialize()
    {
        parent::initialize();
        $this->userService = new UserService();
    }
    
    /**
     * 用户登录
     */
    public function login()
    {
        $params = $this->getPostParams(['username', 'password']);
        
        if (empty($params['username']) || empty($params['password'])) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $result = $this->userService->login($params['username'], $params['password']);
            return $this->success('登录成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 用户注册
     */
    public function register()
    {
        $params = $this->getPostParams(['username', 'password', 'invite_code']);
        
        if (empty($params['username']) || empty($params['password'])) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $result = $this->userService->register(
                $params['username'], 
                $params['password'],
                $params['invite_code'] ?? ''
            );
            return $this->success('注册成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 获取用户信息
     */
    public function info()
    {
        try {
            $result = $this->userService->getUserInfo($this->userId);
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 更新用户资料
     */
    public function updateProfile()
    {
        $params = $this->getPostParams(['realname', 'tel', 'qq', 'email', 'wechat']);
        
        try {
            $this->userService->updateProfile($this->userId, $params);
            return $this->success('更新成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 修改密码
     */
    public function changePassword()
    {
        $params = $this->getPostParams(['type', 'old_password', 'new_password']);
        
        if (empty($params['type']) || empty($params['old_password']) || empty($params['new_password'])) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $this->userService->changePassword(
                $this->userId,
                $params['type'],
                $params['old_password'],
                $params['new_password']
            );
            return $this->success('修改成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

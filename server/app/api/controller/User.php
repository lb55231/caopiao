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
        // ThinkPHP 6 使用 param() 方法可以自动解析JSON
        $username = trim($this->request->param('username', ''));
        $password = trim($this->request->param('password', ''));
        
        if (empty($username) || empty($password)) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $result = $this->userService->login($username, $password);
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
        // 获取JSON数据
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $tradepassword = $data['tradepassword'] ?? '';
        // 前端字段是 reccode，不是 invite_code
        $inviteCode = $data['reccode'] ?? $data['invite_code'] ?? '';
        
        // 如果JSON解析失败，尝试从POST获取
        if (empty($username)) {
            $username = request()->post('username', '');
        }
        if (empty($password)) {
            $password = request()->post('password', '');
        }
        if (empty($tradepassword)) {
            $tradepassword = request()->post('tradepassword', '');
        }
        if (empty($inviteCode)) {
            $inviteCode = request()->post('reccode', '') ?: request()->post('invite_code', '');
        }
        
        if (empty($username) || empty($password)) {
            return $this->error('请填写完整信息');
        }
        
        // 如果没有提供支付密码，使用默认密码
        if (empty($tradepassword)) {
            $tradepassword = '123456';
        }
        
        try {
            $result = $this->userService->register($username, $password, $inviteCode, $tradepassword);
            return $this->success('注册成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 从token获取用户ID
     * @return int
     */
    private function getUserIdFromToken()
    {
        $token = $this->request->header('token', '');
        
        if (empty($token)) {
            return 0;
        }
        
        try {
            // 使用JWT验证
            require_once app()->getRootPath() . 'public/common/Jwt.php';
            $jwt = new \Jwt();
            $payload = $jwt->verifyToken($token);
            
            return $payload['id'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取用户信息
     */
    public function info()
    {
        // 验证token并获取userId
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        try {
            $result = $this->userService->getUserInfo($userId);
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
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        $params = $this->getPostParams(['realname', 'phone', 'tel', 'qq', 'email', 'wechat']);
        
        try {
            $this->userService->updateProfile($userId, $params);
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
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        // 兼容驼峰和下划线两种字段名
        $params = $this->getPostParams();
        $type = $params['type'] ?? '';
        $oldPassword = $params['old_password'] ?? $params['oldPassword'] ?? '';
        $newPassword = $params['new_password'] ?? $params['newPassword'] ?? '';
        
        if (empty($type) || empty($oldPassword) || empty($newPassword)) {
            return $this->error('请填写完整信息');
        }
        
        try {
            $this->userService->changePassword(
                $userId,
                $type,
                $oldPassword,
                $newPassword
            );
            return $this->success('修改成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 获取用户资料
     */
    public function profile()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        try {
            $result = $this->userService->getProfile($userId);
            return $this->success('获取成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 更新用户资料（别名）
     */
    public function profileUpdate()
    {
        return $this->updateProfile();
    }
    
    /**
     * 银行卡列表
     */
    public function banklist()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        try {
            // 直接查询数据库
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            $list = $db->table($prefix . 'banklist')
                ->where('uid', $userId)
                ->where('deleted_at', 0)
                ->order('isdefault', 'desc')
                ->order('id', 'desc')
                ->select()
                ->toArray();
            
            return $this->success('获取成功', ['list' => $list]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 添加银行卡
     */
    public function bankAdd()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        $params = $this->request->post();
        
        try {
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            // 获取用户信息
            $user = $db->table($prefix . 'member')->where('id', $userId)->find();
            
            // 验证支付密码（兼容 password 和 paypassword 字段）
            $payPassword = $params['paypassword'] ?? $params['password'] ?? '';
            if (empty($payPassword)) {
                return $this->error('请输入支付密码');
            }
            
            // 验证支付密码
            if (md5($payPassword) !== $user['tradepassword']) {
                return $this->error('支付密码错误');
            }
            
            // 验证真实姓名
            $accountname = trim($params['accountname'] ?? '');
            if (empty($accountname)) {
                return $this->error('请输入开户姓名');
            }
            
            // 如果用户已设置真实姓名，必须一致
            if (!empty($user['userbankname']) && $accountname !== $user['userbankname']) {
                return $this->error('开户姓名必须与账户真实姓名一致');
            }
            
            // 验证银行卡号
            $banknumber = trim($params['banknumber'] ?? '');
            if (empty($banknumber) || strlen($banknumber) < 10) {
                return $this->error('请输入正确的银行卡号');
            }
            
            // 检查是否已有该银行卡
            $exists = $db->table($prefix . 'banklist')
                ->where('uid', $userId)
                ->where('banknumber', $banknumber)
                ->where('deleted_at', 0)
                ->find();
            
            if ($exists) {
                return $this->error('该银行卡已存在');
            }
            
            // 检查是否是第一张银行卡
            $count = $db->table($prefix . 'banklist')
                ->where('uid', $userId)
                ->where('deleted_at', 0)
                ->count();
            $isDefault = $count == 0 ? 1 : 0;
            
            // 如果用户还没设置真实姓名，保存到用户表
            if (empty($user['userbankname'])) {
                $db->table($prefix . 'member')
                    ->where('id', $userId)
                    ->update(['userbankname' => $accountname]);
            }
            
            // 插入银行卡
            $id = $db->table($prefix . 'banklist')->insertGetId([
                'uid' => $userId,
                'username' => $user['username'],
                'bankaddress' => $params['bankaddress'] ?? '',
                'bankname' => $params['bankname'] ?? '',
                'bankcode' => $params['bankcode'] ?? $params['bankname'] ?? '',
                'bankbranch' => $params['bankbranch'] ?? '',
                'accountname' => $accountname,
                'banknumber' => $banknumber,
                'isdefault' => $isDefault,
                'state' => 1,
                'date' => date('Y-m-d H:i:s'),
                'deleted_at' => 0,
                'created_at' => time()
            ]);
            
            return $this->success('添加成功', ['id' => $id]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 删除银行卡
     */
    public function bankDelete()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        $id = $this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        try {
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            // 检查银行卡是否属于当前用户
            $bank = $db->table($prefix . 'banklist')
                ->where('id', $id)
                ->where('uid', $userId)
                ->where('deleted_at', 0)
                ->find();
            
            if (!$bank) {
                return $this->error('银行卡不存在');
            }
            
            // 软删除银行卡
            $db->table($prefix . 'banklist')
                ->where('id', $id)
                ->update(['deleted_at' => time()]);
            
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 设置默认银行卡
     */
    public function bankSetDefault()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        $id = $this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        try {
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            // 检查银行卡是否属于当前用户
            $bank = $db->table($prefix . 'banklist')
                ->where('id', $id)
                ->where('uid', $userId)
                ->where('deleted_at', 0)
                ->find();
            
            if (!$bank) {
                return $this->error('银行卡不存在');
            }
            
            // 将所有银行卡设为非默认
            $db->table($prefix . 'banklist')
                ->where('uid', $userId)
                ->where('deleted_at', 0)
                ->update(['isdefault' => 0]);
            
            // 设置当前银行卡为默认
            $db->table($prefix . 'banklist')
                ->where('id', $id)
                ->update(['isdefault' => 1]);
            
            return $this->success('设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 支付方式列表（公开接口，不需要登录）
     */
    public function paysetList()
    {
        try {
            // 直接查询数据库
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            $list = $db->table($prefix . 'payset')
                ->where('state', 1)
                ->order('listorder', 'desc')
                ->order('id', 'asc')
                ->select()
                ->toArray();
            
            // 处理configs字段，将PHP序列化字符串转为数组
            foreach ($list as &$item) {
                if (!empty($item['configs'])) {
                    $configArray = @unserialize($item['configs']);
                    $item['configs_array'] = $configArray ?: [];
                } else {
                    $item['configs_array'] = [];
                }
            }
            
            return $this->success('获取成功', ['list' => $list]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 账变记录
     */
    public function accountRecords()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('pageSize', 20);
        $atime = $this->request->param('atime', 1);
        $type = $this->request->param('type', '0');
        
        try {
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            // 时间筛选
            $where = [['uid', '=', $userId]];
            $now = time();
            switch ($atime) {
                case '1': // 今天
                    $startTime = strtotime(date('Y-m-d 00:00:00'));
                    $where[] = ['oddtime', '>=', $startTime];
                    break;
                case '2': // 昨天
                    $startTime = strtotime(date('Y-m-d 00:00:00', $now - 86400));
                    $endTime = strtotime(date('Y-m-d 23:59:59', $now - 86400));
                    $where[] = ['oddtime', 'between', [$startTime, $endTime]];
                    break;
                case '3': // 七天
                    $startTime = strtotime(date('Y-m-d 00:00:00', $now - 86400 * 7));
                    $where[] = ['oddtime', '>=', $startTime];
                    break;
            }
            
            // 类型筛选
            if ($type !== '0') {
                $where[] = ['type', '=', $type];
            }
            
            $list = $db->table($prefix . 'fuddetail')
                ->where($where)
                ->order('id', 'desc')
                ->page($page, $pageSize)
                ->select()
                ->toArray();
            
            $total = $db->table($prefix . 'fuddetail')
                ->where($where)
                ->count();
            
            // 计算总页数
            $totalPages = ceil($total / $pageSize);
            
            return $this->success('获取成功', [
                'records' => $list,
                'pagination' => [
                    'total' => $total,
                    'page' => intval($page),
                    'pageSize' => intval($pageSize),
                    'totalPages' => $totalPages
                ]
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 投注记录
     */
    public function betRecords()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('pageSize', 20);
        $atime = $this->request->param('atime', 1);
        
        try {
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            // 时间筛选
            $where = [['uid', '=', $userId]];
            $now = time();
            switch ($atime) {
                case '1': // 今天
                    $startTime = strtotime(date('Y-m-d 00:00:00'));
                    $where[] = ['oddtime', '>=', $startTime];
                    break;
                case '2': // 昨天
                    $startTime = strtotime(date('Y-m-d 00:00:00', $now - 86400));
                    $endTime = strtotime(date('Y-m-d 23:59:59', $now - 86400));
                    $where[] = ['oddtime', 'between', [$startTime, $endTime]];
                    break;
                case '3': // 七天
                    $startTime = strtotime(date('Y-m-d 00:00:00', $now - 86400 * 7));
                    $where[] = ['oddtime', '>=', $startTime];
                    break;
            }
            
            $list = $db->table($prefix . 'touzhu')
                ->where($where)
                ->order('id', 'desc')
                ->page($page, $pageSize)
                ->select()
                ->toArray();
            
            // 添加状态文本
            foreach ($list as &$item) {
                switch ($item['isdraw']) {
                    case 0:
                        $item['status_text'] = '待匹配';
                        break;
                    case 1:
                        $item['status_text'] = '已匹配订单';
                        break;
                    case -1:
                        $item['status_text'] = '未匹配订单';
                        break;
                    case -2:
                        $item['status_text'] = '已取消';
                        break;
                    default:
                        $item['status_text'] = '未知';
                }
            }
            
            $total = $db->table($prefix . 'touzhu')
                ->where($where)
                ->count();
            
            // 计算今日概况
            $todayStart = strtotime(date('Y-m-d 00:00:00'));
            $todayBet = $db->table($prefix . 'touzhu')
                ->where('uid', $userId)
                ->where('oddtime', '>=', $todayStart)
                ->sum('amount');
            
            $todayWin = $db->table($prefix . 'touzhu')
                ->where('uid', $userId)
                ->where('oddtime', '>=', $todayStart)
                ->where('isdraw', 1)
                ->sum('okamount');
            
            $profit = floatval($todayWin) - floatval($todayBet);
            
            // 计算总页数
            $totalPages = ceil($total / $pageSize);
            
            return $this->success('获取成功', [
                'records' => $list,
                'summary' => [
                    'total_bet' => number_format($todayBet, 2, '.', ''),
                    'total_win' => number_format($todayWin, 2, '.', ''),
                    'profit' => number_format($profit, 2, '.', '')
                ],
                'pagination' => [
                    'total' => $total,
                    'page' => intval($page),
                    'pageSize' => intval($pageSize),
                    'totalPages' => $totalPages
                ]
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 充值申请
     */
    public function rechargeAdd()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        // 获取POST参数（支持JSON和表单）
        $params = $this->request->post();
        if (empty($params)) {
            $params = $this->request->param();
        }
        
        try {
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            // 获取用户信息
            $user = $db->table($prefix . 'member')->where('id', $userId)->find();
            
            // 获取支付方式信息
            $paytype = $params['paytype'] ?? '';
            $payset = $db->table($prefix . 'payset')
                ->where('paytype', $paytype)
                ->find();
            
            if (!$payset) {
                return $this->error('支付方式不存在');
            }
            
            if ($payset['state'] != 1) {
                return $this->error('该支付方式已禁用');
            }
            
            // 验证金额
            $amount = floatval($params['amount'] ?? 0);
            if ($amount < floatval($payset['minmoney']) || $amount > floatval($payset['maxmoney'])) {
                return $this->error('充值金额不在允许范围内');
            }
            
            // 生成订单号
            $trano = date('YmdHis') . rand(1000, 9999);
            
            // 解析configs
            $configs = @unserialize($payset['configs']) ?: [];
            
            // 插入充值记录（使用recharge表）
            $id = $db->table($prefix . 'recharge')->insertGetId([
                'uid' => $userId,
                'username' => $user['username'],
                'paytype' => $params['paytype'] ?? '',
                'paytypetitle' => $payset['paytypetitle'] ?? '',
                'trano' => $trano,
                'threetrano' => '',
                'amount' => $amount,
                'fee' => 0.00,
                'actualamount' => 0.00,
                'actualfee' => 0.00,
                'oldaccountmoney' => 0.00,
                'newaccountmoney' => 0.00,
                'remark' => '充值',
                'ticket_income_report' => 1,
                'payname' => $params['userpayname'] ?? '',
                'fuyanma' => rand(10000000, 99999999),
                'isauto' => 1,
                'sdtype' => 0,
                'state' => 0,
                'oddtime' => time(),
                'stateadmin' => ''
            ]);
            
            return $this->success('申请成功', [
                'id' => $id,
                'trano' => $trano,
                'bankname' => $configs['bankname'] ?? '',
                'bankcode' => $configs['bankcode'] ?? ''
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 提现申请
     */
    public function withdrawAdd()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->error('请先登录', null, 401);
        }
        
        $params = $this->request->post();
        
        try {
            $db = \think\facade\Db::connect();
            $prefix = config('database.connections.mysql.prefix');
            
            // 获取用户信息
            $user = $db->table($prefix . 'member')->where('id', $userId)->find();
            
            // 检查余额
            $amount = floatval($params['amount'] ?? 0);
            if ($amount <= 0) {
                return $this->error('提现金额必须大于0');
            }
            
            if ($user['balance'] < $amount) {
                return $this->error('余额不足');
            }
            
            // 获取银行卡信息
            $bankId = intval($params['bankid'] ?? 0);
            $bank = $db->table($prefix . 'banklist')
                ->where('id', $bankId)
                ->where('uid', $userId)
                ->where('deleted_at', 0)
                ->find();
            
            if (!$bank) {
                return $this->error('请先绑定银行卡');
            }
            
            // 生成订单号
            $trano = substr(strtoupper(md5(uniqid())), 0, 2) . date('ymdHis') . rand(10, 99);
            
            // 开始事务
            $db->startTrans();
            try {
                $oldBalance = floatval($user['balance']);
                $newBalance = $oldBalance - $amount;
                
                // 扣除余额
                $db->table($prefix . 'member')
                    ->where('id', $userId)
                    ->update(['balance' => $newBalance]);
                
                // 插入提现记录
                $id = $db->table($prefix . 'withdraw')->insertGetId([
                    'uid' => $userId,
                    'username' => $user['username'],
                    'trano' => $trano,
                    'amount' => $amount,
                    'actualamount' => $amount,
                    'oldaccountmoney' => $oldBalance,
                    'newaccountmoney' => $newBalance,
                    'fee' => 0.00,
                    'accountname' => $bank['accountname'],
                    'bankname' => $bank['bankname'],
                    'bankbranch' => $bank['bankbranch'] ?? '',
                    'banknumber' => $bank['banknumber'],
                    'remark' => '',
                    'ticket_income_report' => 1,
                    'oddtime' => time(),
                    'state' => 0,
                    'stateadmin' => ''
                ]);
                
                $db->commit();
                
                return $this->success('申请成功', ['id' => $id, 'trano' => $trano]);
            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    /**
     * 魔术方法：处理多级路径（如 bank/list, payset/list 等）
     */
    public function __call($method, $args)
    {
        // 获取完整路径
        $path = $this->request->pathinfo();
        
        // 统一将连字符转为下划线
        $normalizedMethod = str_replace('-', '_', $method);
        
        // 直接映射表
        $directMap = [
            'account_records' => 'accountRecords',
            'bet_records' => 'betRecords',
            'change_password' => 'changePassword',
        ];
        
        // 1. 先尝试直接匹配 (user/xxx_xxx 或 user/xxx-xxx 格式)
        if (isset($directMap[$normalizedMethod]) && method_exists($this, $directMap[$normalizedMethod])) {
            return call_user_func([$this, $directMap[$normalizedMethod]]);
        }
        
        // 2. 处理 user/xxx/xxx 格式的路径
        if (preg_match('#user/([^/]+)/([^/]+)#', $path, $matches)) {
            $controller = $matches[1];  // bank, payset, etc.
            $action = str_replace('-', '_', $matches[2]);  // list, add, delete, etc. (统一转为下划线)
            
            // 映射到实际方法
            $methodMap = [
                'payset_list' => 'paysetList',
                'bank_list' => 'banklist',
                'bank_add' => 'bankAdd',
                'bank_delete' => 'bankDelete',
                'bank_setdefault' => 'bankSetDefault',
                'bank_set_default' => 'bankSetDefault',  // 添加下划线版本
                'account_records' => 'accountRecords',
                'bet_records' => 'betRecords',
                'recharge_add' => 'rechargeAdd',
                'withdraw_add' => 'withdrawAdd',
                'profile_update' => 'profileUpdate',
                'change_password' => 'changePassword',
            ];
            
            $key = $controller . '_' . $action;
            
            if (isset($methodMap[$key]) && method_exists($this, $methodMap[$key])) {
                return call_user_func([$this, $methodMap[$key]]);
            }
        }
        
        // 如果没有匹配到，返回错误（显示更详细的信息）
        return $this->error('方法不存在: ' . $method . ' (路径: ' . $path . ')');
    }
}

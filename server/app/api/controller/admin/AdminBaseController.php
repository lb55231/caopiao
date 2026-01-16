<?php
namespace app\api\controller\admin;

use app\common\controller\BaseController;
use think\Response;

/**
 * 后台管理基础控制器
 */
abstract class AdminBaseController extends BaseController
{
    /**
     * 当前登录管理员信息
     * @var array
     */
    protected $adminInfo = [];

    /**
     * 管理员ID
     * @var int
     */
    protected $adminId = 0;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();

        // 验证管理员权限
        $this->checkAdmin();
    }

    /**
     * 验证管理员权限
     */
    protected function checkAdmin()
    {
        // 获取 Token
        $token = $this->request->header('Authorization', '') ?: $this->request->header('Token', '');
        $token = str_replace('Bearer ', '', $token);

        if (empty($token)) {
            $this->returnError('未提供认证Token', 401);
        }

        try {
            // 验证 Token
            require_once app()->getRootPath() . 'public/common/Jwt.php';
            $jwt = new \Jwt();
            $payload = $jwt->verifyToken($token);

            if (!$payload) {
                $this->returnError('Token无效或已过期', 401);
            }

            // 验证是否为管理员类型
            if (!isset($payload['type']) || $payload['type'] !== 'admin') {
                $this->returnError('无权限访问', 403);
            }

            // 获取管理员信息
            $this->adminId = $payload['id'] ?? 0;
            $this->adminInfo = $payload;

            if (!$this->adminId) {
                $this->returnError('管理员信息错误', 401);
            }

            // 验证管理员是否被锁定
            $admin = $this->getAdminFromDb($this->adminId);
            if (!$admin) {
                $this->returnError('管理员不存在', 401);
            }

            if (isset($admin['islock']) && $admin['islock'] == 1) {
                $this->returnError('账户已被锁定', 403);
            }

            // 更新完整的管理员信息
            $this->adminInfo = array_merge($this->adminInfo, [
                'username' => $admin['username'],
                'groupid' => $admin['groupid'] ?? 0,
            ]);

        } catch (\Exception $e) {
            $this->returnError('认证失败：' . $e->getMessage(), 401);
        }
    }

    /**
     * 从数据库获取管理员信息
     * @param int $adminId
     * @return array|false
     */
    protected function getAdminFromDb($adminId)
    {
        try {
            require_once app()->getRootPath() . 'public/common/Database.php';
            $pdo = \Database::getInstance();
            $prefix = \Database::getPrefix();

            $stmt = $pdo->prepare("SELECT * FROM {$prefix}adminmember WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $adminId]);
            $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $admin ?: false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 直接输出错误并终止（用于初始化阶段）
     */
    protected function returnError($msg, $code = 400)
    {
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 获取数据库连接
     * @return \PDO
     */
    protected function getDb()
    {
        require_once app()->getRootPath() . 'public/common/Database.php';
        return \Database::getInstance();
    }

    /**
     * 获取表前缀
     * @return string
     */
    protected function getPrefix()
    {
        require_once app()->getRootPath() . 'public/common/Database.php';
        return \Database::getPrefix();
    }

    /**
     * 记录管理员操作日志
     * @param string $type 操作类型
     * @param string $info 操作信息
     */
    protected function addAdminLog($type, $info)
    {
        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}adminlog (userid, username, type, info, time, ip) 
                VALUES (:userid, :username, :type, :info, :time, :ip)
            ");

            $stmt->execute([
                ':userid' => $this->adminId,
                ':username' => $this->adminInfo['username'] ?? '',
                ':type' => $type,
                ':info' => $info,
                ':time' => time(),
                ':ip' => $this->request->ip()
            ]);
        } catch (\Exception $e) {
            // 忽略日志错误
        }
    }
}

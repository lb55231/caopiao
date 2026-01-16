<?php
namespace app\api\controller\agent;

use app\common\controller\BaseController;

/**
 * 代理端基础控制器
 */
abstract class AgentBaseController extends BaseController
{
    /**
     * 当前登录代理信息
     * @var array
     */
    protected $agentInfo = [];

    /**
     * 代理ID
     * @var int
     */
    protected $agentId = 0;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();

        // 验证代理权限
        $this->checkAgent();
    }

    /**
     * 验证代理权限
     */
    protected function checkAgent()
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

            // 验证是否为代理类型
            if (!isset($payload['type']) || $payload['type'] !== 'agent') {
                $this->returnError('无权限访问', 403);
            }

            // 获取代理信息
            $this->agentId = $payload['id'] ?? 0;
            $this->agentInfo = $payload;

            if (!$this->agentId) {
                $this->returnError('代理信息错误', 401);
            }

            // 验证代理是否被锁定
            $agent = $this->getAgentFromDb($this->agentId);
            if (!$agent) {
                $this->returnError('代理不存在', 401);
            }

            if (isset($agent['islock']) && $agent['islock'] == 1) {
                $this->returnError('账户已被锁定', 403);
            }

            // 更新完整的代理信息
            $this->agentInfo = array_merge($this->agentInfo, [
                'username' => $agent['username'],
                'balance' => $agent['balance'],
                'proxy' => $agent['proxy'],
            ]);

        } catch (\Exception $e) {
            $this->returnError('认证失败：' . $e->getMessage(), 401);
        }
    }

    /**
     * 从数据库获取代理信息
     * @param int $agentId
     * @return array|false
     */
    protected function getAgentFromDb($agentId)
    {
        try {
            require_once app()->getRootPath() . 'public/common/Database.php';
            $pdo = \Database::getInstance();
            $prefix = \Database::getPrefix();

            $stmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :id AND proxy = 1 LIMIT 1");
            $stmt->execute([':id' => $agentId]);
            $agent = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $agent ?: false;
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
            'code' => 0,
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
     * 记录代理操作日志
     * @param string $type 操作类型
     * @param string $info 操作信息
     */
    protected function addAgentLog($type, $info)
    {
        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}memlog (uid, username, type, info, time, ip) 
                VALUES (:uid, :username, :type, :info, :time, :ip)
            ");

            $stmt->execute([
                ':uid' => $this->agentId,
                ':username' => $this->agentInfo['username'] ?? '',
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

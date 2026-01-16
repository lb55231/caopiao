<?php
namespace app\api\controller\agent;

use app\common\controller\BaseController;

/**
 * 代理端认证控制器
 */
class AuthController extends BaseController
{
    /**
     * 代理登录
     */
    public function login()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求');
        }

        $input = $this->getPostParams();

        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            return $this->error('用户名和密码不能为空');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 查找代理（proxy=1 表示是代理）
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE username = :username AND proxy = 1 LIMIT 1");
            $stmt->execute([':username' => $username]);
            $agent = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$agent) {
                return $this->error('用户名或密码错误');
            }

            // 检查是否被锁定
            if ($agent['islock'] == 1) {
                return $this->error('账户已被锁定');
            }

            // 验证密码
            if (!password_verify($password, $agent['password']) && md5($password) !== $agent['password']) {
                return $this->error('用户名或密码错误');
            }

            // 更新登录信息
            $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET logintime = :time, loginip = :ip WHERE id = :id");
            $updateStmt->execute([
                ':time' => time(),
                ':ip' => $this->request->ip(),
                ':id' => $agent['id']
            ]);

            // 生成 Token
            require_once app()->getRootPath() . 'public/common/Jwt.php';
            $jwt = new \Jwt();

            $payload = [
                'id' => $agent['id'],
                'username' => $agent['username'],
                'type' => 'agent',  // 标识为代理类型
                'proxy' => $agent['proxy'],
            ];

            $token = $jwt->createToken($payload);

            return $this->success('登录成功', [
                'token' => $token,
                'id' => $agent['id'],
                'username' => $agent['username'],
                'balance' => $agent['balance'],
            ]);

        } catch (\Exception $e) {
            return $this->error('登录失败：' . $e->getMessage());
        }
    }

    /**
     * 获取数据库连接
     */
    private function getDb()
    {
        require_once app()->getRootPath() . 'public/common/Database.php';
        return \Database::getInstance();
    }

    /**
     * 获取表前缀
     */
    private function getPrefix()
    {
        require_once app()->getRootPath() . 'public/common/Database.php';
        return \Database::getPrefix();
    }
}

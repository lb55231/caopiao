<?php
namespace app\api\controller\admin;

/**
 * 代理管理控制器
 */
class AgentController extends AdminBaseController
{
    /**
     * 获取代理注册链接列表
     * @return \think\Response
     */
    public function links()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));
            $offset = ($page - 1) * $pageSize;

            // 获取总数
            $countStmt = $pdo->query("SELECT COUNT(*) as total FROM {$prefix}member WHERE proxy = 1 AND invite_code != ''");
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 获取列表，包括注册人数统计
            $stmt = $pdo->query("
                SELECT 
                    m.id,
                    m.username,
                    m.invite_code,
                    m.regtime as created_at,
                    (SELECT COUNT(*) FROM {$prefix}member WHERE parentid = m.id) as reg_count
                FROM {$prefix}member m
                WHERE m.proxy = 1 AND m.invite_code != ''
                ORDER BY m.id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total)
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 添加代理注册链接
     * @return \think\Response
     */
    public function addLink()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['username'])) {
            return $this->error('请输入代理账号');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 检查会员是否存在
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE username = :username");
            $stmt->execute([':username' => $input['username']]);
            $member = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$member) {
                return $this->error('会员不存在');
            }

            // 生成邀请码
            if (empty($input['invite_code'])) {
                $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                $code = '';
                for ($i = 0; $i < 8; $i++) {
                    $code .= $chars[rand(0, strlen($chars) - 1)];
                }
                $inviteCode = $code;
            } else {
                $inviteCode = strtoupper($input['invite_code']);
            }

            // 检查邀请码是否已存在
            $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}member WHERE invite_code = :code");
            $checkStmt->execute([':code' => $inviteCode]);
            if ($checkStmt->fetch()) {
                return $this->error('邀请码已存在');
            }

            // 更新会员信息，设置为代理并添加邀请码
            $updateStmt = $pdo->prepare("
                UPDATE {$prefix}member 
                SET proxy = 1, invite_code = :code 
                WHERE username = :username
            ");
            $result = $updateStmt->execute([
                ':code' => $inviteCode,
                ':username' => $input['username']
            ]);

            if ($result) {
                $this->addAdminLog('agent_add', "添加代理链接：{$input['username']}");
                return $this->success('生成成功', [
                    'invite_code' => $inviteCode
                ]);
            } else {
                return $this->error('生成失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 删除代理注册链接
     * @param int $id
     * @return \think\Response
     */
    public function deleteLink($id)
    {
        if (!$this->request->isDelete()) {
            return $this->error('请使用DELETE请求', null, 405);
        }

        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 清空邀请码
            $stmt = $pdo->prepare("UPDATE {$prefix}member SET invite_code = '' WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('agent_delete', "删除代理链接，ID：{$id}");
                return $this->success('删除成功');
            } else {
                return $this->error('删除失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取登录日志
     * @return \think\Response
     */
    public function loginLogs()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));
            $username = trim($this->request->param('username', ''));
            $ip = trim($this->request->param('ip', ''));
            $startTime = intval($this->request->param('start_time', 0));
            $endTime = intval($this->request->param('end_time', 0));

            $offset = ($page - 1) * $pageSize;

            $where = [];
            $params = [];

            if (!empty($username)) {
                $where[] = "username LIKE :username";
                $params[':username'] = "%{$username}%";
            }

            if (!empty($ip)) {
                $where[] = "loginip LIKE :ip";
                $params[':ip'] = "%{$ip}%";
            }

            if ($startTime > 0) {
                $where[] = "logintime >= :start_time";
                $params[':start_time'] = $startTime;
            }

            if ($endTime > 0) {
                $where[] = "logintime <= :end_time";
                $params[':end_time'] = $endTime;
            }

            $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            // 获取总数
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}member {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 获取列表
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    username,
                    loginip,
                    iparea,
                    loginsource,
                    logintime,
                    '' as device,
                    1 as status
                FROM {$prefix}member 
                {$whereSQL}
                ORDER BY logintime DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total)
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

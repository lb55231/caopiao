<?php
namespace app\api\controller\admin;

/**
 * 管理员管理控制器
 */
class AdminController extends AdminBaseController
{
    /**
     * 获取管理员列表
     */
    public function list()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = max(1, intval($this->request->param('page', 1)));
            $pageSize = min(100, max(10, intval($this->request->param('page_size', 20))));
            $offset = ($page - 1) * $pageSize;

            // 不显示 globaladmin
            $where = "username != 'globaladmin'";
            
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}adminmember WHERE {$where}");
            $countStmt->execute();
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->prepare("
                SELECT a.*, g.groupname 
                FROM {$prefix}adminmember a
                LEFT JOIN {$prefix}admingroup g ON a.groupid = g.groupid
                WHERE {$where}
                ORDER BY a.id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute();
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 不返回密码字段
            foreach ($list as &$item) {
                unset($item['password']);
            }

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total),
                'page' => $page,
                'page_size' => $pageSize
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 添加管理员
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        // 只有超级管理员可以添加
        if ($this->adminInfo['groupid'] != 1) {
            return $this->error('只有超级管理员可以添加管理员');
        }

        $input = $this->getPostParams();

        if (empty($input['username']) || empty($input['password'])) {
            return $this->error('请输入用户名和密码');
        }

        // 验证用户名格式（6-16位字母数字）
        if (!preg_match('/^\w{4,16}$/', $input['username'])) {
            return $this->error('用户名格式错误，4-16位字母数字组合');
        }

        // 验证密码格式（6-16位）
        if (!preg_match('/^\w{6,16}$/', $input['password'])) {
            return $this->error('密码格式错误，6-16位数字字母组合');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 检查用户名是否存在
            $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}adminmember WHERE username = :username");
            $checkStmt->execute([':username' => $input['username']]);
            if ($checkStmt->fetch()) {
                return $this->error('管理用户名已经存在');
            }

            // 加密密码
            require_once app()->getRootPath() . 'public/common/Encrypt.php';
            $encryptedPassword = \Encrypt::encrypt($input['password']);

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}adminmember 
                (username, password, email, groupid, safecode, logintime, islock)
                VALUES 
                (:username, :password, :email, :groupid, :safecode, :logintime, 0)
            ");

            $result = $stmt->execute([
                ':username' => $input['username'],
                ':password' => $encryptedPassword,
                ':email' => $input['email'] ?? '',
                ':groupid' => intval($input['groupid'] ?? 2),
                ':safecode' => intval($input['safecode'] ?? 1234),
                ':logintime' => 0
            ]);

            if ($result) {
                $this->addAdminLog('admin_add', "添加管理员：{$input['username']}");
                return $this->success('添加成功', ['id' => $pdo->lastInsertId()]);
            } else {
                return $this->error('添加失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 更新管理员信息
     */
    public function update($id = null)
    {
        if (!$this->request->isPut() && !$this->request->isPost()) {
            return $this->error('请使用PUT或POST请求', null, 405);
        }

        // 只有超级管理员可以修改
        if ($this->adminInfo['groupid'] != 1) {
            return $this->error('只有超级管理员可以修改管理员');
        }

        $input = $this->getPostParams();
        
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 检查管理员是否存在
            $checkStmt = $pdo->prepare("SELECT * FROM {$prefix}adminmember WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            $admin = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$admin) {
                return $this->error('管理员不存在');
            }

            $updateFields = [];
            $params = [':id' => $id];

            if (isset($input['email'])) {
                $updateFields[] = "email = :email";
                $params[':email'] = $input['email'];
            }

            if (isset($input['groupid'])) {
                $updateFields[] = "groupid = :groupid";
                $params[':groupid'] = intval($input['groupid']);
            }

            if (isset($input['safecode'])) {
                $updateFields[] = "safecode = :safecode";
                $params[':safecode'] = intval($input['safecode']);
            }

            // 如果提供了新密码，则更新密码
            if (!empty($input['password'])) {
                if (!preg_match('/^\w{6,16}$/', $input['password'])) {
                    return $this->error('密码格式错误，6-16位数字字母组合');
                }
                require_once app()->getRootPath() . 'public/common/Encrypt.php';
                $updateFields[] = "password = :password";
                $params[':password'] = \Encrypt::encrypt($input['password']);
            }

            if (empty($updateFields)) {
                return $this->error('没有需要更新的字段');
            }

            $sql = "UPDATE {$prefix}adminmember SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->addAdminLog('admin_update', "更新管理员，ID：{$id}");
                return $this->success('更新成功');
            } else {
                return $this->error('更新失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 删除管理员
     */
    public function delete($id = null)
    {
        if (!$this->request->isDelete()) {
            return $this->error('请使用DELETE请求', null, 405);
        }

        // 只有超级管理员可以删除
        if ($this->adminInfo['groupid'] != 1) {
            return $this->error('只有超级管理员可以删除管理员');
        }

        $id = $id ?: $this->request->param('id');
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 不能删除自己
            if ($id == $this->adminInfo['id']) {
                return $this->error('不能删除自己的账号');
            }

            $stmt = $pdo->prepare("DELETE FROM {$prefix}adminmember WHERE id = :id AND username != 'globaladmin'");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('admin_delete', "删除管理员，ID：{$id}");
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
     * 锁定/解锁管理员
     */
    public function toggleLock($id = null)
    {
        if (!$this->request->isPost() && !$this->request->isPut()) {
            return $this->error('请使用POST或PUT请求', null, 405);
        }

        // 只有超级管理员可以操作
        if ($this->adminInfo['groupid'] != 1) {
            return $this->error('只有超级管理员可以操作');
        }

        $input = $this->getPostParams();
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 不能锁定自己
            if ($id == $this->adminInfo['id']) {
                return $this->error('不能锁定自己的账号');
            }

            $checkStmt = $pdo->prepare("SELECT islock FROM {$prefix}adminmember WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            $admin = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$admin) {
                return $this->error('管理员不存在');
            }

            $newLock = $admin['islock'] == 1 ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE {$prefix}adminmember SET islock = :islock WHERE id = :id");
            $result = $stmt->execute([':islock' => $newLock, ':id' => $id]);

            if ($result) {
                $action = $newLock == 1 ? '锁定' : '解锁';
                $this->addAdminLog('admin_lock', "{$action}管理员，ID：{$id}");
                return $this->success('操作成功', ['islock' => $newLock]);
            } else {
                return $this->error('操作失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 修改密码（管理员修改自己的密码）
     */
    public function changePassword()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['old_password']) || empty($input['new_password'])) {
            return $this->error('请输入旧密码和新密码');
        }

        // 验证新密码格式
        if (!preg_match('/^\w{6,16}$/', $input['new_password'])) {
            return $this->error('新密码格式错误，6-16位数字字母组合');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 获取当前管理员信息
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}adminmember WHERE id = :id");
            $stmt->execute([':id' => $this->adminInfo['id']]);
            $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$admin) {
                return $this->error('管理员信息不存在');
            }

            // 验证旧密码
            require_once app()->getRootPath() . 'public/common/Encrypt.php';
            $encryptedOld = \Encrypt::encrypt($input['old_password']);
            
            if ($encryptedOld !== $admin['password']) {
                return $this->error('旧密码错误');
            }

            // 加密新密码
            $encryptedNew = \Encrypt::encrypt($input['new_password']);

            $updateStmt = $pdo->prepare("UPDATE {$prefix}adminmember SET password = :password WHERE id = :id");
            $result = $updateStmt->execute([
                ':password' => $encryptedNew,
                ':id' => $this->adminInfo['id']
            ]);

            if ($result) {
                $this->addAdminLog('change_password', '修改登录密码');
                return $this->success('密码修改成功，请重新登录');
            } else {
                return $this->error('修改失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 修改安全码
     */
    public function changeSafecode()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (!isset($input['old_safecode']) || !isset($input['new_safecode'])) {
            return $this->error('请输入旧安全码和新安全码');
        }

        // 验证安全码格式（4-7位数字）
        if (!preg_match('/^\d{4,7}$/', $input['new_safecode'])) {
            return $this->error('安全码格式错误，4-7位数字');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 获取当前管理员信息
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}adminmember WHERE id = :id");
            $stmt->execute([':id' => $this->adminInfo['id']]);
            $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$admin) {
                return $this->error('管理员信息不存在');
            }

            // 验证旧安全码
            if (intval($input['old_safecode']) != intval($admin['safecode'])) {
                return $this->error('旧安全码错误');
            }

            $updateStmt = $pdo->prepare("UPDATE {$prefix}adminmember SET safecode = :safecode WHERE id = :id");
            $result = $updateStmt->execute([
                ':safecode' => intval($input['new_safecode']),
                ':id' => $this->adminInfo['id']
            ]);

            if ($result) {
                $this->addAdminLog('change_safecode', '修改安全码');
                return $this->success('安全码修改成功');
            } else {
                return $this->error('修改失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取管理组列表
     */
    public function groups()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->query("SELECT * FROM {$prefix}admingroup ORDER BY groupid ASC");
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', ['list' => $list]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

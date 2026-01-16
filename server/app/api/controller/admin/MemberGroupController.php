<?php
namespace app\api\controller\admin;

/**
 * 会员组管理控制器
 */
class MemberGroupController extends AdminBaseController
{
    /**
     * 获取会员组列表
     * @return \think\Response
     */
    public function list()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->query("
                SELECT * FROM {$prefix}membergroup 
                ORDER BY listorder ASC, groupid ASC
            ");

            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', $list);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 添加会员组
     * @return \think\Response
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['groupname'])) {
            return $this->error('请输入会员组名称');
        }

        // 兼容前端：如果传的是 fandian，转换为 fanshui
        if (isset($input['fandian']) && !isset($input['fanshui'])) {
            $input['fanshui'] = $input['fandian'];
            unset($input['fandian']);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}membergroup 
                (groupname, level, isagent, isdefautreg, groupstatus, listorder, jjje, lowest, highest, fanshui, addtime)
                VALUES 
                (:groupname, :level, :isagent, :isdefautreg, :groupstatus, :listorder, :jjje, :lowest, :highest, :fanshui, :addtime)
            ");

            $result = $stmt->execute([
                ':groupname' => $input['groupname'],
                ':level' => intval($input['level'] ?? 0),
                ':isagent' => intval($input['isagent'] ?? 0),
                ':isdefautreg' => intval($input['isdefautreg'] ?? 0),
                ':groupstatus' => intval($input['groupstatus'] ?? 1),
                ':listorder' => intval($input['listorder'] ?? 0),
                ':jjje' => floatval($input['jjje'] ?? 0),
                ':lowest' => intval($input['lowest'] ?? 10),
                ':highest' => intval($input['highest'] ?? 50000),
                ':fanshui' => $input['fanshui'] ?? '0',
                ':addtime' => time()
            ]);

            if ($result) {
                $this->addAdminLog('membergroup_add', "添加会员组：{$input['groupname']}");
                return $this->success('添加成功');
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
     * 更新会员组
     * @param int $id
     * @return \think\Response
     */
    public function update($id)
    {
        if (!$this->request->isPut()) {
            return $this->error('请使用PUT请求', null, 405);
        }

        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        $input = $this->getPostParams();

        // 兼容前端：如果传的是 fandian，转换为 fanshui
        if (isset($input['fandian']) && !isset($input['fanshui'])) {
            $input['fanshui'] = $input['fandian'];
            unset($input['fandian']);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 构建更新字段（只更新实际存在的字段）
            $updateFields = [];
            $updateData = [':groupid' => $id];

            // 定义字段映射（前端字段 => 数据库字段）
            $fieldMap = [
                'groupname' => 'groupname',
                'level' => 'level',
                'isagent' => 'isagent',
                'isdefautreg' => 'isdefautreg',
                'groupstatus' => 'groupstatus',
                'listorder' => 'listorder',
                'jjje' => 'jjje',
                'lowest' => 'lowest',
                'highest' => 'highest',
                'fanshui' => 'fanshui'
            ];

            foreach ($fieldMap as $inputKey => $dbField) {
                if (isset($input[$inputKey])) {
                    $updateFields[] = "$dbField = :$dbField";

                    // 根据字段类型处理值
                    switch ($dbField) {
                        case 'jjje':
                            $updateData[":$dbField"] = floatval($input[$inputKey]);
                            break;
                        case 'level':
                        case 'isagent':
                        case 'isdefautreg':
                        case 'groupstatus':
                        case 'listorder':
                        case 'lowest':
                        case 'highest':
                            $updateData[":$dbField"] = intval($input[$inputKey]);
                            break;
                        default:
                            $updateData[":$dbField"] = $input[$inputKey];
                    }
                }
            }

            if (empty($updateFields)) {
                return $this->error('没有要更新的数据');
            }

            $sql = "UPDATE {$prefix}membergroup SET " . implode(', ', $updateFields) . " WHERE groupid = :groupid";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($updateData);

            if ($result) {
                $this->addAdminLog('membergroup_update', "更新会员组，ID：{$id}");
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
     * 删除会员组
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
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

            // 检查是否有会员使用该会员组
            $checkStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM {$prefix}member WHERE groupid = :groupid");
            $checkStmt->execute([':groupid' => $id]);
            $count = $checkStmt->fetch(\PDO::FETCH_ASSOC)['cnt'];

            if ($count > 0) {
                return $this->error('该会员组下还有会员，无法删除');
            }

            $stmt = $pdo->prepare("DELETE FROM {$prefix}membergroup WHERE groupid = :groupid");
            $result = $stmt->execute([':groupid' => $id]);

            if ($result) {
                $this->addAdminLog('membergroup_delete', "删除会员组，ID：{$id}");
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
     * 切换会员组状态
     * @param int $id
     * @return \think\Response
     */
    public function toggleStatus($id)
    {
        if (!$this->request->isPut()) {
            return $this->error('请使用PUT请求', null, 405);
        }

        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        $input = $this->getPostParams();
        $status = intval($input['status'] ?? 1);

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->prepare("UPDATE {$prefix}membergroup SET groupstatus = :status WHERE groupid = :groupid");
            $result = $stmt->execute([':status' => $status, ':groupid' => $id]);

            if ($result) {
                $this->addAdminLog('membergroup_status', "切换会员组状态，ID：{$id}，状态：{$status}");
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
}

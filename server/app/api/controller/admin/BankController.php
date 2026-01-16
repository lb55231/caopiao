<?php
namespace app\api\controller\admin;

/**
 * 银行管理控制器
 */
class BankController extends AdminBaseController
{
    /**
     * 获取支付设置列表
     * @return \think\Response
     */
    public function paysetList()
    {
        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->query("SELECT * FROM {$prefix}payset ORDER BY listorder ASC, id DESC");
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', ['list' => $list, 'total' => count($list)]);
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取系统银行列表
     * @return \think\Response
     */
    public function sysbankList()
    {
        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->query("SELECT * FROM {$prefix}sysbank ORDER BY listorder ASC, id DESC");
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', ['list' => $list, 'total' => count($list)]);
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取线路银行列表
     * @return \think\Response
     */
    public function linebankList()
    {
        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->query("SELECT * FROM {$prefix}linebanklist ORDER BY listorder ASC, id DESC");
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', ['list' => $list, 'total' => count($list)]);
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 添加支付设置
     * @return \think\Response
     */
    public function addPayset()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['paytype']) || empty($input['paytypetitle'])) {
            return $this->error('请填写标识和名称');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $configs = '';
            if (isset($input['configs']) && is_array($input['configs'])) {
                $configs = serialize($input['configs']);
            }

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}payset 
                (paytype, paytypetitle, ftitle, minmoney, maxmoney, remark, configs, isonline, listorder, state)
                VALUES 
                (:paytype, :paytypetitle, :ftitle, :minmoney, :maxmoney, :remark, :configs, :isonline, :listorder, :state)
            ");

            $result = $stmt->execute([
                ':paytype' => trim($input['paytype']),
                ':paytypetitle' => trim($input['paytypetitle']),
                ':ftitle' => trim($input['ftitle'] ?? ''),
                ':minmoney' => floatval($input['minmoney'] ?? 0),
                ':maxmoney' => floatval($input['maxmoney'] ?? 0),
                ':remark' => trim($input['remark'] ?? ''),
                ':configs' => $configs,
                ':isonline' => intval($input['isonline'] ?? -1),
                ':listorder' => intval($input['listorder'] ?? 0),
                ':state' => intval($input['state'] ?? 1)
            ]);

            if ($result) {
                $this->addAdminLog('payset_add', "添加支付设置：{$input['paytypetitle']}");
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
     * 更新支付设置
     * @param int $id
     * @return \think\Response
     */
    public function updatePayset($id = null)
    {
        if (!$this->request->isPut()) {
            return $this->error('请使用PUT请求', null, 405);
        }

        $input = $this->getPostParams();
        
        // 支持多种方式获取ID：路径参数、查询参数、请求体
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));

        if (empty($input['paytype']) || empty($input['paytypetitle'])) {
            return $this->error('请填写完整信息');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $configs = '';
            if (isset($input['configs']) && is_array($input['configs'])) {
                $configs = serialize($input['configs']);
            }

            $stmt = $pdo->prepare("
                UPDATE {$prefix}payset 
                SET paytype = :paytype, paytypetitle = :paytypetitle, ftitle = :ftitle,
                    minmoney = :minmoney, maxmoney = :maxmoney, remark = :remark,
                    configs = :configs, isonline = :isonline, listorder = :listorder, state = :state
                WHERE id = :id
            ");

            $result = $stmt->execute([
                ':id' => $id,
                ':paytype' => trim($input['paytype']),
                ':paytypetitle' => trim($input['paytypetitle']),
                ':ftitle' => trim($input['ftitle'] ?? ''),
                ':minmoney' => floatval($input['minmoney'] ?? 0),
                ':maxmoney' => floatval($input['maxmoney'] ?? 0),
                ':remark' => trim($input['remark'] ?? ''),
                ':configs' => $configs,
                ':isonline' => intval($input['isonline'] ?? -1),
                ':listorder' => intval($input['listorder'] ?? 0),
                ':state' => intval($input['state'] ?? 1)
            ]);

            if ($result) {
                $this->addAdminLog('payset_update', "更新支付设置，ID：{$id}");
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
     * 删除支付设置
     * @param int $id
     * @return \think\Response
     */
    public function deletePayset($id = null)
    {
        if (!$this->request->isDelete()) {
            return $this->error('请使用DELETE请求', null, 405);
        }

        // 支持多种方式获取ID：路径参数、查询参数
        $id = $id ?: $this->request->param('id');
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->prepare("DELETE FROM {$prefix}payset WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('payset_delete', "删除支付设置，ID：{$id}");
                return $this->success('删除成功');
            } else {
                return $this->error('删除失败');
            }

        } catch (\PDOException $e) {
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 切换支付设置状态
     * @param int $id
     * @return \think\Response
     */
    public function togglePaysetStatus($id = null)
    {
        if (!$this->request->isPut() && !$this->request->isPost()) {
            return $this->error('请使用PUT或POST请求', null, 405);
        }

        $input = $this->getPostParams();
        
        // 支持多种方式获取ID：路径参数、查询参数、请求体
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $status = isset($input['status']) ? intval($input['status']) : null;

            if ($status === null) {
                $checkStmt = $pdo->prepare("SELECT state FROM {$prefix}payset WHERE id = :id");
                $checkStmt->execute([':id' => $id]);
                $row = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                if (!$row) {
                    return $this->error('记录不存在');
                }
                $status = $row['state'] == 1 ? 0 : 1;
            }

            $stmt = $pdo->prepare("UPDATE {$prefix}payset SET state = :status WHERE id = :id");
            $result = $stmt->execute([':status' => $status, ':id' => $id]);

            if ($result) {
                $this->addAdminLog('payset_status', "切换支付设置状态，ID：{$id}");
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }

        } catch (\PDOException $e) {
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 添加系统银行
     * @return \think\Response
     */
    public function addSysbank()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['bankcode']) || empty($input['bankname'])) {
            return $this->error('请填写银行代码和银行名称');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}sysbank WHERE bankcode = :bankcode");
            $checkStmt->execute([':bankcode' => $input['bankcode']]);
            if ($checkStmt->fetch()) {
                return $this->error('该银行代码已存在');
            }

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}sysbank 
                (bankcode, bankname, banklogo, state, listorder, imgbg)
                VALUES 
                (:bankcode, :bankname, :banklogo, :state, :listorder, :imgbg)
            ");

            $result = $stmt->execute([
                ':bankcode' => $input['bankcode'],
                ':bankname' => $input['bankname'],
                ':banklogo' => $input['banklogo'] ?? '',
                ':state' => isset($input['state']) ? intval($input['state']) : 1,
                ':listorder' => isset($input['listorder']) ? intval($input['listorder']) : 0,
                ':imgbg' => $input['imgbg'] ?? ''
            ]);

            if ($result) {
                $this->addAdminLog('sysbank_add', "添加系统银行：{$input['bankname']}");
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
     * 更新系统银行
     * @param int $id
     * @return \think\Response
     */
    public function updateSysbank($id = null)
    {
        if (!$this->request->isPut()) {
            return $this->error('请使用PUT请求', null, 405);
        }

        $input = $this->getPostParams();
        
        // 支持多种方式获取ID：路径参数、查询参数、请求体
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $updateFields = [];
            $params = [':id' => $id];

            if (isset($input['bankcode'])) {
                $updateFields[] = 'bankcode = :bankcode';
                $params[':bankcode'] = $input['bankcode'];
            }
            if (isset($input['bankname'])) {
                $updateFields[] = 'bankname = :bankname';
                $params[':bankname'] = $input['bankname'];
            }
            if (isset($input['banklogo'])) {
                $updateFields[] = 'banklogo = :banklogo';
                $params[':banklogo'] = $input['banklogo'];
            }
            if (isset($input['imgbg'])) {
                $updateFields[] = 'imgbg = :imgbg';
                $params[':imgbg'] = $input['imgbg'];
            }
            if (isset($input['listorder'])) {
                $updateFields[] = 'listorder = :listorder';
                $params[':listorder'] = intval($input['listorder']);
            }
            if (isset($input['state'])) {
                $updateFields[] = 'state = :state';
                $params[':state'] = intval($input['state']);
            }

            if (empty($updateFields)) {
                return $this->error('没有要更新的字段');
            }

            $sql = "UPDATE {$prefix}sysbank SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->addAdminLog('sysbank_update', "更新系统银行，ID：{$id}");
                return $this->success('更新成功');
            } else {
                return $this->error('更新失败');
            }

        } catch (\PDOException $e) {
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 删除系统银行
     * @param int $id
     * @return \think\Response
     */
    public function deleteSysbank($id = null)
    {
        if (!$this->request->isDelete()) {
            return $this->error('请使用DELETE请求', null, 405);
        }

        // 支持多种方式获取ID：路径参数、查询参数
        $id = $id ?: $this->request->param('id');
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->prepare("DELETE FROM {$prefix}sysbank WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('sysbank_delete', "删除系统银行，ID：{$id}");
                return $this->success('删除成功');
            } else {
                return $this->error('删除失败');
            }

        } catch (\PDOException $e) {
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 切换系统银行状态
     * @param int $id
     * @return \think\Response
     */
    public function toggleSysbankStatus($id = null)
    {
        if (!$this->request->isPut() && !$this->request->isPost()) {
            return $this->error('请使用PUT或POST请求', null, 405);
        }

        $input = $this->getPostParams();
        
        // 支持多种方式获取ID：路径参数、查询参数、请求体
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $checkStmt = $pdo->prepare("SELECT state FROM {$prefix}sysbank WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            $row = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return $this->error('记录不存在');
            }

            $newStatus = $row['state'] == 1 ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE {$prefix}sysbank SET state = :status WHERE id = :id");
            $result = $stmt->execute([':status' => $newStatus, ':id' => $id]);

            if ($result) {
                $this->addAdminLog('sysbank_status', "切换系统银行状态，ID：{$id}");
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }

        } catch (\PDOException $e) {
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 添加线路银行
     * @return \think\Response
     */
    public function addLinebank()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['bankname']) || empty($input['accountname']) || empty($input['banknumber'])) {
            return $this->error('请填写完整的银行信息');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}linebanklist 
                (bankname, accountname, banknumber, banklogo, listorder, state)
                VALUES 
                (:bankname, :accountname, :banknumber, :banklogo, :listorder, :state)
            ");

            $result = $stmt->execute([
                ':bankname' => trim($input['bankname']),
                ':accountname' => trim($input['accountname']),
                ':banknumber' => trim($input['banknumber']),
                ':banklogo' => trim($input['banklogo'] ?? ''),
                ':listorder' => 0,
                ':state' => 1
            ]);

            if ($result) {
                $this->addAdminLog('linebank_add', "添加线路银行：{$input['bankname']}");
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
     * 更新线路银行
     * @param int $id
     * @return \think\Response
     */
    public function updateLinebank($id = null)
    {
        if (!$this->request->isPut()) {
            return $this->error('请使用PUT请求', null, 405);
        }

        $input = $this->getPostParams();
        
        // 支持多种方式获取ID：路径参数、查询参数、请求体
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $updateFields = [];
            $params = [':id' => $id];

            if (isset($input['bankname'])) {
                $updateFields[] = 'bankname = :bankname';
                $params[':bankname'] = trim($input['bankname']);
            }
            if (isset($input['accountname'])) {
                $updateFields[] = 'accountname = :accountname';
                $params[':accountname'] = trim($input['accountname']);
            }
            if (isset($input['banknumber'])) {
                $updateFields[] = 'banknumber = :banknumber';
                $params[':banknumber'] = trim($input['banknumber']);
            }
            if (isset($input['banklogo'])) {
                $updateFields[] = 'banklogo = :banklogo';
                $params[':banklogo'] = trim($input['banklogo']);
            }
            if (isset($input['listorder'])) {
                $updateFields[] = 'listorder = :listorder';
                $params[':listorder'] = intval($input['listorder']);
            }
            if (isset($input['state'])) {
                $updateFields[] = 'state = :state';
                $params[':state'] = intval($input['state']);
            }

            if (empty($updateFields)) {
                return $this->error('没有要更新的字段');
            }

            $sql = "UPDATE {$prefix}linebanklist SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->addAdminLog('linebank_update', "更新线路银行，ID：{$id}");
                return $this->success('更新成功');
            } else {
                return $this->error('更新失败');
            }

        } catch (\PDOException $e) {
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 删除线路银行
     * @param int $id
     * @return \think\Response
     */
    public function deleteLinebank($id = null)
    {
        if (!$this->request->isDelete()) {
            return $this->error('请使用DELETE请求', null, 405);
        }

        // 支持多种方式获取ID：路径参数、查询参数
        $id = $id ?: $this->request->param('id');
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->prepare("DELETE FROM {$prefix}linebanklist WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('linebank_delete', "删除线路银行，ID：{$id}");
                return $this->success('删除成功');
            } else {
                return $this->error('删除失败');
            }

        } catch (\PDOException $e) {
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 切换线路银行状态
     * @param int $id
     * @return \think\Response
     */
    public function toggleLinebankStatus($id = null)
    {
        if (!$this->request->isPut() && !$this->request->isPost()) {
            return $this->error('请使用PUT或POST请求', null, 405);
        }

        $input = $this->getPostParams();
        
        // 支持多种方式获取ID：路径参数、查询参数、请求体
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));
        
        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $checkStmt = $pdo->prepare("SELECT state FROM {$prefix}linebanklist WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            $row = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return $this->error('记录不存在');
            }

            $newStatus = $row['state'] == 1 ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE {$prefix}linebanklist SET state = :status WHERE id = :id");
            $result = $stmt->execute([':status' => $newStatus, ':id' => $id]);

            if ($result) {
                $this->addAdminLog('linebank_status', "切换线路银行状态，ID：{$id}");
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }

        } catch (\PDOException $e) {
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 保存支付设置排序
     * @return \think\Response
     */
    public function savePaysetOrder()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['orders']) || !is_array($input['orders'])) {
            return $this->error('参数错误');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $pdo->beginTransaction();

            foreach ($input['orders'] as $item) {
                if (isset($item['id']) && isset($item['listorder'])) {
                    $stmt = $pdo->prepare("UPDATE {$prefix}payset SET listorder = :order WHERE id = :id");
                    $stmt->execute([':order' => intval($item['listorder']), ':id' => intval($item['id'])]);
                }
            }

            $pdo->commit();

            $this->addAdminLog('payset_order', "保存支付设置排序");
            return $this->success('保存成功');

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

<?php
namespace app\api\controller\admin;

/**
 * 财务管理控制器
 */
class FinanceController extends AdminBaseController
{
    /**
     * 获取账变记录
     * @return \think\Response
     */
    public function fundRecords()
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
            $type = trim($this->request->param('type', ''));
            $startTime = intval($this->request->param('start_time', 0));
            $endTime = intval($this->request->param('end_time', 0));

            $offset = ($page - 1) * $pageSize;

            $where = [];
            $params = [];

            if (!empty($username)) {
                $where[] = "username LIKE :username";
                $params[':username'] = "%{$username}%";
            }

            if (!empty($type)) {
                $where[] = "type = :type";
                $params[':type'] = $type;
            }

            if ($startTime > 0) {
                $where[] = "oddtime >= :start_time";
                $params[':start_time'] = $startTime;
            }

            if ($endTime > 0) {
                $where[] = "oddtime <= :end_time";
                $params[':end_time'] = $endTime;
            }

            $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            // 获取总数
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}fuddetail {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 获取列表
            $stmt = $pdo->prepare("
                SELECT * FROM {$prefix}fuddetail 
                {$whereSQL}
                ORDER BY id DESC
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

    /**
     * 获取充值记录列表
     * @return \think\Response
     */
    public function rechargeList()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 10));
            $state = $this->request->param('state', '');
            $uid = $this->request->param('uid', '');
            $username = trim($this->request->param('username', ''));
            $trano = trim($this->request->param('trano', ''));
            $sDate = trim($this->request->param('sDate', ''));
            $eDate = trim($this->request->param('eDate', ''));
            $sAmout = $this->request->param('sAmout', '');
            $eAmout = $this->request->param('eAmout', '');

            $where = [];
            $params = [];

            if ($state !== '') {
                $where[] = "state = :state";
                $params[':state'] = intval($state);
            }

            if ($uid) {
                $where[] = "uid = :uid";
                $params[':uid'] = intval($uid);
            }

            if ($username) {
                $where[] = "username = :username";
                $params[':username'] = $username;
            }

            if ($trano) {
                $where[] = "trano = :trano";
                $params[':trano'] = $trano;
            }

            if ($sDate) {
                $where[] = "oddtime >= :sDate";
                $params[':sDate'] = strtotime($sDate);
            }

            if ($eDate) {
                $where[] = "oddtime <= :eDate";
                $params[':eDate'] = strtotime($eDate) + 86400;
            }

            if ($sAmout !== '') {
                $where[] = "amount >= :sAmout";
                $params[':sAmout'] = floatval($sAmout);
            }

            if ($eAmout !== '') {
                $where[] = "amount <= :eAmout";
                $params[':eAmout'] = floatval($eAmout);
            }

            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // 统计
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}recharge {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 列表
            $offset = ($page - 1) * $pageSize;
            $stmt = $pdo->prepare("
                SELECT * 
                FROM {$prefix}recharge 
                {$whereSQL}
                ORDER BY id DESC 
                LIMIT :offset, :pageSize
            ");

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->bindValue(':pageSize', $pageSize, \PDO::PARAM_INT);

            $stmt->execute();
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 统计数据
            $statsStmt = $pdo->query("
                SELECT 
                    SUM(CASE WHEN state = 1 THEN amount ELSE 0 END) as total_success_amount,
                    COUNT(CASE WHEN state = 1 THEN 1 END) as total_success_count,
                    SUM(CASE WHEN state = 1 AND isauto = 1 THEN amount ELSE 0 END) as auto_amount,
                    COUNT(CASE WHEN state = 1 AND isauto = 1 THEN 1 END) as auto_count,
                    SUM(CASE WHEN state = 1 AND isauto = 2 THEN amount ELSE 0 END) as manual_amount,
                    COUNT(CASE WHEN state = 1 AND isauto = 2 THEN 1 END) as manual_count
                FROM {$prefix}recharge
            ");
            $stats = $statsStmt->fetch(\PDO::FETCH_ASSOC);

            return $this->success('查询成功', [
                'list' => $list,
                'total' => intval($total),
                'page' => $page,
                'page_size' => $pageSize,
                'stats' => $stats
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 充值审核
     * @return \think\Response
     */
    public function rechargeAudit()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['id'])) {
            return $this->error('缺少ID参数');
        }

        if (!isset($input['state']) || !in_array($input['state'], [1, -1])) {
            return $this->error('状态参数错误');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $pdo->beginTransaction();

            // 获取记录
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}recharge WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $input['id']]);
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$record) {
                $pdo->rollBack();
                return $this->error('记录不存在');
            }

            if ($record['state'] != 0) {
                $pdo->rollBack();
                return $this->error('该记录已审核，无法重复操作');
            }

            $newState = intval($input['state']);
            $remark = isset($input['remark']) ? trim($input['remark']) : '';

            // 更新充值记录状态
            $updateStmt = $pdo->prepare("
                UPDATE {$prefix}recharge 
                SET state = :state,
                    stateadmin = :admin,
                    remark = :remark
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':state' => $newState,
                ':admin' => $this->adminInfo['username'] ?? 'admin',
                ':remark' => $remark ?: $record['remark'],
                ':id' => $input['id']
            ]);

            // 如果审核通过，增加用户余额
            if ($newState == 1) {
                $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :uid");
                $userStmt->execute([':uid' => $record['uid']]);
                $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

                if ($user) {
                    $oldMoney = floatval($user['balance']);
                    $newMoney = $oldMoney + floatval($record['amount']);

                    $updateUserStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = :money WHERE id = :uid");
                    $updateUserStmt->execute([
                        ':money' => $newMoney,
                        ':uid' => $record['uid']
                    ]);

                    // 更新充值记录的账变信息
                    $updateAccountStmt = $pdo->prepare("
                        UPDATE {$prefix}recharge 
                        SET oldaccountmoney = :old,
                            newaccountmoney = :new
                        WHERE id = :id
                    ");
                    $updateAccountStmt->execute([
                        ':old' => $oldMoney,
                        ':new' => $newMoney,
                        ':id' => $input['id']
                    ]);

                    // 处理洗码余额（充值时增加）
                    $settingStmt = $pdo->prepare("SELECT value FROM {$prefix}setting WHERE name = 'damaliang'");
                    $settingStmt->execute();
                    $damaliang = floatval($settingStmt->fetchColumn() ?: 0);

                    if ($damaliang > 0) {
                        // 计算洗码金额：充值金额 × 打码量%
                        $ximaAmount = ($record['amount'] * $damaliang / 100);
                        $ximaAmount = round($ximaAmount, 2);

                        $oldXima = floatval($user['xima'] ?? 0);
                        $newXima = $oldXima + $ximaAmount;

                        // 更新用户洗码余额
                        $updateXimaStmt = $pdo->prepare("UPDATE {$prefix}member SET xima = :xima WHERE id = :uid");
                        $updateXimaStmt->execute([
                            ':xima' => $newXima,
                            ':uid' => $record['uid']
                        ]);

                        // 记录洗码账变
                        $fudStmt = $pdo->prepare("
                            INSERT INTO {$prefix}fuddetail (
                                uid, username, type, typename, amount,
                                amountbefor, amountafter, remark, oddtime,
                                trano, expect, status_show, ticket_income_report
                            ) VALUES (
                                :uid, :username, :type, :typename, :amount,
                                :amountbefor, :amountafter, :remark, :oddtime,
                                :trano, :expect, :status_show, :ticket_income_report
                            )
                        ");

                        $fudStmt->execute([
                            ':uid' => $record['uid'],
                            ':username' => $user['username'],
                            ':type' => 'xima',
                            ':typename' => '洗码',
                            ':amount' => $ximaAmount,
                            ':amountbefor' => $oldXima,
                            ':amountafter' => $newXima,
                            ':remark' => "充值增加洗码额度（打码量{$damaliang}%）",
                            ':oddtime' => time(),
                            ':trano' => $record['trano'],
                            ':expect' => '',
                            ':status_show' => 1,
                            ':ticket_income_report' => 1
                        ]);
                    }
                }
            }

            $pdo->commit();

            $this->addAdminLog('recharge_audit', "审核充值记录，ID：{$input['id']}，状态：{$newState}");

            return $this->success('审核成功');

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 删除充值记录
     * @param int $id
     * @return \think\Response
     */
    public function rechargeDelete($id = null)
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

            $stmt = $pdo->prepare("DELETE FROM {$prefix}recharge WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('recharge_delete', "删除充值记录，ID：{$id}");
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
     * 获取提现记录列表
     * @return \think\Response
     */
    public function withdrawList()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 10));
            $state = $this->request->param('state', '');
            $uid = $this->request->param('uid', '');
            $username = trim($this->request->param('username', ''));
            $trano = trim($this->request->param('trano', ''));
            $sDate = trim($this->request->param('sDate', ''));
            $eDate = trim($this->request->param('eDate', ''));
            $sAmout = $this->request->param('sAmout', '');
            $eAmout = $this->request->param('eAmout', '');

            $where = [];
            $params = [];

            if ($state !== '') {
                $where[] = "state = :state";
                $params[':state'] = intval($state);
            }

            if ($uid) {
                $where[] = "uid = :uid";
                $params[':uid'] = intval($uid);
            }

            if ($username) {
                $where[] = "username = :username";
                $params[':username'] = $username;
            }

            if ($trano) {
                $where[] = "trano = :trano";
                $params[':trano'] = $trano;
            }

            if ($sDate) {
                $where[] = "oddtime >= :sDate";
                $params[':sDate'] = strtotime($sDate);
            }

            if ($eDate) {
                $where[] = "oddtime <= :eDate";
                $params[':eDate'] = strtotime($eDate) + 86400;
            }

            if ($sAmout !== '') {
                $where[] = "amount >= :sAmout";
                $params[':sAmout'] = floatval($sAmout);
            }

            if ($eAmout !== '') {
                $where[] = "amount <= :eAmout";
                $params[':eAmout'] = floatval($eAmout);
            }

            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // 统计
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}withdraw {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 列表
            $offset = ($page - 1) * $pageSize;
            $stmt = $pdo->prepare("
                SELECT * 
                FROM {$prefix}withdraw 
                {$whereSQL}
                ORDER BY id DESC 
                LIMIT :offset, :pageSize
            ");

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->bindValue(':pageSize', $pageSize, \PDO::PARAM_INT);

            $stmt->execute();
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 统计数据
            $statsStmt = $pdo->query("
                SELECT 
                    SUM(CASE WHEN state = 1 THEN amount ELSE 0 END) as total_success_amount,
                    COUNT(CASE WHEN state = 1 THEN 1 END) as total_success_count
                FROM {$prefix}withdraw
            ");
            $stats = $statsStmt->fetch(\PDO::FETCH_ASSOC);

            return $this->success('查询成功', [
                'list' => $list,
                'total' => intval($total),
                'page' => $page,
                'page_size' => $pageSize,
                'stats' => $stats
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 提现审核
     * @return \think\Response
     */
    public function withdrawAudit()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['id'])) {
            return $this->error('缺少ID参数');
        }

        if (!isset($input['state']) || !in_array($input['state'], [1, 2])) {
            return $this->error('状态参数错误');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $pdo->beginTransaction();

            // 获取记录
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}withdraw WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $input['id']]);
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$record) {
                $pdo->rollBack();
                return $this->error('记录不存在');
            }

            if ($record['state'] != 0) {
                $pdo->rollBack();
                return $this->error('该记录已审核，无法重复操作');
            }

            $newState = intval($input['state']);
            $remark = isset($input['remark']) ? trim($input['remark']) : '';

            // 更新提现记录状态
            $updateStmt = $pdo->prepare("
                UPDATE {$prefix}withdraw 
                SET state = :state,
                    stateadmin = :admin,
                    remark = :remark
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':state' => $newState,
                ':admin' => $this->adminInfo['username'] ?? 'admin',
                ':remark' => $remark ?: $record['remark'],
                ':id' => $input['id']
            ]);

            // 如果退回（state=2），需要退还用户余额
            if ($newState == 2) {
                $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :uid");
                $userStmt->execute([':uid' => $record['uid']]);
                $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

                if ($user) {
                    $oldMoney = floatval($user['balance']);
                    $newMoney = $oldMoney + floatval($record['amount']);

                    $updateUserStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = :money WHERE id = :uid");
                    $updateUserStmt->execute([
                        ':money' => $newMoney,
                        ':uid' => $record['uid']
                    ]);
                }
            }

            $pdo->commit();

            $this->addAdminLog('withdraw_audit', "审核提现记录，ID：{$input['id']}，状态：{$newState}");

            return $this->success('审核成功');

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 删除提现记录
     * @param int $id
     * @return \think\Response
     */
    public function withdrawDelete($id = null)
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

            $stmt = $pdo->prepare("DELETE FROM {$prefix}withdraw WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('withdraw_delete', "删除提现记录，ID：{$id}");
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
}

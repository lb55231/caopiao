<?php
namespace app\api\controller\admin;

/**
 * 会员管理控制器
 */
class MemberController extends AdminBaseController
{
    /**
     * 获取会员列表
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

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));
            $username = trim($this->request->param('username', ''));
            $userbankname = trim($this->request->param('userbankname', ''));
            $nickname = trim($this->request->param('nickname', ''));
            $phone = trim($this->request->param('phone', ''));
            $qq = trim($this->request->param('qq', ''));
            $loginip = trim($this->request->param('loginip', ''));
            $groupid = $this->request->param('groupid', '');
            $proxy = $this->request->param('proxy', '');
            $isnb = $this->request->param('isnb', '');
            $islock = $this->request->param('islock', '');
            $isonline = intval($this->request->param('isonline', 0));
            $ordertype = intval($this->request->param('ordertype', 0));
            $sDate = trim($this->request->param('sDate', ''));
            $eDate = trim($this->request->param('eDate', ''));
            $sAmount = trim($this->request->param('sAmount', ''));
            $eAmount = trim($this->request->param('eAmount', ''));
            $parentid = intval($this->request->param('parentid', 0));

            $offset = ($page - 1) * $pageSize;

            $where = [];
            $params = [];

            if (!empty($username)) {
                $where[] = "m.username LIKE :username";
                $params[':username'] = "%{$username}%";
            }

            if (!empty($userbankname)) {
                $where[] = "m.userbankname LIKE :userbankname";
                $params[':userbankname'] = "%{$userbankname}%";
            }

            if (!empty($nickname)) {
                $where[] = "m.nickname LIKE :nickname";
                $params[':nickname'] = "%{$nickname}%";
            }

            if (!empty($phone)) {
                $where[] = "m.phone LIKE :phone";
                $params[':phone'] = "%{$phone}%";
            }

            if (!empty($qq)) {
                $where[] = "m.qq LIKE :qq";
                $params[':qq'] = "%{$qq}%";
            }

            if (!empty($loginip)) {
                $where[] = "m.loginip LIKE :loginip";
                $params[':loginip'] = "%{$loginip}%";
            }

            if ($groupid !== '') {
                $where[] = "m.groupid = :groupid";
                $params[':groupid'] = $groupid;
            }

            if ($proxy !== '') {
                $where[] = "m.proxy = :proxy";
                $params[':proxy'] = $proxy;
            }

            if ($isnb !== '') {
                $where[] = "m.isnb = :isnb";
                $params[':isnb'] = $isnb;
            }

            if ($islock !== '') {
                $where[] = "m.islock = :islock";
                $params[':islock'] = $islock;
            }

            if ($isonline == 1) {
                $where[] = "(UNIX_TIMESTAMP() - m.onlinetime) < 600";
            }

            if (!empty($sDate)) {
                $sTimestamp = strtotime($sDate);
                $where[] = "m.regtime >= :sDate";
                $params[':sDate'] = $sTimestamp;
            }

            if (!empty($eDate)) {
                $eTimestamp = strtotime($eDate . ' 23:59:59');
                $where[] = "m.regtime <= :eDate";
                $params[':eDate'] = $eTimestamp;
            }

            if (!empty($sAmount)) {
                $where[] = "m.balance >= :sAmount";
                $params[':sAmount'] = $sAmount;
            }

            if (!empty($eAmount)) {
                $where[] = "m.balance <= :eAmount";
                $params[':eAmount'] = $eAmount;
            }

            if ($parentid > 0) {
                $where[] = "m.parentid = :parentid";
                $params[':parentid'] = $parentid;
            }

            $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            // 排序
            $orderSQL = 'ORDER BY m.id DESC';
            switch ($ordertype) {
                case 1: $orderSQL = 'ORDER BY m.regtime ASC'; break;
                case 2: $orderSQL = 'ORDER BY m.fandian DESC'; break;
                case 3: $orderSQL = 'ORDER BY m.fandian ASC'; break;
                case 4: $orderSQL = 'ORDER BY m.balance DESC'; break;
                case 5: $orderSQL = 'ORDER BY m.balance ASC'; break;
                case 6: $orderSQL = 'ORDER BY m.point DESC'; break;
                case 7: $orderSQL = 'ORDER BY m.point ASC'; break;
                case 8: $orderSQL = 'ORDER BY m.xima DESC'; break;
                case 9: $orderSQL = 'ORDER BY m.xima ASC'; break;
                case 16: $orderSQL = 'ORDER BY m.logintime DESC'; break;
                case 17: $orderSQL = 'ORDER BY m.logintime ASC'; break;
                case 18: $orderSQL = 'ORDER BY m.onlinetime DESC'; break;
                case 19: $orderSQL = 'ORDER BY m.onlinetime ASC'; break;
            }

            // 获取总数
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}member m {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 获取列表，包括统计数据
            $stmt = $pdo->prepare("
                SELECT 
                    m.*,
                    p.username as parent_username,
                    IF((UNIX_TIMESTAMP() - m.onlinetime) < 600, 1, 0) as isonline,
                    (SELECT COALESCE(SUM(amount), 0) FROM {$prefix}recharge WHERE uid = m.id AND state = 1) as total_recharge,
                    (SELECT COALESCE(SUM(amount), 0) FROM {$prefix}withdraw WHERE uid = m.id AND state = 1) as total_withdraw,
                    0 as total_win
                FROM {$prefix}member m
                LEFT JOIN {$prefix}member p ON m.parentid = p.id
                {$whereSQL}
                {$orderSQL}
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
     * 更新会员信息
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

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 构建更新SQL
            $updateFields = [
                'groupid = :groupid',
                'proxy = :proxy',
                'fandian = :fandian',
                'userbankname = :userbankname',
                'qq = :qq',
                'tel = :tel',
                'email = :email',
                'invite_code = :invite_code',
                'xinyu = :xinyu',
                'zijin_num = :zijin_num',
                'status_order = :status_order',
                'status_withdraw = :status_withdraw',
                'withdraw_deny_message = :withdraw_deny_message',
                'isnb = :isnb',
                'updatetime = :updatetime'
            ];

            $params = [
                ':groupid' => intval($input['groupid'] ?? 0),
                ':proxy' => intval($input['proxy'] ?? 0),
                ':fandian' => floatval($input['fandian'] ?? 0),
                ':userbankname' => $input['userbankname'] ?? '',
                ':qq' => $input['qq'] ?? '',
                ':tel' => $input['tel'] ?? '',
                ':email' => $input['email'] ?? '',
                ':invite_code' => $input['invite_code'] ?? '',
                ':xinyu' => intval($input['xinyu'] ?? 0),
                ':zijin_num' => intval($input['zijin_num'] ?? 0),
                ':status_order' => intval($input['status_order'] ?? 1),
                ':status_withdraw' => intval($input['status_withdraw'] ?? 1),
                ':withdraw_deny_message' => $input['withdraw_deny_message'] ?? '',
                ':isnb' => intval($input['isnb'] ?? 0),
                ':updatetime' => time(),
                ':id' => $id
            ];

            // 如果提供了密码，则更新密码
            if (!empty($input['password'])) {
                $updateFields[] = 'password = :password';
                $params[':password'] = md5($input['password']);
            }

            // 如果提供了资金密码，则更新资金密码
            if (!empty($input['tradepassword'])) {
                $updateFields[] = 'tradepassword = :tradepassword';
                $params[':tradepassword'] = md5($input['tradepassword']);
            }

            $sql = "UPDATE {$prefix}member SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->addAdminLog('member_update', "更新会员信息，ID：{$id}");
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
     * 切换会员锁定状态
     * @param int $id
     * @return \think\Response
     */
    public function toggleLock($id)
    {
        if (!$this->request->isPut()) {
            return $this->error('请使用PUT请求', null, 405);
        }

        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 获取当前状态
            $stmt = $pdo->prepare("SELECT islock FROM {$prefix}member WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $member = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$member) {
                return $this->error('会员不存在');
            }

            $newStatus = $member['islock'] == 0 ? 1 : 0;

            $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET islock = :islock WHERE id = :id");
            $result = $updateStmt->execute([':islock' => $newStatus, ':id' => $id]);

            if ($result) {
                $action = $newStatus == 1 ? '锁定' : '解锁';
                $this->addAdminLog('member_lock', "{$action}会员，ID：{$id}");
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
     * 会员余额变动
     * @return \think\Response
     */
    public function changeBalance()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['uid']) || empty($input['amount']) || empty($input['type'])) {
            return $this->error('参数不完整');
        }

        $uid = intval($input['uid']);
        $amount = floatval($input['amount']);
        $type = $input['type']; // 'add' 或 'sub'
        $remark = trim($input['remark'] ?? '管理员操作');

        if ($amount <= 0) {
            return $this->error('金额必须大于0');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $pdo->beginTransaction();

            // 获取用户信息
            $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :id FOR UPDATE");
            $userStmt->execute([':id' => $uid]);
            $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $pdo->rollBack();
                return $this->error('会员不存在');
            }

            $oldBalance = floatval($user['balance']);

            // 计算新余额
            if ($type === 'add') {
                $newBalance = $oldBalance + $amount;
                $changeAmount = $amount;
                $typename = '人工加款';
            } else {
                if ($oldBalance < $amount) {
                    $pdo->rollBack();
                    return $this->error('余额不足');
                }
                $newBalance = $oldBalance - $amount;
                $changeAmount = -$amount;
                $typename = '人工减款';
            }

            // 更新会员余额
            $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = :balance WHERE id = :id");
            $updateStmt->execute([':balance' => $newBalance, ':id' => $uid]);

            // 记录账变
            $trano = 'ADM' . date('ymdHis') . rand(1000, 9999);
            $insertStmt = $pdo->prepare("
                INSERT INTO {$prefix}fuddetail 
                (trano, uid, username, type, typename, amount, amountbefor, amountafter, oddtime, remark, status_show, ticket_income_report)
                VALUES 
                (:trano, :uid, :username, :type, :typename, :amount, :amountbefor, :amountafter, :oddtime, :remark, 1, 1)
            ");

            $insertStmt->execute([
                ':trano' => $trano,
                ':uid' => $uid,
                ':username' => $user['username'],
                ':type' => $type === 'add' ? 'admin_add' : 'admin_sub',
                ':typename' => $typename,
                ':amount' => $changeAmount,
                ':amountbefor' => $oldBalance,
                ':amountafter' => $newBalance,
                ':oddtime' => time(),
                ':remark' => $remark
            ]);

            $pdo->commit();

            $this->addAdminLog('member_balance', "{$typename}，会员：{$user['username']}，金额：{$amount}");

            return $this->success('操作成功', [
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance
            ]);

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 会员积分变动
     * @return \think\Response
     */
    public function changePoint()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['uid']) || !isset($input['amount']) || empty($input['type'])) {
            return $this->error('参数不完整');
        }

        $uid = intval($input['uid']);
        $amount = intval($input['amount']);
        $type = $input['type'];
        $remark = trim($input['remark'] ?? '管理员操作');

        if ($amount <= 0) {
            return $this->error('积分必须大于0');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $pdo->beginTransaction();

            // 获取用户信息
            $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :id FOR UPDATE");
            $userStmt->execute([':id' => $uid]);
            $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $pdo->rollBack();
                return $this->error('会员不存在');
            }

            $oldPoint = intval($user['point'] ?? 0);

            // 计算新积分
            if ($type === 'add') {
                $newPoint = $oldPoint + $amount;
                $typename = '人工加积分';
            } else {
                if ($oldPoint < $amount) {
                    $pdo->rollBack();
                    return $this->error('积分不足');
                }
                $newPoint = $oldPoint - $amount;
                $amount = -$amount;
                $typename = '人工减积分';
            }

            // 更新会员积分
            $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET point = :point WHERE id = :id");
            $updateStmt->execute([':point' => $newPoint, ':id' => $uid]);

            $pdo->commit();

            $this->addAdminLog('member_point', "{$typename}，会员：{$user['username']}，积分：{$amount}");

            return $this->success('操作成功', [
                'old_point' => $oldPoint,
                'new_point' => $newPoint
            ]);

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 会员洗码余额变动
     * @return \think\Response
     */
    public function changeXima()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['uid']) || empty($input['amount']) || empty($input['type'])) {
            return $this->error('参数不完整');
        }

        $uid = intval($input['uid']);
        $amount = floatval($input['amount']);
        $type = $input['type'];
        $remark = trim($input['remark'] ?? '管理员操作');

        if ($amount <= 0) {
            return $this->error('金额必须大于0');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $pdo->beginTransaction();

            // 获取用户信息
            $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :id FOR UPDATE");
            $userStmt->execute([':id' => $uid]);
            $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $pdo->rollBack();
                return $this->error('会员不存在');
            }

            $oldXima = floatval($user['xima'] ?? 0);

            // 计算新洗码余额
            if ($type === 'add') {
                $newXima = $oldXima + $amount;
                $typename = '人工加洗码';
            } else {
                if ($oldXima < $amount) {
                    $pdo->rollBack();
                    return $this->error('洗码余额不足');
                }
                $newXima = $oldXima - $amount;
                $typename = '人工减洗码';
            }

            // 更新会员洗码余额
            $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET xima = :xima WHERE id = :id");
            $updateStmt->execute([':xima' => $newXima, ':id' => $uid]);

            $pdo->commit();

            $this->addAdminLog('member_xima', "{$typename}，会员：{$user['username']}，金额：{$amount}");

            return $this->success('操作成功', [
                'old_xima' => $oldXima,
                'new_xima' => $newXima
            ]);

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 删除会员
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

            // 检查会员是否存在
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $member = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$member) {
                return $this->error('会员不存在');
            }

            // 检查是否有下级
            $childStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM {$prefix}member WHERE parentid = :id");
            $childStmt->execute([':id' => $id]);
            $childCount = $childStmt->fetch(\PDO::FETCH_ASSOC)['cnt'];

            if ($childCount > 0) {
                return $this->error('该会员还有下级，无法删除');
            }

            // 删除会员
            $deleteStmt = $pdo->prepare("DELETE FROM {$prefix}member WHERE id = :id");
            $result = $deleteStmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('member_delete', "删除会员：{$member['username']}");
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
     * 踢出会员（强制下线）
     * @param int $id
     * @return \think\Response
     */
    public function kickout($id)
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 更新在线时间为0，表示强制下线
            $stmt = $pdo->prepare("UPDATE {$prefix}member SET onlinetime = 0 WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('member_kickout', "踢出会员，ID：{$id}");
                return $this->success('踢出成功');
            } else {
                return $this->error('踢出失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 同IP会员检测
     * @return \think\Response
     */
    public function sameIp()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));
            $ip = trim($this->request->param('ip', ''));
            $minCount = intval($this->request->param('min_count', 2));

            $offset = ($page - 1) * $pageSize;

            $where = '';
            if (!empty($ip)) {
                $where = "WHERE regip = '{$ip}'";
            }

            // 获取同IP会员数据
            $stmt = $pdo->query("
                SELECT 
                    regip as ip,
                    COUNT(*) as member_count,
                    MIN(regtime) as first_reg_time,
                    MAX(regtime) as last_reg_time
                FROM {$prefix}member
                {$where}
                GROUP BY regip
                HAVING member_count >= {$minCount}
                ORDER BY member_count DESC, first_reg_time DESC
                LIMIT {$offset}, {$pageSize}
            ");

            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 获取每个IP的会员列表
            foreach ($list as &$item) {
                $memberStmt = $pdo->prepare("
                    SELECT id, username, balance, regtime 
                    FROM {$prefix}member 
                    WHERE regip = :ip
                    ORDER BY regtime DESC
                ");
                $memberStmt->execute([':ip' => $item['ip']]);
                $item['members'] = $memberStmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // 获取总数
            $countStmt = $pdo->query("
                SELECT COUNT(*) as total FROM (
                    SELECT regip
                    FROM {$prefix}member
                    {$where}
                    GROUP BY regip
                    HAVING COUNT(*) >= {$minCount}
                ) as t
            ");
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

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
     * 获取会员银行卡信息
     * @return \think\Response
     */
    public function banks()
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
            $bankname = trim($this->request->param('bankname', ''));
            $accountname = trim($this->request->param('accountname', ''));

            $offset = ($page - 1) * $pageSize;

            $where = ["state = 1"];
            $params = [];

            if (!empty($username)) {
                $where[] = "username LIKE :username";
                $params[':username'] = "%{$username}%";
            }

            if (!empty($bankname)) {
                $where[] = "bankname LIKE :bankname";
                $params[':bankname'] = "%{$bankname}%";
            }

            if (!empty($accountname)) {
                $where[] = "accountname LIKE :accountname";
                $params[':accountname'] = "%{$accountname}%";
            }

            $whereSQL = 'WHERE ' . implode(' AND ', $where);

            // 获取总数
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}banklist {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 获取列表
            $stmt = $pdo->prepare("
                SELECT * FROM {$prefix}banklist 
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
     * 获取会员充值记录
     * @return \think\Response
     */
    public function rechargeRecords()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $uid = intval($this->request->param('uid', 0));
            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));

            if (empty($uid)) {
                return $this->error('缺少会员ID');
            }

            $offset = ($page - 1) * $pageSize;

            // 获取总数和总金额
            $countStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(CASE WHEN state = 1 THEN amount ELSE 0 END), 0) as totalAmount
                FROM {$prefix}recharge 
                WHERE uid = :uid
            ");
            $countStmt->execute([':uid' => $uid]);
            $stats = $countStmt->fetch(\PDO::FETCH_ASSOC);

            // 获取列表
            $stmt = $pdo->prepare("
                SELECT * FROM {$prefix}recharge 
                WHERE uid = :uid
                ORDER BY id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute([':uid' => $uid]);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($stats['total']),
                'totalAmount' => floatval($stats['totalAmount'])
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取会员提现记录
     * @return \think\Response
     */
    public function withdrawRecords()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $uid = intval($this->request->param('uid', 0));
            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));

            if (empty($uid)) {
                return $this->error('缺少会员ID');
            }

            $offset = ($page - 1) * $pageSize;

            // 获取总数和总金额
            $countStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(CASE WHEN state = 1 THEN amount ELSE 0 END), 0) as totalAmount
                FROM {$prefix}withdraw 
                WHERE uid = :uid
            ");
            $countStmt->execute([':uid' => $uid]);
            $stats = $countStmt->fetch(\PDO::FETCH_ASSOC);

            // 获取列表
            $stmt = $pdo->prepare("
                SELECT * FROM {$prefix}withdraw 
                WHERE uid = :uid
                ORDER BY id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute([':uid' => $uid]);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($stats['total']),
                'totalAmount' => floatval($stats['totalAmount'])
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取会员游戏记录
     * @return \think\Response
     */
    public function gameRecords()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $uid = intval($this->request->param('uid', 0));
            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));

            if (empty($uid)) {
                return $this->error('缺少会员ID');
            }

            $offset = ($page - 1) * $pageSize;

            // 获取总数、总投注和总中奖
            $countStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(amount), 0) as totalBet,
                    COALESCE(SUM(okamount), 0) as totalWin
                FROM {$prefix}touzhu 
                WHERE uid = :uid
            ");
            $countStmt->execute([':uid' => $uid]);
            $stats = $countStmt->fetch(\PDO::FETCH_ASSOC);

            // 获取列表
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    expect,
                    cpname,
                    playtitle as wanfa,
                    tzcode as haoma,
                    amount,
                    okamount,
                    isdraw,
                    oddtime
                FROM {$prefix}touzhu
                WHERE uid = :uid
                ORDER BY id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute([':uid' => $uid]);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($stats['total']),
                'totalBet' => floatval($stats['totalBet']),
                'totalWin' => floatval($stats['totalWin'])
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

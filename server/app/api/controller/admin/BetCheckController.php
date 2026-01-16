<?php
namespace app\api\controller\admin;

/**
 * 注单异常检查控制器
 */
class BetCheckController extends AdminBaseController
{
    /**
     * 获取异常注单列表
     * 检查投注时间和开奖时间差值较小的注单（可能存在作弊）
     */
    public function check()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $cpname = $this->request->param('cpname', '');
            $username = $this->request->param('username', '');
            $shijiancha = intval($this->request->param('shijiancha', 130)); // 时间差（秒），默认130秒
            $page = max(1, intval($this->request->param('page', 1)));
            $pageSize = min(100, max(10, intval($this->request->param('page_size', 20))));
            $offset = ($page - 1) * $pageSize;

            $where = "b.name != ''";
            $params = [];

            if ($cpname) {
                $where .= " AND a.cpname = :cpname";
                $params[':cpname'] = $cpname;
            }

            if ($username) {
                $where .= " AND a.username = :username";
                $params[':username'] = $username;
            }

            if ($shijiancha > 0) {
                $where .= " AND (b.opentime - a.oddtime) <= :shijiancha";
                $params[':shijiancha'] = $shijiancha;
            }

            // 查询总数
            $countSql = "
                SELECT COUNT(*) as total
                FROM {$prefix}touzhu a
                LEFT JOIN {$prefix}kaijiang b ON a.cpname = b.name AND a.expect = b.expect
                LEFT JOIN {$prefix}caipiao c ON a.cpname = c.name
                WHERE {$where}
            ";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 查询列表
            $sql = "
                SELECT 
                    a.*,
                    b.name as kj_name,
                    b.opentime as kj_opentime,
                    b.expect as kj_expect,
                    b.opencode as kj_opencode,
                    c.ftime,
                    c.issys,
                    c.name as cp_name,
                    c.title as cp_title,
                    (b.opentime - a.oddtime) as time_diff
                FROM {$prefix}touzhu a
                LEFT JOIN {$prefix}kaijiang b ON a.cpname = b.name AND a.expect = b.expect
                LEFT JOIN {$prefix}caipiao c ON a.cpname = c.name
                WHERE {$where}
                ORDER BY a.id DESC
                LIMIT {$offset}, {$pageSize}
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 添加风险等级标记
            foreach ($list as &$item) {
                $timeDiff = intval($item['time_diff'] ?? 0);
                if ($timeDiff <= 30) {
                    $item['risk_level'] = 'high';      // 高风险（30秒内）
                } elseif ($timeDiff <= 60) {
                    $item['risk_level'] = 'medium';    // 中风险（60秒内）
                } else {
                    $item['risk_level'] = 'low';       // 低风险
                }
            }

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total),
                'page' => $page,
                'page_size' => $pageSize,
                'shijiancha' => $shijiancha
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 撤单操作
     */
    public function cancelBet()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['id'])) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $pdo->beginTransaction();

            // 获取投注信息
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}touzhu WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);
            $bet = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$bet) {
                $pdo->rollBack();
                return $this->error('投注记录不存在');
            }

            // 只有未派奖的注单才能撤销（isdraw = 0 或 -1）
            if (!in_array($bet['isdraw'], [0, -1])) {
                $pdo->rollBack();
                return $this->error('该注单状态不允许撤单');
            }

            // 更新投注状态为已撤单（-2）
            $updateBetStmt = $pdo->prepare("UPDATE {$prefix}touzhu SET isdraw = -2 WHERE id = :id");
            $updateBetStmt->execute([':id' => $input['id']]);

            // 获取用户信息
            $userStmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :uid");
            $userStmt->execute([':uid' => $bet['uid']]);
            $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                $nowTime = time();
                $trano = $bet['trano'];
                $amount = abs(floatval($bet['amount']));

                // 1. 退回投注金额到余额
                $updateBalanceStmt = $pdo->prepare("UPDATE {$prefix}member SET balance = balance + :amount WHERE id = :uid");
                $updateBalanceStmt->execute([':amount' => $amount, ':uid' => $bet['uid']]);

                // 记录账变：退回投注金额
                $fudStmt = $pdo->prepare("
                    INSERT INTO {$prefix}fuddetail 
                    (trano, uid, username, amount, amountbefor, amountafter, oddtime, remark, type, typename)
                    VALUES 
                    (:trano, :uid, :username, :amount, :amountbefor, :amountafter, :oddtime, :remark, :type, :typename)
                ");
                $fudStmt->execute([
                    ':trano' => $trano,
                    ':uid' => $user['id'],
                    ':username' => $user['username'],
                    ':amount' => $amount,
                    ':amountbefor' => $user['balance'],
                    ':amountafter' => $user['balance'] + $amount,
                    ':oddtime' => $nowTime,
                    ':remark' => '撤单退回',
                    ':type' => 'cancel',
                    ':typename' => '撤单退回'
                ]);

                // 2. 退回洗码金额
                $updateXimaStmt = $pdo->prepare("UPDATE {$prefix}member SET xima = xima + :amount WHERE id = :uid");
                $updateXimaStmt->execute([':amount' => $amount, ':uid' => $bet['uid']]);

                // 记录账变：退回洗码
                $fudStmt->execute([
                    ':trano' => $trano,
                    ':uid' => $user['id'],
                    ':username' => $user['username'],
                    ':amount' => $amount,
                    ':amountbefor' => $user['xima'],
                    ':amountafter' => $user['xima'] + $amount,
                    ':oddtime' => $nowTime,
                    ':remark' => '撤单退回洗码账户',
                    ':type' => 'xima',
                    ':typename' => '洗码'
                ]);
            }

            $pdo->commit();

            $this->addAdminLog('bet_cancel', "投注撤单，单号：{$trano}");

            return $this->success('撤单成功');

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 修改投注号码
     */
    public function updateBetCode()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['trano']) || empty($input['tzcode'])) {
            return $this->error('参数错误');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 获取投注记录
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}touzhu WHERE trano = :trano");
            $stmt->execute([':trano' => $input['trano']]);
            $bet = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$bet) {
                return $this->error('投注记录不存在');
            }

            // 验证长度一致
            if (strlen($bet['tzcode']) != strlen($input['tzcode'])) {
                return $this->error('修改后的记录号和修改前的记录号长度应保持一致');
            }

            $updateStmt = $pdo->prepare("UPDATE {$prefix}touzhu SET tzcode = :tzcode WHERE trano = :trano");
            $result = $updateStmt->execute([
                ':tzcode' => $input['tzcode'],
                ':trano' => $input['trano']
            ]);

            if ($result) {
                $this->addAdminLog('bet_update_code', "修改投注号码，单号：{$input['trano']}");
                return $this->success('修改成功');
            } else {
                return $this->error('修改失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

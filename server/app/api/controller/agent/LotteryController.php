<?php
namespace app\api\controller\agent;

/**
 * 代理彩票控制器
 */
class LotteryController extends AgentBaseController
{
    /**
     * 获取彩种列表
     */
    public function types()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->query("
                SELECT 
                    id, typeid, title, name, ftime, qishu, ftitle, logo,
                    issys, isopen, iswh, listorder, allsort,
                    closetime1, closetime2, expecttime
                FROM {$prefix}caipiao
                WHERE isopen = 1
                ORDER BY allsort ASC, id DESC
            ");
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => count($list)
            ]);

        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取开奖记录
     */
    public function results()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $cpname = $this->request->param('cpname', '');
            $expect = $this->request->param('expect', '');
            $startDate = $this->request->param('start_date', '');
            $endDate = $this->request->param('end_date', '');
            $page = max(1, intval($this->request->param('page', 1)));
            $pageSize = min(100, max(10, intval($this->request->param('page_size', 20))));

            $where = ['1=1'];
            $params = [];

            if ($cpname) {
                $where[] = 'name = :cpname';
                $params[':cpname'] = $cpname;
            }

            if ($expect) {
                $where[] = 'expect LIKE :expect';
                $params[':expect'] = '%' . $expect . '%';
            }

            if ($startDate) {
                $where[] = 'FROM_UNIXTIME(opentime) >= :start_date';
                $params[':start_date'] = $startDate . ' 00:00:00';
            }

            if ($endDate) {
                $where[] = 'FROM_UNIXTIME(opentime) <= :end_date';
                $params[':end_date'] = $endDate . ' 23:59:59';
            }

            $whereSQL = implode(' AND ', $where);

            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}kaijiang WHERE {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $offset = ($page - 1) * $pageSize;
            $stmt = $pdo->prepare("
                SELECT 
                    id, name as cpname, title as cptitle, expect,
                    opencode, opentime, drawtime, isdraw, source, addtime
                FROM {$prefix}kaijiang
                WHERE {$whereSQL}
                ORDER BY id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total),
                'page' => $page,
                'page_size' => $pageSize
            ]);

        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取注单记录（只能查看下级用户的）
     */
    public function betRecords()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $cpname = $this->request->param('cpname', '');
            $username = $this->request->param('username', '');
            $expect = $this->request->param('expect', '');
            $trano = $this->request->param('trano', '');
            $status = $this->request->param('status', '');
            $sDate = $this->request->param('sDate', '');
            $eDate = $this->request->param('eDate', '');
            $page = max(1, intval($this->request->param('page', 1)));
            $pageSize = min(100, max(10, intval($this->request->param('page_size', 20))));

            // 获取下级用户ID列表
            $userStmt = $pdo->prepare("SELECT id FROM {$prefix}member WHERE parentid = :pid");
            $userStmt->execute([':pid' => $this->agentId]);
            $userIds = $userStmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($userIds)) {
                return $this->success('获取成功', [
                    'list' => [],
                    'total' => 0,
                    'stats' => [
                        'total_count' => 0,
                        'total_amount' => 0,
                        'total_award' => 0,
                    ]
                ]);
            }

            $userIdStr = implode(',', $userIds);

            $where = ["uid IN ({$userIdStr})"];
            $params = [];

            if ($cpname) {
                $where[] = 'cpname = :cpname';
                $params[':cpname'] = $cpname;
            }

            if ($username) {
                $where[] = 'username LIKE :username';
                $params[':username'] = '%' . $username . '%';
            }

            if ($expect) {
                $where[] = 'expect LIKE :expect';
                $params[':expect'] = '%' . $expect . '%';
            }

            if ($trano) {
                $where[] = 'trano LIKE :trano';
                $params[':trano'] = '%' . $trano . '%';
            }

            if ($status !== '' && $status != '999') {
                $where[] = 'isdraw = :isdraw';
                $params[':isdraw'] = $status;
            }

            if ($sDate) {
                $where[] = 'FROM_UNIXTIME(addtime) >= :start_date';
                $params[':start_date'] = $sDate . ' 00:00:00';
            }

            if ($eDate) {
                $where[] = 'FROM_UNIXTIME(addtime) <= :end_date';
                $params[':end_date'] = $eDate . ' 23:59:59';
            }

            $whereSQL = implode(' AND ', $where);

            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}touzhu WHERE {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $offset = ($page - 1) * $pageSize;
            $stmt = $pdo->prepare("
                SELECT 
                    id, uid, username, cpname, cptitle, expect,
                    playid, playtitle, tzcode, opencode, trano,
                    amount, okamount, isdraw, oddtime, source
                FROM {$prefix}touzhu
                WHERE {$whereSQL}
                ORDER BY id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 统计数据
            $statsStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_count,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN isdraw = 1 THEN okamount ELSE 0 END) as total_award,
                    SUM(CASE WHEN isdraw = 0 THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN isdraw = 1 THEN 1 ELSE 0 END) as win_count,
                    SUM(CASE WHEN isdraw = 2 THEN 1 ELSE 0 END) as lose_count
                FROM {$prefix}touzhu
                WHERE {$whereSQL}
            ");
            $statsStmt->execute($params);
            $stats = $statsStmt->fetch(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total),
                'page' => $page,
                'page_size' => $pageSize,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

<?php
namespace app\api\controller\agent;

/**
 * 代理财务控制器
 */
class FinanceController extends AgentBaseController
{
    /**
     * 获取账变记录
     */
    public function accountChange()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));
            $type = trim($this->request->param('type', ''));
            $startTime = intval($this->request->param('start_time', 0));
            $endTime = intval($this->request->param('end_time', 0));

            $offset = ($page - 1) * $pageSize;

            $where = ['uid = :uid'];
            $params = [':uid' => $this->agentId];

            if ($type) {
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

            $whereSQL = implode(' AND ', $where);

            // 获取总数
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}fuddetail WHERE {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 获取列表
            $stmt = $pdo->prepare("
                SELECT * FROM {$prefix}fuddetail 
                WHERE {$whereSQL}
                ORDER BY id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total)
            ]);

        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取充值记录
     */
    public function rechargeList()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 10));
            $state = $this->request->param('state', '');

            $offset = ($page - 1) * $pageSize;

            $where = ['uid = :uid'];
            $params = [':uid' => $this->agentId];

            if ($state !== '') {
                $where[] = "state = :state";
                $params[':state'] = intval($state);
            }

            $whereSQL = implode(' AND ', $where);

            // 统计
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}recharge WHERE {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 列表
            $stmt = $pdo->prepare("
                SELECT * 
                FROM {$prefix}recharge 
                WHERE {$whereSQL}
                ORDER BY id DESC 
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('查询成功', [
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
     * 获取提现记录
     */
    public function withdrawList()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 10));
            $state = $this->request->param('state', '');

            $offset = ($page - 1) * $pageSize;

            $where = ['uid = :uid'];
            $params = [':uid' => $this->agentId];

            if ($state !== '') {
                $where[] = "state = :state";
                $params[':state'] = intval($state);
            }

            $whereSQL = implode(' AND ', $where);

            // 统计
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}withdraw WHERE {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 列表
            $stmt = $pdo->prepare("
                SELECT * 
                FROM {$prefix}withdraw 
                WHERE {$whereSQL}
                ORDER BY id DESC 
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('查询成功', [
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
     * 获取收益报表
     */
    public function profitReport()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 10));
            $startTime = intval($this->request->param('start_time', 0));
            $endTime = intval($this->request->param('end_time', 0));

            $offset = ($page - 1) * $pageSize;

            $where = ['uid = :uid', "type IN ('rebate', 'commission', 'fandian')"];
            $params = [':uid' => $this->agentId];

            if ($startTime > 0) {
                $where[] = "oddtime >= :start_time";
                $params[':start_time'] = $startTime;
            }

            if ($endTime > 0) {
                $where[] = "oddtime <= :end_time";
                $params[':end_time'] = $endTime;
            }

            $whereSQL = implode(' AND ', $where);

            // 获取总数
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}fuddetail WHERE {$whereSQL}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 获取列表
            $stmt = $pdo->prepare("
                SELECT * FROM {$prefix}fuddetail 
                WHERE {$whereSQL}
                ORDER BY id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total)
            ]);

        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

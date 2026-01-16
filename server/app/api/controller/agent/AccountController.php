<?php
namespace app\api\controller\agent;

/**
 * 代理账户控制器
 */
class AccountController extends AgentBaseController
{
    /**
     * 获取代理账户信息
     */
    public function info()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 获取代理信息
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE id = :id");
            $stmt->execute([':id' => $this->agentId]);
            $agent = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$agent) {
                return $this->error('代理不存在');
            }

            // 获取下级用户数量
            $userCountStmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$prefix}member WHERE parentid = :id");
            $userCountStmt->execute([':id' => $this->agentId]);
            $userCount = $userCountStmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // 获取今日收益
            $todayStart = strtotime(date('Y-m-d 00:00:00'));
            $todayEnd = strtotime(date('Y-m-d 23:59:59'));
            
            $todayProfitStmt = $pdo->prepare("
                SELECT SUM(amount) as total 
                FROM {$prefix}fuddetail 
                WHERE uid = :uid 
                AND type IN ('rebate', 'commission', 'fandian') 
                AND oddtime >= :start 
                AND oddtime <= :end
            ");
            $todayProfitStmt->execute([
                ':uid' => $this->agentId,
                ':start' => $todayStart,
                ':end' => $todayEnd
            ]);
            $todayProfit = $todayProfitStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            // 获取累计收益
            $totalProfitStmt = $pdo->prepare("
                SELECT SUM(amount) as total 
                FROM {$prefix}fuddetail 
                WHERE uid = :uid 
                AND type IN ('rebate', 'commission', 'fandian')
            ");
            $totalProfitStmt->execute([':uid' => $this->agentId]);
            $totalProfit = $totalProfitStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            return $this->success('获取成功', [
                'username' => $agent['username'],
                'realname' => $agent['realname'] ?? '',
                'phone' => $agent['phone'] ?? '',
                'balance' => number_format($agent['balance'], 2, '.', ''),
                'todayProfit' => number_format($todayProfit, 2, '.', ''),
                'totalProfit' => number_format($totalProfit, 2, '.', ''),
                'userCount' => intval($userCount),
                'registerTime' => date('Y-m-d H:i:s', $agent['regtime']),
                'lastLoginTime' => date('Y-m-d H:i:s', $agent['logintime']),
                'status' => $agent['islock'] == 0 ? 1 : 0,
                'level' => '代理',
                'remark' => $agent['remark'] ?? '',
            ]);

        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

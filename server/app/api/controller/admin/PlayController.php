<?php
namespace app\api\controller\admin;

/**
 * 玩法管理控制器
 */
class PlayController extends AdminBaseController
{
    /**
     * 获取玩法列表
     */
    public function list()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $typeid = $this->request->param('typeid', '');
            $playid = $this->request->param('playid', '');
            $page = max(1, intval($this->request->param('page', 1)));
            $pageSize = min(100, max(10, intval($this->request->param('page_size', 50))));
            $offset = ($page - 1) * $pageSize;

            $where = '1=1';
            $params = [];

            if ($typeid) {
                $where .= ' AND typeid = :typeid';
                $params[':typeid'] = $typeid;
            }

            if ($playid) {
                $where .= ' AND playid = :playid';
                $params[':playid'] = $playid;
            }

            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}wanfa WHERE {$where}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->prepare("
                SELECT * FROM {$prefix}wanfa 
                WHERE {$where}
                ORDER BY typeid ASC, playid ASC, id ASC
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

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 更新玩法
     */
    public function update($id = null)
    {
        if (!$this->request->isPut() && !$this->request->isPost()) {
            return $this->error('请使用PUT或POST请求', null, 405);
        }

        $input = $this->getPostParams();
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));

        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $updateFields = [];
            $params = [':id' => $id];

            // 可更新的字段
            $allowedFields = [
                'rate' => 'rate',           // 赔率
                'maxrate' => 'maxrate',     // 最大赔率
                'minrate' => 'minrate',     // 最小赔率
                'maxzs' => 'maxzs',         // 最高注数
                'minxf' => 'minxf',         // 最低消费
                'maxxf' => 'maxxf',         // 最大投注金额
                'maxprize' => 'maxprize',   // 最高奖金
                'isopen' => 'isopen',       // 是否开启
                'remark' => 'remark'        // 备注
            ];

            foreach ($allowedFields as $field => $dbField) {
                if (isset($input[$field])) {
                    $updateFields[] = "{$dbField} = :{$field}";
                    $params[":{$field}"] = $input[$field];
                }
            }

            if (empty($updateFields)) {
                return $this->error('没有需要更新的字段');
            }

            $sql = "UPDATE {$prefix}wanfa SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->addAdminLog('play_update', "更新玩法，ID：{$id}");
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
     * 批量更新赔率
     */
    public function batchUpdateRate()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['typeid']) || !isset($input['rate_change'])) {
            return $this->error('参数错误');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $typeid = $input['typeid'];
            $rateChange = floatval($input['rate_change']); // 赔率变化值（正数增加，负数减少）

            $pdo->beginTransaction();

            // 更新该彩种的所有玩法赔率
            $stmt = $pdo->prepare("
                UPDATE {$prefix}wanfa 
                SET rate = rate + :rate_change
                WHERE typeid = :typeid AND rate + :rate_change > 0
            ");
            
            $result = $stmt->execute([
                ':rate_change' => $rateChange,
                ':typeid' => $typeid
            ]);

            $pdo->commit();

            if ($result) {
                $this->addAdminLog('play_batch_rate', "批量调整 {$typeid} 玩法赔率：{$rateChange}");
                return $this->success('批量更新成功');
            } else {
                return $this->error('更新失败');
            }

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 切换玩法状态
     */
    public function toggleStatus($id = null)
    {
        if (!$this->request->isPost() && !$this->request->isPut()) {
            return $this->error('请使用POST或PUT请求', null, 405);
        }

        $input = $this->getPostParams();
        $id = $id ?: ($this->request->param('id') ?: ($input['id'] ?? null));

        if (empty($id)) {
            return $this->error('缺少ID参数');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $checkStmt = $pdo->prepare("SELECT isopen FROM {$prefix}wanfa WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            $play = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$play) {
                return $this->error('玩法不存在');
            }

            $newStatus = $play['isopen'] == 1 ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE {$prefix}wanfa SET isopen = :isopen WHERE id = :id");
            $result = $stmt->execute([':isopen' => $newStatus, ':id' => $id]);

            if ($result) {
                $action = $newStatus == 1 ? '开启' : '关闭';
                $this->addAdminLog('play_toggle', "{$action}玩法，ID：{$id}");
                return $this->success('操作成功', ['isopen' => $newStatus]);
            } else {
                return $this->error('操作失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

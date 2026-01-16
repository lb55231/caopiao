<?php
namespace app\api\controller\admin;

/**
 * 彩票管理控制器
 */
class LotteryController extends AdminBaseController
{
    /**
     * 获取彩种列表
     * @return \think\Response
     */
    public function types()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $typeid = $this->request->param('typeid', '');

            $where = '1=1';
            $params = [];

            if ($typeid) {
                $where .= ' AND typeid = :typeid';
                $params[':typeid'] = $typeid;
            }

            $orderBy = $typeid ? 'listorder ASC, id DESC' : 'allsort ASC, id DESC';

            $stmt = $pdo->prepare("
                SELECT 
                    id, typeid, title, name, ftime, qishu, ftitle, logo,
                    issys, isopen, iswh, listorder, allsort,
                    closetime1, closetime2, expecttime
                FROM {$prefix}caipiao
                WHERE {$where}
                ORDER BY {$orderBy}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => count($list)
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取开奖记录列表
     * @return \think\Response
     */
    public function results()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
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

            $where = '1=1';
            $params = [];

            if ($cpname) {
                $where .= ' AND k.name = :cpname';
                $params[':cpname'] = $cpname;
            }

            if ($expect) {
                $where .= ' AND k.expect LIKE :expect';
                $params[':expect'] = '%' . $expect . '%';
            }

            if ($startDate) {
                $where .= ' AND FROM_UNIXTIME(k.opentime) >= :start_date';
                $params[':start_date'] = $startDate . ' 00:00:00';
            }

            if ($endDate) {
                $where .= ' AND FROM_UNIXTIME(k.opentime) <= :end_date';
                $params[':end_date'] = $endDate . ' 23:59:59';
            }

            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}kaijiang k WHERE {$where}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $offset = ($page - 1) * $pageSize;
            $stmt = $pdo->prepare("
                SELECT 
                    k.id, k.name as cpname, k.title as cptitle, k.expect,
                    k.opencode, k.opentime, k.drawtime, k.isdraw, k.source, k.addtime
                FROM {$prefix}kaijiang k
                WHERE {$where}
                ORDER BY k.id DESC
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
     * 添加开奖记录
     * @return \think\Response
     */
    public function addResult()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['cpname']) || empty($input['expect']) || empty($input['opencode'])) {
            return $this->error('请填写完整的开奖信息');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $cpStmt = $pdo->prepare("SELECT title FROM {$prefix}caipiao WHERE name = :name");
            $cpStmt->execute([':name' => $input['cpname']]);
            $cpInfo = $cpStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$cpInfo) {
                return $this->error('彩种不存在');
            }

            $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}kaijiang WHERE name = :name AND expect = :expect");
            $checkStmt->execute([':name' => $input['cpname'], ':expect' => $input['expect']]);

            if ($checkStmt->fetch()) {
                return $this->error('该期号已存在开奖记录');
            }

            $opentime = isset($input['opentime']) && $input['opentime'] ? strtotime($input['opentime']) : time();

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}kaijiang 
                (addtime, name, title, opencode, sourcecode, remarks, source, expect, opentime, isdraw, drawtime)
                VALUES 
                (:addtime, :name, :title, :opencode, :sourcecode, :remarks, :source, :expect, :opentime, :isdraw, :drawtime)
            ");

            $result = $stmt->execute([
                ':addtime' => time(),
                ':name' => $input['cpname'],
                ':title' => $cpInfo['title'],
                ':opencode' => $input['opencode'],
                ':sourcecode' => $input['sourcecode'] ?? '',
                ':remarks' => $input['remarks'] ?? '手动开奖',
                ':source' => '后台手动',
                ':expect' => $input['expect'],
                ':opentime' => $opentime,
                ':isdraw' => 0,
                ':drawtime' => $opentime
            ]);

            if ($result) {
                $this->addAdminLog('lottery_add', "添加开奖记录：{$input['cpname']} {$input['expect']}");
                return $this->success('开奖记录添加成功');
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
     * 删除开奖记录
     * @param int $id
     * @return \think\Response
     */
    public function deleteResult($id)
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

            $stmt = $pdo->prepare("DELETE FROM {$prefix}kaijiang WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('lottery_delete', "删除开奖记录，ID：{$id}");
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
     * 获取投注记录
     * @return \think\Response
     */
    public function betRecords()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
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

            $where = '1=1';
            $params = [];

            if ($cpname) {
                $where .= ' AND t.cpname = :cpname';
                $params[':cpname'] = $cpname;
            }

            if ($username) {
                $where .= ' AND t.username LIKE :username';
                $params[':username'] = '%' . $username . '%';
            }

            if ($expect) {
                $where .= ' AND t.expect LIKE :expect';
                $params[':expect'] = '%' . $expect . '%';
            }

            if ($trano) {
                $where .= ' AND t.trano LIKE :trano';
                $params[':trano'] = '%' . $trano . '%';
            }

            if ($status !== '' && $status != '999') {
                $where .= ' AND t.isdraw = :isdraw';
                $params[':isdraw'] = $status;
            }

            if ($sDate) {
                $where .= ' AND FROM_UNIXTIME(t.addtime) >= :start_date';
                $params[':start_date'] = $sDate . ' 00:00:00';
            }

            if ($eDate) {
                $where .= ' AND FROM_UNIXTIME(t.addtime) <= :end_date';
                $params[':end_date'] = $eDate . ' 23:59:59';
            }

            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}touzhu t WHERE {$where}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $offset = ($page - 1) * $pageSize;
            $stmt = $pdo->prepare("
                SELECT 
                    t.id, t.uid, t.username, t.cpname, t.cptitle, t.expect,
                    t.playid, t.playtitle, t.tzcode, t.opencode, t.trano,
                    t.amount, t.okamount, t.isdraw, t.oddtime, t.source
                FROM {$prefix}touzhu t
                WHERE {$where}
                ORDER BY t.id DESC
                LIMIT {$offset}, {$pageSize}
            ");
            $stmt->execute($params);
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $statsStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_count,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN isdraw = 1 THEN okamount ELSE 0 END) as total_award,
                    SUM(CASE WHEN isdraw = 0 THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN isdraw = 1 THEN 1 ELSE 0 END) as win_count,
                    SUM(CASE WHEN isdraw = 2 THEN 1 ELSE 0 END) as lose_count
                FROM {$prefix}touzhu t
                WHERE {$where}
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

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 添加彩种
     * @return \think\Response
     */
    public function addType()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['typeid']) || empty($input['title']) || empty($input['name'])) {
            return $this->error('请填写完整信息');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}caipiao WHERE name = :name");
            $checkStmt->execute([':name' => $input['name']]);
            if ($checkStmt->fetch()) {
                return $this->error('彩种标识已存在');
            }

            if (isset($input['issys']) && $input['issys'] == 1) {
                $validExpectTimes = ['1', '1.5', '2', '2.5', '3', '5', '10'];
                if (!isset($input['expecttime']) || !in_array($input['expecttime'], $validExpectTimes)) {
                    return $this->error('请设置正确的开奖时间');
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO {$prefix}caipiao 
                (typeid, title, name, ftime, qishu, ftitle, logo, issys, isopen, iswh, 
                 closetime1, closetime2, expecttime, listorder, allsort)
                VALUES 
                (:typeid, :title, :name, :ftime, :qishu, :ftitle, :logo, :issys, 1, 0,
                 :closetime1, :closetime2, :expecttime, :listorder, :allsort)
            ");

            $result = $stmt->execute([
                ':typeid' => $input['typeid'],
                ':title' => $input['title'],
                ':name' => $input['name'],
                ':ftime' => $input['ftime'] ?? '',
                ':qishu' => $input['qishu'] ?? 0,
                ':ftitle' => $input['ftitle'] ?? '',
                ':logo' => $input['logo'] ?? '',
                ':issys' => $input['issys'] ?? 1,
                ':closetime1' => $input['closetime1'] ?? '00:00:00',
                ':closetime2' => $input['closetime2'] ?? '23:59:59',
                ':expecttime' => $input['expecttime'] ?? '1',
                ':listorder' => $input['listorder'] ?? 0,
                ':allsort' => $input['allsort'] ?? 0
            ]);

            if ($result) {
                $this->addAdminLog('lottery_type_add', "添加彩种：{$input['title']}");
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
     * 更新彩种
     * @param int $id
     * @return \think\Response
     */
    public function updateType($id)
    {
        if (!$this->request->isPut()) {
            return $this->error('请使用PUT请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['typeid']) || empty($input['title'])) {
            return $this->error('请填写完整信息');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}caipiao WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            if (!$checkStmt->fetch()) {
                return $this->error('彩种不存在');
            }

            if (isset($input['issys']) && $input['issys'] == 1) {
                $validExpectTimes = ['1', '1.5', '2', '2.5', '3', '5', '10'];
                if (!isset($input['expecttime']) || !in_array($input['expecttime'], $validExpectTimes)) {
                    return $this->error('请设置正确的开奖时间');
                }
            }

            $stmt = $pdo->prepare("
                UPDATE {$prefix}caipiao 
                SET typeid = :typeid, title = :title, ftime = :ftime, qishu = :qishu,
                    ftitle = :ftitle, logo = :logo, issys = :issys,
                    closetime1 = :closetime1, closetime2 = :closetime2,
                    expecttime = :expecttime, listorder = :listorder
                WHERE id = :id
            ");

            $result = $stmt->execute([
                ':id' => $id,
                ':typeid' => $input['typeid'],
                ':title' => $input['title'],
                ':ftime' => $input['ftime'] ?? '',
                ':qishu' => $input['qishu'] ?? 0,
                ':ftitle' => $input['ftitle'] ?? '',
                ':logo' => $input['logo'] ?? '',
                ':issys' => $input['issys'] ?? 1,
                ':closetime1' => $input['closetime1'] ?? '00:00:00',
                ':closetime2' => $input['closetime2'] ?? '23:59:59',
                ':expecttime' => $input['expecttime'] ?? '1',
                ':listorder' => $input['listorder'] ?? 0
            ]);

            if ($result) {
                $this->addAdminLog('lottery_type_update', "更新彩种，ID：{$id}");
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
     * 删除彩种
     * @param int $id
     * @return \think\Response
     */
    public function deleteType($id)
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

            $stmt = $pdo->prepare("DELETE FROM {$prefix}caipiao WHERE id = :id");
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                $this->addAdminLog('lottery_type_delete', "删除彩种，ID：{$id}");
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
     * 切换彩种状态
     * @return \think\Response
     */
    public function toggleStatus()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['id']) || empty($input['field'])) {
            return $this->error('缺少必要参数');
        }

        $allowedFields = ['isopen', 'iswh'];
        if (!in_array($input['field'], $allowedFields)) {
            return $this->error('非法操作');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $field = $input['field'];

            $checkStmt = $pdo->prepare("SELECT id, {$field} FROM {$prefix}caipiao WHERE id = :id");
            $checkStmt->execute([':id' => $input['id']]);
            $lottery = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$lottery) {
                return $this->error('彩种不存在');
            }

            $newStatus = $lottery[$field] == 1 ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE {$prefix}caipiao SET {$field} = :status WHERE id = :id");
            $result = $stmt->execute([':status' => $newStatus, ':id' => $input['id']]);

            if ($result) {
                $this->addAdminLog('lottery_toggle', "切换彩种状态，ID：{$input['id']}");
                return $this->success('操作成功', ['newStatus' => $newStatus]);
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
     * 保存彩种排序
     * @return \think\Response
     */
    public function saveOrder()
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
                    $stmt = $pdo->prepare("UPDATE {$prefix}caipiao SET listorder = :order WHERE id = :id");
                    $stmt->execute([':order' => intval($item['listorder']), ':id' => intval($item['id'])]);
                }
            }

            $pdo->commit();

            $this->addAdminLog('lottery_order', "保存彩种排序");

            return $this->success('保存成功');

        } catch (\PDOException $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 预开奖管理 - 获取待开奖期号列表
     * @return \think\Response
     */
    public function yukaijiang()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求', null, 405);
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 获取所有开启的彩种
            $stmt = $pdo->query("SELECT * FROM {$prefix}caipiao WHERE isopen = 1 ORDER BY typeid DESC");
            $cplist = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 过滤掉某些彩种
            $my_list = [];
            foreach ($cplist as $v) {
                if (!in_array($v['name'], ['lhc', 'fc3d', 'pl3', 'jxk3'])) {
                    $my_list[] = $v;
                }
            }

            $name = $this->request->param('name', '');
            if (!$name && count($my_list) > 0) {
                $name = $my_list[0]['name'];
            }

            // 查找当前彩种信息
            $cpinfo = null;
            foreach ($cplist as $v) {
                if ($v['name'] === $name) {
                    $cpinfo = $v;
                    break;
                }
            }

            if (!$cpinfo) {
                return $this->error('彩种不存在');
            }

            $typeid = $cpinfo['typeid'];
            $expecttime = $cpinfo['expecttime'];
            $_expecttime = $expecttime * 60;
            $totalcount = floor(abs(strtotime($cpinfo['closetime2']) - strtotime($cpinfo['closetime1'])) / $_expecttime);
            $_length = $totalcount >= 1000 ? 4 : 3;
            $jgtime = $expecttime * 60;
            $_t = time();
            $_t1 = strtotime(date('Y-m-d ') . $cpinfo['closetime1']);

            // 计算当前期号
            if ($_t < $_t1) {
                $actNo_t = $totalcount;
            } else {
                $actNo_t = ($_t - strtotime(date('Y-m-d ') . $cpinfo['closetime1']) + intval($cpinfo['ftime'])) / $_expecttime;
            }
            $actNo_t = floor($actNo_t);
            $actNo = is_numeric($actNo_t) ? ($actNo_t == $totalcount ? 1 : $actNo_t + 1) : ceil($actNo_t);

            // 生成待开奖期号列表（未来20期）
            $openlist = [];

            if ($cpinfo['issys'] == 1) {
                // 系统彩
                if ($actNo > $totalcount) {
                    if ($_t > strtotime($cpinfo['closetime2'])) {
                        // 跨天到明天
                        for ($j = 20; $j >= 1; $j--) {
                            $rand_keys = $this->returnrankey($cpinfo['typeid']);
                            if ($cpinfo['typeid'] == 'k3' || $cpinfo['typeid'] == 'keno') sort($rand_keys);
                            $opentime = date('Y-m-d H:i:s', strtotime($cpinfo['closetime1']) + $j * $jgtime + 86400);
                            $expect = str_pad($j, $_length, 0, STR_PAD_LEFT);
                            $openlist[] = [
                                'expect' => date('Ymd', strtotime('+1 day')) . $expect,
                                'opencode' => implode(',', $rand_keys),
                                'opentime' => $opentime,
                                'cptitle' => $cpinfo['title'],
                                'name' => $cpinfo['name']
                            ];
                        }
                    } else {
                        for ($j = 20; $j >= 1; $j--) {
                            $rand_keys = $this->returnrankey($cpinfo['typeid']);
                            if ($cpinfo['typeid'] == 'k3' || $cpinfo['typeid'] == 'keno') sort($rand_keys);
                            $opentime = date('Y-m-d H:i:s', strtotime($cpinfo['closetime1']) + $j * $jgtime + 86400);
                            $expect = str_pad($j, $_length, 0, STR_PAD_LEFT);
                            $openlist[] = [
                                'expect' => date('Ymd', strtotime('+1 day')) . $expect,
                                'opencode' => implode(',', $rand_keys),
                                'opentime' => $opentime,
                                'cptitle' => $cpinfo['title'],
                                'name' => $cpinfo['name']
                            ];
                        }
                    }
                } else {
                    if ($actNo + 19 <= $totalcount) {
                        for ($j = $actNo + 19; $j >= $actNo; $j--) {
                            $rand_keys = $this->returnrankey($cpinfo['typeid']);
                            if ($cpinfo['typeid'] == 'k3' || $cpinfo['typeid'] == 'keno') sort($rand_keys);
                            $opentime = date('Y-m-d H:i:s', strtotime($cpinfo['closetime2']) - ($totalcount - $j) * $jgtime);
                            $expect = str_pad($j, $_length, 0, STR_PAD_LEFT);
                            $openlist[] = [
                                'expect' => date('Ymd') . $expect,
                                'opencode' => implode(',', $rand_keys),
                                'opentime' => $opentime,
                                'cptitle' => $cpinfo['title'],
                                'name' => $cpinfo['name']
                            ];
                        }
                    } else {
                        for ($j = $totalcount; $j >= $actNo; $j--) {
                            $rand_keys = $this->returnrankey($cpinfo['typeid']);
                            if ($cpinfo['typeid'] == 'k3' || $cpinfo['typeid'] == 'keno') sort($rand_keys);
                            $opentime = date('Y-m-d H:i:s', strtotime($cpinfo['closetime2']) - ($totalcount - $j) * $jgtime);
                            $expect = str_pad($j, $_length, 0, STR_PAD_LEFT);
                            $openlist[] = [
                                'expect' => date('Ymd') . $expect,
                                'opencode' => implode(',', $rand_keys),
                                'opentime' => $opentime,
                                'cptitle' => $cpinfo['title'],
                                'name' => $cpinfo['name']
                            ];
                        }
                    }
                }
            }

            // 查询预开奖表，标记已保存的期号
            $yukaijiangStmt = $pdo->prepare("SELECT * FROM {$prefix}yukaijiang WHERE name = :name AND hid = 0");
            $yukaijiangStmt->execute([':name' => $name]);
            $yukaijiangList = $yukaijiangStmt->fetchAll(\PDO::FETCH_ASSOC);

            $yukaijiangMap = [];
            foreach ($yukaijiangList as $ykj) {
                $yukaijiangMap[$ykj['expect']] = $ykj;
            }

            foreach ($openlist as $k => $v) {
                $v['isbc'] = 0;
                $v['stateadmin'] = '';
                if (isset($yukaijiangMap[$v['expect']])) {
                    $v['opencode'] = $yukaijiangMap[$v['expect']]['opencode'];
                    $v['isbc'] = 1;
                    $v['stateadmin'] = $yukaijiangMap[$v['expect']]['stateadmin'] ?? '';
                }
                $openlist[$k] = $v;
            }

            // 按期号排序
            usort($openlist, function($a, $b) {
                return strcmp($a['expect'], $b['expect']);
            });

            return $this->success('获取成功', [
                'cplist' => $my_list,
                'openlist' => $openlist,
                'typeid' => $typeid,
                'cpname' => $name
            ]);

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 保存预开奖号码
     * @return \think\Response
     */
    public function ykjbaocun()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        $expect = $input['expect'] ?? '';
        $name = $input['name'] ?? '';
        $opentime = $input['opentime'] ?? '';
        $opentime = str_replace('：', ':', $opentime);

        if (!$expect || !$name) {
            return $this->error('参数错误');
        }

        // 拼接开奖号码
        $opencode = '';
        for ($i = 1; $i <= 20; $i++) {
            $codeKey = 'opencode' . $i;
            if (isset($input[$codeKey])) {
                if ($input[$codeKey] === '0' || $input[$codeKey] === 0) {
                    $opencode .= '0,';
                } elseif ($input[$codeKey] !== '') {
                    $opencode .= $input[$codeKey] . ',';
                } else {
                    break;
                }
            } else {
                break;
            }
        }
        $opencode = rtrim($opencode, ',');

        if (!$opencode) {
            return $this->error('请选择开奖号码');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // 查询彩种信息
            $stmt = $pdo->prepare("SELECT * FROM {$prefix}caipiao WHERE name = :name");
            $stmt->execute([':name' => $name]);
            $cpinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$cpinfo) {
                return $this->error('彩种不存在');
            }

            // 检查是否已存在
            $checkStmt = $pdo->prepare("SELECT * FROM {$prefix}yukaijiang WHERE name = :name AND expect = :expect AND hid = 0");
            $checkStmt->execute([':name' => $name, ':expect' => $expect]);
            $existData = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            // 将时间字符串转换为时间戳
            $opentimeTimestamp = strtotime($opentime);
            if ($opentimeTimestamp === false) {
                $opentimeTimestamp = strtotime(date('Y-m-d') . ' ' . $opentime);
            }

            if ($existData) {
                // 更新
                $updateStmt = $pdo->prepare("
                    UPDATE {$prefix}yukaijiang 
                    SET opencode = :opencode, stateadmin = :stateadmin
                    WHERE name = :name AND expect = :expect AND hid = 0
                ");

                $result = $updateStmt->execute([
                    ':opencode' => $opencode,
                    ':stateadmin' => $this->adminInfo['username'] ?? '',
                    ':name' => $name,
                    ':expect' => $expect
                ]);
            } else {
                // 插入
                $insertStmt = $pdo->prepare("
                    INSERT INTO {$prefix}yukaijiang 
                    (name, expect, opencode, opentime, stateadmin, hid)
                    VALUES 
                    (:name, :expect, :opencode, :opentime, :stateadmin, 0)
                ");

                $result = $insertStmt->execute([
                    ':name' => $name,
                    ':expect' => $expect,
                    ':opencode' => $opencode,
                    ':opentime' => $opentimeTimestamp,
                    ':stateadmin' => $this->adminInfo['username'] ?? ''
                ]);
            }

            if ($result) {
                $this->addAdminLog('ykj_save', "保存预开奖：{$name} {$expect}");
                return $this->success('保存成功', [
                    'stateadmin' => $this->adminInfo['username'] ?? ''
                ]);
            } else {
                return $this->error('保存失败');
            }

        } catch (\PDOException $e) {
            return $this->error('数据库操作失败：' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 生成随机号码（辅助方法）
     * @param string $typeid
     * @return array
     */
    private function returnrankey($typeid)
    {
        $rand_keys = [];
        switch ($typeid) {
            case 'k3':
                $rand_keys = explode(',', $this->rand_keys('3', '123456'));
                break;
            case 'ssc':
                $rand_keys = explode(',', $this->rand_keys('5', '0123456789'));
                break;
            case 'x5':
                $rand_keys = explode(',', $this->rand_keys_x('5', '01,02,03,04,05,06,07,08,09,10,11'));
                break;
            case 'pk10':
                $rand_keys = explode(',', $this->rand_keys_x('10', '01,02,03,04,05,06,07,08,09,10'));
                break;
            case 'keno':
                $num = '';
                for ($i = 1; $i <= 80; $i++) {
                    $num .= ($i < 10 ? '0' . $i : $i) . ',';
                }
                $num = substr($num, 0, -1);
                $rand_keys = explode(',', $this->rand_keys_x('20', $num));
                break;
            case 'dpc':
                $rand_keys = explode(',', $this->rand_keys('3', '0123456789'));
                break;
            case 'lhc':
                $nums = [];
                for ($i = 1; $i <= 49; $i++) {
                    $nums[] = $i < 10 ? '0' . $i : $i;
                }
                $rand_keys = explode(',', $this->rand_keys_x('7', implode(',', $nums)));
                break;
        }
        return $rand_keys;
    }

    /**
     * 生成随机号码（简单模式）
     * @param int $count
     * @param string $chars
     * @return string
     */
    private function rand_keys($count, $chars)
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $chars[rand(0, strlen($chars) - 1)];
        }
        return implode(',', $result);
    }

    /**
     * 生成随机号码（不重复模式）
     * @param int $count
     * @param string $nums
     * @return string
     */
    private function rand_keys_x($count, $nums)
    {
        $arr = explode(',', $nums);
        shuffle($arr);
        return implode(',', array_slice($arr, 0, $count));
    }
}

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

    /**
     * 获取预开奖列表（与后台完全一致）
     */
    public function yukaijiang()
    {
        if (!$this->request->isGet()) {
            return $this->error('请使用GET请求');
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

            // 查询已开奖的期号（需要过滤掉）
            $kaijiangStmt = $pdo->prepare("SELECT expect FROM {$prefix}kaijiang WHERE name = :name");
            $kaijiangStmt->execute([':name' => $name]);
            $kaijiangList = $kaijiangStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $kaijiangExpects = [];
            foreach ($kaijiangList as $kj) {
                $kaijiangExpects[$kj['expect']] = true;
            }

            // 查询预开奖表，标记已保存的期号
            $yukaijiangStmt = $pdo->prepare("SELECT * FROM {$prefix}yukaijiang WHERE name = :name AND hid = 0");
            $yukaijiangStmt->execute([':name' => $name]);
            $yukaijiangList = $yukaijiangStmt->fetchAll(\PDO::FETCH_ASSOC);

            $yukaijiangMap = [];
            foreach ($yukaijiangList as $ykj) {
                $yukaijiangMap[$ykj['expect']] = $ykj;
            }

            // 过滤已开奖的期号，并标记预开奖状态
            $filteredOpenlist = [];
            foreach ($openlist as $v) {
                // 如果该期号已经在开奖表中，则跳过
                if (isset($kaijiangExpects[$v['expect']])) {
                    continue;
                }
                
                $v['isbc'] = 0;
                $v['stateadmin'] = '';
                if (isset($yukaijiangMap[$v['expect']])) {
                    $v['opencode'] = $yukaijiangMap[$v['expect']]['opencode'];
                    $v['isbc'] = 1;
                    $v['stateadmin'] = $yukaijiangMap[$v['expect']]['stateadmin'] ?? '';
                }
                $filteredOpenlist[] = $v;
            }
            
            $openlist = $filteredOpenlist;

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

        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 生成随机号码（与后台一致）
     */
    private function returnrankey($typeid)
    {
        $rand_keys = [];
        
        if ($typeid == 'k3') {
            for ($i = 0; $i < 3; $i++) {
                $rand_keys[] = mt_rand(1, 6);
            }
        } elseif ($typeid == 'ssc' || $typeid == 'dpc') {
            for ($i = 0; $i < 5; $i++) {
                $rand_keys[] = mt_rand(0, 9);
            }
        } elseif ($typeid == 'pk10') {
            $nums = range(1, 10);
            shuffle($nums);
            for ($i = 0; $i < 10; $i++) {
                $rand_keys[] = str_pad($nums[$i], 2, '0', STR_PAD_LEFT);
            }
        } elseif ($typeid == 'x5') {
            $nums = range(1, 11);
            shuffle($nums);
            for ($i = 0; $i < 5; $i++) {
                $rand_keys[] = str_pad($nums[$i], 2, '0', STR_PAD_LEFT);
            }
        } elseif ($typeid == 'keno') {
            $nums = range(1, 80);
            shuffle($nums);
            for ($i = 0; $i < 20; $i++) {
                $rand_keys[] = str_pad($nums[$i], 2, '0', STR_PAD_LEFT);
            }
        }
        
        return $rand_keys;
    }

    /**
     * 保存预开奖号码
     */
    public function saveYukaijiang()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $input = $this->request->post();
            $expect = $input['expect'] ?? '';
            $name = $input['name'] ?? '';
            $opentime = $input['opentime'] ?? '';

            if (!$expect || !$name) {
                return $this->error('参数错误：缺少必要参数');
            }

            // 拼接开奖号码
            $opencode = $input['opencode'] ?? '';
            if (!$opencode) {
                // 从 opencode1, opencode2... 拼接
                $codes = [];
                for ($i = 1; $i <= 20; $i++) {
                    $codeKey = 'opencode' . $i;
                    if (isset($input[$codeKey]) && $input[$codeKey] !== '') {
                        $codes[] = $input[$codeKey];
                    }
                }
                $opencode = implode(',', $codes);
            }

            if (!$opencode) {
                return $this->error('请填写开奖号码');
            }

            // 转换时间
            $opentimeTimestamp = $opentime ? strtotime(str_replace('：', ':', $opentime)) : time();

            // 检查是否已存在
            $checkStmt = $pdo->prepare("
                SELECT id FROM {$prefix}yukaijiang 
                WHERE name = :name AND expect = :expect
            ");
            $checkStmt->execute([':name' => $name, ':expect' => $expect]);
            $exists = $checkStmt->fetch();

            if ($exists) {
                // 更新
                $updateStmt = $pdo->prepare("
                    UPDATE {$prefix}yukaijiang 
                    SET opencode = :opencode, opentime = :opentime, stateadmin = :stateadmin
                    WHERE name = :name AND expect = :expect
                ");
                $result = $updateStmt->execute([
                    ':opencode' => $opencode,
                    ':opentime' => $opentimeTimestamp,
                    ':stateadmin' => $this->agentInfo['username'] ?? '',
                    ':name' => $name,
                    ':expect' => $expect
                ]);
            } else {
                // 插入
                $insertStmt = $pdo->prepare("
                    INSERT INTO {$prefix}yukaijiang 
                    (name, expect, opencode, opentime, stateadmin)
                    VALUES (:name, :expect, :opencode, :opentime, :stateadmin)
                ");
                $result = $insertStmt->execute([
                    ':name' => $name,
                    ':expect' => $expect,
                    ':opencode' => $opencode,
                    ':opentime' => $opentimeTimestamp,
                    ':stateadmin' => $this->agentInfo['username'] ?? ''
                ]);
            }

            if ($result) {
                return $this->success('保存成功', [
                    'stateadmin' => $this->agentInfo['username'] ?? ''
                ]);
            } else {
                return $this->error('保存失败');
            }

        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}

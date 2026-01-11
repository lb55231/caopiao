<?php
/**
 * 彩种管理 - 更新接口
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// 只接受PUT请求
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    Database::error('请使用PUT请求', 405);
}

// 验证Token
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

$jwt = new Jwt();
$adminInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$adminInfo) {
    Database::error('Token无效或已过期', 401);
}

// 获取PUT数据
$input = json_decode(file_get_contents('php://input'), true);

// 验证必填字段
if (empty($input['id']) || empty($input['typeid']) || empty($input['title'])) {
    Database::error('请填写完整信息');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查彩种是否存在
    $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}caipiao WHERE id = :id");
    $checkStmt->execute([':id' => $input['id']]);
    if (!$checkStmt->fetch()) {
        Database::error('彩种不存在');
    }
    
    // 系统彩票验证
    if (isset($input['issys']) && $input['issys'] == 1) {
        $validExpectTimes = ['1', '1.5', '2', '2.5', '3', '5', '10'];
        if (!isset($input['expecttime']) || !in_array($input['expecttime'], $validExpectTimes)) {
            Database::error('请设置正确的开奖时间');
        }
    }
    
    // 更新数据
    $stmt = $pdo->prepare("
        UPDATE {$prefix}caipiao 
        SET typeid = :typeid,
            title = :title,
            ftime = :ftime,
            qishu = :qishu,
            ftitle = :ftitle,
            logo = :logo,
            issys = :issys,
            closetime1 = :closetime1,
            closetime2 = :closetime2,
            expecttime = :expecttime,
            listorder = :listorder
        WHERE id = :id
    ");
    
    $result = $stmt->execute([
        ':id' => $input['id'],
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
        Database::success('更新成功');
    } else {
        Database::error('更新失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


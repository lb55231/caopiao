<?php
/**
 * 开奖管理 - 手动添加开奖记录
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

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

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);

// 验证必填字段
if (empty($input['cpname']) || empty($input['expect']) || empty($input['opencode'])) {
    Database::error('请填写完整的开奖信息');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 检查彩种是否存在
    $cpStmt = $pdo->prepare("SELECT title FROM {$prefix}caipiao WHERE name = :name");
    $cpStmt->execute([':name' => $input['cpname']]);
    $cpInfo = $cpStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cpInfo) {
        Database::error('彩种不存在');
    }
    
    // 检查期号是否已存在
    $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}kaijiang WHERE name = :name AND expect = :expect");
    $checkStmt->execute([
        ':name' => $input['cpname'],
        ':expect' => $input['expect']
    ]);
    
    if ($checkStmt->fetch()) {
        Database::error('该期号已存在开奖记录');
    }
    
    // 插入开奖记录
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
        ':isdraw' => 0, // 待结算
        ':drawtime' => $opentime
    ]);
    
    if ($result) {
        Database::success('开奖记录添加成功');
    } else {
        Database::error('添加失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


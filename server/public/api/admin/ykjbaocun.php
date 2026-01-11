<?php
/**
 * 保存预开奖号码
 * 完全按照老项目 CaipiaoController::ykjbaocun 实现
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

$expect = $input['expect'] ?? '';
$name = $input['name'] ?? '';
$opentime = $input['opentime'] ?? '';
$opentime = str_replace('：', ':', $opentime);

if (!$expect || !$name) {
    Database::error('参数错误');
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
    Database::error('请选择开奖号码');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 查询彩种信息
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}caipiao WHERE name = :name");
    $stmt->execute([':name' => $name]);
    $cpinfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cpinfo) {
        Database::error('彩种不存在');
    }
    
    // 检查是否已存在
    $checkStmt = $pdo->prepare("SELECT * FROM {$prefix}yukaijiang WHERE name = :name AND expect = :expect AND hid = 0");
    $checkStmt->execute([':name' => $name, ':expect' => $expect]);
    $existData = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // 将时间字符串转换为时间戳（与旧项目保持一致）
    $opentimeTimestamp = strtotime($opentime);
    if ($opentimeTimestamp === false) {
        // 如果转换失败，尝试使用当前日期 + 时间
        $opentimeTimestamp = strtotime(date('Y-m-d') . ' ' . $opentime);
    }
    
    if ($existData) {
        // 更新（旧项目只更新 opencode，不更新 opentime）
        $updateStmt = $pdo->prepare("
            UPDATE {$prefix}yukaijiang 
            SET opencode = :opencode,
                stateadmin = :stateadmin
            WHERE name = :name AND expect = :expect AND hid = 0
        ");
        
        $result = $updateStmt->execute([
            ':opencode' => $opencode,
            ':stateadmin' => $adminInfo['username'] ?? '',
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
            ':stateadmin' => $adminInfo['username'] ?? ''
        ]);
    }
    
    if ($result) {
        Database::success('保存成功', [
            'stateadmin' => $adminInfo['username'] ?? ''
        ]);
    } else {
        Database::error('保存失败');
    }
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('操作失败：' . $e->getMessage());
}


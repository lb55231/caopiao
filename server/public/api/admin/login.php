<?php
/**
 * 管理员登录接口
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Database::error('请使用POST请求', 405);
}

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);

// 验证必填字段
if (empty($input['username']) || empty($input['password']) || empty($input['safecode'])) {
    Database::error('登录信息不完整！');
}

$username = trim($input['username']);
$password = trim($input['password']);
$safecode = trim($input['safecode']);

// 基本格式验证
if (strlen($username) < 3) {
    Database::error('用户名格式错误！');
}

if (strlen($password) < 6) {
    Database::error('密码格式错误！');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 查询管理员信息
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}adminmember WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        Database::error('管理员不存在！');
    }
    
    // 验证安全码（数据库中safecode是整数，需要转换比较）
    if ($safecode != $admin['safecode']) {
        Database::error('安全码错误！');
    }
    
    // 验证密码（使用老系统的encrypt函数）
    require_once __DIR__ . '/../../common/Encrypt.php';
    
    // 尝试使用encrypt函数验证
    if (!Encrypt::verifyPassword($password, $admin['password'])) {
        // 如果encrypt验证失败，尝试MD5（兼容旧数据）
        $md5Password = md5($password);
        if ($admin['password'] !== $md5Password) {
            // 记录登录失败日志（可选）
            try {
                $logStmt = $pdo->prepare("
                    INSERT INTO {$prefix}adminlog (userid, username, type, info, time, ip) 
                    VALUES (:userid, :username, 'login', '登录失败，密码错误', :time, :ip)
                ");
                $logStmt->execute([
                    ':userid' => $admin['id'],
                    ':username' => $admin['username'],
                    ':time' => time(),
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);
            } catch (PDOException $e) {
                // 忽略日志错误
            }
            Database::error('密码错误！');
        }
    }
    
    // 检查账户状态
    if (isset($admin['islock']) && $admin['islock'] == 1) {
        Database::error('账户已被锁定！');
    }
    
    // 生成JWT Token
    $jwt = new Jwt();
    $tokenData = [
        'id' => $admin['id'],
        'username' => $admin['username'],
        'groupid' => $admin['groupid'] ?? 0,
        'type' => 'admin'
    ];
    $token = $jwt->createToken($tokenData);
    
    // 更新登录信息
    $loginTime = time();
    $loginIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    $updateStmt = $pdo->prepare("
        UPDATE {$prefix}adminmember 
        SET logintime = :logintime, loginip = :loginip 
        WHERE id = :id
    ");
    $updateStmt->execute([
        ':logintime' => $loginTime,
        ':loginip' => $loginIp,
        ':id' => $admin['id']
    ]);
    
    // 记录登录成功日志（可选，如果有 adminlog 表）
    try {
        $logStmt = $pdo->prepare("
            INSERT INTO {$prefix}adminlog (userid, username, type, info, time, ip) 
            VALUES (:userid, :username, 'login', '登录成功', :time, :ip)
        ");
        $logStmt->execute([
            ':userid' => $admin['id'],
            ':username' => $admin['username'],
            ':time' => $loginTime,
            ':ip' => $loginIp
        ]);
    } catch (PDOException $e) {
        // 日志表可能不存在，忽略错误
    }
    
    // 返回成功信息
    Database::success('登录成功', [
        'token' => $token,
        'adminInfo' => [
            'id' => (int)$admin['id'],
            'username' => $admin['username'],
            'groupid' => (int)($admin['groupid'] ?? 0),
            'groupname' => '管理员', // 固定返回
            'logintime' => $loginTime,
            'loginip' => $loginIp
        ]
    ]);
    
} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    Database::error('登录失败：' . $e->getMessage());
}


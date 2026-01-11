<?php
/**
 * 用户登录接口
 */

// 获取数据库连接
$pdo = Database::getInstance();
$prefix = Database::getPrefix();

// 获取请求参数
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

// 参数验证
if (empty($username) || empty($password)) {
    Database::error('用户名和密码不能为空');
}

// 查询用户
try {
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}member WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        Database::error('用户不存在');
    }

    // 验证密码（MD5加密）
    if (md5($password) !== $user['password']) {
        Database::error('密码错误');
    }

    // 检查账号状态
    if (isset($user['islock']) && $user['islock'] == 1) {
        Database::error('账号已被锁定');
    }

    // 更新登录信息
    $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET logintime = :logintime, loginip = :loginip WHERE id = :id");
    $updateStmt->execute([
        ':logintime' => time(),
        ':loginip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ':id' => $user['id']
    ]);

    // 生成 JWT Token
    require_once __DIR__ . '/../../common/Jwt.php';
    $jwt = new Jwt();
    $token = $jwt->createToken([
        'id' => $user['id'],
        'username' => $user['username']
    ], 604800); // 7天有效期

    // 返回成功响应
    Database::success('登录成功', [
        'token' => $token,
        'userInfo' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'] ?? $user['username'],
            'avatar' => $user['face'] ?? '',
            'balance' => number_format($user['balance'], 2, '.', '')
        ]
    ]);

} catch (PDOException $e) {
    Database::error('数据库操作失败：' . $e->getMessage(), 500);
}

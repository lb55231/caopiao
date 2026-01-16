<?php
/**
 * 密码验证测试脚本
 * 用于检查数据库中密码的加密方式
 */

// 引入数据库类
require_once __DIR__ . '/common/Database.php';

// 测试参数
$username = isset($_GET['username']) ? $_GET['username'] : 'agent001';
$testPassword = isset($_GET['password']) ? $_GET['password'] : '123456';

echo "<h2>代理登录密码测试</h2>";
echo "<p>测试用户名: <strong>{$username}</strong></p>";
echo "<p>测试密码: <strong>{$testPassword}</strong></p>";
echo "<hr>";

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 查找用户
    $stmt = $pdo->prepare("SELECT id, username, password, proxy, islock FROM {$prefix}member WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p style='color:red;'>❌ 用户不存在</p>";
        exit;
    }
    
    echo "<h3>1. 数据库信息</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>字段</th><th>值</th></tr>";
    echo "<tr><td>ID</td><td>{$user['id']}</td></tr>";
    echo "<tr><td>用户名</td><td>{$user['username']}</td></tr>";
    echo "<tr><td>是否代理</td><td>" . ($user['proxy'] == 1 ? '是' : '否') . "</td></tr>";
    echo "<tr><td>是否锁定</td><td>" . ($user['islock'] == 1 ? '是' : '否') . "</td></tr>";
    echo "<tr><td>数据库密码</td><td style='font-family:monospace;'>{$user['password']}</td></tr>";
    echo "</table>";
    
    // 检查密码长度
    $passwordLength = strlen($user['password']);
    echo "<p>密码长度: <strong>{$passwordLength}</strong> 字符</p>";
    
    echo "<h3>2. 密码验证测试</h3>";
    
    // 测试1: MD5
    $md5Hash = md5($testPassword);
    $md5Match = ($md5Hash === $user['password']);
    echo "<p><strong>MD5 验证:</strong></p>";
    echo "<p>MD5('{$testPassword}') = <code>{$md5Hash}</code></p>";
    echo "<p>匹配结果: " . ($md5Match ? "<span style='color:green;'>✓ 匹配成功</span>" : "<span style='color:red;'>✗ 不匹配</span>") . "</p>";
    
    // 测试2: password_verify
    $passwordVerify = password_verify($testPassword, $user['password']);
    echo "<p><strong>password_verify 验证:</strong></p>";
    echo "<p>password_verify('{$testPassword}', '{$user['password']}') = " . ($passwordVerify ? 'true' : 'false') . "</p>";
    echo "<p>匹配结果: " . ($passwordVerify ? "<span style='color:green;'>✓ 匹配成功</span>" : "<span style='color:red;'>✗ 不匹配</span>") . "</p>";
    
    echo "<h3>3. 密码格式判断</h3>";
    if ($passwordLength == 32) {
        echo "<p>密码长度为32位，可能是 <strong>MD5</strong> 加密</p>";
    } elseif ($passwordLength == 60 && substr($user['password'], 0, 4) === '$2y$') {
        echo "<p>密码以 \$2y$ 开头，是 <strong>bcrypt</strong> (password_hash) 加密</p>";
    } else {
        echo "<p>密码格式: <strong>未知</strong>（长度: {$passwordLength}）</p>";
    }
    
    echo "<h3>4. 解决方案</h3>";
    if (!$md5Match && !$passwordVerify) {
        echo "<div style='background:#fff3cd; padding:15px; border-left:4px solid #ffc107;'>";
        echo "<h4>密码不匹配，请执行以下操作：</h4>";
        echo "<p><strong>方法1：重置为 MD5 密码（简单）</strong></p>";
        echo "<pre style='background:#f5f5f5; padding:10px; overflow-x:auto;'>";
        echo "UPDATE {$prefix}member SET password = '" . md5($testPassword) . "' WHERE username = '{$username}';</pre>";
        echo "<p><strong>方法2：重置为 bcrypt 密码（更安全）</strong></p>";
        echo "<pre style='background:#f5f5f5; padding:10px; overflow-x:auto;'>";
        echo "UPDATE {$prefix}member SET password = '" . password_hash($testPassword, PASSWORD_DEFAULT) . "' WHERE username = '{$username}';</pre>";
        echo "</div>";
    } else {
        echo "<div style='background:#d4edda; padding:15px; border-left:4px solid #28a745;'>";
        echo "<p style='color:green; font-weight:bold;'>✓ 密码验证成功！可以正常登录</p>";
        if ($user['proxy'] != 1) {
            echo "<p style='color:orange;'>⚠️ 但该账号不是代理账号（proxy=0），请执行：</p>";
            echo "<pre style='background:#f5f5f5; padding:10px;'>UPDATE {$prefix}member SET proxy = 1 WHERE username = '{$username}';</pre>";
        }
        if ($user['islock'] == 1) {
            echo "<p style='color:orange;'>⚠️ 账号已锁定，请执行：</p>";
            echo "<pre style='background:#f5f5f5; padding:10px;'>UPDATE {$prefix}member SET islock = 0 WHERE username = '{$username}';</pre>";
        }
        echo "</div>";
    }
    
    echo "<h3>5. 常用密码 MD5 值</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>密码</th><th>MD5</th></tr>";
    $commonPasswords = ['123456', '111111', '888888', 'admin', 'admin123'];
    foreach ($commonPasswords as $pwd) {
        echo "<tr><td>{$pwd}</td><td>" . md5($pwd) . "</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>错误: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p style='color:#666; font-size:12px;'>使用方法: test_password.php?username=用户名&password=密码</p>";
?>

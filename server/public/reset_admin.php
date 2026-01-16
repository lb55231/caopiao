<?php
/**
 * 重置管理员密码和安全码
 * 直接在浏览器访问：http://127.0.0.1:8000/reset_admin.php
 */

require_once __DIR__ . '/common/Database.php';
require_once __DIR__ . '/common/Encrypt.php';

echo "<h1>管理员密码重置工具</h1>";
echo "<hr>";

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    // 设置新的密码和安全码
    $username = 'admin';
    $newPassword = '123456';
    $newSafecode = '888888'; // 安全码
    
    // 查询管理员是否存在
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}adminmember WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "<p style='color: orange;'>❌ 管理员账号不存在，正在创建...</p>";
        
        // 创建管理员账号
        // 使用MD5加密密码（简单兼容）
        $encryptedPassword = md5($newPassword);
        
        $insertStmt = $pdo->prepare("
            INSERT INTO {$prefix}adminmember 
            (username, password, safecode, groupid, addtime, logintime, loginip, islock) 
            VALUES 
            (:username, :password, :safecode, 1, :addtime, 0, '', 0)
        ");
        
        $insertStmt->execute([
            ':username' => $username,
            ':password' => $encryptedPassword,
            ':safecode' => $newSafecode,
            ':addtime' => time()
        ]);
        
        echo "<p style='color: green;'>✅ 管理员账号创建成功！</p>";
        
    } else {
        echo "<p style='color: blue;'>✓ 找到管理员账号</p>";
        echo "<p>ID: {$admin['id']}</p>";
        echo "<p>用户名: {$admin['username']}</p>";
        echo "<p>原安全码: {$admin['safecode']}</p>";
        
        // 使用MD5加密新密码（兼容方式）
        $encryptedPassword = md5($newPassword);
        
        // 更新密码和安全码
        $updateStmt = $pdo->prepare("
            UPDATE {$prefix}adminmember 
            SET password = :password, safecode = :safecode 
            WHERE username = :username
        ");
        
        $updateStmt->execute([
            ':password' => $encryptedPassword,
            ':safecode' => $newSafecode,
            ':username' => $username
        ]);
        
        echo "<p style='color: green;'>✅ 密码和安全码重置成功！</p>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>🎉 重置完成！</h2>";
    echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 5px;'>";
    echo "<h3>新的登录信息：</h3>";
    echo "<p><strong>用户名：</strong>{$username}</p>";
    echo "<p><strong>密码：</strong>{$newPassword}</p>";
    echo "<p><strong>安全码：</strong>{$newSafecode}</p>";
    echo "</div>";
    
    echo "<p style='color: red; margin-top: 20px;'>⚠️ 请妥善保管登录信息，并在登录后及时修改密码！</p>";
    echo "<p style='color: orange;'>⚠️ 重置完成后，请<strong>删除此文件</strong>以确保安全！</p>";
    
    echo "<hr>";
    echo "<p><a href='/' style='color: blue;'>← 返回首页</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ 数据库错误：" . $e->getMessage() . "</p>";
    echo "<p>可能原因：</p>";
    echo "<ul>";
    echo "<li>数据库连接配置不正确（检查 public/config/database.php）</li>";
    echo "<li>数据库服务未启动</li>";
    echo "<li>表 {$prefix}adminmember 不存在</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 错误：" . $e->getMessage() . "</p>";
}
?>

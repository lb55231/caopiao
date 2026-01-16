# 代理登录密码问题排查指南

## 问题现象
登录时一直提示"用户名或密码错误"

## 密码验证逻辑

```php
// 代码位置: server/app/api/controller/agent/AuthController.php:48

if (!password_verify($password, $agent['password']) && md5($password) !== $agent['password']) {
    return $this->error('用户名或密码错误');
}
```

**验证逻辑说明**：
1. 首先尝试 `password_verify()` - PHP 的 bcrypt 验证
2. 如果失败，再尝试 `md5()` 验证
3. 两个都失败才返回错误

## 快速排查

### 步骤 1: 使用测试脚本

访问测试页面：
```
http://localhost:8000/test_password.php?username=agent001&password=123456
```

这个脚本会：
- ✅ 检查用户是否存在
- ✅ 显示数据库中的密码格式
- ✅ 测试 MD5 和 bcrypt 验证
- ✅ 提供修复 SQL 语句

### 步骤 2: 检查数据库密码

直接在数据库中查询：
```sql
SELECT id, username, password, proxy, islock 
FROM caipiao_member 
WHERE username = 'agent001';
```

**常见密码格式**：
- **MD5 (32位)**：`e10adc3949ba59abbe56e057f20f883e`（这是 `123456` 的 MD5）
- **bcrypt (60位)**：`$2y$10$xxxxx...`（以 `$2y$` 开头）

### 步骤 3: 修复密码

根据你的情况选择：

#### 方法1：使用 MD5（简单，与旧系统兼容）
```sql
-- 设置密码为 123456
UPDATE caipiao_member 
SET password = 'e10adc3949ba59abbe56e057f20f883e' 
WHERE username = 'agent001';

-- 或者使用 MD5 函数
UPDATE caipiao_member 
SET password = MD5('123456') 
WHERE username = 'agent001';
```

#### 方法2：使用 bcrypt（更安全）
```php
// 在 PHP 中生成 bcrypt 密码
<?php
echo password_hash('123456', PASSWORD_DEFAULT);
// 输出类似: $2y$10$xxxxx...
?>
```

然后更新数据库：
```sql
UPDATE caipiao_member 
SET password = '$2y$10$生成的密码哈希' 
WHERE username = 'agent001';
```

## 常见问题

### Q1: 为什么密码总是错误？
**可能原因**：
1. 数据库中的密码格式不对
2. 密码不是 MD5 也不是 bcrypt
3. 可能是其他加密方式（如 sha1）

**解决方案**：
使用上面的测试脚本检查，然后重置密码

### Q2: 如何确认代理账号设置正确？
```sql
-- 检查完整信息
SELECT 
    id,
    username,
    password,
    proxy,      -- 必须是 1
    islock,     -- 必须是 0
    LENGTH(password) as pwd_length  -- 查看密码长度
FROM caipiao_member 
WHERE username = 'agent001';
```

**必须满足**：
- `proxy = 1` （是代理）
- `islock = 0` （未锁定）
- `password` 格式正确（MD5 或 bcrypt）

### Q3: 创建新的测试代理账号

```sql
-- 创建新的代理账号，密码为 123456
INSERT INTO caipiao_member 
(username, password, proxy, balance, regtime, logintime, loginip, islock) 
VALUES 
('testagent', 'e10adc3949ba59abbe56e057f20f883e', 1, 10000, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '127.0.0.1', 0);
```

然后用以下信息登录：
- 用户名：`testagent`
- 密码：`123456`

## 常用密码的 MD5 值

| 密码 | MD5 |
|------|-----|
| 123456 | e10adc3949ba59abbe56e057f20f883e |
| 111111 | 96e79218965eb72c92a549dd5a330112 |
| 888888 | 21218cca77804d2ba1922c33e0151105 |
| admin | 21232f297a57a5a743894a0e4a801fc3 |
| admin123 | 0192023a7bbd73250516f069df18b500 |

## 调试技巧

### 1. 临时添加调试日志

在 `AuthController.php` 第 48 行之前添加：
```php
// 临时调试
error_log("Username: {$username}");
error_log("Input Password: {$password}");
error_log("DB Password: {$agent['password']}");
error_log("MD5: " . md5($password));
error_log("password_verify: " . (password_verify($password, $agent['password']) ? 'true' : 'false'));
```

查看日志：`server/runtime/log/`

### 2. 修改为临时明文比对（仅测试用）

```php
// 仅用于测试，找到问题后立即删除！
if ($password === $agent['password']) {
    // 密码是明文存储的
    return $this->success('登录成功（明文密码）', [...]);
}
```

### 3. 返回详细错误信息（仅开发环境）

修改第 48-50 行为：
```php
// 验证密码
$md5Match = (md5($password) === $agent['password']);
$bcryptMatch = password_verify($password, $agent['password']);

if (!$bcryptMatch && !$md5Match) {
    return $this->error(
        "密码验证失败 | " .
        "MD5: " . ($md5Match ? '匹配' : '不匹配') . " | " .
        "bcrypt: " . ($bcryptMatch ? '匹配' : '不匹配') . " | " .
        "DB密码长度: " . strlen($agent['password'])
    );
}
```

## 最终检查清单

- [ ] 用户存在且 `proxy = 1`
- [ ] 账户未锁定 `islock = 0`
- [ ] 密码格式正确（MD5 32位 或 bcrypt 60位）
- [ ] 后端服务正常运行
- [ ] 前端能连接到后端
- [ ] 网络请求正常（检查浏览器开发者工具）

## 推荐解决方案

**最简单的方法**：
1. 删除旧的测试账号（如果有）
2. 创建新的测试账号（使用上面的 SQL）
3. 使用 `testagent` / `123456` 登录
4. 如果能登录，说明系统正常，再检查原账号密码

**如果还是不行**：
使用测试脚本 `test_password.php` 详细排查！

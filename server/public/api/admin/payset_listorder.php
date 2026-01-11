<?php
/**
 * 存款方式配置 - 批量更新排序
 */
require_once __DIR__ . '/../../common/Database.php';
require_once __DIR__ . '/../../common/Jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Database::error('请使用POST请求', 405);
}

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

$jwt = new Jwt();
$adminInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$adminInfo) {
    Database::error('Token无效或已过期', 401);
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input) || !is_array($input)) {
    Database::error('参数错误');
}

try {
    $pdo = Database::getInstance();
    $prefix = Database::getPrefix();
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE {$prefix}payset SET listorder = :listorder WHERE id = :id");
    
    foreach ($input as $item) {
        if (isset($item['id']) && isset($item['listorder'])) {
            $stmt->execute([
                ':id' => $item['id'],
                ':listorder' => intval($item['listorder'])
            ]);
        }
    }
    
    $pdo->commit();
    
    Database::success('排序更新成功');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    Database::error('数据库操作失败：' . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    Database::error('操作失败：' . $e->getMessage());
}


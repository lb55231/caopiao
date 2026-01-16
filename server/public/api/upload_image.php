<?php
/**
 * 图片上传接口（通用）
 */
require_once __DIR__ . '/../common/Database.php';
require_once __DIR__ . '/../common/Jwt.php';

// 验证Token
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if (empty($token)) {
    Database::error('未提供认证Token', 401);
}

$jwt = new Jwt();
$userInfo = $jwt->verifyToken(str_replace('Bearer ', '', $token));
if (!$userInfo) {
    Database::error('Token无效或已过期', 401);
}

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Database::error('请使用POST请求', 405);
}

try {
    // 检查是否有文件上传
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        Database::error('请选择要上传的文件');
    }
    
    $file = $_FILES['file'];
    
    // 验证文件类型
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        Database::error('只允许上传图片文件（jpg, png, gif, webp）');
    }
    
    // 验证文件大小（最大5MB）
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        Database::error('文件大小不能超过5MB');
    }
    
    // 生成文件名
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
    
    // 创建上传目录
    $uploadDir = __DIR__ . '/../uploads/images/' . date('Ymd') . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 移动文件
    $filePath = $uploadDir . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        Database::error('文件上传失败');
    }
    
    // 返回文件URL
    $fileUrl = '/uploads/images/' . date('Ymd') . '/' . $fileName;
    
    Database::success('上传成功', [
        'url' => $fileUrl,
        'name' => $file['name'],
        'size' => $file['size']
    ]);
    
} catch (Exception $e) {
    Database::error('上传失败：' . $e->getMessage());
}

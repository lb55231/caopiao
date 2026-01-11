<?php
/**
 * 图片上传API
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../common/Database.php';

// 验证请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Database::error('请使用POST方法上传');
}

// 验证文件
if (!isset($_FILES['file'])) {
    Database::error('请选择要上传的文件');
}

$file = $_FILES['file'];

// 验证文件错误
if ($file['error'] !== UPLOAD_ERR_OK) {
    Database::error('文件上传失败，错误代码：' . $file['error']);
}

// 验证文件大小（2MB）
$maxSize = 2 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    Database::error('文件大小不能超过2MB');
}

// 验证文件类型
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    Database::error('只支持JPG、PNG、GIF、WEBP格式的图片');
}

// 获取文件扩展名
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    $extension = 'jpg';
}

// 创建上传目录
$uploadDir = __DIR__ . '/../../uploads/lottery/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 生成唯一文件名
$fileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
$filePath = $uploadDir . $fileName;

// 移动文件
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    Database::error('文件保存失败');
}

// 返回相对路径URL
$fileUrl = '/uploads/lottery/' . $fileName;

Database::success('上传成功', [
    'path' => $fileUrl,
    'url' => $fileUrl,
    'filename' => $fileName,
    'size' => $file['size']
]);


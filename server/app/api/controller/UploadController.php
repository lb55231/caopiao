<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\common\controller\BaseController;
use think\facade\Filesystem;

/**
 * 上传控制器
 */
class UploadController extends BaseController
{
    /**
     * 图片上传
     */
    public function image()
    {
        // 验证请求方法
        if (!$this->request->isPost()) {
            return $this->error('请使用POST方法上传');
        }
        
        // 获取上传文件
        $file = $this->request->file('file');
        
        if (!$file) {
            return $this->error('请选择要上传的文件');
        }
        
        try {
            // 验证文件大小（5MB）
            $maxSize = 5 * 1024 * 1024;
            if ($file->getSize() > $maxSize) {
                return $this->error('文件大小不能超过5MB');
            }
            
            // 获取文件扩展名并验证
            $extension = strtolower($file->extension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($extension, $allowedExtensions)) {
                return $this->error('只支持JPG、PNG、GIF、WEBP格式的图片');
            }
            
            // 使用 getimagesize 验证是否为真实图片
            $imageInfo = @getimagesize($file->getPathname());
            if ($imageInfo === false) {
                return $this->error('上传的文件不是有效的图片');
            }
            
            // 创建上传目录（按日期分目录）
            $uploadDir = app()->getRootPath() . 'public/uploads/images/' . date('Ymd') . '/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    return $this->error('创建上传目录失败');
                }
                @chmod($uploadDir, 0777);
            }
            
            // 生成唯一文件名
            $fileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            
            // 移动文件
            $file->move($uploadDir, $fileName);
            
            // 设置文件权限
            @chmod($uploadDir . $fileName, 0644);
            
            // 返回相对路径URL
            $fileUrl = '/uploads/images/' . date('Ymd') . '/' . $fileName;
            
            return $this->success('上传成功', [
                'url' => $fileUrl,
                'name' => basename($file->getOriginalName()),
                'size' => $file->getSize()
            ]);
            
        } catch (\Exception $e) {
            return $this->error('文件上传失败：' . $e->getMessage());
        }
    }
}

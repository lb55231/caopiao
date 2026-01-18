<?php
namespace app\api\controller\admin;

/**
 * 系统管理控制器
 */
class SystemController extends AdminBaseController
{
    /**
     * 获取系统设置
     * @return \think\Response
     */
    public function settings()
    {
        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $stmt = $pdo->query("SELECT name, value FROM {$prefix}setting");
            $settings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $config = [];
            foreach ($settings as $item) {
                $config[$item['name']] = $item['value'];
            }

            $defaults = [
                'webtitle' => '彩票系统',
                'weblogo' => '',
                'keywords' => '',
                'description' => '',
                'copyright' => '',
                'icp' => '',
                'serviceqq' => '',
                'servicecode' => '',
                'registerbonus' => '0',
                'needinvitecode' => '0',
                'damaliang' => '0'
            ];

            foreach ($defaults as $key => $value) {
                if (!isset($config[$key])) {
                    $config[$key] = $value;
                }
            }

            return $this->success('获取成功', $config);
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 保存系统设置
     * @return \think\Response
     */
    public function saveSettings()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            foreach ($input as $name => $value) {
                $checkStmt = $pdo->prepare("SELECT id FROM {$prefix}setting WHERE name = :name");
                $checkStmt->execute([':name' => $name]);
                
                if ($checkStmt->fetch()) {
                    $stmt = $pdo->prepare("UPDATE {$prefix}setting SET value = :value WHERE name = :name");
                    $stmt->execute([':value' => $value, ':name' => $name]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO {$prefix}setting (name, value) VALUES (:name, :value)");
                    $stmt->execute([':name' => $name, ':value' => $value]);
                }
            }

            $this->addAdminLog('settings_save', "保存系统设置");
            return $this->success('保存成功');
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取活动列表
     * @return \think\Response
     */
    public function activities()
    {
        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $page = intval($this->request->param('page', 1));
            $pageSize = intval($this->request->param('page_size', 20));
            $offset = ($page - 1) * $pageSize;

            // 获取所有新闻/活动数据，不限制 catid
            $countStmt = $pdo->query("SELECT COUNT(*) as total FROM {$prefix}news");
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->query("SELECT * FROM {$prefix}news ORDER BY id DESC LIMIT {$offset}, {$pageSize}");
            $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->success('获取成功', [
                'list' => $list,
                'total' => intval($total)
            ]);
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 添加活动
     * @return \think\Response
     */
    public function addActivity()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        $input = $this->getPostParams();

        if (empty($input['title']) || empty($input['content'])) {
            return $this->error('缺少必填字段');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            // news 表字段：id, catid, catname, title, content, oddtime
            $sql = "INSERT INTO {$prefix}news (title, content, catid, catname, oddtime) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $input['title'],
                $input['content'],
                $input['catid'] ?? 41,
                '活动公告', // catname 默认值
                time()
            ]);

            $this->addAdminLog('activity_add', "添加活动：{$input['title']}");

            return $this->success('添加成功', ['id' => $pdo->lastInsertId()]);
        } catch (\Exception $e) {
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    /**
     * 更新活动
     * @param int $id
     * @return \think\Response
     */
    public function updateActivity($id)
    {
        if (!$this->request->isPut() && !$this->request->isPost()) {
            return $this->error('请使用PUT或POST请求', null, 405);
        }

        if (empty($id)) {
            return $this->error('缺少活动ID');
        }

        $input = $this->getPostParams();

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $updateFields = [];
            $params = [];

            if (isset($input['title'])) {
                $updateFields[] = "title = ?";
                $params[] = $input['title'];
            }

            if (isset($input['content'])) {
                $updateFields[] = "content = ?";
                $params[] = $input['content'];
            }

            if (isset($input['catid'])) {
                $updateFields[] = "catid = ?";
                $params[] = $input['catid'];
            }

            if (isset($input['catname'])) {
                $updateFields[] = "catname = ?";
                $params[] = $input['catname'];
            }

            if (empty($updateFields)) {
                return $this->error('没有需要更新的字段');
            }

            $params[] = $id;
            $sql = "UPDATE {$prefix}news SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $this->addAdminLog('activity_update', "更新活动，ID：{$id}");

            return $this->success('更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除活动
     * @param int $id
     * @return \think\Response
     */
    public function deleteActivity($id)
    {
        if (!$this->request->isDelete()) {
            return $this->error('请使用DELETE请求', null, 405);
        }

        if (empty($id)) {
            return $this->error('缺少活动ID');
        }

        try {
            $pdo = $this->getDb();
            $prefix = $this->getPrefix();

            $sql = "DELETE FROM {$prefix}news WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            $this->addAdminLog('activity_delete', "删除活动，ID：{$id}");

            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 图片上传
     * @return \think\Response
     */
    public function uploadImage()
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用POST请求', null, 405);
        }

        try {
            // 检查是否有文件上传
            if (!isset($_FILES['file'])) {
                return $this->error('未接收到上传文件');
            }

            $file = $_FILES['file'];
            
            // 检查上传错误
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => '文件大小超过 php.ini 中 upload_max_filesize 限制',
                    UPLOAD_ERR_FORM_SIZE => '文件大小超过表单中 MAX_FILE_SIZE 限制',
                    UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
                    UPLOAD_ERR_NO_FILE => '没有文件被上传',
                    UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
                    UPLOAD_ERR_CANT_WRITE => '文件写入失败',
                    UPLOAD_ERR_EXTENSION => 'PHP扩展阻止了文件上传',
                ];
                $errorMsg = $errorMessages[$file['error']] ?? '未知上传错误（错误代码：' . $file['error'] . '）';
                return $this->error($errorMsg);
            }

            // 验证文件大小（最大5MB）
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                return $this->error('文件大小不能超过5MB');
            }

            // 获取文件扩展名并验证（使用扩展名代替 MIME 类型检测）
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($extension, $allowedExtensions)) {
                return $this->error('只允许上传图片文件（jpg, jpeg, png, gif, webp）');
            }

            // 可选：使用 getimagesize 进一步验证是否为真实图片
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return $this->error('上传的文件不是有效的图片');
            }

            // 生成文件名
            $fileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;

            // 创建上传目录
            $uploadDir = app()->getRootPath() . 'public/uploads/images/' . date('Ymd') . '/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    return $this->error('创建上传目录失败，请检查目录权限');
                }
                @chmod($uploadDir, 0777);
            }

            // 移动文件
            $filePath = $uploadDir . $fileName;
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return $this->error('文件移动失败，请检查目录权限');
            }

            // 设置文件权限
            @chmod($filePath, 0644);

            // 返回文件URL
            $fileUrl = '/uploads/images/' . date('Ymd') . '/' . $fileName;

            return $this->success('上传成功', [
                'url' => $fileUrl,
                'name' => $file['name'],
                'size' => $file['size']
            ]);

        } catch (\Exception $e) {
            return $this->error('上传失败：' . $e->getMessage());
        }
    }
}

<?php
namespace app\common\controller;

use think\App;
use think\Response;

/**
 * 基础控制器
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 当前登录用户ID
     * @var int
     */
    protected $userId;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
        // 获取用户ID（由中间件注入）
        // 注意：使用动态属性访问
        $userId = $this->request->userId ?? null;
        
        // 如果没有获取到，尝试从 header 直接解析
        if (!$userId) {
            $token = $this->request->header('token', '') ?: $this->request->header('Token', '');
            if ($token) {
                try {
                    require_once app()->getRootPath() . 'public/common/Jwt.php';
                    $jwt = new \Jwt();
                    $payload = $jwt->verifyToken($token);
                    $userId = $payload['id'] ?? 0;
                } catch (\Exception $e) {
                    $userId = 0;
                }
            }
        }
        
        $this->userId = $userId ?: 0;
    }

    /**
     * 成功返回
     * @param string $msg 提示信息
     * @param mixed $data 返回数据
     * @param int $code 状态码
     * @return Response
     */
    protected function success($msg = '操作成功', $data = null, $code = 200)
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ]);
    }

    /**
     * 失败返回
     * @param string $msg 提示信息
     * @param mixed $data 返回数据
     * @param int $code 状态码
     * @return Response
     */
    protected function error($msg = '操作失败', $data = null, $code = 400)
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ]);
    }

    /**
     * 获取请求参数
     * @param array $fields 需要的字段
     * @return array
     */
    protected function getParams($fields = [])
    {
        $params = $this->request->param();
        
        if (!empty($fields)) {
            $result = [];
            foreach ($fields as $field) {
                $result[$field] = $params[$field] ?? null;
            }
            return $result;
        }
        
        return $params;
    }

    /**
     * 获取POST参数（支持 JSON 和 表单）
     * @param array $fields 需要的字段
     * @return array
     */
    protected function getPostParams($fields = [])
    {
        // 先尝试从 POST 获取
        $params = $this->request->post();
        
        // 如果 POST 为空，尝试解析 JSON
        if (empty($params)) {
            $json = file_get_contents('php://input');
            $jsonData = json_decode($json, true);
            if (is_array($jsonData)) {
                $params = $jsonData;
            }
        }
        
        // 如果还是空，从 param 获取（兼容 GET 和其他方式）
        if (empty($params)) {
            $params = $this->request->param();
        }
        
        if (!empty($fields)) {
            $result = [];
            foreach ($fields as $field) {
                $result[$field] = $params[$field] ?? null;
            }
            return $result;
        }
        
        return $params;
    }

    /**
     * 分页参数
     * @return array
     */
    protected function getPagination()
    {
        return [
            'page' => $this->request->param('page', 1),
            'page_size' => $this->request->param('page_size', 20)
        ];
    }
}


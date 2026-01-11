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
        $this->userId = $this->request->userId ?? 0;
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
     * 获取POST参数
     * @param array $fields 需要的字段
     * @return array
     */
    protected function getPostParams($fields = [])
    {
        $params = $this->request->post();
        
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


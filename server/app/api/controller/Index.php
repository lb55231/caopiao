<?php
namespace app\api\controller;

use think\facade\Request;

/**
 * API首页控制器
 */
class Index
{
    /**
     * 首页接口
     */
    public function index()
    {
        return json([
            'code' => 200,
            'msg' => 'success',
            'data' => [
                'message' => 'Welcome to Lottery API',
                'version' => '1.0.0',
                'time' => date('Y-m-d H:i:s'),
                'server' => Request::host()
            ]
        ]);
    }

    /**
     * 测试接口
     */
    public function ping()
    {
        return json([
            'code' => 200,
            'msg' => 'pong',
            'data' => [
                'time' => date('Y-m-d H:i:s'),
                'version' => '1.0.0',
                'server' => Request::host()
            ]
        ]);
    }
}


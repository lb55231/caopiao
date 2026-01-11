<?php
namespace app\api\controller;

use think\facade\Request;

/**
 * API首页控制器
 */
class Index
{
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


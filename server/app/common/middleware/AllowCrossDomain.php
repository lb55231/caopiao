<?php
namespace app\common\middleware;

use Closure;
use think\Response;

/**
 * 跨域中间件
 */
class AllowCrossDomain
{
    public function handle($request, Closure $next)
    {
        // 获取请求的 Origin
        $origin = $request->header('origin', '*');
        
        // 设置 CORS 头部
        $headers = [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, token, Token',
            'Access-Control-Max-Age' => '86400',
        ];
        
        // 处理 OPTIONS 预检请求
        if ($request->isOptions()) {
            return Response::create()->code(204)->header($headers);
        }
        
        // 执行请求并添加 CORS 头部
        $response = $next($request);
        
        // 添加 CORS 头部到响应
        return $response->header($headers);
    }
}


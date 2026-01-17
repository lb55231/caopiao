<?php
namespace app\common\middleware;

use Closure;
use think\Response;

/**
 * 跨域中间件 - 统一处理所有跨域请求
 * 支持 admin、agent、mobile 三个前端项目
 */
class AllowCrossDomain
{
    public function handle($request, Closure $next)
    {
        // 获取请求的 Origin（允许所有来源）
        $origin = $request->header('origin', '*');
        
        // 设置 CORS 响应头
        $headers = [
            // 允许的来源（动态设置，支持携带凭证）
            'Access-Control-Allow-Origin' => $origin,
            // 允许携带凭证（cookies, authorization headers 等）
            'Access-Control-Allow-Credentials' => 'true',
            // 允许的 HTTP 方法
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD',
            // 允许的请求头
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization, Token, token, X-Token, X-CSRF-TOKEN, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, Cache-Control, Pragma',
            // 允许前端访问的响应头
            'Access-Control-Expose-Headers' => 'Authorization, Token, Content-Disposition',
            // 预检请求的缓存时间（24小时）
            'Access-Control-Max-Age' => '86400',
        ];
        
        // 处理 OPTIONS 预检请求
        if ($request->isOptions()) {
            // 返回 204 No Content，不需要响应体
            return Response::create()->code(204)->header($headers);
        }
        
        // 继续执行请求
        $response = $next($request);
        
        // 为所有响应添加 CORS 头部
        return $response->header($headers);
    }
}


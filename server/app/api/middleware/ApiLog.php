<?php
namespace app\api\middleware;

use Closure;

/**
 * API日志中间件
 */
class ApiLog
{
    public function handle($request, Closure $next)
    {
        // 记录请求日志
        $log = [
            'url' => $request->url(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'params' => $request->param(),
            'time' => date('Y-m-d H:i:s'),
        ];
        
        // 这里可以写入日志文件或数据库
        // Log::write(json_encode($log, JSON_UNESCAPED_UNICODE));
        
        return $next($request);
    }
}


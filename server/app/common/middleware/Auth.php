<?php
namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * Token验证中间件
 */
class Auth
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取token
        $token = $request->header('token', '') ?: $request->header('Token', '');
        
        if (empty($token)) {
            // 没有token，设置userId为0（游客模式）
            $request->userId = 0;
            return $next($request);
        }
        
        try {
            // 验证JWT token
            require_once app()->getRootPath() . 'public/common/Jwt.php';
            $jwt = new \Jwt();
            $payload = $jwt->verifyToken($token);
            
            // 注入userId到request对象
            $request->userId = $payload['id'] ?? 0;
            
        } catch (\Exception $e) {
            // token无效，设置为0
            $request->userId = 0;
        }
        
        return $next($request);
    }
}

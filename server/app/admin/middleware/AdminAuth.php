<?php
namespace app\admin\middleware;

use app\common\service\JwtService;
use app\admin\model\Admin;
use think\Response;

/**
 * 后台认证中间件
 */
class AdminAuth
{
    /**
     * 处理请求
     */
    public function handle($request, \Closure $next)
    {
        // 获取token
        $token = $request->header('authorization', '');
        
        if (empty($token)) {
            return json([
                'code' => 401,
                'msg' => '请先登录',
                'data' => null
            ]);
        }

        // 移除Bearer前缀
        if (stripos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        try {
            // 验证token
            $payload = JwtService::verify($token);
            $adminId = $payload['uid'] ?? 0;

            if (!$adminId) {
                return json([
                    'code' => 401,
                    'msg' => 'Token无效',
                    'data' => null
                ]);
            }

            // 检查管理员状态
            $admin = Admin::find($adminId);
            
            if (!$admin) {
                return json([
                    'code' => 401,
                    'msg' => '管理员不存在',
                    'data' => null
                ]);
            }

            if ($admin->status != 1) {
                return json([
                    'code' => 403,
                    'msg' => '账号已被禁用',
                    'data' => null
                ]);
            }

            // 将管理员ID注入到请求中
            $request->adminId = $adminId;
            $request->adminInfo = [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role
            ];

            return $next($request);

        } catch (\Exception $e) {
            return json([
                'code' => 401,
                'msg' => 'Token验证失败：' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}


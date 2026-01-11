<?php
namespace app\api\middleware;

use app\common\service\JwtService;
use app\api\model\Member;
use think\Response;

/**
 * API认证中间件
 */
class Auth
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
            $userId = $payload['uid'] ?? 0;

            if (!$userId) {
                return json([
                    'code' => 401,
                    'msg' => 'Token无效',
                    'data' => null
                ]);
            }

            // 检查用户状态
            $user = Member::find($userId);
            
            if (!$user) {
                return json([
                    'code' => 401,
                    'msg' => '用户不存在',
                    'data' => null
                ]);
            }

            // 检查锁定状态
            if ($user->islock == 1) {
                return json([
                    'code' => 403,
                    'msg' => '账号已被锁定',
                    'data' => null
                ]);
            }

            // 检查在线状态（踢出功能）
            if ($user->onlinetime == 0) {
                return json([
                    'code' => 401,
                    'msg' => '账号已在其他地方登录',
                    'data' => null
                ]);
            }

            // 将用户ID注入到请求中
            $request->userId = $userId;
            $request->userInfo = [
                'id' => $user->id,
                'username' => $user->username,
                'balance' => $user->balance,
                'xima' => $user->xima,
                'groupid' => $user->groupid
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

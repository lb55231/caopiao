<?php
namespace app\common\service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Env;

/**
 * JWT服务类
 */
class JwtService
{
    /**
     * 生成token
     */
    public static function create($uid, $data = [])
    {
        $secret = Env::get('jwt.secret', 'your-secret-key');
        $expire = Env::get('jwt.expire', 7200);
        $iss = Env::get('jwt.iss', 'lottery-api');
        
        $payload = [
            'iss' => $iss,
            'iat' => time(),
            'exp' => time() + $expire,
            'uid' => $uid,
            'data' => $data
        ];
        
        return JWT::encode($payload, $secret, 'HS256');
    }
    
    /**
     * 验证token
     */
    public static function verify($token)
    {
        $secret = Env::get('jwt.secret', 'your-secret-key');
        
        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return (array)$decoded;
        } catch (\Exception $e) {
            throw new \Exception('Token验证失败');
        }
    }
}


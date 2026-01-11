<?php
/**
 * JWT Token 工具类
 * 简化版，用于生成和验证 Token
 */
class Jwt {
    private $secret = 'caopiao_secret_key_2026'; // 密钥，实际应该从配置文件读取
    
    /**
     * 生成 Token
     * @param array $data 要编码的数据
     * @param int $expire 过期时间（秒），默认7天
     * @return string
     */
    public function createToken($data, $expire = 604800) {
        $payload = [
            'iss' => 'caopiao',  // 签发者
            'iat' => time(),      // 签发时间
            'exp' => time() + $expire,  // 过期时间
            'data' => $data       // 用户数据
        ];
        
        // 简化的 JWT: base64(header).base64(payload).signature
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payloadEncoded = base64_encode(json_encode($payload));
        
        $signature = hash_hmac('sha256', "$header.$payloadEncoded", $this->secret);
        
        return "$header.$payloadEncoded.$signature";
    }
    
    /**
     * 验证 Token
     * @param string $token
     * @return array|false 成功返回用户数据，失败返回 false
     */
    public function verifyToken($token) {
        if (empty($token)) {
            return false;
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payloadEncoded, $signature) = $parts;
        
        // 验证签名
        $expectedSignature = hash_hmac('sha256', "$header.$payloadEncoded", $this->secret);
        if ($signature !== $expectedSignature) {
            return false;
        }
        
        // 解码 payload
        $payload = json_decode(base64_decode($payloadEncoded), true);
        if (!$payload) {
            return false;
        }
        
        // 检查是否过期
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        // 验证用户状态（锁定和在线状态）
        if (isset($payload['data']['id'])) {
            $userData = $this->checkUserStatus($payload['data']['id']);
            if (!$userData) {
                return false;
            }
            // 合并最新的用户数据
            $payload['data'] = array_merge($payload['data'], $userData);
        }
        
        return $payload['data'] ?? false;
    }
    
    /**
     * 检查用户状态
     * @param int $userId
     * @return array|false
     */
    private function checkUserStatus($userId) {
        try {
            require_once __DIR__ . '/Database.php';
            $pdo = Database::getInstance();
            $prefix = Database::getPrefix();
            
            $stmt = $pdo->prepare("SELECT id, username, islock, onlinetime, balance, point, xima FROM {$prefix}member WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            // 检查账号是否被锁定
            if ($user['islock'] == 1) {
                return false; // 账号已锁定，拒绝访问
            }
            
            // 检查是否被踢出（onlinetime为0表示已踢出）
            // 注意：这里只检查是否为0，如果是时间戳则正常
            if (isset($user['onlinetime']) && $user['onlinetime'] === 0) {
                // 被踢出后，重置onlinetime为当前时间，以便下次登录
                $updateStmt = $pdo->prepare("UPDATE {$prefix}member SET onlinetime = :time WHERE id = :id");
                $updateStmt->execute([':time' => time(), ':id' => $userId]);
                return false; // 已被踢出，需要重新登录
            }
            
            return $user;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 刷新 Token（延长过期时间）
     * @param string $token
     * @param int $expire
     * @return string|false
     */
    public function refreshToken($token, $expire = 604800) {
        $data = $this->verifyToken($token);
        if (!$data) {
            return false;
        }
        
        return $this->createToken($data, $expire);
    }
}



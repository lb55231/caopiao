<?php
/**
 * 数据库连接类
 * 统一管理数据库连接
 */

class Database
{
    private static $instance = null;
    private static $config = null;

    /**
     * 加载数据库配置（从主配置文件）
     */
    private static function loadConfig()
    {
        if (self::$config === null) {
            // 引入主配置文件
            $mainConfig = require __DIR__ . '/../../config/database.php';
            $mysql = $mainConfig['connections']['mysql'];
            
            // 转换为当前类使用的格式
            self::$config = [
                'type'      => $mysql['type'],
                'host'      => $mysql['hostname'],
                'port'      => $mysql['hostport'],
                'database'  => $mysql['database'],
                'username'  => $mysql['username'],
                'password'  => $mysql['password'],
                'charset'   => $mysql['charset'],
                'prefix'    => $mysql['prefix'],
            ];
        }
        return self::$config;
    }

    /**
     * 获取数据库连接实例（单例模式）
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            $config = self::loadConfig();
            
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $config['type'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                self::error('数据库连接失败：' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * 获取表前缀
     */
    public static function getPrefix()
    {
        $config = self::loadConfig();
        return $config['prefix'];
    }

    /**
     * 返回成功响应
     */
    public static function success($msg = 'success', $data = null)
    {
        echo json_encode([
            'code' => 200,
            'msg' => $msg,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 返回错误响应
     */
    public static function error($msg = 'error', $code = 400, $data = null)
    {
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

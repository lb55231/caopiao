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
     * 获取数据库连接实例（单例模式）
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$config = require __DIR__ . '/../config/database.php';
            
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                self::$config['type'],
                self::$config['host'],
                self::$config['port'],
                self::$config['database'],
                self::$config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    self::$config['username'],
                    self::$config['password'],
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
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/database.php';
        }
        return self::$config['prefix'];
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


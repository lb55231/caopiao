<?php
/**
 * 自动开奖和结算定时任务脚本
 * 每5秒执行一次：开奖 → 结算
 * 
 * 使用方法：
 * Windows: 在命令行运行 php cron_lottery.php
 * Linux: 设置 crontab 或使用 while true; do php cron_lottery.php; sleep 5; done
 */

// 设置时区
date_default_timezone_set('PRC');

// 引入数据库配置
require_once __DIR__ . '/common/Database.php';

// 日志文件
$logFile = __DIR__ . '/logs/cron_lottery.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// 写日志函数
function writeLog($message) {
    global $logFile;
    $time = date('Y-m-d H:i:s');
    $log = "[{$time}] {$message}\n";
    file_put_contents($logFile, $log, FILE_APPEND);
    echo $log;
}

// 防止重复执行（文件锁）
$lockFile = __DIR__ . '/logs/cron_lottery.lock';
$fp = fopen($lockFile, 'w+');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    writeLog('❌ 任务正在运行中，跳过本次执行');
    exit;
}

try {
    writeLog('🚀 开始执行定时任务...');
    
    // 1. 调用开奖API
    $kaijiangUrl = 'http://127.0.0.1:8000/api/lottery/auto_kaijiang';
    $kaijiangResponse = file_get_contents($kaijiangUrl);
    $kaijiangResult = json_decode($kaijiangResponse, true);
    
    if ($kaijiangResult && $kaijiangResult['code'] == 200) {
        $generated = $kaijiangResult['data']['generated'] ?? 0;
        $skipped = $kaijiangResult['data']['skipped'] ?? 0;
        writeLog("✅ 开奖完成：生成 {$generated} 条，跳过 {$skipped} 条");
        
        // 如果有新的开奖数据，等待1秒后执行结算
        if ($generated > 0) {
            sleep(1);
            
            // 2. 调用结算API
            $settlementUrl = 'http://127.0.0.1:8000/api/lottery/settlement';
            $settlementResponse = file_get_contents($settlementUrl);
            $settlementResult = json_decode($settlementResponse, true);
            
            if ($settlementResult && $settlementResult['code'] == 200) {
                $settledCount = $settlementResult['data']['settled_count'] ?? 0;
                if ($settledCount > 0) {
                    writeLog("💰 结算完成：已结算 {$settledCount} 条投注");
                } else {
                    writeLog("ℹ️ 暂无需要结算的投注");
                }
            } else {
                $msg = $settlementResult['msg'] ?? '未知错误';
                writeLog("⚠️ 结算失败：{$msg}");
            }
        } else {
            writeLog("ℹ️ 无新开奖数据，跳过结算");
        }
    } else {
        $msg = $kaijiangResult['msg'] ?? '未知错误';
        writeLog("❌ 开奖失败：{$msg}");
    }
    
    writeLog("✨ 任务执行完成\n");
    
} catch (Exception $e) {
    writeLog("❌ 执行异常：" . $e->getMessage());
} finally {
    // 释放锁
    flock($fp, LOCK_UN);
    fclose($fp);
}


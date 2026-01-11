#!/bin/bash

echo "========================================"
echo "   🎰 自动开奖定时任务启动"
echo "========================================"
echo ""
echo "✅ 任务已启动，每5秒执行一次"
echo "💡 按 Ctrl+C 可以停止任务"
echo ""
echo "========================================"
echo ""

while true; do
    php cron_lottery.php
    sleep 5
done


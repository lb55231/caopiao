@echo off
echo =============================================
echo   彩票系统 - 停止定时任务
echo =============================================
echo.

:: 关闭自动开奖任务
taskkill /FI "WINDOWTITLE eq 自动开奖*" /F > nul 2>&1
if %errorlevel%==0 (
    echo [√] 自动开奖任务已停止
) else (
    echo [!] 自动开奖任务未运行
)

:: 关闭自动结算任务
taskkill /FI "WINDOWTITLE eq 自动结算*" /F > nul 2>&1
if %errorlevel%==0 (
    echo [√] 自动结算任务已停止
) else (
    echo [!] 自动结算任务未运行
)

echo.
echo =============================================
echo   定时任务已停止
echo =============================================
echo.
pause

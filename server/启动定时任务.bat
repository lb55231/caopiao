@echo off
echo =============================================
echo   彩票系统 - 定时任务启动脚本
echo =============================================
echo.
echo 正在启动定时任务...
echo.

:: 启动自动开奖任务（后台运行）
start "" /min cmd /c "%~dp0auto_kaijiang.bat"
echo [√] 自动开奖任务已启动（每5秒执行一次）

:: 等待1秒
timeout /t 1 /nobreak > nul

:: 启动自动结算任务（后台运行）
start "" /min cmd /c "%~dp0auto_settlement.bat"
echo [√] 自动结算任务已启动（每10秒执行一次）

echo.
echo =============================================
echo   所有定时任务已启动完成！
echo.
echo   提示：
echo   - 自动开奖：每5秒执行一次
echo   - 自动结算：每10秒执行一次
echo   - 关闭任务：关闭对应的命令行窗口
echo =============================================
echo.
pause

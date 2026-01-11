@echo off
title 自动结算 - 每10秒执行一次
:start
echo [%date% %time%] 执行自动结算...
curl -s http://127.0.0.1:8000/api/lottery/settlement
echo.
choice /t 10 /d y /n > nul
goto start

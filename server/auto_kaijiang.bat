@echo off
title 自动开奖 - 每5秒执行一次
:start
echo [%date% %time%] 执行自动开奖...
curl -s http://127.0.0.1:8000/api/lottery/auto_kaijiang
echo.
choice /t 5 /d y /n > nul
goto start

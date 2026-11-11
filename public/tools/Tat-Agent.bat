@echo off
chcp 65001 >nul
title Tat Toan Bo Gervin Zebra Agent
color 0C
cls
echo ======================================================================
echo    GERVIN WOOD - TAT TOAN BO TIEN TRINH ZEBRA AGENT CHAY NGAM
echo ======================================================================
echo.

powershell -NoProfile -Command "Write-Host 'Dang tim va tat cac tien trinh Agent...' -ForegroundColor Yellow; $count = 0; Get-CimInstance Win32_Process | Where-Object { ($_.CommandLine -like '*Gervin-Zebra-Agent*' -or $_.CommandLine -like '*processes/packing/printer/agent/poll*') -and $_.ProcessId -ne $PID } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue; $count++ }; if ($count -gt 0) { Write-Host ('[THANH CONG] Da tat ' + $count + ' tien trinh Agent chay ngam!') -ForegroundColor Green; Write-Host 'RAM va CPU may tinh da duoc giai phong hoan toan.' -ForegroundColor Cyan; } else { Write-Host '[THONG BAO] Khong co tien trinh Agent nao dang chay ngam.' -ForegroundColor Yellow; }"

echo.
echo ======================================================================
echo Cua so se tu dong dong sau 2 giay...
timeout /t 2 >nul
exit

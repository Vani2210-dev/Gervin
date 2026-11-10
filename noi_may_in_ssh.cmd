@echo off
chcp 65001 >nul
title KẾT NỐI MÁY IN ZEBRA VỚI GERVINWOOD.VN
cd /d "%~dp0"
echo ===================================================================
echo   ĐƯỜNG HẦM KẾT NỐI MÁY IN ZEBRA (192.168.1.67) VỚI GERVINWOOD.VN
echo ===================================================================
echo.
echo   Máy in: 192.168.1.67:9100 ^<===^> gervinwood.vn:9100
echo.
echo   * Vui lòng GIỮ NGUYÊN cửa sổ này trong suốt ca làm việc.
echo   * Nếu yêu cầu mật khẩu/passphrase, hãy nhập và bấm Enter.
echo.
ssh -i ssh/id_rsa_chuan -p 26266 -N -o ServerAliveInterval=30 -o ServerAliveCountMax=3 -R 9100:192.168.1.67:9100 gervin@gervinwood.vn
pause


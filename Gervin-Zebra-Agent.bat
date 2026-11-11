@echo off
chcp 65001 >nul
title Gervin Wood - Zebra ZT411 Print Agent
color 0A
cls
echo ======================================================================
echo    GERVIN WOOD - PRINT AGENT KET NOI MAY IN ZEBRA QUA LAN XUONG
echo ======================================================================
echo  Server:        https://gervinwood.vn
echo  May in Zebra:  192.168.1.94:9100
echo  Trang thai:    Dang chay tu dong. Giu nguyen cua so nay khi lam viec.
echo ======================================================================
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command "$server = 'https://gervinwood.vn'; $printerHost = '192.168.1.94'; $printerPort = 9100; Write-Host ('[BAT DAU] Dang ket noi server: ' + $server) -ForegroundColor Cyan; while ($true) { try { $body = @{ station_name = $env:COMPUTERNAME; ready = $true; printer_ip = $printerHost; printer_port = $printerPort; message = 'Zebra ZT411 san sang tai xuong' } | ConvertTo-Json; $resp = Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/poll') -Method Post -ContentType 'application/json' -Body $body -TimeoutSec 10; if ($resp.has_job -and $resp.job) { $job = $resp.job; Write-Host ('[' + (Get-Date -Format 'HH:mm:ss') + '] NHAN LENH IN: ' + $job.id + ' (' + $job.quantity + ' tem)'); try { $client = New-Object System.Net.Sockets.TcpClient; $client.SendTimeout = 8000; $client.Connect($job.host, [int]$job.port); $stream = $client.GetStream(); $bytes = [System.Text.Encoding]::UTF8.GetBytes($job.zpl); $stream.Write($bytes, 0, $bytes.Length); $stream.Flush(); $stream.Close(); $client.Close(); Write-Host ('[' + (Get-Date -Format 'HH:mm:ss') + '] IN THANH CONG: ' + $bytes.Length + ' bytes da gui den ' + $job.host) -ForegroundColor Green; $ackBody = @{ id = $job.id; status = 'sent'; message = ('Da in ' + $job.quantity + ' tem qua LAN xuong (' + $job.host + ')'); bytes = $bytes.Length } | ConvertTo-Json; Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/ack') -Method Post -ContentType 'application/json' -Body $ackBody -TimeoutSec 10 | Out-Null; } catch { $errMsg = $_.Exception.Message; Write-Host ('[' + (Get-Date -Format 'HH:mm:ss') + '] LOI IN LAN: ' + $errMsg) -ForegroundColor Red; $ackBody = @{ id = $job.id; status = 'failed'; message = ('Loi gui LAN: ' + $errMsg); bytes = 0 } | ConvertTo-Json; Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/ack') -Method Post -ContentType 'application/json' -Body $ackBody -TimeoutSec 10 | Out-Null; } } } catch { Start-Sleep -Seconds 2 } Start-Sleep -Milliseconds 1200 }"

pause


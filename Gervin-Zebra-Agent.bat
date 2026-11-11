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
echo  Huong dan:     Giu nguyen cua so nay khi lam viec de nhan lenh in.
echo ======================================================================
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$server = 'https://gervinwood.vn';" ^
"$printerHost = '192.168.1.94';" ^
"$printerPort = 9100;" ^
"Write-Host '[1/2] Kiem tra ket noi mang toi may in Zebra: ' $printerHost ':' $printerPort -ForegroundColor Cyan;" ^
"try {" ^
"    $testClient = New-Object System.Net.Sockets.TcpClient;" ^
"    $async = $testClient.BeginConnect($printerHost, $printerPort, $null, $null);" ^
"    if ($async.AsyncWaitHandle.WaitOne(3000)) {" ^
"        $testClient.EndConnect($async);" ^
"        $testClient.Close();" ^
"        Write-Host ('[OK] Ket noi may in Zebra thanh cong: ' + $printerHost + ':' + $printerPort) -ForegroundColor Green;" ^
"    } else {" ^
"        $testClient.Close();" ^
"        Write-Host ('[CANH BAO] Khong thay may in Zebra tai ' + $printerHost + ':' + $printerPort + '. Kiem tra IP, nguon may in va day mang LAN.') -ForegroundColor Yellow;" ^
"    }" ^
"} catch {" ^
"    Write-Host ('[CANH BAO] Loi ket noi may in Zebra: ' + $_.Exception.Message) -ForegroundColor Yellow;" ^
"}" ^
"Write-Host '[2/2] Dang ket noi toi Server: ' $server -ForegroundColor Cyan;" ^
"$hasAnnouncedOnline = $false;" ^
"while ($true) {" ^
"    try {" ^
"        $body = @{" ^
"            station_name = $env:COMPUTERNAME;" ^
"            ready = $true;" ^
"            printer_ip = $printerHost;" ^
"            printer_port = $printerPort;" ^
"            message = 'Zebra ZT411 san sang tai xuong'" ^
"        } | ConvertTo-Json;" ^
"        $resp = Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/poll') -Method Post -ContentType 'application/json' -Body $body -TimeoutSec 10;" ^
"        if (-not $hasAnnouncedOnline) {" ^
"            Write-Host ('[KET NOI SERVER THANH CONG] Da ket noi toi ' + $server + '. Agent dang chay va san sang nhan lenh in!') -ForegroundColor Green;" ^
"            $hasAnnouncedOnline = $true;" ^
"        }" ^
"        if ($resp.has_job -and $resp.job) {" ^
"            $job = $resp.job;" ^
"            Write-Host ('[' + (Get-Date -Format 'HH:mm:ss') + '] NHAN LENH IN: ' + $job.id + ' (' + $job.quantity + ' tem). Dang gui toi ' + $job.host + '...') -ForegroundColor Yellow;" ^
"            try {" ^
"                $client = New-Object System.Net.Sockets.TcpClient;" ^
"                $client.SendTimeout = 8000;" ^
"                $client.Connect($job.host, [int]$job.port);" ^
"                $stream = $client.GetStream();" ^
"                $bytes = [System.Text.Encoding]::UTF8.GetBytes($job.zpl);" ^
"                $stream.Write($bytes, 0, $bytes.Length);" ^
"                $stream.Flush();" ^
"                $stream.Close();" ^
"                $client.Close();" ^
"                Write-Host ('[' + (Get-Date -Format 'HH:mm:ss') + '] IN THANH CONG: ' + $bytes.Length + ' bytes da gui den may in ' + $job.host + '. May in dang nha tem!') -ForegroundColor Green;" ^
"                $ackBody = @{ id = $job.id; status = 'sent'; message = ('Da in ' + $job.quantity + ' tem qua LAN xuong (' + $job.host + ')'); bytes = $bytes.Length } | ConvertTo-Json;" ^
"                Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/ack') -Method Post -ContentType 'application/json' -Body $ackBody -TimeoutSec 10 | Out-Null;" ^
"            } catch {" ^
"                $errMsg = $_.Exception.Message;" ^
"                Write-Host ('[' + (Get-Date -Format 'HH:mm:ss') + '] LOI IN LAN: ' + $errMsg) -ForegroundColor Red;" ^
"                $ackBody = @{ id = $job.id; status = 'failed'; message = ('Loi gui LAN: ' + $errMsg); bytes = 0 } | ConvertTo-Json;" ^
"                Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/ack') -Method Post -ContentType 'application/json' -Body $ackBody -TimeoutSec 10 | Out-Null;" ^
"            }" ^
"        }" ^
"    } catch {" ^
"        $statusErr = $_.Exception.Message;" ^
"        Write-Host ('[' + (Get-Date -Format 'HH:mm:ss') + '] Loi ket noi server: ' + $statusErr + '. Thu lai sau 3s...') -ForegroundColor Red;" ^
"        $hasAnnouncedOnline = $false;" ^
"        Start-Sleep -Seconds 3;" ^
"    }" ^
"    Start-Sleep -Milliseconds 1200;" ^
"}"

pause

<# :
@echo off
chcp 65001 >nul
title Gervin Wood - Zebra ZT411 Print Agent
color 0A
cls
powershell -NoProfile -ExecutionPolicy Bypass -Command "$curr = $PID; Get-CimInstance Win32_Process -Filter \"Name = 'powershell.exe'\" | Where-Object { $_.ProcessId -ne $curr -and $_.CommandLine -like '*processes/packing/printer/agent/poll*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" >nul 2>&1
powershell -NoProfile -ExecutionPolicy Bypass -Command "(Get-Content -LiteralPath '%~f0' -Raw) | Invoke-Expression"
echo.
pause
exit /b
#>

$server = 'https://gervinwood.vn'
$printerHost = '192.168.1.94'
$printerPort = 9100

Write-Host "======================================================================" -ForegroundColor Green
Write-Host "   GERVIN WOOD - PRINT AGENT KET NOI MAY IN ZEBRA QUA LAN XUONG" -ForegroundColor Green
Write-Host "======================================================================" -ForegroundColor Green
Write-Host (" Server:        " + $server)
Write-Host (" May in Zebra:  " + $printerHost + ":" + $printerPort)
Write-Host " Huong dan:     Giu nguyen cua so nay khi lam viec de nhan lenh in."
Write-Host "                Bam [Ctrl + C] hoac dong cua so de TAT."
Write-Host "======================================================================"
Write-Host ""

Write-Host ("[1/2] Kiem tra ket noi mang toi may in Zebra: " + $printerHost + ":" + $printerPort) -ForegroundColor Cyan
try {
    $testClient = New-Object System.Net.Sockets.TcpClient
    $async = $testClient.BeginConnect($printerHost, $printerPort, $null, $null)
    if ($async.AsyncWaitHandle.WaitOne(3000)) {
        $testClient.EndConnect($async)
        $testClient.Close()
        Write-Host ("[OK] Ket noi may in Zebra thanh cong: " + $printerHost + ":" + $printerPort) -ForegroundColor Green
    } else {
        $testClient.Close()
        Write-Host ("[CANH BAO] Khong thay may in Zebra tai " + $printerHost + ":" + $printerPort + ". Kiem tra IP, nguon may in va day mang LAN.") -ForegroundColor Yellow
    }
} catch {
    Write-Host ("[CANH BAO] Loi ket noi may in Zebra: " + $_.Exception.Message) -ForegroundColor Yellow
}

Write-Host ("[2/2] Dang ket noi toi Server: " + $server) -ForegroundColor Cyan
$hasAnnouncedOnline = $false

while ($true) {
    try {
        $body = @{
            station_name = $env:COMPUTERNAME
            ready = $true
            printer_ip = $printerHost
            printer_port = $printerPort
            message = 'Zebra ZT411 san sang tai xuong'
        } | ConvertTo-Json

        $resp = Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/poll') -Method Post -ContentType 'application/json' -Body $body -TimeoutSec 10

        if (-not $hasAnnouncedOnline) {
            Write-Host ("[KET NOI SERVER THANH CONG] Da ket noi toi " + $server + ". Agent dang chay va san sang nhan lenh in!") -ForegroundColor Green
            $hasAnnouncedOnline = $true
        }

        if ($resp.has_job -and $resp.job) {
            $job = $resp.job
            $timeStr = Get-Date -Format 'HH:mm:ss'
            Write-Host ("[" + $timeStr + "] NHAN LENH IN: " + $job.id + " (" + $job.quantity + " tem). Dang gui toi " + $job.host + "...") -ForegroundColor Yellow

            try {
                $client = New-Object System.Net.Sockets.TcpClient
                $client.SendTimeout = 8000
                $client.Connect($job.host, [int]$job.port)
                $stream = $client.GetStream()
                $bytes = [System.Text.Encoding]::UTF8.GetBytes($job.zpl)
                $stream.Write($bytes, 0, $bytes.Length)
                $stream.Flush()
                $stream.Close()
                $client.Close()

                Write-Host ("[" + $timeStr + "] IN THANH CONG: " + $bytes.Length + " bytes da gui den may in " + $job.host + ". May in dang nha tem!") -ForegroundColor Green

                $ackBody = @{
                    id = $job.id
                    status = 'sent'
                    message = ("Da in " + $job.quantity + " tem qua LAN xuong (" + $job.host + ")")
                    bytes = $bytes.Length
                } | ConvertTo-Json

                Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/ack') -Method Post -ContentType 'application/json' -Body $ackBody -TimeoutSec 10 | Out-Null
            } catch {
                $errMsg = $_.Exception.Message
                Write-Host ("[" + $timeStr + "] LOI IN LAN: " + $errMsg) -ForegroundColor Red

                $ackBody = @{
                    id = $job.id
                    status = 'failed'
                    message = ("Loi gui LAN: " + $errMsg)
                    bytes = 0
                } | ConvertTo-Json

                Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/ack') -Method Post -ContentType 'application/json' -Body $ackBody -TimeoutSec 10 | Out-Null
            }
        }
    } catch {
        $statusErr = $_.Exception.Message
        $timeStr = Get-Date -Format 'HH:mm:ss'
        Write-Host ("[" + $timeStr + "] Loi ket noi server: " + $statusErr + ". Thu lai sau 3s...") -ForegroundColor Red
        $hasAnnouncedOnline = $false
        Start-Sleep -Seconds 3
    }

    Start-Sleep -Milliseconds 1200
}

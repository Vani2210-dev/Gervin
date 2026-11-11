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
$printerHost = $null
$printerPort = 9100

Write-Host "======================================================================" -ForegroundColor Green
Write-Host "   GERVIN WOOD - PRINT AGENT KET NOI MAY IN ZEBRA QUA LAN XUONG" -ForegroundColor Green
Write-Host "======================================================================" -ForegroundColor Green
Write-Host (" Server:        " + $server)
Write-Host " Che do:        Tu dong dong bo dia chi IP theo Data cau hinh"
Write-Host " Huong dan:     Giu nguyen cua so nay khi lam viec de nhan lenh in."
Write-Host "                Bam [Ctrl + C] hoac dong cua so de TAT."
Write-Host "======================================================================"
Write-Host ""

Write-Host ("[1/2] Dang ket noi toi Server: " + $server) -ForegroundColor Cyan
$hasAnnouncedOnline = $false

while ($true) {
    try {
        $body = @{
            station_name = $env:COMPUTERNAME
            ready = $true
            message = 'Zebra ZT411 san sang tai xuong'
        } | ConvertTo-Json

        $resp = Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/poll') -Method Post -ContentType 'application/json' -Body $body -TimeoutSec 10

        if ($resp.printer_host -and ($resp.printer_host -ne $printerHost -or [int]$resp.printer_port -ne $printerPort)) {
            $printerHost = $resp.printer_host
            $printerPort = [int]$resp.printer_port
            Write-Host ("[DONG BO IP DATA] May in Zebra duoc dong bo: " + $printerHost + ":" + $printerPort) -ForegroundColor Cyan
        }

        if (-not $hasAnnouncedOnline) {
            Write-Host ("[KET NOI SERVER THANH CONG] Da ket noi toi " + $server + ". Agent dang chay va san sang nhan lenh in!") -ForegroundColor Green
            $hasAnnouncedOnline = $true
        }

        if ($resp.has_job -and $resp.job) {
            $job = $resp.job
            $targetHost = if ($job.host) { $job.host } else { $printerHost }
            $targetPort = if ($job.port) { [int]$job.port } else { $printerPort }
            $timeStr = Get-Date -Format 'HH:mm:ss'
            Write-Host ("[" + $timeStr + "] NHAN LENH IN: " + $job.id + " (" + $job.quantity + " tem). Dang gui toi " + $targetHost + ":" + $targetPort + "...") -ForegroundColor Yellow

            try {
                $client = New-Object System.Net.Sockets.TcpClient
                $client.SendTimeout = 8000
                $client.Connect($targetHost, $targetPort)
                $stream = $client.GetStream()
                $bytes = [System.Text.Encoding]::UTF8.GetBytes($job.zpl)
                $stream.Write($bytes, 0, $bytes.Length)
                $stream.Flush()
                $stream.Close()
                $client.Close()

                Write-Host ("[" + $timeStr + "] IN THANH CONG: " + $bytes.Length + " bytes da gui den may in " + $targetHost + ". May in dang nha tem!") -ForegroundColor Green

                $ackBody = @{
                    id = $job.id
                    status = 'sent'
                    message = ("Da in " + $job.quantity + " tem qua LAN xuong (" + $targetHost + ")")
                    bytes = $bytes.Length
                } | ConvertTo-Json

                Invoke-RestMethod -Uri ($server + '/processes/packing/printer/agent/ack') -Method Post -ContentType 'application/json' -Body $ackBody -TimeoutSec 10 | Out-Null
            } catch {
                $errMsg = $_.Exception.Message
                Write-Host ("[" + $timeStr + "] LOI IN LAN (" + $targetHost + "): " + $errMsg) -ForegroundColor Red

                $ackBody = @{
                    id = $job.id
                    status = 'failed'
                    message = ("Loi gui LAN (" + $targetHost + "): " + $errMsg)
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

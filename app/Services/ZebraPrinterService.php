<?php

namespace App\Services;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class ZebraPrinterService
{
    const DEFAULT_SETTINGS = [
        'host'      => '192.168.1.94', // Địa chỉ máy in Zebra ZT411 tại xưởng
        'port'      => 9100,
        'timeout'   => 3,
        'dpi'       => 300,
        'margin'    => 1.5,
        'darkness'  => 25,
        'speed'     => 3,
        'offsetX'   => 0,
        'offsetY'   => 0,
        'labelSize' => 60, // Khổ tem thực tế 60 x 60 mm
    ];

    protected function getQueueFilePath(): string
    {
        return storage_path('app/zebra_queue.json');
    }

    protected function getAgentStatusFilePath(): string
    {
        return storage_path('app/zebra_agent_status.json');
    }

    /**
     * Lấy trạng thái của Print Agent tại máy xưởng
     */
    public function getAgentStatus(): array
    {
        $path = $this->getAgentStatusFilePath();
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                $lastSeen = (int) ($data['seen_at'] ?? 0);
                $isOnline = (time() - $lastSeen) <= 30;
                return array_merge($data, [
                    'online'      => $isOnline,
                    'seconds_ago' => time() - $lastSeen,
                ]);
            }
        }
        return [
            'online'     => false,
            'ready'      => false,
            'seen_at'    => null,
            'printer_ip' => null,
            'message'    => 'Chưa có Gervin Zebra Agent nào kết nối.',
        ];
    }

    /**
     * Đọc danh sách hàng đợi in
     */
    public function readQueue(): array
    {
        $path = $this->getQueueFilePath();
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                return $data;
            }
        }
        return ['jobs' => []];
    }

    /**
     * Ghi danh sách hàng đợi in
     */
    public function writeQueue(array $queue): void
    {
        $path = $this->getQueueFilePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        file_put_contents($path, json_encode($queue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Đưa một lệnh in vào hàng đợi cho Agent xưởng
     */
    public function enqueueJob(string $zpl, int $quantity, array $settings, string $kind = 'label'): array
    {
        $queue = $this->readQueue();
        $id = uniqid('job_', true);
        $job = [
            'id'         => $id,
            'time'       => date('c'),
            'host'       => $settings['host'] ?? '192.168.1.94',
            'port'       => (int) ($settings['port'] ?? 9100),
            'quantity'   => $quantity,
            'kind'       => $kind,
            'status'     => 'queued', // queued -> claimed -> sent / failed
            'zpl'        => $zpl,
            'message'    => 'Đang chờ máy tính xưởng nhận lệnh...',
            'created_at' => time(),
        ];
        $queue['jobs'][$id] = $job;

        if (count($queue['jobs']) > 50) {
            $queue['jobs'] = array_slice($queue['jobs'], -50, 50, true);
        }

        $this->writeQueue($queue);
        return $job;
    }

    /**
     * Agent tại xưởng gửi heartbeat và nhận job đang chờ
     */
    public function agentPoll(array $payload): array
    {
        $agentStatus = [
            'station_name' => $payload['station_name'] ?? 'Trạm Đóng Gói Gervin',
            'seen_at'      => time(),
            'ready'        => !empty($payload['ready']),
            'printer_ip'   => $payload['printer_ip'] ?? '192.168.1.94',
            'printer_port' => (int) ($payload['printer_port'] ?? 9100),
            'message'      => $payload['message'] ?? 'Zebra ZT411 sẵn sàng tại xưởng',
        ];
        $path = $this->getAgentStatusFilePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        file_put_contents($path, json_encode($agentStatus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $queue = $this->readQueue();
        $assignedJob = null;

        foreach ($queue['jobs'] as $id => &$job) {
            if ($job['status'] === 'queued') {
                $job['status'] = 'claimed';
                $job['claimed_at'] = time();
                $job['message'] = 'Trạm xưởng đang in...';
                $assignedJob = $job;
                break;
            }
        }
        unset($job);

        if ($assignedJob) {
            $this->writeQueue($queue);
        }

        return [
            'success' => true,
            'has_job' => $assignedJob !== null,
            'job'     => $assignedJob,
        ];
    }

    /**
     * Agent tại xưởng báo cáo kết quả hoàn thành in
     */
    public function agentAck(string $jobId, string $status, string $message, int $bytes = 0): array
    {
        $queue = $this->readQueue();
        if (isset($queue['jobs'][$jobId])) {
            $queue['jobs'][$jobId]['status'] = $status;
            $queue['jobs'][$jobId]['message'] = $message;
            $queue['jobs'][$jobId]['bytes'] = $bytes;
            $queue['jobs'][$jobId]['completed_at'] = time();
            unset($queue['jobs'][$jobId]['zpl']);

            $job = $queue['jobs'][$jobId];
            $this->writeQueue($queue);

            $this->recordHistory([
                'time'     => date('c'),
                'host'     => "{$job['host']}:{$job['port']}",
                'quantity' => $job['quantity'] ?? 1,
                'status'   => $status,
                'bytes'    => $bytes,
                'message'  => $message . ' (qua Agent xưởng)',
            ]);

            return ['success' => true, 'job' => $job];
        }

        return ['success' => false, 'message' => 'Lệnh in không tồn tại.'];
    }

    /**
     * Lấy trạng thái của 1 job in
     */
    public function getJobStatus(string $jobId): array
    {
        $queue = $this->readQueue();
        if (isset($queue['jobs'][$jobId])) {
            $job = $queue['jobs'][$jobId];
            return [
                'success' => true,
                'status'  => $job['status'] ?? 'unknown',
                'message' => $job['message'] ?? '',
                'bytes'   => $job['bytes'] ?? 0,
            ];
        }
        return ['success' => false, 'status' => 'not_found', 'message' => 'Không tìm thấy lệnh in.'];
    }

    /**
     * Đường dẫn file lưu cấu hình máy in Zebra trong storage
     */
    protected function getSettingsFilePath(): string
    {
        return storage_path('app/zebra_printer_settings.json');
    }

    /**
     * Đọc cấu hình máy in hiện tại
     */
    public function getSettings(): array
    {
        $path = $this->getSettingsFilePath();
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $saved = json_decode($content, true);
            if (is_array($saved)) {
                return array_merge(self::DEFAULT_SETTINGS, $saved);
            }
        }

        // Fallback kiểm tra file cài đặt của C:\Code\ver-1 nếu có
        $fallbackVer1 = 'C:\\Code\\ver-1\\storage\\settings.php';
        if (file_exists($fallbackVer1)) {
            $raw = file_get_contents($fallbackVer1);
            $jsonStr = preg_replace('/^<\?php exit;\s*\?>\s*/', '', $raw);
            $saved = json_decode($jsonStr, true);
            if (is_array($saved)) {
                return array_merge(self::DEFAULT_SETTINGS, $saved);
            }
        }

        return self::DEFAULT_SETTINGS;
    }

    /**
     * Lưu cấu hình máy in
     */
    public function saveSettings(array $input): array
    {
        $current = $this->getSettings();
        $settings = array_intersect_key($input, self::DEFAULT_SETTINGS) + $current;

        if (empty($settings['host'])) {
            throw new InvalidArgumentException('Vui lòng nhập địa chỉ IP máy in Zebra (ví dụ: 192.168.1.67).');
        }

        $settings['host'] = trim((string)$settings['host']);
        $settings['port'] = (int)($settings['port'] ?? 9100);
        $settings['timeout'] = max(1, min(10, (int)($settings['timeout'] ?? 3)));
        $settings['dpi'] = in_array((int)($settings['dpi'] ?? 300), [203, 300, 600], true) ? (int)$settings['dpi'] : 300;
        $settings['labelSize'] = max(25, min(104, (float)($settings['labelSize'] ?? 60)));
        $settings['darkness'] = max(0, min(30, (int)($settings['darkness'] ?? 25)));
        $settings['speed'] = max(2, min(6, (int)($settings['speed'] ?? 3)));
        $settings['margin'] = max(0, min(5, (float)($settings['margin'] ?? 1.5)));
        $settings['offsetX'] = max(-5, min(5, (float)($settings['offsetX'] ?? 0)));
        $settings['offsetY'] = max(-5, min(5, (float)($settings['offsetY'] ?? 0)));

        $path = $this->getSettingsFilePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $settings;
    }

    /**
     * Mở socket TCP đến máy in Zebra (thử IP chính trước, fallback sang 127.0.0.1 nếu qua đường hầm SSH)
     */
    public function openSocket(array $settings)
    {
        $primaryHost = trim($settings['host'] ?? self::DEFAULT_SETTINGS['host']);
        $port = (int) ($settings['port'] ?? 9100);
        $timeout = (float) ($settings['timeout'] ?? 3);

        $candidateHosts = [$primaryHost];
        $fallbackHost = ($primaryHost === '127.0.0.1' || $primaryHost === 'localhost') ? '192.168.1.67' : '127.0.0.1';
        if (!in_array($fallbackHost, $candidateHosts, true)) {
            $candidateHosts[] = $fallbackHost;
        }

        $lastErrno = 0;
        $lastErrstr = '';

        foreach ($candidateHosts as $host) {
            $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);
            if ($socket) {
                stream_set_timeout($socket, (int)$timeout);
                return $socket;
            }
            $lastErrno = $errno;
            $lastErrstr = $errstr;
        }

        throw new RuntimeException("Không kết nối được máy in tại {$primaryHost}:{$port}. Lỗi ({$lastErrno}): {$lastErrstr}. Kiểm tra IP, nguồn và mạng LAN/Wi-Fi của máy in.");
    }

    /**
     * Đọc các biến cấu hình SGD của Zebra để phát hiện chế độ giả lập APL-E hoặc Diagnostic
     */
    public function printerModeStatus(array $settings): array
    {
        $names = ['apl.enable', 'input.capture', 'device.diagnostic_print', 'device.languages'];
        $query = '';
        foreach ($names as $name) {
            $query .= '! U1 getvar "' . $name . '"' . "\r\n";
        }

        $socket = $this->openSocket($settings);
        try {
            if (@fwrite($socket, $query) !== strlen($query)) {
                throw new RuntimeException('Không gửi đủ yêu cầu đọc chế độ máy in.');
            }
            stream_set_blocking($socket, false);
            $deadline = microtime(true) + (float)$settings['timeout'];
            $reply = '';
            $matches = [];
            while (microtime(true) < $deadline && strlen($reply) < 4096) {
                $remaining = $deadline - microtime(true);
                $read = [$socket];
                $write = null;
                $except = null;
                $sec = (int)$remaining;
                $usec = (int)(($remaining - $sec) * 1000000);
                if (@stream_select($read, $write, $except, $sec, $usec) !== 1) {
                    break;
                }
                $chunk = @fread($socket, 4096);
                if ($chunk === false || $chunk === '') {
                    break;
                }
                $reply .= $chunk;
                preg_match_all('/"([^"\r\n]*)"/', $reply, $matches);
                if (count($matches[1]) >= count($names)) {
                    return array_combine($names, array_slice($matches[1], 0, count($names)));
                }
            }
            throw new RuntimeException('Máy in chưa trả lời đủ chế độ ngôn ngữ SGD. Kiểm tra máy đã khởi động xong.');
        } finally {
            fclose($socket);
        }
    }

    /**
     * Kiểm tra bắt buộc chế độ ZPL hợp lệ, không bị dính Diagnostic hay APL-E
     */
    public function requireZplMode(array $mode): void
    {
        if (strtolower($mode['apl.enable'] ?? '') !== 'none') {
            throw new InvalidArgumentException('Máy in đang bật Virtual Device ' . ($mode['apl.enable'] ?? '') . ' (gây in ra mã HEX). Bấm "Khôi phục ZPL" để đưa về NONE và tắt/bật lại máy in trước khi in.');
        }
        if (strtolower($mode['input.capture'] ?? '') !== 'off' || strtolower($mode['device.diagnostic_print'] ?? '') !== 'disabled') {
            throw new InvalidArgumentException('Máy in đang bật chẩn đoán dữ liệu (Diagnostic Mode). Bấm "Thoát Diagnostic (~JE)" trên thanh công cụ.');
        }
        if (!in_array(strtolower($mode['device.languages'] ?? ''), ['zpl', 'hybrid_xml_zpl', 'epl_zpl'], true)) {
            throw new InvalidArgumentException('Ngôn ngữ hiện tại của máy in (' . ($mode['device.languages'] ?? '') . ') chưa hỗ trợ ZPL.');
        }
    }

    /**
     * Đọc trạng thái cảm biến qua ~HS (Host Status)
     */
    public function requirePrinterReady(array $settings): void
    {
        $socket = $this->openSocket($settings);
        try {
            if (@fwrite($socket, "~HS\r\n") !== 5) {
                throw new RuntimeException('Không gửi được lệnh đọc trạng thái ZPL (~HS).');
            }
            stream_set_blocking($socket, false);
            $deadline = microtime(true) + (float)$settings['timeout'];
            $reply = '';
            $frames = [];
            while (microtime(true) < $deadline && strlen($reply) < 4096) {
                $remaining = $deadline - microtime(true);
                $read = [$socket];
                $write = null;
                $except = null;
                $sec = (int)$remaining;
                $usec = (int)(($remaining - $sec) * 1000000);
                if (@stream_select($read, $write, $except, $sec, $usec) !== 1) {
                    break;
                }
                $chunk = @fread($socket, 4096);
                if ($chunk === false || $chunk === '') {
                    break;
                }
                $reply .= $chunk;
                preg_match_all('/\x02([^\x03]+)\x03/', $reply, $frames);
                if (count($frames[1]) >= 3) {
                    break;
                }
            }

            if (count($frames[1] ?? []) < 3) {
                throw new RuntimeException('Máy in chưa trả lời đủ trạng thái ZPL (~HS). Kiểm tra màn hình máy.');
            }

            $first = explode(',', $frames[1][0]);
            $second = explode(',', $frames[1][1]);
            if (count($first) < 12 || count($second) < 11) {
                throw new RuntimeException('Phản hồi trạng thái ZPL (~HS) không hợp lệ.');
            }

            $errors = [];
            if ((int)$first[1] === 1) $errors[] = 'Hết giấy (PAPER OUT): kiểm tra cuộn tem hoặc cảm biến';
            if ((int)$first[2] === 1) $errors[] = 'Máy đang tạm dừng (PAUSED): bấm nút PAUSE trên máy để tiếp tục';
            if ((int)$first[5] === 1) $errors[] = 'Bộ đệm máy in đã đầy (BUFFER FULL)';
            if ((int)$second[2] === 1) $errors[] = 'Đầu in chưa đóng kín (HEAD OPEN)';
            if ((int)$second[3] === 1) $errors[] = 'Hết ribbon (RIBBON OUT)';

            if ($errors) {
                throw new InvalidArgumentException(implode('; ', $errors) . '. Chưa gửi tem mới.');
            }
        } finally {
            fclose($socket);
        }
    }

    /**
     * Lệnh ZPL thoát Diagnostic và đưa Virtual Device về NONE
     */
    public function recoveryZpl(): string
    {
        return "~JE\r\n"
            . "! U1 setvar \"input.capture\" \"off\"\r\n"
            . "! U1 setvar \"device.diagnostic_print\" \"disabled\"\r\n"
            . "! U1 setvar \"apl.enable\" \"none\"\r\n"
            . "! U1 setvar \"device.languages\" \"zpl\"\r\n"
            . "~JE\r\n~JA\r\n";
    }

    /**
     * Mẫu in thử chữ văn bản ZPL đơn giản (không raster ảnh)
     */
    public function simpleTestZpl(array $settings): string
    {
        $dpmm = [203 => 8, 300 => 12, 600 => 24][$settings['dpi'] ?? 300] ?? 12;
        $dots = (int)round(($settings['labelSize'] ?? 60) * $dpmm);
        $x = 5 * $dpmm;
        $y2 = 15 * $dpmm;
        $font = 3 * $dpmm;
        return "~JE\r\n^XA^MTD^PW{$dots}^LL{$dots}^LH0,0^LS0^LT0^PON^FWN^PMN"
            . "^FO{$x},{$x}^A0N,{$font},{$font}^FDZEBRA ZPL OK^FS"
            . "^FO{$x},{$y2}^A0N,{$font},{$font}^FD{$settings['labelSize']} x {$settings['labelSize']} mm - 1 label^FS^PQ1,0,1,N^XZ\r\n";
    }

    /**
     * Kiểm tra trạng thái kết nối và chế độ máy in Zebra
     */
    public function checkConnection(?array $customSettings = null): array
    {
        $settings = $customSettings ? array_merge($this->getSettings(), $customSettings) : $this->getSettings();
        $start = microtime(true);

        try {
            $socket = $this->openSocket($settings);
            fclose($socket);

            $mode = [];
            $modeOk = true;
            $modeError = null;

            try {
                $mode = $this->printerModeStatus($settings);
                $this->requireZplMode($mode);
            } catch (\Throwable $e) {
                $modeOk = false;
                $modeError = $e->getMessage();
            }

            $readyOk = true;
            $readyError = null;
            try {
                $this->requirePrinterReady($settings);
            } catch (\Throwable $e) {
                $readyOk = false;
                $readyError = $e->getMessage();
            }

            $ms = (int)round((microtime(true) - $start) * 1000);
            $isFullyReady = $modeOk && $readyOk;
            $statusMsg = $isFullyReady
                ? "Máy in sẵn sàng (trực tiếp LAN): ZPL II, Virtual Device NONE ({$ms} ms)."
                : "Đã kết nối TCP LAN ({$ms} ms), nhưng cảnh báo: " . ($modeError ?: $readyError);

            return [
                'connected'  => true,
                'ready'      => $isFullyReady,
                'via_agent'  => false,
                'host'       => $settings['host'],
                'port'       => $settings['port'],
                'ms'         => $ms,
                'mode'       => $mode,
                'modeError'  => $modeError,
                'readyError' => $readyError,
                'message'    => $statusMsg,
            ];
        } catch (\Throwable $directEx) {
            // Khi không kết nối trực tiếp được (như Cloud VPS trên Internet gọi vào IP 192.168.1.94 nội bộ)
            // Kiểm tra xem Print Agent tại xưởng có đang Online không
            $agent = $this->getAgentStatus();
            if ($agent['online']) {
                return [
                    'connected'  => true,
                    'ready'      => $agent['ready'] ?? true,
                    'via_agent'  => true,
                    'host'       => $settings['host'],
                    'port'       => $settings['port'],
                    'ms'         => 0,
                    'agent_info' => $agent,
                    'message'    => "Trạm xưởng Online: Gervin Zebra Agent đang chạy ({$agent['station_name']}) · Máy in {$agent['printer_ip']}:{$agent['printer_port']} sẵn sàng nhận lệnh in từ Cloud.",
                ];
            }

            // Cả kết nối trực tiếp lẫn Agent xưởng đều chưa có
            return [
                'connected'  => false,
                'ready'      => false,
                'via_agent'  => false,
                'host'       => $settings['host'],
                'port'       => $settings['port'],
                'ms'         => 0,
                'message'    => "Không kết nối được ({$settings['host']}:{$settings['port']}): " . $directEx->getMessage() . " Hãy bật file 'Gervin Zebra Agent' trên máy tính xưởng để in qua mạng LAN xưởng, hoặc bấm 'In qua trình duyệt (Dự phòng)'.",
            ];
        }
    }

    /**
     * Thực hiện gửi lệnh thoát Diagnostic (~JE)
     */
    public function exitDiagnostics(?array $customSettings = null): array
    {
        $settings = $customSettings ? array_merge($this->getSettings(), $customSettings) : $this->getSettings();
        $this->sendZpl($settings, "~JE\r\n");
        return [
            'success' => true,
            'message' => 'Đã gửi lệnh ~JE thoát Diagnostic. Hãy kiểm tra màn hình máy in (Diagnostic Mode = OFF).'
        ];
    }

    /**
     * Khôi phục ZPL: chuyển Virtual Device NONE, tắt chẩn đoán, hủy lệnh chờ
     */
    public function recoverZpl(?array $customSettings = null): array
    {
        $settings = $customSettings ? array_merge($this->getSettings(), $customSettings) : $this->getSettings();
        $this->sendZpl($settings, $this->recoveryZpl());
        return [
            'success' => true,
            'message' => 'Đã gửi lệnh: Virtual Device = NONE, Diagnostic = OFF, Language = ZPL và hủy lệnh chờ (~JA). Vui lòng tắt/bật lại máy in nếu vừa chuyển Virtual Device.'
        ];
    }

    /**
     * In thử 1 tem chữ ZPL
     */
    public function printSimpleTest(?array $customSettings = null): array
    {
        $settings = $customSettings ? array_merge($this->getSettings(), $customSettings) : $this->getSettings();
        $zpl = $this->simpleTestZpl($settings);

        try {
            $mode = $this->printerModeStatus($settings);
            $this->requireZplMode($mode);
            $this->requirePrinterReady($settings);

            $bytes = $this->sendZpl($settings, $zpl);

            $this->recordHistory([
                'time'     => date('c'),
                'host'     => "{$settings['host']}:{$settings['port']}",
                'quantity' => 1,
                'status'   => 'sent',
                'bytes'    => $bytes,
                'message'  => 'In thử chữ ZPL OK thành công (trực tiếp LAN)',
            ]);

            return [
                'success'   => true,
                'queued'    => false,
                'bytes'     => $bytes,
                'message'   => 'Đã gửi 1 tem thử chữ ZEBRA ZPL OK đến máy in thành công!'
            ];
        } catch (\Throwable $directEx) {
            $agent = $this->getAgentStatus();
            if ($agent['online']) {
                $job = $this->enqueueJob($zpl, 1, $settings, 'simple-test');
                return [
                    'success'  => true,
                    'queued'   => true,
                    'jobId'    => $job['id'],
                    'message'  => 'Đã gửi 1 tem thử vào hàng đợi Trạm Xưởng (Agent đang chạy và sẽ in ngay)!',
                ];
            }
            throw new RuntimeException("Không thể in thử: " . $directEx->getMessage() . " Hãy bật 'Gervin Zebra Agent' tại xưởng hoặc kiểm tra kết nối.");
        }
    }

    /**
     * Tạo mã ZPL từ chuỗi ảnh đơn sắc (hex bitmap)
     */
    public function makeZpl(array $settings, string $hex, int $quantity = 1): string
    {
        $qty = max(1, min(100, $quantity));
        $dpiMap = [203 => 8, 300 => 12, 600 => 24];
        $dpmm = $dpiMap[$settings['dpi']] ?? 12;
        $dots = (int)round(($settings['labelSize'] ?? 60) * $dpmm);

        $rowBytes = (int)ceil($dots / 8);
        $totalBytes = $rowBytes * $dots;

        if (strlen($hex) !== $totalBytes * 2 || !ctype_xdigit($hex)) {
            throw new InvalidArgumentException("Dữ liệu ảnh tem không khớp kích thước: cần {$totalBytes} bytes ({$dots}x{$dots} dots ở {$settings['dpi']} DPI). Vui lòng bấm Ctrl+F5 tải lại.");
        }

        // Direct Thermal (^MTD), kích thước nhãn, độ đậm, tốc độ
        $zpl = "~JE\n^XA\n^MTD\n^PW{$dots}\n^LL{$dots}\n^LH0,0\n^LS0\n^LT0\n^PON\n^FWN\n^PMN\n~SD{$settings['darkness']}\n^PR{$settings['speed']}\n";

        // Tách các khối ^GFA không quá 99,999 bytes theo đặc tả ZPL
        $rowsPerField = (int)floor(99999 / $rowBytes);
        for ($y = 0; $y < $dots; $y += $rowsPerField) {
            $fieldBytes = min($rowsPerField, $dots - $y) * $rowBytes;
            $data = strtoupper(substr($hex, $y * $rowBytes * 2, $fieldBytes * 2));
            $zpl .= "^FO0,{$y}^GFA,{$fieldBytes},{$fieldBytes},{$rowBytes},{$data}^FS\n";
        }

        $zpl .= "^PQ{$qty},0,1,N\n^XZ\n";

        return $zpl;
    }

    /**
     * Gửi chuỗi ZPL qua socket TCP có khóa đồng thời (flock) và timeout
     */
    public function sendZpl(array $settings, string $zpl): int
    {
        $lockFile = storage_path('app/zebra_print.lock');
        $dir = dirname($lockFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $lock = fopen($lockFile, 'c');
        if (!$lock) {
            throw new RuntimeException('Không thể mở khóa hàng đợi in.');
        }

        $lockDeadline = microtime(true) + 2.0;
        while (!flock($lock, LOCK_EX | LOCK_NB)) {
            if (microtime(true) >= $lockDeadline) {
                fclose($lock);
                throw new RuntimeException('Máy in đang bận xử lý một lệnh in khác. Vui lòng chờ vài giây.');
            }
            usleep(50000);
        }

        try {
            $socket = $this->openSocket($settings);
            stream_set_blocking($socket, false);

            $sent = 0;
            $length = strlen($zpl);
            $deadline = microtime(true) + (float)$settings['timeout'] + 7.0;

            try {
                while ($sent < $length) {
                    $remaining = $deadline - microtime(true);
                    if ($remaining <= 0) {
                        throw new RuntimeException("Hết thời gian chờ gửi dữ liệu in đến máy in.");
                    }

                    $read = null;
                    $except = null;
                    $write = [$socket];
                    $sec = (int)$remaining;
                    $usec = (int)(($remaining - $sec) * 1000000);

                    $writable = @stream_select($read, $write, $except, $sec, $usec);
                    if ($writable === false || $writable === 0) {
                        throw new RuntimeException("Kết nối máy in không phản hồi trong lúc truyền dữ liệu.");
                    }

                    $chunk = substr($zpl, $sent, 16384);
                    $written = @fwrite($socket, $chunk);
                    if ($written === false || $written === 0 || stream_get_meta_data($socket)['timed_out']) {
                        throw new RuntimeException("Quá trình gửi lệnh in bị ngắt quãng.");
                    }

                    $sent += $written;
                }
            } finally {
                fclose($socket);
            }

            return $sent;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * Đọc lịch sử 50 lệnh in gần nhất
     */
    public function getHistory(): array
    {
        $path = storage_path('app/zebra_print_history.json');
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * Ghi lịch sử in
     */
    public function recordHistory(array $entry): void
    {
        try {
            $history = $this->getHistory();
            array_unshift($history, $entry);
            $history = array_slice($history, 0, 50);
            $path = storage_path('app/zebra_print_history.json');
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            file_put_contents($path, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            Log::warning('Không thể ghi lịch sử in Zebra: ' . $e->getMessage());
        }
    }

    /**
     * In tem từ dữ liệu ảnh đơn sắc hex (Hỗ trợ cả in trực tiếp LAN và in qua Agent Xưởng khi chạy trên Cloud)
     */
    public function printLabelHex(string $hex, int $quantity = 1, ?array $customSettings = null): array
    {
        $settings = $customSettings ? array_merge($this->getSettings(), $customSettings) : $this->getSettings();
        $qty = max(1, min(100, $quantity));

        // 1. Tạo chuỗi ZPL
        $zpl = $this->makeZpl($settings, $hex, $qty);

        // 2. Thử in trực tiếp qua TCP Socket LAN (chạy khi ở môi trường local cùng mạng)
        try {
            $mode = $this->printerModeStatus($settings);
            $this->requireZplMode($mode);
            $this->requirePrinterReady($settings);

            $bytesSent = $this->sendZpl($settings, $zpl);

            $this->recordHistory([
                'time'     => date('c'),
                'host'     => "{$settings['host']}:{$settings['port']}",
                'quantity' => $qty,
                'status'   => 'sent',
                'bytes'    => $bytesSent,
                'message'  => "Đã in {$qty} tem thành công (trực tiếp LAN)",
            ]);

            return [
                'success'   => true,
                'queued'    => false,
                'host'      => $settings['host'],
                'port'      => $settings['port'],
                'quantity'  => $qty,
                'bytesSent' => $bytesSent,
                'message'   => "Đã gửi thành công {$qty} tem đến máy in Zebra ({$settings['host']}:{$settings['port']}) qua LAN!",
            ];
        } catch (\Throwable $directEx) {
            // 3. Nếu kết nối trực tiếp thất bại (trên Cloud VPS không nhìn thấy IP xưởng 192.168.1.94)
            // Kiểm tra xem Gervin Zebra Agent tại máy xưởng có đang Online không
            $agent = $this->getAgentStatus();
            if ($agent['online']) {
                $job = $this->enqueueJob($zpl, $qty, $settings, 'label');
                return [
                    'success'   => true,
                    'queued'    => true,
                    'jobId'     => $job['id'],
                    'host'      => $settings['host'],
                    'port'      => $settings['port'],
                    'quantity'  => $qty,
                    'message'   => "Đã chuyển lệnh in {$qty} tem vào hàng đợi Trạm Xưởng (Print Agent đang xử lý và máy in sẽ nhả tem ngay)!",
                ];
            }

            // 4. Cả trực tiếp lẫn Agent xưởng đều không kết nối được
            throw new RuntimeException("Máy chủ Cloud không kết nối được tới máy in nội bộ ({$settings['host']}:{$settings['port']}): " . $directEx->getMessage() . " Vui lòng bật file 'Gervin Zebra Agent' trên máy tính xưởng để in tự động, hoặc bấm 'In qua trình duyệt (Dự phòng)'.");
        }
    }
}

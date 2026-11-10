<?php

namespace App\Services;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class ZebraPrinterService
{
    const DEFAULT_SETTINGS = [
        'host'      => '192.168.1.67',
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

        // Fallback kiểm tra file cài đặt cũ của testintem nếu có
        $fallbackTestPath = 'C:\\Code\\testintem\\storage\\settings.php';
        if (file_exists($fallbackTestPath)) {
            $raw = file_get_contents($fallbackTestPath);
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
            throw new InvalidArgumentException('Vui lòng nhập địa chỉ IP máy in Zebra.');
        }

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
     * Mở socket TCP đến máy in Zebra
     */
    protected function openSocket(array $settings)
    {
        $host = trim($settings['host']);
        $port = (int)$settings['port'];
        $timeout = (float)$settings['timeout'];

        $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);
        if (!$socket) {
            throw new RuntimeException("Không thể kết nối đến máy in {$host}:{$port}. Lỗi ({$errno}): {$errstr}. Vui lòng kiểm tra nguồn và kết nối mạng máy in.");
        }

        stream_set_timeout($socket, (int)$timeout);
        return $socket;
    }

    /**
     * Kiểm tra trạng thái kết nối tới máy in
     */
    public function checkConnection(?array $customSettings = null): array
    {
        $settings = $customSettings ? array_merge($this->getSettings(), $customSettings) : $this->getSettings();
        $start = microtime(true);

        $socket = $this->openSocket($settings);
        fclose($socket);

        $ms = (int)round((microtime(true) - $start) * 1000);
        return [
            'connected' => true,
            'host'      => $settings['host'],
            'port'      => $settings['port'],
            'ms'        => $ms,
            'message'   => "Kết nối máy in Zebra {$settings['host']}:{$settings['port']} thành công ({$ms} ms).",
        ];
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
            throw new InvalidArgumentException("Dữ liệu ảnh tem không khớp kích thước: cần {$totalBytes} bytes ({$dots}x{$dots} dots ở {$settings['dpi']} DPI).");
        }

        // Khởi tạo ZPL: thoát Diagnostic, bật Direct Thermal (^MTD), đặt khổ (^PW, ^LL), tốc độ, độ đậm
        $zpl = "~JE\n^XA\n^MTD\n^PW{$dots}\n^LL{$dots}\n^LH0,0\n^LS0\n^LT0\n^PON\n^FWN\n^PMN\n~SD{$settings['darkness']}\n^PR{$settings['speed']}\n";

        // Tách thành các trường ^GFA không vượt quá giới hạn 99,999 bytes của ZPL
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
     * Gửi chuỗi ZPL qua socket mạng đến máy in
     */
    public function sendZpl(array $settings, string $zpl): int
    {
        $socket = $this->openSocket($settings);
        stream_set_blocking($socket, false);

        $sent = 0;
        $length = strlen($zpl);
        $deadline = microtime(true) + (float)$settings['timeout'] + 5.0; // Cho thêm 5s khi gửi payload lớn

        try {
            while ($sent < $length) {
                $remaining = $deadline - microtime(true);
                if ($remaining <= 0) {
                    throw new RuntimeException("Hết thời gian gửi dữ liệu in. Kiểm tra lại trạng thái máy in.");
                }

                $read = null;
                $except = null;
                $write = [$socket];
                $sec = (int)$remaining;
                $usec = (int)(($remaining - $sec) * 1000000);

                $writable = @stream_select($read, $write, $except, $sec, $usec);
                if ($writable === false || $writable === 0) {
                    throw new RuntimeException("Kết nối máy in bị ngắt hoặc không phản hồi trong lúc gửi.");
                }

                $chunk = substr($zpl, $sent, 16384);
                $written = @fwrite($socket, $chunk);
                if ($written === false || $written === 0 || stream_get_meta_data($socket)['timed_out']) {
                    throw new RuntimeException("Gửi dữ liệu in bị gián đoạn giữa chừng.");
                }

                $sent += $written;
            }
        } finally {
            fclose($socket);
        }

        return $sent;
    }

    /**
     * In tem từ dữ liệu ảnh đơn sắc hex
     */
    public function printLabelHex(string $hex, int $quantity = 1, ?array $customSettings = null): array
    {
        $settings = $customSettings ? array_merge($this->getSettings(), $customSettings) : $this->getSettings();
        $zpl = $this->makeZpl($settings, $hex, $quantity);
        $bytesSent = $this->sendZpl($settings, $zpl);

        return [
            'success'   => true,
            'host'      => $settings['host'],
            'port'      => $settings['port'],
            'quantity'  => $quantity,
            'bytesSent' => $bytesSent,
            'message'   => "Đã gửi thành công {$quantity} tem đến máy in Zebra ({$settings['host']}:{$settings['port']})!",
        ];
    }
}


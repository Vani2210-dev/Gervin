<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeGeneratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'text' => ['nullable', 'string', 'max:1000'],
            'size' => ['nullable', 'integer', 'min:120', 'max:600'],
            'margin' => ['nullable', 'integer', 'min:0', 'max:10'],
            'error_correction' => ['nullable', 'in:L,M,Q,H'],
        ]);

        $text = $request->query('text') === null ? 'DA00003' : trim((string) ($validated['text'] ?? ''));
        $size = (int) ($validated['size'] ?? $request->query('size', 240));
        $margin = (int) ($validated['margin'] ?? $request->query('margin', 2));
        $errorCorrection = (string) ($validated['error_correction'] ?? $request->query('error_correction', 'M'));

        $qrSvg = null;

        if ($text !== '') {
            $qrSvg = QrCode::format('svg')
                ->size($size)
                ->margin($margin)
                ->errorCorrection($errorCorrection)
                ->generate($text);

            // Bỏ khai báo XML đầu file để nhúng SVG an toàn vào HTML.
            $qrSvg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $qrSvg);
        }

        return view('qrcode.index', compact('text', 'size', 'margin', 'errorCorrection', 'qrSvg'));
    }
}

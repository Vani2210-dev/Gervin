@extends('layout.layout')
@php
    $title = 'QR Generator';
    $subTitle = 'QR Code / Generator';
    $script = '<script>
        function fillQrText(value) {
            const input = document.getElementById("qr_text");
            if (!input) {
                return;
            }

            input.value = value;
            input.focus();
        }

        function downloadQrCode(format) {
            const preview = document.querySelector(".qr-generator-page__preview");
            const svgElement = preview ? preview.querySelector("svg") : null;

            if (!svgElement) {
                alert("Chưa có mã QR để tải.");
                return;
            }

            // Tải trực tiếp file SVG từ nội dung đang hiển thị.
            if (format === "svg") {
                const serializer = new XMLSerializer();
                const source = serializer.serializeToString(svgElement);
                const svgBlob = new Blob([source], { type: "image/svg+xml;charset=utf-8" });
                const url = URL.createObjectURL(svgBlob);
                const link = document.createElement("a");

                link.href = url;
                link.download = "qr-generator.svg";
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                return;
            }

            // Chuyển SVG sang PNG ngay trên trình duyệt để người dùng tải về.
            const serializer = new XMLSerializer();
            const source = serializer.serializeToString(svgElement);
            const svgBlob = new Blob([source], { type: "image/svg+xml;charset=utf-8" });
            const svgUrl = URL.createObjectURL(svgBlob);
            const image = new Image();
            const width = Number(svgElement.getAttribute("width")) || 240;
            const height = Number(svgElement.getAttribute("height")) || 240;

            image.onload = function () {
                const canvas = document.createElement("canvas");
                canvas.width = width;
                canvas.height = height;

                const context = canvas.getContext("2d");
                if (!context) {
                    URL.revokeObjectURL(svgUrl);
                    alert("Không thể tạo canvas để tải ảnh.");
                    return;
                }

                context.fillStyle = "#ffffff";
                context.fillRect(0, 0, width, height);
                context.drawImage(image, 0, 0, width, height);
                setTimeout(() => URL.revokeObjectURL(svgUrl), 1000);

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        alert("Không thể tạo file PNG.");
                        return;
                    }

                    const pngUrl = URL.createObjectURL(blob);
                    const link = document.createElement("a");
                    link.href = pngUrl;
                    link.download = "qr-generator.png";
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(pngUrl), 1000);
                }, "image/png");
            };

            image.onerror = function () {
                URL.revokeObjectURL(svgUrl);
                alert("Không thể chuyển QR sang PNG.");
            };

            image.src = svgUrl;
        }
    </script>';
@endphp

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="card h-full border-0 overflow-hidden">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h6 class="text-lg font-semibold mb-1">Tạo mã QR</h6>
                        <p class="text-sm text-secondary-light mb-0">Nhập text thuần để QR scan ra đúng chuỗi, không nhúng đường dẫn.</p>
                    </div>
                    <span class="w-11 h-11 rounded-xl bg-primary-50 text-primary-600 inline-flex items-center justify-center shrink-0">
                        <iconify-icon icon="lucide:qr-code" class="text-2xl"></iconify-icon>
                    </span>
                </div>
            </div>
            <div class="card-body p-6">
                @if(isset($errors) && $errors->any())
                    <div class="mb-6 rounded-xl border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="GET" action="{{ route('qrcode.index') }}" class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label for="qr_text" class="form-label">Nội dung QR</label>
                        <textarea
                            id="qr_text"
                            name="text"
                            rows="4"
                            class="form-control font-mono"
                            placeholder="Ví dụ: DA00003 hoặc một chuỗi text bất kỳ"
                        >{{ old('text', $text) }}</textarea>
                    </div>

                    <div class="sm:col-span-4 col-span-12">
                        <label for="qr_size" class="form-label">Kích thước</label>
                        <input
                            id="qr_size"
                            type="number"
                            name="size"
                            min="120"
                            max="600"
                            value="{{ old('size', $size) }}"
                            class="form-control"
                        >
                    </div>

                    <div class="sm:col-span-4 col-span-12">
                        <label for="qr_margin" class="form-label">Margin</label>
                        <input
                            id="qr_margin"
                            type="number"
                            name="margin"
                            min="0"
                            max="10"
                            value="{{ old('margin', $margin) }}"
                            class="form-control"
                        >
                    </div>

                    <div class="sm:col-span-4 col-span-12">
                        <label for="qr_error_correction" class="form-label">Error correction</label>
                        <select id="qr_error_correction" name="error_correction" class="form-select">
                            @foreach (['L' => 'L - Thấp', 'M' => 'M - Trung bình', 'Q' => 'Q - Cao', 'H' => 'H - Rất cao'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('error_correction', $errorCorrection) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-12">
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white rounded-lg px-5 py-[11px] inline-flex items-center gap-2">
                                <iconify-icon icon="lucide:sparkles" class="text-xl"></iconify-icon>
                                Tạo QR
                            </button>
                            <a href="{{ route('qrcode.index') }}" class="btn bg-light-100 hover:bg-neutral-200 text-dark rounded-lg px-5 py-[11px] inline-flex items-center gap-2">
                                <iconify-icon icon="lucide:refresh-ccw" class="text-xl"></iconify-icon>
                                Đặt lại
                            </a>
                        </div>
                    </div>
                </form>

                <div class="mt-6">
                    <h6 class="text-base font-semibold mb-3">Mẫu nhanh</h6>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="btn bg-neutral-100 hover:bg-neutral-200 text-secondary-light rounded-lg px-3 py-2 text-sm font-mono" onclick="fillQrText('DA00003')">DA00003</button>
                        <button type="button" class="btn bg-neutral-100 hover:bg-neutral-200 text-secondary-light rounded-lg px-3 py-2 text-sm font-mono" onclick="fillQrText('GLA00012')">GLA00012</button>
                        <button type="button" class="btn bg-neutral-100 hover:bg-neutral-200 text-secondary-light rounded-lg px-3 py-2 text-sm font-mono" onclick="fillQrText('https://example.com/item/DA00003')">URL mẫu</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card h-full border-0 overflow-hidden">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h6 class="text-lg font-semibold mb-1">Kết quả</h6>
                        <p class="text-sm text-secondary-light mb-0">QR bên dưới đang mã hóa đúng nội dung text bạn nhập vào.</p>
                    </div>
                    <span class="w-11 h-11 rounded-xl bg-success-50 text-success-600 inline-flex items-center justify-center shrink-0">
                        <iconify-icon icon="lucide:badge-check" class="text-2xl"></iconify-icon>
                    </span>
                </div>
            </div>
            <div class="card-body p-6">
                @if($qrSvg)
                    <div class="flex flex-col items-center gap-6">
                        <div class="w-full max-w-[360px] rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm flex items-center justify-center">
                            <div class="qr-generator-page__preview inline-flex items-center justify-center">
                                {!! $qrSvg !!}
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <button type="button" class="btn bg-success-600 hover:bg-success-700 text-white rounded-lg px-5 py-[11px] inline-flex items-center gap-2" onclick="downloadQrCode('png')">
                                <iconify-icon icon="lucide:download" class="text-xl"></iconify-icon>
                                Tải PNG
                            </button>
                            <button type="button" class="btn bg-light-100 hover:bg-neutral-200 text-dark rounded-lg px-5 py-[11px] inline-flex items-center gap-2" onclick="downloadQrCode('svg')">
                                <iconify-icon icon="lucide:file-code-2" class="text-xl"></iconify-icon>
                                Tải SVG
                            </button>
                        </div>

                        <div class="w-full">
                            <label class="form-label">Nội dung đã mã hóa</label>
                            <div class="qr-generator-page__value rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-3 font-mono text-sm break-all text-neutral-800">
                                {{ $text }}
                            </div>
                        </div>

                        <div class="w-full grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="rounded-xl border border-neutral-200 bg-white px-4 py-3">
                                <p class="text-xs text-secondary-light mb-1">Kích thước</p>
                                <p class="text-sm font-semibold text-neutral-900 mb-0">{{ $size }} px</p>
                            </div>
                            <div class="rounded-xl border border-neutral-200 bg-white px-4 py-3">
                                <p class="text-xs text-secondary-light mb-1">Margin</p>
                                <p class="text-sm font-semibold text-neutral-900 mb-0">{{ $margin }}</p>
                            </div>
                            <div class="rounded-xl border border-neutral-200 bg-white px-4 py-3">
                                <p class="text-xs text-secondary-light mb-1">Error correction</p>
                                <p class="text-sm font-semibold text-neutral-900 mb-0">{{ $errorCorrection }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="min-h-[320px] flex flex-col items-center justify-center text-center rounded-2xl border border-dashed border-neutral-200 bg-neutral-50 px-6 py-10">
                        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 inline-flex items-center justify-center mb-4">
                            <iconify-icon icon="lucide:qr-code" class="text-3xl"></iconify-icon>
                        </div>
                        <h6 class="text-lg font-semibold mb-2">Chưa có mã QR</h6>
                        <p class="text-sm text-secondary-light mb-0">Nhập nội dung bên trái rồi bấm <span class="font-semibold">Tạo QR</span> để hiển thị kết quả ở đây.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

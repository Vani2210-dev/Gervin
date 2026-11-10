/**
 * Gervin Wood - Zebra ZT411 Direct Label Printer Integration (60x60mm, 300 DPI)
 * Rasterizes crisp Vietnamese labels on HTML5 canvas and sends raw ZPL ^GFA via TCP Socket
 */
(function (window, document) {
    'use strict';

    const ZebraPacking = {
        settings: {
            host: '192.168.1.67',
            port: 9100,
            timeout: 3,
            dpi: 300,
            labelSize: 60,
            darkness: 25,
            speed: 3,
            margin: 1.5,
            offsetX: 0,
            offsetY: 0
        },
        logoImage: null,
        logoLoaded: false,
        currentPackageData: null,
        isPrinting: false,

        // Khởi tạo và tải logo
        init: function (customSettings) {
            if (customSettings && typeof customSettings === 'object') {
                Object.assign(this.settings, customSettings);
            }

            this.logoImage = new Image();
            this.logoImage.crossOrigin = 'anonymous';
            this.logoImage.onload = () => {
                this.logoLoaded = true;
            };
            this.logoImage.onerror = () => {
                console.warn('Không thể tải logo công ty Gervin đen trắng. Sẽ bỏ qua logo khi vẽ.');
            };
            this.logoImage.src = '/assets/images/company-logo-black.png';

            // Tải cấu hình từ server nếu có
            this.loadSettings();
        },

        loadSettings: async function () {
            try {
                const res = await fetch('/processes/packing/printer/settings', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.settings) {
                        Object.assign(this.settings, data.settings);
                    }
                }
            } catch (e) {
                // Giữ default
            }
        },

        saveSettings: async function (newSettings) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/processes/packing/printer/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(newSettings)
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Không thể lưu cấu hình');
            }
            Object.assign(this.settings, data.settings);
            return data;
        },

        checkConnection: async function (customSettings) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/processes/packing/printer/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(customSettings || this.settings)
            });
            const data = await res.json();
            return data;
        },

        // Hàm ngắt dòng thông minh trên canvas master 1200x1200
        wrapText: function (ctx, text, x, y, size, maxWidth, maxLines, lineHeightOffset = 10) {
            let lines = [];
            let currentSize = size;
            for (; currentSize >= 20; currentSize -= 2) {
                ctx.font = `700 ${currentSize}px Arial, sans-serif`;
                const words = (text || '').toString().trim().split(/\s+/);
                lines = [];
                let current = '';
                for (const word of words) {
                    const candidate = current ? current + ' ' + word : word;
                    if (ctx.measureText(candidate).width > maxWidth && current) {
                        lines.push(current);
                        current = word;
                    } else {
                        current = candidate;
                    }
                }
                if (current) lines.push(current);
                if (lines.length <= maxLines) break;
            }

            if (lines.length > maxLines) {
                lines = lines.slice(0, maxLines);
                if (lines.length > 0) {
                    let last = lines[lines.length - 1];
                    while (ctx.measureText(last + '...').width > maxWidth && last.length > 0) {
                        last = last.slice(0, -1);
                    }
                    lines[lines.length - 1] = last + '...';
                }
            }

            lines.forEach((lineText, i) => {
                this.drawText(ctx, lineText, x, y + i * (currentSize + lineHeightOffset), currentSize, maxWidth);
            });
        },

        drawText: function (ctx, value, x, y, size, maxWidth, align = 'left', color = '#000') {
            ctx.fillStyle = color;
            ctx.textAlign = align;
            ctx.textBaseline = 'middle';
            ctx.font = `700 ${size}px Arial, sans-serif`;
            while (ctx.measureText(value).width > maxWidth && size > 14) {
                size--;
                ctx.font = `700 ${size}px Arial, sans-serif`;
            }
            ctx.fillText(value, x, y);
            ctx.fillStyle = '#000';
        },

        drawLine: function (ctx, x1, y1, x2, y2, width = 3) {
            ctx.lineWidth = width;
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.stroke();
        },

        drawRect: function (ctx, x, y, w, h, r = 0, fill = false) {
            ctx.beginPath();
            if (ctx.roundRect) {
                ctx.roundRect(x, y, w, h, r);
            } else {
                ctx.rect(x, y, w, h);
            }
            if (fill) ctx.fill(); else ctx.stroke();
        },

        // Vẽ QR Code trực tiếp lên canvas đích để không bị mờ do scale
        drawQrCode: function (targetCtx, totalDots, margin, code, settings) {
            if (typeof qrcode === 'undefined') {
                console.error('Thư viện qrcode.js chưa được tải!');
                return;
            }

            qrcode.stringToBytes = (str) => Array.from(new TextEncoder().encode(str));
            const qr = qrcode(0, 'H');
            qr.addData(code, /^\d+$/.test(code) ? 'Numeric' : 'Byte');
            qr.make();

            const count = qr.getModuleCount();
            const scale = (totalDots - 2 * margin) / 1200;
            const boxSize = 560 * scale;
            const moduleSize = Math.floor(boxSize / (count + 8));
            const size = (count + 8) * moduleSize;

            const labelSizeMm = Number(settings.labelSize) || 60;
            const offsetX = Number(settings.offsetX || 0) * totalDots / labelSizeMm;
            const offsetY = Number(settings.offsetY || 0) * totalDots / labelSizeMm;

            const x = Math.round(margin + offsetX + 624 * scale + (boxSize - size) / 2);
            const y = Math.round(margin + offsetY + 156 * scale + (boxSize - size) / 2);

            // Xóa vùng an toàn (quiet zone) màu trắng
            targetCtx.fillStyle = '#fff';
            targetCtx.fillRect(x, y, size, size);

            // Vẽ các khối đen QR
            targetCtx.fillStyle = '#000';
            for (let r = 0; r < count; r++) {
                for (let c = 0; c < count; c++) {
                    if (qr.isDark(r, c)) {
                        targetCtx.fillRect(x + (c + 4) * moduleSize, y + (r + 4) * moduleSize, moduleSize, moduleSize);
                    }
                }
            }
        },

        // Render nhãn kích thước chuẩn master 1200x1200 rồi thu về kích thước in thực tế theo DPI (720x720 ở 300DPI)
        rasterize: function (pkgData, customSettings) {
            const settings = Object.assign({}, this.settings, customSettings || {});
            const code = (pkgData.packageCode || pkgData.packageId || '').toString().trim();
            if (!code) {
                throw new Error('Thiếu mã kiện để tạo tem và QR code.');
            }

            // 1. Tạo Canvas Master độ phân giải cao 1200x1200
            const master = document.createElement('canvas');
            master.width = master.height = 1200;
            const m = master.getContext('2d', { willReadFrequently: true });

            m.fillStyle = '#fff';
            m.fillRect(0, 0, 1200, 1200);
            m.strokeStyle = '#000';
            m.fillStyle = '#000';
            m.lineWidth = 4;

            // Khung viền ngoài
            this.drawRect(m, 12, 12, 1176, 1176);
            this.drawLine(m, 12, 150, 1188, 150, 4);

            // Cột phân cách giữa thông tin kiện và QR
            this.drawLine(m, 618, 150, 618, 760, 3);
            this.drawLine(m, 12, 760, 1188, 760, 3);
            this.drawLine(m, 12, 1068, 1188, 1068, 3);

            // Header: Brand & Tiêu đề
            this.drawText(m, 'GERVIN WOOD', 42, 58, 32, 900);
            this.drawText(m, 'TEM KIỆN ĐÓNG GÓI', 42, 108, 54, 990);

            // Logo công ty góc trên phải
            if (this.logoLoaded && this.logoImage && this.logoImage.naturalWidth > 0) {
                try {
                    m.drawImage(this.logoImage, 1034, 14, 132, 132);
                } catch (e) {}
            }

            // Thông tin kiện (Cột trái)
            this.drawText(m, code, 42, 220, 90, 550);
            this.drawText(m, 'ĐƠN: ' + (pkgData.orderCode || '-'), 42, 309, 50, 550);
            this.drawText(m, 'LOẠI: ' + (pkgData.typeSummary || '-'), 42, 376, 50, 550);

            const total = Number(pkgData.totalItems) || 0;
            this.drawText(m, 'TỔNG SL: ' + total + ' linh kiện', 42, 443, 52, 550);
            this.drawText(m, 'STT TẤM / LINH KIỆN', 42, 510, 33, 550);
            this.wrapText(m, pkgData.plateSttString || '-', 42, 567, 44, 550, 3, 10);

            // Mã kiện phía dưới vùng QR (Cột phải)
            this.drawText(m, code, 904, 739, 34, 540, 'center');

            // Phần GIAO HÀNG (Dưới 760)
            this.drawText(m, 'GIAO HÀNG', 42, 786, 34, 1110);
            this.drawText(m, 'KH: ' + (pkgData.customerName || '—'), 42, 851, 64, 1110);
            this.drawText(m, 'SĐT: ' + (pkgData.customerPhone || '—'), 42, 921, 60, 1110);
            this.wrapText(m, 'Đ/c: ' + (pkgData.deliveryAddress || '—'), 42, 985, 46, 1110, 2, 8);

            // Phần Footer (Dưới 1068)
            this.drawText(m, 'Ngày: ' + (pkgData.packedAt || '-'), 42, 1110, 35, 1110);
            this.drawText(m, 'Đóng gói: ' + (pkgData.packedBy || 'Gervin Wood'), 42, 1160, 36, 1110);

            // 2. Chuyển sang kích thước dot thực tế của máy in Zebra
            const dpiMap = { 203: 8, 300: 12, 600: 24 };
            const dpmm = dpiMap[settings.dpi] || 12;
            const labelMm = Number(settings.labelSize) || 60;
            const totalDots = Math.round(labelMm * dpmm);
            const marginDots = Number(settings.margin || 1.5) * dpmm;
            const offsetXDots = Number(settings.offsetX || 0) * dpmm;
            const offsetYDots = Number(settings.offsetY || 0) * dpmm;

            const printCanvas = document.createElement('canvas');
            printCanvas.width = printCanvas.height = totalDots;
            const pCtx = printCanvas.getContext('2d', { willReadFrequently: true });

            pCtx.fillStyle = '#fff';
            pCtx.fillRect(0, 0, totalDots, totalDots);
            pCtx.drawImage(
                master,
                marginDots + offsetXDots,
                marginDots + offsetYDots,
                totalDots - 2 * marginDots,
                totalDots - 2 * marginDots
            );

            // Ngưỡng hóa đơn sắc monochrome (Thresholding)
            const imgData = pCtx.getImageData(0, 0, totalDots, totalDots);
            const px = imgData.data;
            for (let i = 0; i < px.length; i += 4) {
                const bw = (px[i] * 0.299 + px[i + 1] * 0.587 + px[i + 2] * 0.114) < 165 ? 0 : 255;
                px[i] = px[i + 1] = px[i + 2] = bw;
                px[i + 3] = 255;
            }
            pCtx.putImageData(imgData, 0, 0);

            // Vẽ QR Code trực tiếp lên canvas độ nét cao
            this.drawQrCode(pCtx, totalDots, marginDots, code, settings);

            // Đóng gói bitmap 1-bit thành chuỗi Hex cho lệnh ZPL ^GFA
            const finalPixels = pCtx.getImageData(0, 0, totalDots, totalDots).data;
            const hexArr = [];
            for (let y = 0; y < totalDots; y++) {
                for (let x = 0; x < totalDots; x += 8) {
                    let byte = 0;
                    for (let b = 0; b < 8; b++) {
                        if (x + b < totalDots && finalPixels[(y * totalDots + x + b) * 4] === 0) {
                            byte |= (128 >> b);
                        }
                    }
                    hexArr.push(byte.toString(16).padStart(2, '0'));
                }
            }

            return {
                hex: hexArr.join('').toUpperCase(),
                previewDataUrl: printCanvas.toDataURL('image/png'),
                dots: totalDots,
                settings: settings
            };
        },

        // Gửi lệnh in đến máy in Zebra qua API Gervin
        printDirect: async function (packageId, quantity = 1, pkgData = null) {
            if (this.isPrinting) {
                throw new Error('Đang có lệnh in được xử lý, vui lòng chờ.');
            }

            const data = pkgData || this.currentPackageData;
            if (!data) {
                throw new Error('Không có dữ liệu kiện đóng gói.');
            }

            const rasterResult = this.rasterize(data);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            this.isPrinting = true;
            try {
                const url = `/processes/packing/${packageId}/print-zebra`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        hex: rasterResult.hex,
                        quantity: Math.max(1, Math.min(100, Number(quantity) || 1)),
                        renderSettings: JSON.stringify(rasterResult.settings)
                    })
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Lỗi gửi lệnh in tem Zebra.');
                }

                return result;
            } finally {
                this.isPrinting = false;
            }
        },

        // Mở Modal xem trước và in tem Zebra
        openModal: function (pkgData) {
            this.currentPackageData = pkgData || this.currentPackageData || window.zebraPackageData;
            if (!this.currentPackageData) {
                console.error('Chưa có dữ liệu kiện đóng gói.');
                if (typeof window.showToast === 'function') {
                    window.showToast('Không tìm thấy dữ liệu kiện đóng gói.', 'error');
                } else {
                    alert('Không tìm thấy dữ liệu kiện đóng gói.');
                }
                return;
            }
            const data = this.currentPackageData;
            let modal = document.getElementById('zebraPrintModal');
            if (!modal) {
                modal = this.createModalElement();
                document.body.appendChild(modal);
            }

            // Cập nhật thông tin kiện trong modal
            const codeEl = document.getElementById('zebraModalPackageCode');
            if (codeEl) codeEl.textContent = data.packageCode || data.packageId || '—';

            const qtyInput = document.getElementById('zebraModalQuantity');
            if (qtyInput) qtyInput.value = '1';

            modal.style.display = 'block';

            // Render bản xem trước nhãn tem
            this.updateModalPreview();

            // Kiểm tra tình trạng kết nối máy in
            this.testConnectionInModal();
        },

        closeModal: function () {
            const modal = document.getElementById('zebraPrintModal');
            if (modal) {
                modal.style.display = 'none';
            }
        },

        updateModalPreview: function () {
            if (!this.currentPackageData) return;
            const previewImg = document.getElementById('zebraModalPreviewImg');
            try {
                const res = this.rasterize(this.currentPackageData);
                if (previewImg) {
                    previewImg.src = res.previewDataUrl;
                }
            } catch (err) {
                console.error(err);
            }
        },

        testConnectionInModal: async function () {
            const statusBadge = document.getElementById('zebraModalStatusBadge');
            const statusText = document.getElementById('zebraModalStatusText');
            if (!statusBadge || !statusText) return;

            statusBadge.className = 'zp-status-badge checking';
            statusText.textContent = `Kiểm tra ${this.settings.host}:${this.settings.port}...`;

            try {
                const res = await this.checkConnection();
                if (res.connected) {
                    statusBadge.className = 'zp-status-badge online';
                    statusText.textContent = `Đã kết nối (${res.ms || 3} ms)`;
                } else {
                    statusBadge.className = 'zp-status-badge offline';
                    statusText.textContent = `Không kết nối (${this.settings.host})`;
                }
            } catch (e) {
                statusBadge.className = 'zp-status-badge offline';
                statusText.textContent = `Không kết nối (${this.settings.host})`;
            }
        },

        executePrintFromModal: async function () {
            if (!this.currentPackageData) return;
            const printBtn = document.getElementById('zebraModalPrintBtn');
            const qtyInput = document.getElementById('zebraModalQuantity');
            const qty = parseInt(qtyInput ? qtyInput.value : '1', 10) || 1;

            if (printBtn) {
                printBtn.disabled = true;
                printBtn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon> Đang gửi in...`;
            }

            try {
                const result = await this.printDirect(this.currentPackageData.packageId, qty);
                if (typeof window.showToast === 'function') {
                    window.showToast(result.message || `Đã in thành công ${qty} tem Zebra!`, 'success');
                } else {
                    alert(result.message || `Đã in thành công ${qty} tem Zebra!`);
                }
                this.closeModal();
            } catch (err) {
                if (typeof window.showToast === 'function') {
                    window.showToast(err.message, 'error');
                } else {
                    alert('Lỗi: ' + err.message);
                }
            } finally {
                if (printBtn) {
                    printBtn.disabled = false;
                    printBtn.innerHTML = `<iconify-icon icon="lucide:printer"></iconify-icon> In ngay tem Zebra`;
                }
            }
        },

        createModalElement: function () {
            const wrap = document.createElement('div');
            wrap.id = 'zebraPrintModal';
            wrap.style.display = 'none';

            wrap.innerHTML = `
                <style>
                    #zebraPrintModal {
                        position: fixed;
                        inset: 0;
                        z-index: 99999999;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                        color: #111827;
                        line-height: 1.4;
                    }
                    #zebraPrintModal * { box-sizing: border-box; }
                    #zebraPrintModal .zp-overlay {
                        position: absolute;
                        inset: 0;
                        background: rgba(0, 0, 0, 0.6);
                        backdrop-filter: blur(2px);
                    }
                    #zebraPrintModal .zp-wrap {
                        position: absolute;
                        inset: 0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 16px;
                        pointer-events: none;
                    }
                    #zebraPrintModal .zp-card {
                        position: relative;
                        background: #ffffff;
                        border-radius: 12px;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
                        width: 100%;
                        max-width: 540px;
                        pointer-events: auto;
                        overflow: hidden;
                        display: flex;
                        flex-direction: column;
                    }
                    #zebraPrintModal .zp-header {
                        padding: 14px 20px;
                        background: #f9fafb;
                        border-bottom: 1px solid #e5e7eb;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                    }
                    #zebraPrintModal .zp-header-left {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                    }
                    #zebraPrintModal .zp-icon-badge {
                        width: 40px;
                        height: 40px;
                        border-radius: 10px;
                        background: #d1fae5;
                        color: #047857;
                        border: 1px solid #a7f3d0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 22px;
                    }
                    #zebraPrintModal .zp-title {
                        font-size: 16px;
                        font-weight: 700;
                        color: #111827;
                        margin: 0;
                    }
                    #zebraPrintModal .zp-subtitle {
                        font-size: 12px;
                        color: #6b7280;
                        margin: 2px 0 0;
                    }
                    #zebraPrintModal .zp-close-btn {
                        background: none;
                        border: none;
                        cursor: pointer;
                        color: #9ca3af;
                        font-size: 24px;
                        line-height: 1;
                        padding: 4px;
                        border-radius: 6px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    #zebraPrintModal .zp-close-btn:hover {
                        color: #111827;
                        background: #f3f4f6;
                    }
                    #zebraPrintModal .zp-body {
                        padding: 20px;
                        display: flex;
                        flex-direction: row;
                        gap: 20px;
                        align-items: flex-start;
                    }
                    #zebraPrintModal .zp-preview-box {
                        width: 180px;
                        height: 180px;
                        flex-shrink: 0;
                        background: #f9fafb;
                        border: 2px dashed #d1d5db;
                        border-radius: 10px;
                        padding: 4px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    #zebraPrintModal .zp-preview-img {
                        max-width: 100%;
                        max-height: 100%;
                        object-fit: contain;
                        border-radius: 6px;
                        background: #ffffff;
                    }
                    #zebraPrintModal .zp-preview-hint {
                        font-size: 11px;
                        color: #6b7280;
                        text-align: center;
                        margin-top: 6px;
                    }
                    #zebraPrintModal .zp-content-box {
                        flex: 1;
                        min-width: 0;
                        display: flex;
                        flex-direction: column;
                        gap: 14px;
                    }
                    #zebraPrintModal .zp-row-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 2px;
                    }
                    #zebraPrintModal .zp-label-sm {
                        font-size: 11px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        color: #6b7280;
                    }
                    #zebraPrintModal .zp-status-badge {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 3px 10px;
                        border-radius: 9999px;
                        font-size: 12px;
                        font-weight: 600;
                    }
                    #zebraPrintModal .zp-status-badge.online {
                        background: #ecfdf5;
                        color: #065f46;
                        border: 1px solid #a7f3d0;
                    }
                    #zebraPrintModal .zp-status-badge.offline {
                        background: #fef2f2;
                        color: #991b1b;
                        border: 1px solid #fecaca;
                    }
                    #zebraPrintModal .zp-status-badge.checking {
                        background: #fffbeb;
                        color: #92400e;
                        border: 1px solid #fde68a;
                    }
                    #zebraPrintModal .zp-dot {
                        width: 7px;
                        height: 7px;
                        border-radius: 50%;
                    }
                    #zebraPrintModal .zp-status-badge.online .zp-dot { background: #10b981; }
                    #zebraPrintModal .zp-status-badge.offline .zp-dot { background: #ef4444; }
                    #zebraPrintModal .zp-status-badge.checking .zp-dot { background: #f59e0b; }
                    #zebraPrintModal .zp-package-code {
                        font-size: 22px;
                        font-weight: 900;
                        font-family: monospace;
                        color: #111827;
                        letter-spacing: 0.5px;
                    }
                    #zebraPrintModal .zp-field-label {
                        display: block;
                        font-size: 12px;
                        font-weight: 600;
                        color: #374151;
                        margin-bottom: 6px;
                    }
                    #zebraPrintModal .zp-qty-group {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        flex-wrap: wrap;
                    }
                    #zebraPrintModal .zp-qty-stepper {
                        display: inline-flex;
                        align-items: center;
                        border: 1px solid #d1d5db;
                        border-radius: 8px;
                        overflow: hidden;
                        background: #fff;
                    }
                    #zebraPrintModal .zp-qty-btn {
                        width: 34px;
                        height: 34px;
                        background: #f3f4f6;
                        border: none;
                        font-size: 16px;
                        font-weight: bold;
                        cursor: pointer;
                        color: #374151;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    #zebraPrintModal .zp-qty-btn:hover { background: #e5e7eb; }
                    #zebraPrintModal .zp-qty-input {
                        width: 50px;
                        height: 34px;
                        border: none;
                        text-align: center;
                        font-weight: 700;
                        font-size: 14px;
                        color: #111827;
                        outline: none;
                    }
                    #zebraPrintModal .zp-chip {
                        padding: 6px 12px;
                        border-radius: 6px;
                        border: 1px solid #e5e7eb;
                        background: #f9fafb;
                        font-size: 12px;
                        font-weight: 600;
                        cursor: pointer;
                        color: #374151;
                    }
                    #zebraPrintModal .zp-chip:hover {
                        background: #f3f4f6;
                        border-color: #d1d5db;
                    }
                    #zebraPrintModal .zp-printer-card {
                        padding: 10px 12px;
                        border-radius: 8px;
                        background: #f9fafb;
                        border: 1px solid #e5e7eb;
                        font-size: 12px;
                    }
                    #zebraPrintModal .zp-printer-card-top {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                    }
                    #zebraPrintModal .zp-retry-link {
                        color: #0284c7;
                        background: none;
                        border: none;
                        cursor: pointer;
                        font-weight: 600;
                        font-size: 12px;
                        text-decoration: underline;
                        padding: 0;
                    }
                    #zebraPrintModal .zp-printer-card-sub {
                        color: #6b7280;
                        font-size: 11px;
                        margin-top: 3px;
                    }
                    #zebraPrintModal .zp-footer {
                        padding: 14px 20px;
                        background: #f9fafb;
                        border-top: 1px solid #e5e7eb;
                        display: flex;
                        align-items: center;
                        justify-content: flex-end;
                        gap: 10px;
                    }
                    #zebraPrintModal .zp-cancel-btn {
                        padding: 8px 16px;
                        border-radius: 8px;
                        border: 1px solid #d1d5db;
                        background: #ffffff;
                        color: #374151;
                        font-weight: 600;
                        font-size: 13px;
                        cursor: pointer;
                    }
                    #zebraPrintModal .zp-cancel-btn:hover { background: #f3f4f6; }
                    #zebraPrintModal .zp-print-btn {
                        padding: 9px 20px;
                        border-radius: 8px;
                        border: none;
                        background: #059669;
                        color: #ffffff;
                        font-weight: 700;
                        font-size: 13px;
                        cursor: pointer;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                    }
                    #zebraPrintModal .zp-print-btn:hover { background: #047857; }
                    #zebraPrintModal .zp-print-btn:disabled {
                        opacity: 0.6;
                        cursor: not-allowed;
                    }
                </style>

                <div class="zp-overlay" onclick="ZebraPacking.closeModal()"></div>
                <div class="zp-wrap">
                    <div class="zp-card">
                        <div class="zp-header">
                            <div class="zp-header-left">
                                <span class="zp-icon-badge">
                                    <iconify-icon icon="lucide:printer"></iconify-icon>
                                </span>
                                <div>
                                    <h5 class="zp-title">In tem Zebra ZT411</h5>
                                    <p class="zp-subtitle">Khổ tem 60 x 60 mm · 300 DPI · In trực tiếp qua LAN/Wi-Fi</p>
                                </div>
                            </div>
                            <button type="button" onclick="ZebraPacking.closeModal()" class="zp-close-btn" title="Đóng">
                                <iconify-icon icon="lucide:x"></iconify-icon>
                            </button>
                        </div>

                        <div class="zp-body">
                            <!-- Preview Box -->
                            <div style="text-align: center;">
                                <div class="zp-preview-box">
                                    <img id="zebraModalPreviewImg" class="zp-preview-img" alt="Xem trước tem">
                                </div>
                                <div class="zp-preview-hint">Khổ tem thực tế 60 × 60 mm</div>
                            </div>

                            <!-- Controls & Status -->
                            <div class="zp-content-box">
                                <div>
                                    <div class="zp-row-header">
                                        <span class="zp-label-sm">Mã kiện</span>
                                        <span id="zebraModalStatusBadge" class="zp-status-badge checking">
                                            <span class="zp-dot"></span>
                                            <span id="zebraModalStatusText">Kiểm tra kết nối...</span>
                                        </span>
                                    </div>
                                    <div id="zebraModalPackageCode" class="zp-package-code">261110001</div>
                                </div>

                                <div>
                                    <label class="zp-field-label">Số lượng tem cần in</label>
                                    <div class="zp-qty-group">
                                        <div class="zp-qty-stepper">
                                            <button type="button" onclick="ZebraPacking.changeQuantity(-1)" class="zp-qty-btn">−</button>
                                            <input type="number" id="zebraModalQuantity" min="1" max="50" value="1" class="zp-qty-input">
                                            <button type="button" onclick="ZebraPacking.changeQuantity(1)" class="zp-qty-btn">+</button>
                                        </div>
                                        <button type="button" onclick="document.getElementById('zebraModalQuantity').value = '1'" class="zp-chip">1 tem</button>
                                        <button type="button" onclick="document.getElementById('zebraModalQuantity').value = '2'" class="zp-chip">2 tem</button>
                                        <button type="button" onclick="document.getElementById('zebraModalQuantity').value = '3'" class="zp-chip">3 tem</button>
                                    </div>
                                </div>

                                <div class="zp-printer-card">
                                    <div class="zp-printer-card-top">
                                        <span>Máy in: <strong>Zebra ZT411</strong></span>
                                        <button type="button" onclick="ZebraPacking.testConnectionInModal()" class="zp-retry-link">Thử kết nối lại</button>
                                    </div>
                                    <div class="zp-printer-card-sub">IP: ${this.settings.host}:${this.settings.port} · Direct Thermal</div>
                                </div>
                            </div>
                        </div>

                        <div class="zp-footer">
                            <button type="button" onclick="ZebraPacking.closeModal()" class="zp-cancel-btn">Hủy</button>
                            <button type="button" id="zebraModalPrintBtn" onclick="ZebraPacking.executePrintFromModal()" class="zp-print-btn">
                                <iconify-icon icon="lucide:printer"></iconify-icon>
                                In ngay tem Zebra
                            </button>
                        </div>
                    </div>
                </div>
            `;
            return wrap;
        },

        changeQuantity: function (delta) {
            const input = document.getElementById('zebraModalQuantity');
            if (input) {
                let val = parseInt(input.value, 10) || 1;
                val = Math.max(1, Math.min(50, val + delta));
                input.value = val;
            }
        }
    };

    // Tự động khởi chạy
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ZebraPacking.init());
    } else {
        ZebraPacking.init();
    }

    window.ZebraPacking = ZebraPacking;
})(window, document);


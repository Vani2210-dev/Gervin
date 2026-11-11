/**
 * Gervin Wood - Tích hợp máy in nhãn Zebra ZT411 cho Đóng gói (/processes/packing)
 * Khổ tem: 60 x 60 mm · Độ phân giải: 300 DPI · Gửi trực tiếp qua RAW TCP Socket (LAN / Wi-Fi)
 * Dự phòng: In qua trình duyệt
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
        isChecking: false,

        init: function (customSettings) {
            if (customSettings && typeof customSettings === 'object') {
                Object.assign(this.settings, customSettings);
            }

            this.logoImage = new Image();
            this.logoImage.crossOrigin = 'anonymous';
            this.logoImage.onload = () => {
                this.logoLoaded = true;
                this.updateModalPreview();
            };
            this.logoImage.onerror = () => {
                console.warn('Không tải được logo công ty đen trắng. Sẽ bỏ qua logo khi vẽ.');
            };
            this.logoImage.src = '/assets/images/company-logo-black.png';

            this.loadSettings();
        },

        getCsrfToken: function () {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        toast: function (msg, type = 'info') {
            if (typeof window.showToast === 'function') {
                window.showToast(msg, type);
            } else {
                alert(msg);
            }
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
                        this.updateStatusDot(null);
                    }
                }
            } catch (e) {}
        },

        saveSettings: async function (newSettings) {
            const res = await fetch('/processes/packing/printer/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(newSettings)
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Không thể lưu cấu hình máy in.');
            }
            Object.assign(this.settings, data.settings);
            return data;
        },

        checkConnection: async function (customSettings) {
            const res = await fetch('/processes/packing/printer/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(customSettings || this.settings)
            });
            return await res.json();
        },

        simpleTest: async function () {
            const res = await fetch('/processes/packing/printer/simple-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(this.settings)
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Lỗi in thử chữ ZPL.');
            }
            return data;
        },

        exitDiagnostics: async function () {
            const res = await fetch('/processes/packing/printer/exit-diagnostics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(this.settings)
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Lỗi gửi lệnh thoát Diagnostic.');
            }
            return data;
        },

        recoverZpl: async function () {
            const res = await fetch('/processes/packing/printer/recover-zpl', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(this.settings)
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Lỗi gửi lệnh khôi phục ZPL.');
            }
            return data;
        },

        loadHistory: async function () {
            const res = await fetch('/processes/packing/printer/history', {
                headers: { 'Accept': 'application/json' }
            });
            return await res.json();
        },

        updateStatusDot: function (isOnline) {
            const dot = document.getElementById('packingPrinterStatusDot');
            if (!dot) return;
            if (isOnline === true) {
                dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block shadow-sm';
                dot.title = `Zebra ZT411 (${this.settings.host}): Đã sẵn sàng`;
            } else if (isOnline === false) {
                dot.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 inline-block shadow-sm';
                dot.title = `Zebra ZT411 (${this.settings.host}): Mất kết nối`;
            } else {
                dot.className = 'w-2.5 h-2.5 rounded-full bg-amber-400 inline-block shadow-sm';
                dot.title = `Zebra ZT411 (${this.settings.host}): Chưa kiểm tra`;
            }
        },

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

        drawQrCode: function (targetCtx, totalDots, margin, code, settings) {
            if (typeof qrcode === 'undefined') {
                console.error('qrcode.js chưa tải');
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

            targetCtx.fillStyle = '#fff';
            targetCtx.fillRect(x, y, size, size);

            targetCtx.fillStyle = '#000';
            for (let r = 0; r < count; r++) {
                for (let c = 0; c < count; c++) {
                    if (qr.isDark(r, c)) {
                        targetCtx.fillRect(x + (c + 4) * moduleSize, y + (r + 4) * moduleSize, moduleSize, moduleSize);
                    }
                }
            }
        },

        rasterize: function (pkgData, customSettings) {
            const settings = Object.assign({}, this.settings, customSettings || {});
            const code = (pkgData.packageCode || pkgData.packageId || '').toString().trim();
            if (!code) {
                throw new Error('Thiếu mã kiện để tạo tem.');
            }

            const master = document.createElement('canvas');
            master.width = master.height = 1200;
            const m = master.getContext('2d', { willReadFrequently: true });

            m.fillStyle = '#fff';
            m.fillRect(0, 0, 1200, 1200);
            m.strokeStyle = '#000';
            m.fillStyle = '#000';
            m.lineWidth = 4;

            // Viền ngoài
            this.drawRect(m, 12, 12, 1176, 1176);
            this.drawLine(m, 12, 150, 1188, 150, 4);

            // Cột phân cách
            this.drawLine(m, 618, 150, 618, 760, 3);
            this.drawLine(m, 12, 760, 1188, 760, 3);
            this.drawLine(m, 12, 1068, 1188, 1068, 3);

            // Header
            this.drawText(m, 'GERVIN WOOD', 42, 58, 32, 900);
            this.drawText(m, 'TEM KIỆN ĐÓNG GÓI', 42, 108, 54, 990);

            // Logo
            if (this.logoLoaded && this.logoImage && this.logoImage.naturalWidth > 0) {
                try {
                    m.drawImage(this.logoImage, 1034, 14, 132, 132);
                } catch (e) {}
            }

            // Cột trái
            this.drawText(m, code, 42, 220, 90, 550);
            this.drawText(m, 'ĐƠN: ' + (pkgData.orderCode || '-'), 42, 309, 50, 550);
            this.drawText(m, 'LOẠI: ' + (pkgData.typeSummary || '-'), 42, 376, 50, 550);

            const total = Number(pkgData.totalItems) || 0;
            this.drawText(m, 'TỔNG SL: ' + total + ' linh kiện', 42, 443, 52, 550);
            this.drawText(m, 'STT TẤM / LINH KIỆN', 42, 510, 33, 550);
            this.wrapText(m, pkgData.plateSttString || '-', 42, 567, 44, 550, 3, 10);

            // Cột phải dưới QR
            this.drawText(m, code, 904, 739, 34, 540, 'center');

            // Giao hàng
            this.drawText(m, 'GIAO HÀNG', 42, 786, 34, 1110);
            this.drawText(m, 'KH: ' + (pkgData.customerName || '—'), 42, 851, 64, 1110);
            this.drawText(m, 'SĐT: ' + (pkgData.customerPhone || '—'), 42, 921, 60, 1110);
            this.wrapText(m, 'Đ/c: ' + (pkgData.deliveryAddress || '—'), 42, 985, 46, 1110, 2, 8);

            // Footer
            this.drawText(m, 'Ngày: ' + (pkgData.packedAt || '-'), 42, 1110, 35, 1110);
            this.drawText(m, 'Đóng gói: ' + (pkgData.packedBy || 'Gervin Wood'), 42, 1160, 36, 1110);

            // Target Dots Canvas
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

            // Monochrome Thresholding (< 165 -> black)
            const imgData = pCtx.getImageData(0, 0, totalDots, totalDots);
            const px = imgData.data;
            for (let i = 0; i < px.length; i += 4) {
                const bw = (px[i] * 0.299 + px[i + 1] * 0.587 + px[i + 2] * 0.114) < 165 ? 0 : 255;
                px[i] = px[i + 1] = px[i + 2] = bw;
                px[i + 3] = 255;
            }
            pCtx.putImageData(imgData, 0, 0);

            // Vẽ QR Code
            this.drawQrCode(pCtx, totalDots, marginDots, code, settings);

            // Đóng gói 1-bit thành chuỗi Hex
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

        // In trực tiếp qua RAW TCP Socket Zebra
        printDirect: async function (packageId, quantity = 1, pkgData = null) {
            if (this.isPrinting) {
                throw new Error('Đang xử lý một lệnh in khác, vui lòng chờ.');
            }

            const data = pkgData || this.currentPackageData;
            if (!data) {
                throw new Error('Không có dữ liệu kiện đóng gói.');
            }

            const rasterResult = this.rasterize(data);
            this.isPrinting = true;

            try {
                const url = `/processes/packing/${packageId}/print-zebra`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken()
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

        // Dự phòng in qua trình duyệt
        printViaBrowser: function (pkgData, quantity = 1) {
            const data = pkgData || this.currentPackageData;
            if (!data) {
                this.toast('Không có dữ liệu kiện đóng gói.', 'error');
                return;
            }

            const rasterResult = this.rasterize(data);
            const dataUrl = rasterResult.previewDataUrl;
            const qty = Math.max(1, Math.min(50, Number(quantity) || 1));

            let iframe = document.getElementById('zebraPrintHiddenIframe');
            if (iframe) iframe.remove();

            iframe = document.createElement('iframe');
            iframe.id = 'zebraPrintHiddenIframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.style.visibility = 'hidden';
            document.body.appendChild(iframe);

            let pagesHtml = '';
            for (let i = 0; i < qty; i++) {
                pagesHtml += `<div class="zp-print-page"><img class="zp-print-img" src="${dataUrl}" alt="Tem ${i + 1}"></div>`;
            }

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>In tem Zebra - ${data.packageCode || ''}</title>
                    <style>
                        @page { size: 60mm 60mm; margin: 0; }
                        *, *:before, *:after { box-sizing: border-box; margin: 0; padding: 0; }
                        html, body {
                            margin: 0; padding: 0; background: #ffffff; width: 60mm;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .zp-print-page {
                            width: 60mm; height: 60mm;
                            page-break-after: always; break-after: page;
                            display: flex; align-items: center; justify-content: center;
                            overflow: hidden;
                        }
                        .zp-print-page:last-child { page-break-after: auto; break-after: auto; }
                        .zp-print-img {
                            width: 60mm; height: 60mm; display: block;
                            image-rendering: pixelated;
                        }
                    </style>
                </head>
                <body>${pagesHtml}</body>
                </html>
            `);
            doc.close();

            const triggerPrint = () => {
                setTimeout(() => {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        window.print();
                    }
                }, 200);
            };

            const imgs = iframe.contentWindow.document.querySelectorAll('img');
            let loaded = 0;
            if (imgs.length === 0) {
                triggerPrint();
            } else {
                imgs.forEach(img => {
                    if (img.complete) {
                        loaded++;
                        if (loaded === imgs.length) triggerPrint();
                    } else {
                        img.onload = img.onerror = () => {
                            loaded++;
                            if (loaded === imgs.length) triggerPrint();
                        };
                    }
                });
            }
        },

        // Mở Modal in tem bằng packageId (tự động fetch print-data)
        openModalForPackage: async function (packageId) {
            try {
                this.toast('Đang nạp dữ liệu tem kiện #' + packageId + '...', 'info');
                const res = await fetch(`/processes/packing/${packageId}/print-data`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok || !data.success || !data.printData) {
                    throw new Error(data.message || 'Không thể tải dữ liệu kiện.');
                }
                this.openModal(data.printData);
            } catch (err) {
                this.toast('Lỗi: ' + err.message, 'error');
            }
        },

        // Mở Modal in tem
        openModal: function (pkgData) {
            this.currentPackageData = pkgData || this.currentPackageData || window.zebraPackageData;
            if (!this.currentPackageData) {
                this.toast('Chưa có thông tin kiện đóng gói để in tem.', 'error');
                return;
            }

            let modal = document.getElementById('zebraPrintModal');
            if (!modal) {
                modal = this.createPrintModalElement();
                document.body.appendChild(modal);
            }

            const data = this.currentPackageData;
            const codeEl = document.getElementById('zebraModalPackageCode');
            if (codeEl) codeEl.textContent = data.packageCode || data.packageId || '—';

            const qtyInput = document.getElementById('zebraModalQuantity');
            if (qtyInput) qtyInput.value = '1';

            modal.style.display = 'block';
            this.updateModalPreview();
            this.testConnectionQuick();
        },

        closeModal: function () {
            const modal = document.getElementById('zebraPrintModal');
            if (modal) modal.style.display = 'none';
        },

        updateModalPreview: function () {
            if (!this.currentPackageData) return;
            const previewImg = document.getElementById('zebraModalPreviewImg');
            try {
                const res = this.rasterize(this.currentPackageData);
                if (previewImg) previewImg.src = res.previewDataUrl;
            } catch (err) {
                console.error(err);
            }
        },

        testConnectionQuick: async function () {
            const statusBadge = document.getElementById('zebraModalStatusBadge');
            const statusText = document.getElementById('zebraModalStatusText');
            const ipLabel = document.getElementById('zebraModalPrinterIp');
            if (ipLabel) ipLabel.textContent = `${this.settings.host}:${this.settings.port}`;

            if (statusBadge && statusText) {
                statusBadge.className = 'zp-status-badge checking';
                statusText.textContent = 'Đang kiểm tra kết nối…';
            }

            try {
                const res = await this.checkConnection();
                if (res.connected && res.ready) {
                    if (statusBadge && statusText) {
                        statusBadge.className = 'zp-status-badge online';
                        statusText.textContent = `Online (${res.ms}ms) · Sẵn sàng`;
                    }
                    this.updateStatusDot(true);
                } else if (res.connected) {
                    if (statusBadge && statusText) {
                        statusBadge.className = 'zp-status-badge checking';
                        statusText.textContent = res.message || 'Cảnh báo máy in';
                    }
                    this.updateStatusDot(true);
                } else {
                    if (statusBadge && statusText) {
                        statusBadge.className = 'zp-status-badge offline';
                        statusText.textContent = 'Không kết nối được';
                    }
                    this.updateStatusDot(false);
                }
            } catch (e) {
                if (statusBadge && statusText) {
                    statusBadge.className = 'zp-status-badge offline';
                    statusText.textContent = 'Lỗi kết nối';
                }
                this.updateStatusDot(false);
            }
        },

        executePrintFromModal: async function () {
            if (!this.currentPackageData) return;
            const printBtn = document.getElementById('zebraModalPrintBtn');
            const qtyInput = document.getElementById('zebraModalQuantity');
            const qty = parseInt(qtyInput ? qtyInput.value : '1', 10) || 1;

            if (printBtn) {
                printBtn.disabled = true;
                printBtn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="animate-spin text-lg"></iconify-icon> Đang gửi lệnh in tới máy Zebra…`;
            }

            try {
                const result = await this.printDirect(this.currentPackageData.packageId, qty);
                this.toast(result.message || `Đã gửi thành công ${qty} tem đến máy in Zebra!`, 'success');
                this.updateStatusDot(true);
                this.closeModal();
            } catch (err) {
                this.toast('Lỗi in Zebra: ' + err.message, 'error');
                this.updateStatusDot(false);
            } finally {
                if (printBtn) {
                    printBtn.disabled = false;
                    printBtn.innerHTML = `<iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon> In ngay qua máy in Zebra (LAN)`;
                }
            }
        },

        // Mở modal cấu hình & chẩn đoán máy in Zebra
        openSettingsModal: async function () {
            let modal = document.getElementById('zebraSettingsModal');
            if (!modal) {
                modal = this.createSettingsModalElement();
                document.body.appendChild(modal);
            }

            // Điền giá trị settings hiện tại
            const form = document.getElementById('zebraSettingsForm');
            if (form) {
                for (const [key, val] of Object.entries(this.settings)) {
                    if (form.elements[key]) form.elements[key].value = val;
                }
            }

            modal.style.display = 'block';
            this.handleSettingsCheckConnection();
            this.refreshHistoryInModal();
        },

        closeSettingsModal: function () {
            const modal = document.getElementById('zebraSettingsModal');
            if (modal) modal.style.display = 'none';
        },

        handleSettingsCheckConnection: async function () {
            const resultBox = document.getElementById('zebraSettingsCheckResult');
            if (!resultBox) return;

            resultBox.className = 'zp-diag-alert zp-diag-alert-info';
            resultBox.innerHTML = `<iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon> Đang kết nối tới ${this.settings.host}:${this.settings.port}…`;

            try {
                const res = await this.checkConnection();
                if (res.connected && res.ready) {
                    resultBox.className = 'zp-diag-alert zp-diag-alert-success';
                    resultBox.innerHTML = `<strong>Kết nối thành công (${res.ms} ms)</strong>: Máy in sẵn sàng, Virtual Device = NONE, ZPL II, cảm biến giấy/ribbon bình thường.`;
                    this.updateStatusDot(true);
                } else if (res.connected) {
                    resultBox.className = 'zp-diag-alert zp-diag-alert-warning';
                    resultBox.innerHTML = `<strong>Cảnh báo (${res.ms} ms)</strong>: ${res.message}`;
                    this.updateStatusDot(true);
                } else {
                    resultBox.className = 'zp-diag-alert zp-diag-alert-danger';
                    resultBox.innerHTML = `<strong>Không thể kết nối (${this.settings.host}:${this.settings.port})</strong>: ${res.message}. Kiểm tra máy in đã cắm mạng LAN/Wi-Fi và bật nguồn.`;
                    this.updateStatusDot(false);
                }
            } catch (err) {
                resultBox.className = 'zp-diag-alert zp-diag-alert-danger';
                resultBox.innerHTML = `<strong>Lỗi kết nối</strong>: ${err.message}`;
                this.updateStatusDot(false);
            }
        },

        handleSettingsSave: async function (e) {
            e.preventDefault();
            const form = document.getElementById('zebraSettingsForm');
            if (!form) return;

            const formData = new FormData(form);
            const newSettings = Object.fromEntries(formData.entries());

            try {
                this.toast('Đang lưu cài đặt máy in…', 'info');
                await this.saveSettings(newSettings);
                this.toast('Đã lưu cấu hình máy in Zebra thành công!', 'success');
                this.handleSettingsCheckConnection();
                this.updateModalPreview();
            } catch (err) {
                this.toast('Lỗi: ' + err.message, 'error');
            }
        },

        handleSimpleTest: async function () {
            if (!confirm('Gửi 1 tem in thử chữ ZEBRA ZPL OK đến máy in?')) return;
            try {
                this.toast('Đang gửi 1 tem in thử…', 'info');
                const res = await this.simpleTest();
                this.toast(res.message || 'In thử thành công!', 'success');
                this.refreshHistoryInModal();
            } catch (err) {
                this.toast('Lỗi: ' + err.message, 'error');
            }
        },

        handleExitDiagnostics: async function () {
            try {
                this.toast('Đang gửi lệnh thoát Diagnostic (~JE)…', 'info');
                const res = await this.exitDiagnostics();
                this.toast(res.message, 'success');
            } catch (err) {
                this.toast('Lỗi: ' + err.message, 'error');
            }
        },

        handleRecoverZpl: async function () {
            if (!confirm('Khôi phục ZPL: Chuyển Virtual Device = NONE, tắt Diagnostic, đặt Language = ZPL và hủy hàng đợi. Bạn có chắc chắn?')) return;
            try {
                this.toast('Đang gửi lệnh khôi phục ZPL…', 'info');
                const res = await this.recoverZpl();
                this.toast(res.message, 'success');
                this.refreshHistoryInModal();
            } catch (err) {
                this.toast('Lỗi: ' + err.message, 'error');
            }
        },

        refreshHistoryInModal: async function () {
            const listEl = document.getElementById('zebraSettingsHistoryList');
            if (!listEl) return;

            try {
                const res = await this.loadHistory();
                const history = res.history || [];
                if (!history.length) {
                    listEl.innerHTML = `<div class="p-4 text-center text-xs text-neutral-400">Chưa có lượt in nào được ghi nhận.</div>`;
                    return;
                }

                let html = `
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-100 text-neutral-600 font-bold border-b border-neutral-200">
                                <th class="p-2">Thời gian</th>
                                <th class="p-2">Máy in</th>
                                <th class="p-2 text-center">SL</th>
                                <th class="p-2">Kết quả</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                history.forEach(item => {
                    const isSuccess = item.status === 'sent';
                    const timeStr = item.time ? new Date(item.time).toLocaleString('vi-VN', { timeZone: 'Asia/Ho_Chi_Minh' }) : '-';
                    html += `
                        <tr class="border-b border-neutral-100 hover:bg-neutral-50">
                            <td class="p-2 text-neutral-600">${timeStr}</td>
                            <td class="p-2 font-mono text-neutral-800">${item.host || '-'}</td>
                            <td class="p-2 text-center font-bold">${item.quantity || 1}</td>
                            <td class="p-2 ${isSuccess ? 'text-emerald-700 font-semibold' : 'text-rose-600 font-semibold'}">
                                ${isSuccess ? 'Đã gửi LAN' : 'Thất bại'}
                                <div class="text-[10px] text-neutral-500 font-normal">${item.message || (item.bytes ? `${item.bytes} bytes` : '')}</div>
                            </td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                listEl.innerHTML = html;
            } catch (err) {
                listEl.innerHTML = `<div class="p-3 text-danger-600 text-xs">Không tải được lịch sử: ${err.message}</div>`;
            }
        },

        createPrintModalElement: function () {
            const wrap = document.createElement('div');
            wrap.id = 'zebraPrintModal';
            wrap.style.display = 'none';

            wrap.innerHTML = `
                <style>
                    #zebraPrintModal {
                        position: fixed; inset: 0; z-index: 99999999;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                        color: #111827; line-height: 1.4;
                    }
                    #zebraPrintModal * { box-sizing: border-box; }
                    #zebraPrintModal .zp-overlay {
                        position: absolute; inset: 0; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(2px);
                    }
                    #zebraPrintModal .zp-wrap {
                        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
                        padding: 16px; pointer-events: none;
                    }
                    #zebraPrintModal .zp-card {
                        position: relative; background: #ffffff; border-radius: 14px;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); width: 100%; max-width: 560px;
                        pointer-events: auto; overflow: hidden; display: flex; flex-direction: column;
                    }
                    #zebraPrintModal .zp-header {
                        padding: 14px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
                        display: flex; align-items: center; justify-content: space-between;
                    }
                    #zebraPrintModal .zp-header-left { display: flex; align-items: center; gap: 12px; }
                    #zebraPrintModal .zp-icon-badge {
                        width: 42px; height: 42px; border-radius: 10px; background: #ecfdf5;
                        color: #059669; border: 1px solid #a7f3d0; display: flex;
                        align-items: center; justify-content: center; font-size: 24px;
                    }
                    #zebraPrintModal .zp-title { font-size: 16px; font-weight: 700; color: #111827; margin: 0; }
                    #zebraPrintModal .zp-subtitle { font-size: 12px; color: #6b7280; margin: 2px 0 0; }
                    #zebraPrintModal .zp-close-btn {
                        background: none; border: none; cursor: pointer; color: #9ca3af;
                        font-size: 24px; line-height: 1; padding: 4px; border-radius: 6px;
                        display: flex; align-items: center; justify-content: center;
                    }
                    #zebraPrintModal .zp-close-btn:hover { color: #111827; background: #f3f4f6; }
                    #zebraPrintModal .zp-body {
                        padding: 20px; display: flex; flex-direction: row; gap: 20px; align-items: flex-start;
                    }
                    #zebraPrintModal .zp-preview-box {
                        width: 180px; height: 180px; flex-shrink: 0; background: #f9fafb;
                        border: 2px dashed #d1d5db; border-radius: 10px; padding: 4px;
                        display: flex; align-items: center; justify-content: center;
                    }
                    #zebraPrintModal .zp-preview-img {
                        max-width: 100%; max-height: 100%; object-fit: contain;
                        border-radius: 6px; background: #ffffff;
                    }
                    #zebraPrintModal .zp-preview-hint { font-size: 11px; color: #6b7280; text-align: center; margin-top: 6px; font-weight: 600; }
                    #zebraPrintModal .zp-content-box { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 14px; }
                    #zebraPrintModal .zp-row-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
                    #zebraPrintModal .zp-label-sm { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; }
                    #zebraPrintModal .zp-status-badge {
                        display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px;
                        border-radius: 9999px; font-size: 11px; font-weight: 600;
                    }
                    #zebraPrintModal .zp-status-badge.online { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
                    #zebraPrintModal .zp-status-badge.offline { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
                    #zebraPrintModal .zp-status-badge.checking { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
                    #zebraPrintModal .zp-dot { width: 7px; height: 7px; border-radius: 50%; }
                    #zebraPrintModal .zp-status-badge.online .zp-dot { background: #10b981; }
                    #zebraPrintModal .zp-status-badge.offline .zp-dot { background: #ef4444; }
                    #zebraPrintModal .zp-status-badge.checking .zp-dot { background: #f59e0b; }
                    #zebraPrintModal .zp-package-code {
                        font-size: 22px; font-weight: 900; font-family: monospace; color: #111827; letter-spacing: 0.5px;
                    }
                    #zebraPrintModal .zp-field-label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
                    #zebraPrintModal .zp-qty-group { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
                    #zebraPrintModal .zp-qty-stepper {
                        display: inline-flex; align-items: center; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; background: #fff;
                    }
                    #zebraPrintModal .zp-qty-btn {
                        width: 34px; height: 34px; background: #f3f4f6; border: none; font-size: 16px; font-weight: bold; cursor: pointer; color: #374151;
                    }
                    #zebraPrintModal .zp-qty-btn:hover { background: #e5e7eb; }
                    #zebraPrintModal .zp-qty-input { width: 45px; height: 34px; border: none; text-align: center; font-weight: 700; font-size: 14px; outline: none; }
                    #zebraPrintModal .zp-chip {
                        padding: 6px 10px; border-radius: 6px; border: 1px solid #e5e7eb; background: #f9fafb; font-size: 12px; font-weight: 600; cursor: pointer; color: #374151;
                    }
                    #zebraPrintModal .zp-chip:hover { background: #f3f4f6; border-color: #d1d5db; }
                    #zebraPrintModal .zp-printer-card {
                        padding: 10px 12px; border-radius: 8px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 12px;
                    }
                    #zebraPrintModal .zp-printer-card-top { display: flex; align-items: center; justify-content: space-between; }
                    #zebraPrintModal .zp-footer {
                        padding: 14px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb;
                        display: flex; align-items: center; justify-content: space-between; gap: 10px;
                    }
                    #zebraPrintModal .zp-cancel-btn {
                        padding: 8px 16px; border-radius: 8px; border: 1px solid #d1d5db; background: #ffffff; color: #374151; font-weight: 600; font-size: 13px; cursor: pointer;
                    }
                    #zebraPrintModal .zp-cancel-btn:hover { background: #f3f4f6; }
                    #zebraPrintModal .zp-print-btn {
                        padding: 10px 20px; border-radius: 8px; border: none; background: #059669; color: #ffffff;
                        font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                    }
                    #zebraPrintModal .zp-print-btn:hover { background: #047857; }
                    #zebraPrintModal .zp-print-btn:disabled { opacity: 0.6; cursor: not-allowed; }
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
                                    <h5 class="zp-title">In tem kiện đóng gói Zebra ZT411</h5>
                                    <p class="zp-subtitle">Khổ tem 60 × 60 mm · 300 DPI · In trực tiếp qua LAN/Wi-Fi</p>
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
                                <div class="zp-preview-hint">60 × 60 mm (300 DPI)</div>
                            </div>

                            <!-- Controls & Status -->
                            <div class="zp-content-box">
                                <div>
                                    <div class="zp-row-header">
                                        <span class="zp-label-sm">Mã kiện</span>
                                        <span id="zebraModalStatusBadge" class="zp-status-badge checking">
                                            <span class="zp-dot"></span>
                                            <span id="zebraModalStatusText">Đang kiểm tra…</span>
                                        </span>
                                    </div>
                                    <div id="zebraModalPackageCode" class="zp-package-code">---</div>
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
                                        <button type="button" onclick="document.getElementById('zebraModalQuantity').value = '4'" class="zp-chip">4 tem</button>
                                    </div>
                                </div>

                                <div class="zp-printer-card">
                                    <div class="zp-printer-card-top">
                                        <span>Máy in: <strong>Zebra ZT411 (<span id="zebraModalPrinterIp">192.168.1.67:9100</span>)</strong></span>
                                        <button type="button" onclick="ZebraPacking.openSettingsModal()" class="text-primary-600 hover:underline font-semibold" style="font-size:11px; background:none; border:none; cursor:pointer;">
                                            ⚙ Cài đặt máy in
                                        </button>
                                    </div>
                                    <div class="text-[11px] text-neutral-500 mt-1">Nhiệt trực tiếp (Direct Thermal, không ribbon) · Cổng RAW TCP 9100</div>
                                </div>
                            </div>
                        </div>

                        <div class="zp-footer">
                            <div>
                                <button type="button" onclick="ZebraPacking.printViaBrowser()" class="text-neutral-500 hover:text-neutral-800 text-xs font-semibold underline bg-transparent border-0 cursor-pointer" title="In bằng hộp thoại in trình duyệt nếu máy in mạng chưa sẵn sàng">
                                    In qua trình duyệt (Dự phòng)
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="ZebraPacking.closeModal()" class="zp-cancel-btn">Đóng</button>
                                <button type="button" id="zebraModalPrintBtn" onclick="ZebraPacking.executePrintFromModal()" class="zp-print-btn">
                                    <iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon>
                                    In ngay qua máy in Zebra (LAN)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            return wrap;
        },

        createSettingsModalElement: function () {
            const wrap = document.createElement('div');
            wrap.id = 'zebraSettingsModal';
            wrap.style.display = 'none';

            wrap.innerHTML = `
                <style>
                    #zebraSettingsModal {
                        position: fixed; inset: 0; z-index: 999999999;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                        color: #111827; line-height: 1.4;
                    }
                    #zebraSettingsModal * { box-sizing: border-box; }
                    #zebraSettingsModal .zp-diag-alert {
                        padding: 10px 14px; border-radius: 8px; font-size: 12px; margin-bottom: 14px;
                    }
                    #zebraSettingsModal .zp-diag-alert-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
                    #zebraSettingsModal .zp-diag-alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
                    #zebraSettingsModal .zp-diag-alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
                    #zebraSettingsModal .zp-diag-alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
                    #zebraSettingsModal .zp-form-grid {
                        display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;
                    }
                    #zebraSettingsModal .zp-input {
                        width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px 10px; font-size: 13px;
                    }
                    #zebraSettingsModal .zp-label {
                        display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px;
                    }
                </style>

                <div class="zp-overlay" onclick="ZebraPacking.closeSettingsModal()"></div>
                <div class="zp-wrap">
                    <div class="zp-card" style="max-width: 620px; max-height: 90vh; overflow-y: auto;">
                        <div class="zp-header">
                            <div class="zp-header-left">
                                <span class="zp-icon-badge" style="background: #e0f2fe; color: #0284c7; border-color: #bae6fd;">
                                    <iconify-icon icon="lucide:settings"></iconify-icon>
                                </span>
                                <div>
                                    <h5 class="zp-title">Cài đặt & chẩn đoán máy in Zebra ZT411</h5>
                                    <p class="zp-subtitle">Kết nối mạng LAN/Wi-Fi xưởng · Giao thức RAW TCP</p>
                                </div>
                            </div>
                            <button type="button" onclick="ZebraPacking.closeSettingsModal()" class="zp-close-btn" title="Đóng">
                                <iconify-icon icon="lucide:x"></iconify-icon>
                            </button>
                        </div>

                        <div style="padding: 20px;">
                            <div id="zebraSettingsCheckResult" class="zp-diag-alert zp-diag-alert-info">
                                Đang kiểm tra kết nối tới máy in…
                            </div>

                            <form id="zebraSettingsForm" onsubmit="ZebraPacking.handleSettingsSave(event)">
                                <div class="zp-form-grid">
                                    <div>
                                        <label class="zp-label">Địa chỉ IP máy in</label>
                                        <input type="text" name="host" class="zp-input" required placeholder="192.168.1.67">
                                    </div>
                                    <div>
                                        <label class="zp-label">Cổng TCP (RAW)</label>
                                        <input type="number" name="port" class="zp-input" required value="9100">
                                    </div>
                                    <div>
                                        <label class="zp-label">Độ phân giải (DPI)</label>
                                        <select name="dpi" class="zp-input">
                                            <option value="203">203 dpi (8 dots/mm)</option>
                                            <option value="300" selected>300 dpi (12 dots/mm - Chuẩn ZT411)</option>
                                            <option value="600">600 dpi (24 dots/mm)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="zp-label">Cạnh tem thực tế (mm)</label>
                                        <input type="number" name="labelSize" step="0.5" class="zp-input" required value="60">
                                    </div>
                                    <div>
                                        <label class="zp-label">Độ đậm nhiệt (Darkness 0-30)</label>
                                        <input type="number" name="darkness" min="0" max="30" class="zp-input" required value="25">
                                    </div>
                                    <div>
                                        <label class="zp-label">Tốc độ in (inch/s)</label>
                                        <select name="speed" class="zp-input">
                                            <option value="2">2 ips</option>
                                            <option value="3" selected>3 ips</option>
                                            <option value="4">4 ips</option>
                                            <option value="5">5 ips</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="zp-label">Lề an toàn (mm)</label>
                                        <input type="number" name="margin" step="0.5" class="zp-input" required value="1.5">
                                    </div>
                                    <div>
                                        <label class="zp-label">Thời gian chờ socket (s)</label>
                                        <input type="number" name="timeout" min="1" max="10" class="zp-input" required value="3">
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-neutral-200 pt-4">
                                    <button type="button" onclick="ZebraPacking.handleSettingsCheckConnection()" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-800 text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5">
                                        <iconify-icon icon="lucide:refresh-cw"></iconify-icon>
                                        Kiểm tra lại kết nối
                                    </button>
                                    <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                                        <iconify-icon icon="lucide:check"></iconify-icon>
                                        Lưu cấu hình
                                    </button>
                                </div>
                            </form>

                            <!-- Khối Chẩn đoán & Khôi phục lỗi in (như ver-1) -->
                            <div class="mt-6 border-t border-neutral-200 pt-4">
                                <h6 class="text-xs font-bold text-neutral-800 uppercase tracking-wider mb-2">Công cụ chẩn đoán & khôi phục máy in</h6>
                                <p class="text-xs text-neutral-500 mb-3">Nếu máy in ra hàng loạt tem toàn mã HEX hoặc chữ số vô nghĩa, sử dụng các nút dưới đây:</p>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" onclick="ZebraPacking.handleSimpleTest()" class="btn bg-white hover:bg-neutral-50 border border-neutral-300 text-neutral-700 text-xs font-semibold px-3 py-2 rounded-lg">
                                        1. In thử chữ ZPL (1 tem)
                                    </button>
                                    <button type="button" onclick="ZebraPacking.handleExitDiagnostics()" class="btn bg-white hover:bg-neutral-50 border border-neutral-300 text-neutral-700 text-xs font-semibold px-3 py-2 rounded-lg" title="Gửi ~JE để máy in thoát Communication Diagnostics">
                                        2. Thoát Diagnostic (~JE)
                                    </button>
                                    <button type="button" onclick="ZebraPacking.handleRecoverZpl()" class="btn bg-white hover:bg-rose-50 border border-rose-300 text-rose-700 text-xs font-semibold px-3 py-2 rounded-lg" title="Chọn Virtual Device NONE, chuyển ZPL và hủy lệnh chờ">
                                        3. Khôi phục ZPL (Tắt APL-E)
                                    </button>
                                </div>
                            </div>

                            <!-- Lịch sử in gần nhất -->
                            <div class="mt-6 border-t border-neutral-200 pt-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h6 class="text-xs font-bold text-neutral-800 uppercase tracking-wider">Lịch sử gửi lệnh in</h6>
                                    <button type="button" onclick="ZebraPacking.refreshHistoryInModal()" class="text-primary-600 hover:underline text-xs font-semibold">
                                        Làm mới
                                    </button>
                                </div>
                                <div id="zebraSettingsHistoryList" class="border border-neutral-200 rounded-lg max-h-40 overflow-y-auto bg-white">
                                    <div class="p-4 text-center text-xs text-neutral-400">Đang tải lịch sử…</div>
                                </div>
                            </div>
                        </div>

                        <div class="zp-footer">
                            <button type="button" onclick="ZebraPacking.closeSettingsModal()" class="zp-cancel-btn">Đóng</button>
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ZebraPacking.init());
    } else {
        ZebraPacking.init();
    }

    window.ZebraPacking = ZebraPacking;
})(window, document);

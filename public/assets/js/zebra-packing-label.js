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
            if (!res.ok || !data.connected) {
                throw new Error(data.message || 'Không thể kết nối máy in');
            }
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
            const m = master.getContext('2d');

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
            const pCtx = printCanvas.getContext('2d');

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
                return;
            }
            let modal = document.getElementById('zebraPrintModal');
            if (!modal) {
                modal = this.createModalElement();
                document.body.appendChild(modal);
            }

            // Cập nhật thông tin kiện trong modal
            const codeEl = document.getElementById('zebraModalPackageCode');
            if (codeEl) codeEl.textContent = pkgData.packageCode || pkgData.packageId || '—';

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

            statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200';
            statusText.textContent = `Kiểm tra ${this.settings.host}:${this.settings.port}...`;

            try {
                const res = await this.checkConnection();
                statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                statusText.textContent = `Đã kết nối (${res.ms} ms)`;
            } catch (e) {
                statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200';
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
                printBtn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="text-lg animate-spin"></iconify-icon> Đang gửi in...`;
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
                    printBtn.innerHTML = `<iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon> In ngay tem Zebra`;
                }
            }
        },

        createModalElement: function () {
            const wrap = document.createElement('div');
            wrap.id = 'zebraPrintModal';
            wrap.style.display = 'none';
            wrap.style.position = 'fixed';
            wrap.style.inset = '0';
            wrap.style.zIndex = '9999999';

            wrap.innerHTML = `
                <div style="position:absolute; inset:0; background:rgba(0,0,0,0.55);" onclick="ZebraPacking.closeModal()"></div>
                <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:1rem; pointer-events:none;">
                    <div style="position:relative; background:#fff; border-radius:0.75rem; box-shadow:0 20px 60px rgba(0,0,0,0.3); width:100%; max-width:540px; pointer-events:auto; overflow:hidden;">
                        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold">
                                    <iconify-icon icon="lucide:printer" class="text-xl"></iconify-icon>
                                </span>
                                <div>
                                    <h5 class="text-lg font-bold text-neutral-900 mb-0">In tem Zebra ZT411</h5>
                                    <p class="text-xs text-neutral-500 mb-0">Khổ tem 60 x 60 mm · 300 DPI · In trực tiếp qua LAN/Wi-Fi</p>
                                </div>
                            </div>
                            <button type="button" onclick="ZebraPacking.closeModal()" class="text-neutral-400 hover:text-neutral-900 text-2xl leading-none">
                                <iconify-icon icon="lucide:x"></iconify-icon>
                            </button>
                        </div>

                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row items-center gap-6">
                                <!-- Preview Canvas Box -->
                                <div class="shrink-0 text-center">
                                    <div class="w-48 h-48 border-2 border-dashed border-neutral-300 rounded-lg p-1 bg-neutral-100 flex items-center justify-center shadow-inner">
                                        <img id="zebraModalPreviewImg" class="w-full h-full object-contain rounded" alt="Xem trước tem">
                                    </div>
                                    <span class="text-xs text-neutral-500 mt-1 block">Khổ in thực tế 60 × 60 mm</span>
                                </div>

                                <!-- Controls & Status -->
                                <div class="flex-1 w-full space-y-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Mã kiện</span>
                                            <span id="zebraModalStatusBadge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-neutral-400"></span>
                                                <span id="zebraModalStatusText">Kiểm tra kết nối...</span>
                                            </span>
                                        </div>
                                        <div id="zebraModalPackageCode" class="text-xl font-black text-neutral-900 font-mono tracking-wide">261110001</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-neutral-600 mb-1.5">Số lượng tem cần in</label>
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center border border-neutral-300 rounded-lg overflow-hidden bg-white">
                                                <button type="button" onclick="ZebraPacking.changeQuantity(-1)" class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold transition-colors">−</button>
                                                <input type="number" id="zebraModalQuantity" min="1" max="50" value="1" class="w-16 text-center border-0 py-2 font-bold text-neutral-900 focus:ring-0">
                                                <button type="button" onclick="ZebraPacking.changeQuantity(1)" class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold transition-colors">+</button>
                                            </div>
                                            <button type="button" onclick="document.getElementById('zebraModalQuantity').value = '1'" class="px-2.5 py-1.5 text-xs font-semibold rounded bg-neutral-100 hover:bg-neutral-200 text-neutral-700">1 tem</button>
                                            <button type="button" onclick="document.getElementById('zebraModalQuantity').value = '2'" class="px-2.5 py-1.5 text-xs font-semibold rounded bg-neutral-100 hover:bg-neutral-200 text-neutral-700">2 tem</button>
                                            <button type="button" onclick="document.getElementById('zebraModalQuantity').value = '3'" class="px-2.5 py-1.5 text-xs font-semibold rounded bg-neutral-100 hover:bg-neutral-200 text-neutral-700">3 tem</button>
                                        </div>
                                    </div>

                                    <div class="text-xs text-neutral-500 bg-neutral-50 p-2.5 rounded-lg border border-neutral-200">
                                        <div class="flex items-center justify-between">
                                            <span>Máy in: <strong>Zebra ZT411</strong></span>
                                            <button type="button" onclick="ZebraPacking.testConnectionInModal()" class="text-primary-600 hover:underline font-semibold">Thử kết nối lại</button>
                                        </div>
                                        <div class="text-neutral-400 mt-0.5">IP: ${this.settings.host}:${this.settings.port} · Direct Thermal</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-neutral-200 bg-neutral-50 flex items-center justify-end gap-3">
                            <button type="button" onclick="ZebraPacking.closeModal()" class="px-4 py-2 rounded-lg font-semibold text-neutral-600 hover:bg-neutral-200 transition-colors text-sm">Hủy</button>
                            <button type="button" id="zebraModalPrintBtn" onclick="ZebraPacking.executePrintFromModal()" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-semibold inline-flex items-center gap-2 shadow-sm transition-colors text-sm">
                                <iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon>
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


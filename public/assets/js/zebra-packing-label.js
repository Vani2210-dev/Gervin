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

            this.ensureStylesInjected();

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

        ensureStylesInjected: function () {
            if (document.getElementById('zebraPackingUniversalStyles')) return;
            const style = document.createElement('style');
            style.id = 'zebraPackingUniversalStyles';
            style.textContent = `
                /* CSS Hệ thống Popup Máy in Zebra ZT411 - Gervin Wood */
                .zebra-modal-root {
                    position: fixed !important;
                    inset: 0 !important;
                    z-index: 99999999 !important;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
                    color: #0f172a !important;
                    line-height: 1.5 !important;
                }
                .zebra-modal-root * {
                    box-sizing: border-box !important;
                }
                .zebra-modal-root .zp-overlay {
                    position: fixed !important;
                    inset: 0 !important;
                    background-color: rgba(15, 23, 42, 0.68) !important;
                    backdrop-filter: blur(3px) !important;
                    -webkit-backdrop-filter: blur(3px) !important;
                    z-index: 1 !important;
                }
                .zebra-modal-root .zp-wrap {
                    position: fixed !important;
                    inset: 0 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    padding: 16px !important;
                    pointer-events: none !important;
                    z-index: 2 !important;
                }
                .zebra-modal-root .zp-card {
                    pointer-events: auto !important;
                    position: relative !important;
                    background-color: #ffffff !important;
                    border-radius: 14px !important;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(0, 0, 0, 0.08) !important;
                    width: 100% !important;
                    display: flex !important;
                    flex-direction: column !important;
                    overflow: hidden !important;
                    border: 1px solid #e2e8f0 !important;
                }
                .zebra-modal-root .zp-header {
                    padding: 14px 20px !important;
                    background-color: #f8fafc !important;
                    border-bottom: 1px solid #e2e8f0 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: space-between !important;
                    flex-shrink: 0 !important;
                }
                .zebra-modal-root .zp-header-left {
                    display: flex !important;
                    align-items: center !important;
                    gap: 12px !important;
                }
                .zebra-modal-root .zp-icon-badge {
                    width: 40px !important;
                    height: 40px !important;
                    border-radius: 10px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    font-size: 22px !important;
                    flex-shrink: 0 !important;
                }
                .zebra-modal-root .zp-title {
                    font-size: 16px !important;
                    font-weight: 700 !important;
                    color: #0f172a !important;
                    margin: 0 !important;
                    line-height: 1.3 !important;
                }
                .zebra-modal-root .zp-subtitle {
                    font-size: 12px !important;
                    color: #64748b !important;
                    margin: 2px 0 0 !important;
                }
                .zebra-modal-root .zp-close-btn {
                    background: none !important;
                    border: none !important;
                    cursor: pointer !important;
                    color: #94a3b8 !important;
                    font-size: 22px !important;
                    line-height: 1 !important;
                    padding: 6px !important;
                    border-radius: 8px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    transition: background 0.15s, color 0.15s !important;
                }
                .zebra-modal-root .zp-close-btn:hover {
                    color: #0f172a !important;
                    background-color: #e2e8f0 !important;
                }
                .zebra-modal-root .zp-body-scroll {
                    padding: 20px !important;
                    overflow-y: auto !important;
                    max-height: calc(85vh - 130px) !important;
                    background-color: #ffffff !important;
                }
                .zebra-modal-root .zp-body {
                    padding: 20px !important;
                    display: flex !important;
                    flex-direction: row !important;
                    gap: 20px !important;
                    align-items: flex-start !important;
                    background-color: #ffffff !important;
                }
                .zebra-modal-root .zp-footer {
                    padding: 14px 20px !important;
                    background-color: #f8fafc !important;
                    border-top: 1px solid #e2e8f0 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: space-between !important;
                    flex-shrink: 0 !important;
                    gap: 10px !important;
                }
                .zebra-modal-root .zp-cancel-btn {
                    padding: 8px 16px !important;
                    border-radius: 8px !important;
                    border: 1px solid #cbd5e1 !important;
                    background-color: #ffffff !important;
                    color: #334155 !important;
                    font-weight: 600 !important;
                    font-size: 13px !important;
                    cursor: pointer !important;
                    transition: background 0.15s !important;
                }
                .zebra-modal-root .zp-cancel-btn:hover {
                    background-color: #f1f5f9 !important;
                }

                /* Print Modal Controls */
                .zebra-modal-root .zp-preview-box {
                    width: 180px !important;
                    height: 180px !important;
                    flex-shrink: 0 !important;
                    background-color: #f8fafc !important;
                    border: 2px dashed #cbd5e1 !important;
                    border-radius: 10px !important;
                    padding: 4px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                }
                .zebra-modal-root .zp-preview-img {
                    max-width: 100% !important;
                    max-height: 100% !important;
                    object-fit: contain !important;
                    border-radius: 6px !important;
                    background-color: #ffffff !important;
                }
                .zebra-modal-root .zp-preview-hint {
                    font-size: 11px !important;
                    color: #64748b !important;
                    text-align: center !important;
                    margin-top: 6px !important;
                    font-weight: 600 !important;
                }
                .zebra-modal-root .zp-content-box {
                    flex: 1 !important;
                    min-width: 0 !important;
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 14px !important;
                }
                .zebra-modal-root .zp-row-header {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: space-between !important;
                    margin-bottom: 2px !important;
                }
                .zebra-modal-root .zp-label-sm {
                    font-size: 11px !important;
                    font-weight: 700 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.5px !important;
                    color: #64748b !important;
                }
                .zebra-modal-root .zp-status-badge {
                    display: inline-flex !important;
                    align-items: center !important;
                    gap: 6px !important;
                    padding: 3px 10px !important;
                    border-radius: 9999px !important;
                    font-size: 11px !important;
                    font-weight: 600 !important;
                }
                .zebra-modal-root .zp-status-badge.online {
                    background-color: #ecfdf5 !important;
                    color: #065f46 !important;
                    border: 1px solid #a7f3d0 !important;
                }
                .zebra-modal-root .zp-status-badge.offline {
                    background-color: #fef2f2 !important;
                    color: #991b1b !important;
                    border: 1px solid #fecaca !important;
                }
                .zebra-modal-root .zp-status-badge.checking {
                    background-color: #fffbeb !important;
                    color: #92400e !important;
                    border: 1px solid #fde68a !important;
                }
                .zebra-modal-root .zp-dot {
                    width: 7px !important;
                    height: 7px !important;
                    border-radius: 50% !important;
                }
                .zebra-modal-root .zp-status-badge.online .zp-dot { background-color: #10b981 !important; }
                .zebra-modal-root .zp-status-badge.offline .zp-dot { background-color: #ef4444 !important; }
                .zebra-modal-root .zp-status-badge.checking .zp-dot { background-color: #f59e0b !important; }
                .zebra-modal-root .zp-package-code {
                    font-size: 22px !important;
                    font-weight: 900 !important;
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
                    color: #0f172a !important;
                    letter-spacing: 0.5px !important;
                }
                .zebra-modal-root .zp-field-label {
                    display: block !important;
                    font-size: 12px !important;
                    font-weight: 600 !important;
                    color: #334155 !important;
                    margin-bottom: 6px !important;
                }
                .zebra-modal-root .zp-qty-group {
                    display: flex !important;
                    align-items: center !important;
                    gap: 6px !important;
                    flex-wrap: wrap !important;
                }
                .zebra-modal-root .zp-qty-stepper {
                    display: inline-flex !important;
                    align-items: center !important;
                    border: 1px solid #cbd5e1 !important;
                    border-radius: 8px !important;
                    overflow: hidden !important;
                    background-color: #ffffff !important;
                }
                .zebra-modal-root .zp-qty-btn {
                    width: 34px !important;
                    height: 34px !important;
                    background-color: #f1f5f9 !important;
                    border: none !important;
                    font-size: 16px !important;
                    font-weight: bold !important;
                    cursor: pointer !important;
                    color: #334155 !important;
                }
                .zebra-modal-root .zp-qty-btn:hover { background-color: #e2e8f0 !important; }
                .zebra-modal-root .zp-qty-input {
                    width: 45px !important;
                    height: 34px !important;
                    border: none !important;
                    text-align: center !important;
                    font-weight: 700 !important;
                    font-size: 14px !important;
                    outline: none !important;
                    background-color: #ffffff !important;
                    color: #0f172a !important;
                }
                .zebra-modal-root .zp-chip {
                    padding: 6px 10px !important;
                    border-radius: 6px !important;
                    border: 1px solid #cbd5e1 !important;
                    background-color: #f8fafc !important;
                    font-size: 12px !important;
                    font-weight: 600 !important;
                    cursor: pointer !important;
                    color: #334155 !important;
                }
                .zebra-modal-root .zp-chip:hover {
                    background-color: #f1f5f9 !important;
                    border-color: #94a3b8 !important;
                }
                .zebra-modal-root .zp-printer-card {
                    padding: 10px 12px !important;
                    border-radius: 8px !important;
                    background-color: #f8fafc !important;
                    border: 1px solid #e2e8f0 !important;
                    font-size: 12px !important;
                }
                .zebra-modal-root .zp-printer-card-top {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: space-between !important;
                }
                .zebra-modal-root .zp-print-btn {
                    padding: 10px 20px !important;
                    border-radius: 8px !important;
                    border: none !important;
                    background-color: #059669 !important;
                    color: #ffffff !important;
                    font-weight: 700 !important;
                    font-size: 13px !important;
                    cursor: pointer !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    gap: 8px !important;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
                }
                .zebra-modal-root .zp-print-btn:hover { background-color: #047857 !important; }
                .zebra-modal-root .zp-print-btn:disabled { opacity: 0.6 !important; cursor: not-allowed !important; }

                /* Settings Modal Specific Controls */
                .zebra-modal-root .zp-diag-alert {
                    padding: 12px 14px !important;
                    border-radius: 8px !important;
                    font-size: 12px !important;
                    margin-bottom: 14px !important;
                    line-height: 1.5 !important;
                }
                .zebra-modal-root .zp-diag-alert-info { background-color: #eff6ff !important; color: #1e40af !important; border: 1px solid #bfdbfe !important; }
                .zebra-modal-root .zp-diag-alert-success { background-color: #ecfdf5 !important; color: #065f46 !important; border: 1px solid #a7f3d0 !important; }
                .zebra-modal-root .zp-diag-alert-warning { background-color: #fffbeb !important; color: #92400e !important; border: 1px solid #fde68a !important; }
                .zebra-modal-root .zp-diag-alert-danger { background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecaca !important; }
                .zebra-modal-root .zp-form-grid {
                    display: grid !important;
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 12px !important;
                }
                .zebra-modal-root .zp-input {
                    width: 100% !important;
                    border: 1px solid #cbd5e1 !important;
                    border-radius: 6px !important;
                    padding: 7px 10px !important;
                    font-size: 13px !important;
                    background-color: #ffffff !important;
                    color: #0f172a !important;
                    outline: none !important;
                }
                .zebra-modal-root .zp-input:focus {
                    border-color: #3b82f6 !important;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
                }
                .zebra-modal-root .zp-label {
                    display: block !important;
                    font-size: 12px !important;
                    font-weight: 600 !important;
                    color: #475569 !important;
                    margin-bottom: 4px !important;
                }
                .zebra-modal-root .zp-btn {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    gap: 6px !important;
                    padding: 8px 14px !important;
                    border-radius: 8px !important;
                    font-size: 13px !important;
                    font-weight: 600 !important;
                    cursor: pointer !important;
                    transition: all 0.15s ease !important;
                    border: 1px solid transparent !important;
                    text-decoration: none !important;
                }
                .zebra-modal-root .zp-btn-primary { background-color: #2563eb !important; color: #ffffff !important; }
                .zebra-modal-root .zp-btn-primary:hover { background-color: #1d4ed8 !important; }
                .zebra-modal-root .zp-btn-default { background-color: #ffffff !important; border-color: #cbd5e1 !important; color: #334155 !important; }
                .zebra-modal-root .zp-btn-default:hover { background-color: #f1f5f9 !important; border-color: #94a3b8 !important; }
                .zebra-modal-root .zp-btn-danger-outline { background-color: #ffffff !important; border-color: #fca5a5 !important; color: #b91c1c !important; }
                .zebra-modal-root .zp-btn-danger-outline:hover { background-color: #fef2f2 !important; border-color: #f87171 !important; }

                @media (max-width: 640px) {
                    .zebra-modal-root .zp-body { flex-direction: column !important; align-items: stretch !important; }
                    .zebra-modal-root .zp-form-grid { grid-template-columns: 1fr !important; }
                    .zebra-modal-root .zp-preview-box { margin: 0 auto !important; }
                }

                /* Toast Thông báo không chặn màn hình */
                #zebraPackingToastContainer {
                    position: fixed !important;
                    top: 20px !important;
                    right: 20px !important;
                    z-index: 1000000000 !important;
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 8px !important;
                    pointer-events: none !important;
                }
                .zp-toast-pill {
                    pointer-events: auto !important;
                    min-width: 220px !important;
                    max-width: 420px !important;
                    padding: 9px 15px !important;
                    border-radius: 9px !important;
                    font-size: 13px !important;
                    font-weight: 600 !important;
                    color: #ffffff !important;
                    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.28), 0 1px 3px rgba(0,0,0,0.12) !important;
                    display: flex !important;
                    align-items: center !important;
                    gap: 8px !important;
                    opacity: 0;
                    transform: translateY(-8px);
                    transition: opacity 0.2s ease, transform 0.2s ease !important;
                }
                .zp-toast-pill.show {
                    opacity: 1 !important;
                    transform: translateY(0) !important;
                }
                .zp-toast-pill.zp-toast-success { background-color: #059669 !important; }
                .zp-toast-pill.zp-toast-error { background-color: #dc2626 !important; }
                .zp-toast-pill.zp-toast-warning { background-color: #d97706 !important; }
                .zp-toast-pill.zp-toast-info { background-color: #0284c7 !important; }
            `;
            document.head.appendChild(style);
        },

        getCsrfToken: function () {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        toast: function (msg, type = 'info', duration = 2500) {
            this.ensureStylesInjected();

            let container = document.getElementById('zebraPackingToastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'zebraPackingToastContainer';
                document.body.appendChild(container);
            }

            const pill = document.createElement('div');
            pill.className = `zp-toast-pill zp-toast-${type}`;

            let iconName = 'lucide:info';
            if (type === 'success') iconName = 'lucide:check-circle';
            else if (type === 'error') iconName = 'lucide:alert-circle';
            else if (type === 'warning') iconName = 'lucide:alert-triangle';

            pill.innerHTML = `
                <iconify-icon icon="${iconName}" style="font-size:18px; flex-shrink:0;"></iconify-icon>
                <span style="flex:1; line-height: 1.4;">${msg}</span>
            `;

            container.appendChild(pill);
            requestAnimationFrame(() => {
                pill.classList.add('show');
            });

            setTimeout(() => {
                pill.classList.remove('show');
                setTimeout(() => {
                    if (pill.parentNode) pill.remove();
                }, 250);
            }, duration);
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

        // In nhanh 1 tem trực tiếp (Bấm là in luôn - không mở modal, không alert phiền phức)
        quickPrint: async function (packageId, buttonEl, evt) {
            if (evt && evt.shiftKey) {
                // Nếu giữ phím Shift thì mở modal xem trước
                return this.openModalForPackage(packageId);
            }

            if (this.isPrinting) {
                this.toast('Đang có lệnh in khác, vui lòng chờ giây lát.', 'warning');
                return;
            }

            let originalHtml = '';
            if (buttonEl) {
                originalHtml = buttonEl.innerHTML;
                buttonEl.disabled = true;
                buttonEl.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin text-base"></iconify-icon>';
            }

            try {
                // 1. Nạp dữ liệu kiện nhanh
                const res = await fetch(`/processes/packing/${packageId}/print-data`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok || !data.success || !data.printData) {
                    throw new Error(data.message || 'Không thể tải dữ liệu kiện.');
                }

                const pkgData = data.printData;
                this.currentPackageData = pkgData;

                // 2. Chuyển đổi ZPL Hex Bitmap
                const rasterResult = this.rasterize(pkgData);

                // 3. Gửi lệnh in (1 tem)
                this.isPrinting = true;
                const postRes = await fetch(`/processes/packing/${packageId}/print-zebra`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken()
                    },
                    body: JSON.stringify({
                        hex: rasterResult.hex,
                        quantity: 1,
                        renderSettings: JSON.stringify(rasterResult.settings)
                    })
                });

                const result = await postRes.json();
                if (!postRes.ok || !result.success) {
                    throw new Error(result.message || 'Lỗi gửi lệnh in tem Zebra.');
                }

                // 4. Nếu qua Cloud Queue: đợi Agent xác nhận ngầm
                if (result.queued && result.jobId) {
                    const confirmed = await this.trackJobStatusSilent(result.jobId, 1);
                    if (confirmed) {
                        if (buttonEl) {
                            buttonEl.innerHTML = '<iconify-icon icon="lucide:check" class="text-base text-emerald-600 font-bold"></iconify-icon>';
                        }
                        this.toast(`Đã in tem kiện ${pkgData.packageCode || '#' + packageId}!`, 'success');
                        this.updateStatusDot(true);
                    } else {
                        if (buttonEl) {
                            buttonEl.innerHTML = '<iconify-icon icon="lucide:check" class="text-base text-amber-500 font-bold"></iconify-icon>';
                        }
                        this.toast(`Lệnh in đã vào hàng đợi xưởng.`, 'warning');
                    }
                } else {
                    // Trực tiếp LAN
                    if (buttonEl) {
                        buttonEl.innerHTML = '<iconify-icon icon="lucide:check" class="text-base text-emerald-600 font-bold"></iconify-icon>';
                    }
                    this.toast(`Đã in tem kiện ${pkgData.packageCode || '#' + packageId}!`, 'success');
                    this.updateStatusDot(true);
                }
            } catch (err) {
                if (buttonEl) {
                    buttonEl.innerHTML = '<iconify-icon icon="lucide:alert-circle" class="text-base text-rose-600"></iconify-icon>';
                }
                this.toast('Lỗi in: ' + err.message, 'error');
                this.updateStatusDot(false);
            } finally {
                this.isPrinting = false;
                if (buttonEl) {
                    setTimeout(() => {
                        buttonEl.disabled = false;
                        buttonEl.innerHTML = originalHtml;
                    }, 1500);
                }
            }
        },

        // Theo dõi trạng thái in của Cloud Agent trong nền (không popup, không alert)
        trackJobStatusSilent: async function (jobId, qty) {
            let attempts = 0;
            const maxAttempts = 12;
            return new Promise((resolve) => {
                const timer = setInterval(async () => {
                    attempts++;
                    try {
                        const res = await fetch(`/processes/packing/printer/agent/job/${jobId}`);
                        if (res.ok) {
                            const data = await res.json();
                            if (data.status === 'sent') {
                                clearInterval(timer);
                                resolve(true);
                                return;
                            }
                            if (data.status === 'failed') {
                                clearInterval(timer);
                                resolve(false);
                                return;
                            }
                        }
                    } catch (e) {}

                    if (attempts >= maxAttempts) {
                        clearInterval(timer);
                        resolve(false);
                    }
                }, 1000);
            });
        },

        // Alias tương thích
        openForPackage: function (packageId, buttonEl, evt) {
            return this.quickPrint(packageId, buttonEl, evt);
        },

        printPackage: function (packageId, buttonEl, evt) {
            return this.quickPrint(packageId, buttonEl, evt);
        },

        // Mở Modal in tem bằng packageId (tự động fetch print-data)
        openModalForPackage: async function (packageId) {
            try {
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
                        statusText.textContent = res.via_agent ? 'Trạm xưởng Online (Agent)' : `Online (${res.ms}ms) · Sẵn sàng`;
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
                if (result.queued && result.jobId) {
                    if (printBtn) {
                        printBtn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="animate-spin text-lg"></iconify-icon> Trạm xưởng đang in tem…`;
                    }
                    await this.trackJobStatus(result.jobId, qty);
                } else {
                    this.toast(result.message || `Đã gửi thành công ${qty} tem đến máy in Zebra!`, 'success');
                    this.updateStatusDot(true);
                    this.closeModal();
                }
            } catch (err) {
                this.toast('Lỗi in Zebra: ' + err.message, 'error');
                this.updateStatusDot(false);
            } finally {
                if (printBtn) {
                    printBtn.disabled = false;
                    printBtn.innerHTML = `<iconify-icon icon="lucide:printer" style="font-size:18px;"></iconify-icon> In ngay qua máy in Zebra`;
                }
            }
        },

        trackJobStatus: async function (jobId, qty) {
            let attempts = 0;
            const maxAttempts = 15;
            return new Promise((resolve) => {
                const timer = setInterval(async () => {
                    attempts++;
                    try {
                        const res = await fetch(`/processes/packing/printer/agent/job/${jobId}`);
                        if (res.ok) {
                            const data = await res.json();
                            if (data.status === 'sent') {
                                clearInterval(timer);
                                this.toast(`Đã in thành công ${qty} tem tại máy in Zebra xưởng!`, 'success');
                                this.updateStatusDot(true);
                                this.closeModal();
                                resolve(true);
                                return;
                            }
                            if (data.status === 'failed') {
                                clearInterval(timer);
                                this.toast(`Lỗi tại máy in xưởng: ${data.message}`, 'error');
                                resolve(false);
                                return;
                            }
                        }
                    } catch (e) {}

                    if (attempts >= maxAttempts) {
                        clearInterval(timer);
                        this.toast(`Lệnh in ${qty} tem đã lưu vào hàng đợi; máy in xưởng sẽ in ngay khi kết nối.`, 'warning');
                        this.closeModal();
                        resolve(false);
                    }
                }, 1000);
            });
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
                    resultBox.innerHTML = `<strong>Kết nối thành công</strong>: ${res.message}`;
                    this.updateStatusDot(true);
                } else if (res.connected) {
                    resultBox.className = 'zp-diag-alert zp-diag-alert-warning';
                    resultBox.innerHTML = `<strong>Cảnh báo</strong>: ${res.message}`;
                    this.updateStatusDot(true);
                } else {
                    resultBox.className = 'zp-diag-alert zp-diag-alert-danger';
                    resultBox.innerHTML = `<strong>Không thể kết nối</strong>: ${res.message}`;
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
                const res = await this.simpleTest();
                this.toast(res.message || 'In thử thành công!', 'success');
                this.refreshHistoryInModal();
            } catch (err) {
                this.toast('Lỗi: ' + err.message, 'error');
            }
        },

        handleExitDiagnostics: async function () {
            try {
                const res = await this.exitDiagnostics();
                this.toast(res.message, 'success');
            } catch (err) {
                this.toast('Lỗi: ' + err.message, 'error');
            }
        },

        handleRecoverZpl: async function () {
            if (!confirm('Khôi phục ZPL: Chuyển Virtual Device = NONE, tắt Diagnostic, đặt Language = ZPL và hủy hàng đợi. Bạn có chắc chắn?')) return;
            try {
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
            this.ensureStylesInjected();
            const wrap = document.createElement('div');
            wrap.id = 'zebraPrintModal';
            wrap.className = 'zebra-modal-root';
            wrap.style.display = 'none';

            wrap.innerHTML = `
                <div class="zp-overlay" onclick="ZebraPacking.closeModal()"></div>
                <div class="zp-wrap">
                    <div class="zp-card" style="max-width: 580px;">
                        <div class="zp-header">
                            <div class="zp-header-left">
                                <span class="zp-icon-badge" style="background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
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
                            <div style="text-align: center; flex-shrink: 0;">
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
                                        <button type="button" onclick="ZebraPacking.openSettingsModal()" style="font-size:11px; color:#2563eb; background:none; border:none; cursor:pointer; font-weight:600; text-decoration:underline;">
                                            ⚙ Cài đặt máy in
                                        </button>
                                    </div>
                                    <div style="font-size:11px; color:#64748b; margin-top:4px;">Nhiệt trực tiếp (Direct Thermal, không ribbon) · Cổng RAW TCP 9100</div>
                                </div>
                            </div>
                        </div>

                        <div class="zp-footer">
                            <div>
                                <button type="button" onclick="ZebraPacking.printViaBrowser()" style="color:#64748b; font-size:12px; font-weight:600; text-decoration:underline; background:transparent; border:0; cursor:pointer;" title="In bằng hộp thoại in trình duyệt nếu máy in mạng chưa sẵn sàng">
                                    In qua trình duyệt (Dự phòng)
                                </button>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button type="button" onclick="ZebraPacking.closeModal()" class="zp-cancel-btn">Đóng</button>
                                <button type="button" id="zebraModalPrintBtn" onclick="ZebraPacking.executePrintFromModal()" class="zp-print-btn">
                                    <iconify-icon icon="lucide:printer" style="font-size:18px;"></iconify-icon>
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
            this.ensureStylesInjected();
            const wrap = document.createElement('div');
            wrap.id = 'zebraSettingsModal';
            wrap.className = 'zebra-modal-root';
            wrap.style.display = 'none';

            wrap.innerHTML = `
                <div class="zp-overlay" onclick="ZebraPacking.closeSettingsModal()"></div>
                <div class="zp-wrap">
                    <div class="zp-card" style="max-width: 640px;">
                        <div class="zp-header">
                            <div class="zp-header-left">
                                <span class="zp-icon-badge" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">
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

                        <div class="zp-body-scroll">
                            <div id="zebraSettingsCheckResult" class="zp-diag-alert zp-diag-alert-info">
                                Đang kiểm tra kết nối tới máy in…
                            </div>

                            <!-- Khối Hỗ trợ in qua Cloud Server (gervinwood.vn) -->
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                                    <div>
                                        <div style="font-size: 13px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px;">
                                            <iconify-icon icon="lucide:radio" style="font-size: 16px;"></iconify-icon>
                                            In qua mạng xưởng khi dùng Server (gervinwood.vn)
                                        </div>
                                        <div style="font-size: 11px; color: #15803d; margin-top: 2px;">
                                            Chạy file Agent tại máy tính xưởng để tự động nhận lệnh in qua mạng LAN.
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <a href="/processes/packing/printer/agent/download" class="zp-btn zp-btn-primary" style="padding: 6px 12px; font-size: 12px; text-decoration: none;" title="Bấm Q hoặc Ctrl+C để tắt nhanh khi chạy">
                                            <iconify-icon icon="lucide:download" style="font-size: 14px;"></iconify-icon>
                                            Tải Gervin-Zebra-Agent.bat
                                        </a>
                                        <a href="/processes/packing/printer/agent/download-kill" class="zp-btn zp-btn-danger" style="padding: 6px 12px; font-size: 12px; text-decoration: none;" title="Nhấp đúp để tắt sạch mọi Agent chạy ngầm">
                                            <iconify-icon icon="lucide:power" style="font-size: 14px;"></iconify-icon>
                                            Tải Tat-Agent.bat
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <form id="zebraSettingsForm" onsubmit="ZebraPacking.handleSettingsSave(event)">
                                <div class="zp-form-grid">
                                    <div>
                                        <label class="zp-label">Địa chỉ IP máy in</label>
                                        <input type="text" name="host" class="zp-input" required placeholder="192.168.1.94">
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

                                <div style="margin-top: 16px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                                    <button type="button" onclick="ZebraPacking.handleSettingsCheckConnection()" class="zp-btn zp-btn-default">
                                        <iconify-icon icon="lucide:refresh-cw"></iconify-icon>
                                        Kiểm tra lại kết nối
                                    </button>
                                    <button type="submit" class="zp-btn zp-btn-primary">
                                        <iconify-icon icon="lucide:check"></iconify-icon>
                                        Lưu cấu hình
                                    </button>
                                </div>
                            </form>

                            <!-- Khối Chẩn đoán & Khôi phục lỗi in (như ver-1) -->
                            <div style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                                <h6 style="font-size: 12px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 6px;">
                                    Công cụ chẩn đoán & khôi phục máy in
                                </h6>
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 12px;">
                                    Nếu máy in ra hàng loạt tem toàn mã HEX hoặc chữ số vô nghĩa, sử dụng các nút dưới đây:
                                </p>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <button type="button" onclick="ZebraPacking.handleSimpleTest()" class="zp-btn zp-btn-default" title="Gửi 1 tem mẫu in chữ ZPL">
                                        1. In thử chữ ZPL (1 tem)
                                    </button>
                                    <button type="button" onclick="ZebraPacking.handleExitDiagnostics()" class="zp-btn zp-btn-default" title="Gửi ~JE để máy in thoát Communication Diagnostics">
                                        2. Thoát Diagnostic (~JE)
                                    </button>
                                    <button type="button" onclick="ZebraPacking.handleRecoverZpl()" class="zp-btn zp-btn-danger-outline" title="Chọn Virtual Device NONE, chuyển ZPL và hủy lệnh chờ">
                                        3. Khôi phục ZPL (Tắt APL-E)
                                    </button>
                                </div>
                            </div>

                            <!-- Lịch sử in gần nhất -->
                            <div style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <h6 style="font-size: 12px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">
                                        Lịch sử gửi lệnh in
                                    </h6>
                                    <button type="button" onclick="ZebraPacking.refreshHistoryInModal()" style="font-size: 12px; color: #2563eb; background: none; border: none; font-weight: 600; cursor: pointer;">
                                        Làm mới
                                    </button>
                                </div>
                                <div id="zebraSettingsHistoryList" style="border: 1px solid #e2e8f0; border-radius: 8px; max-height: 160px; overflow-y: auto; background-color: #ffffff;">
                                    <div style="padding: 16px; text-align: center; font-size: 12px; color: #94a3b8;">Đang tải lịch sử…</div>
                                </div>
                            </div>
                        </div>

                        <div class="zp-footer">
                            <div style="font-size: 11px; color: #64748b;">
                                Cấu hình lưu trong session · Mặc định ZT411 300DPI Direct Thermal
                            </div>
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

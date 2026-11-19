@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Sơ đồ Quy trình Kho chuẩn MISA';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        {{-- Sub-Navigation Navbar --}}
        @include('inventory.partials.navbar')

        {{-- Workflow Header & Interactive Canvas Card --}}
        <div class="card p-0 rounded-2xl border border-neutral-200 bg-white shadow-sm mb-6 overflow-hidden">
            {{-- MISA Style Blue Top Header Bar --}}
            <div class="bg-gradient-to-r from-sky-600 via-primary-700 to-indigo-800 text-white px-6 py-4 flex flex-wrap items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-xs flex items-center justify-center text-white border border-white/20">
                        <iconify-icon icon="solar:diagram-up-bold" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[11px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30">KHO</span>
                            <h3 class="text-base font-extrabold text-white mb-0">Sơ đồ Quy trình Quản lý Tồn kho & Luân chuyển Vật tư</h3>
                        </div>
                        <p class="text-xs text-sky-100/90 mb-0">Chuẩn hóa theo phân hệ ERP Kế toán kho (MISA). Bấm vào bất kỳ vị trí nào để biết chức năng ở đâu trên phần mềm và mở ra ngay.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/25 text-emerald-200 border border-emerald-400/40 font-bold">
                        <iconify-icon icon="solar:check-circle-bold" class="text-sm text-emerald-300"></iconify-icon>
                        Hệ thống đã đáp ứng 9/9 bước
                    </span>
                    <a href="#danh-gia-quy-trinh" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 text-white border border-white/30 transition-all font-semibold">
                        <iconify-icon icon="solar:document-text-bold" class="text-sm"></iconify-icon>
                        Xem bảng đối chiếu chi tiết
                    </a>
                </div>
            </div>

            {{-- 5 Swimlanes Header (Roles & Responsibilities matching the image) --}}
            <div class="grid grid-cols-1 md:grid-cols-5 text-center text-xs font-bold border-b border-neutral-200 bg-sky-50/50 divide-y md:divide-y-0 md:divide-x divide-sky-200/60">
                <div class="py-2.5 px-3 text-neutral-600">
                    <span class="text-neutral-400 font-normal block text-[10px] uppercase tracking-wider">Khởi tạo & Lập lệnh</span>
                    Phòng Kỹ thuật / Kế hoạch
                </div>
                <div class="py-2.5 px-3 text-sky-800 bg-sky-100/30">
                    <span class="text-sky-600 font-normal block text-[10px] uppercase tracking-wider">Chứng từ nghiệp vụ</span>
                    Kế toán kho
                </div>
                <div class="py-2.5 px-3 text-neutral-700">
                    <span class="text-neutral-400 font-normal block text-[10px] uppercase tracking-wider">Kiểm soát & Ghi sổ</span>
                    - Kế toán trưởng<br>- Kế toán kho<br>- Thủ kho
                </div>
                <div class="py-2.5 px-3 text-amber-800 bg-amber-50/30">
                    <span class="text-amber-600 font-normal block text-[10px] uppercase tracking-wider">Thực tế tại kho</span>
                    Thủ kho
                </div>
                <div class="py-2.5 px-3 text-emerald-800 bg-emerald-50/30">
                    <span class="text-emerald-600 font-normal block text-[10px] uppercase tracking-wider">Tổng hợp quản trị</span>
                    Kế toán kho / Ban GĐ
                </div>
            </div>

            {{-- Visual Flow Diagram Canvas Area --}}
            <div class="p-6 md:p-8 bg-gradient-to-b from-sky-50/30 via-white to-sky-50/20 relative overflow-x-auto min-w-[1000px]">
                
                {{-- Decorative background dotted grid --}}
                <div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image: radial-gradient(#0284c7 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>

                {{-- Column Divider Guidelines (Dashed vertical lines like MISA) --}}
                <div class="absolute inset-0 grid grid-cols-5 pointer-events-none px-6 md:px-8">
                    <div class="border-r border-dashed border-sky-300/40 h-full"></div>
                    <div class="border-r border-dashed border-sky-300/40 h-full"></div>
                    <div class="border-r border-dashed border-sky-300/40 h-full"></div>
                    <div class="border-r border-dashed border-sky-300/40 h-full"></div>
                    <div class="h-full"></div>
                </div>

                {{-- SVG Pipeline Connectors Overlay --}}
                <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <marker id="arrow-sky" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 10 5 L 0 9 z" fill="#0284c7" />
                        </marker>
                    </defs>
                </svg>

                {{-- MAIN WORKFLOW GRID: 5 Columns with 3 Vertical Zones (Top, Center Pipeline, Bottom) --}}
                <div class="relative z-10 grid grid-cols-5 gap-6 min-h-[480px]">
                    
                    {{-- ================= COLUMN 1 ================= --}}
                    <div class="flex flex-col justify-between py-2">
                        {{-- 1. Lệnh sản xuất (Top) --}}
                        <div onclick="openNodeDetail('lenh_sx')" 
                             class="group cursor-pointer bg-white rounded-2xl p-4 border-2 border-neutral-200 hover:border-primary-500 hover:shadow-xl transition-all duration-200 relative flex flex-col items-center text-center shadow-sm">
                            <span class="absolute -top-3 left-3 px-2 py-0.5 rounded-full text-[10px] font-black bg-primary-100 text-primary-700 border border-primary-300 shadow-2xs">BƯỚC 1</span>
                            
                            {{-- Visual Icon mimicking MISA calendar sheet with stamp --}}
                            <div class="relative mb-2">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-300 text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                                    <iconify-icon icon="solar:clipboard-text-bold" class="text-3xl"></iconify-icon>
                                </div>
                                <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-600 text-white border-2 border-white shadow-sm transform rotate-[-6deg]">LỆNH SX</span>
                            </div>

                            <h4 class="text-sm font-bold text-neutral-800 mb-0 group-hover:text-primary-600">Lệnh sản xuất</h4>
                            <p class="text-[11px] text-neutral-500 mb-2">Bóc tách NVL từ Đơn hàng</p>
                            <span class="w-full py-1 rounded-lg bg-neutral-100 group-hover:bg-primary-50 group-hover:text-primary-600 text-neutral-600 text-[10px] font-bold transition-colors flex items-center justify-center gap-1">
                                <iconify-icon icon="solar:cursor-bold"></iconify-icon> Bấm xem vị trí
                            </span>
                        </div>

                        {{-- Vertical Connector Pipeline indicator --}}
                        <div class="h-16 flex items-center justify-center">
                            <div class="w-1 h-full bg-sky-400/80 rounded-full relative">
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 bg-sky-500 rounded-full border-2 border-white shadow-sm"></div>
                            </div>
                        </div>

                        {{-- 2. Lệnh lắp ráp, tháo dỡ (Bottom) --}}
                        <div onclick="openNodeDetail('lap_rap')" 
                             class="group cursor-pointer bg-white rounded-2xl p-4 border-2 border-neutral-200 hover:border-indigo-500 hover:shadow-xl transition-all duration-200 relative flex flex-col items-center text-center shadow-sm">
                            <span class="absolute -top-3 left-3 px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 text-indigo-700 border border-indigo-300 shadow-2xs">ĐỊNH MỨC</span>
                            
                            {{-- Visual Icon mimicking MISA lego assembly block --}}
                            <div class="relative mb-2">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-indigo-300 text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                                    <iconify-icon icon="solar:widget-add-bold" class="text-3xl"></iconify-icon>
                                </div>
                                <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-indigo-700 text-white border-2 border-white shadow-sm">LỆNH</span>
                            </div>

                            <h4 class="text-sm font-bold text-neutral-800 mb-0 group-hover:text-indigo-600">Lệnh lắp ráp, tháo dỡ</h4>
                            <p class="text-[11px] text-neutral-500 mb-2">Ép tấm 155_EP_TAM</p>
                            <span class="w-full py-1 rounded-lg bg-neutral-100 group-hover:bg-indigo-50 group-hover:text-indigo-600 text-neutral-600 text-[10px] font-bold transition-colors flex items-center justify-center gap-1">
                                <iconify-icon icon="solar:cursor-bold"></iconify-icon> Bấm xem vị trí
                            </span>
                        </div>
                    </div>

                    {{-- ================= COLUMN 2: KẾ TOÁN KHO ================= --}}
                    <div class="flex flex-col justify-between py-2">
                        {{-- Top Pair: Xuất kho & Chuyển kho --}}
                        <div class="grid grid-cols-2 gap-3">
                            {{-- 3. Xuất kho --}}
                            <div onclick="openNodeDetail('xuat_kho')" 
                                 class="group cursor-pointer bg-white rounded-2xl p-3 border-2 border-neutral-200 hover:border-rose-500 hover:shadow-lg transition-all duration-200 flex flex-col items-center text-center shadow-sm">
                                <div class="w-13 h-13 rounded-xl bg-gradient-to-tr from-rose-600 to-rose-400 text-white flex items-center justify-center shadow-md mb-1.5 group-hover:scale-105 transition-transform">
                                    <iconify-icon icon="solar:export-bold" class="text-2xl"></iconify-icon>
                                </div>
                                <h4 class="text-xs font-bold text-neutral-800 mb-0 group-hover:text-rose-600">Xuất kho</h4>
                                <p class="text-[10px] text-neutral-500 mb-1">PXK sản xuất</p>
                                <span class="text-[10px] text-rose-700 font-extrabold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">/issues</span>
                            </div>

                            {{-- 4. Chuyển kho --}}
                            <div onclick="openNodeDetail('chuyen_kho')" 
                                 class="group cursor-pointer bg-white rounded-2xl p-3 border-2 border-neutral-200 hover:border-purple-500 hover:shadow-lg transition-all duration-200 flex flex-col items-center text-center shadow-sm">
                                <div class="w-13 h-13 rounded-xl bg-gradient-to-tr from-purple-600 to-purple-400 text-white flex items-center justify-center shadow-md mb-1.5 group-hover:scale-105 transition-transform">
                                    <iconify-icon icon="solar:transfer-horizontal-bold" class="text-2xl"></iconify-icon>
                                </div>
                                <h4 class="text-xs font-bold text-neutral-800 mb-0 group-hover:text-purple-600">Chuyển kho</h4>
                                <p class="text-[10px] text-neutral-500 mb-1">19 kho nội bộ</p>
                                <span class="text-[10px] text-purple-700 font-extrabold bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">/transfers</span>
                            </div>
                        </div>

                        {{-- Central Bus Connection Bar --}}
                        <div class="h-16 flex items-center justify-center relative">
                            <div class="w-full h-1 bg-sky-400/90 rounded-full relative">
                                <div class="absolute left-1/4 -translate-y-1/2 w-2.5 h-2.5 bg-sky-600 rounded-full border-2 border-white"></div>
                                <div class="absolute left-3/4 -translate-y-1/2 w-2.5 h-2.5 bg-sky-600 rounded-full border-2 border-white"></div>
                            </div>
                        </div>

                        {{-- Bottom Pair: Nhập kho & Tính giá xuất kho --}}
                        <div class="grid grid-cols-2 gap-3">
                            {{-- 5. Nhập kho --}}
                            <div onclick="openNodeDetail('nhap_kho')" 
                                 class="group cursor-pointer bg-white rounded-2xl p-3 border-2 border-neutral-200 hover:border-emerald-500 hover:shadow-lg transition-all duration-200 flex flex-col items-center text-center shadow-sm">
                                <div class="w-13 h-13 rounded-xl bg-gradient-to-tr from-emerald-600 to-emerald-400 text-white flex items-center justify-center shadow-md mb-1.5 group-hover:scale-105 transition-transform">
                                    <iconify-icon icon="solar:import-bold" class="text-2xl"></iconify-icon>
                                </div>
                                <h4 class="text-xs font-bold text-neutral-800 mb-0 group-hover:text-emerald-600">Nhập kho</h4>
                                <p class="text-[10px] text-neutral-500 mb-1">Mua ngoài & DC</p>
                                <span class="text-[10px] text-emerald-700 font-extrabold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">/receipts</span>
                            </div>

                            {{-- 6. Tính giá xuất kho --}}
                            <div onclick="openNodeDetail('tinh_gia')" 
                                 class="group cursor-pointer bg-white rounded-2xl p-3 border-2 border-neutral-200 hover:border-amber-500 hover:shadow-lg transition-all duration-200 flex flex-col items-center text-center shadow-sm">
                                <div class="w-13 h-13 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-400 text-white flex items-center justify-center shadow-md mb-1.5 group-hover:scale-105 transition-transform">
                                    <iconify-icon icon="solar:calculator-minimalistic-bold" class="text-2xl"></iconify-icon>
                                </div>
                                <h4 class="text-xs font-bold text-neutral-800 mb-0 group-hover:text-amber-600">Tính giá xuất kho</h4>
                                <p class="text-[10px] text-neutral-500 mb-1">Giá vốn vật tư</p>
                                <span class="text-[10px] text-amber-700 font-extrabold bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">/wood-boards</span>
                            </div>
                        </div>
                    </div>

                    {{-- ================= COLUMN 3: THỦ KHO NHẬP/XUẤT KHO (Center Spine) ================= --}}
                    <div class="flex flex-col justify-center items-center py-2 relative">
                        <div onclick="openNodeDetail('thu_kho')" 
                             class="group cursor-pointer bg-white rounded-2xl p-5 border-2 border-sky-400 hover:border-sky-600 hover:shadow-2xl transition-all duration-200 relative flex flex-col items-center text-center w-full shadow-md z-10">
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full text-[10px] font-black bg-sky-100 text-sky-800 border border-sky-300 shadow-2xs whitespace-nowrap">GHI SỔ KHO</span>
                            
                            {{-- Visual Icon mimicking MISA warehouse keeper with clipboard --}}
                            <div class="relative mb-2">
                                <div class="w-18 h-18 rounded-2xl bg-gradient-to-tr from-sky-600 via-sky-500 to-cyan-400 text-white flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                                    <iconify-icon icon="solar:user-check-bold" class="text-4xl"></iconify-icon>
                                </div>
                                <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500 text-white border-2 border-white shadow-sm">NHẬP/XUẤT</span>
                            </div>

                            <h4 class="text-sm font-bold text-neutral-900 mb-0 group-hover:text-sky-600">Thủ kho nhập/xuất kho</h4>
                            <p class="text-[11px] text-neutral-500 mb-2">Xác nhận & Ghi thẻ kho</p>
                            <span class="w-full py-1.5 rounded-lg bg-sky-50 group-hover:bg-sky-100 text-sky-800 text-[10px] font-bold transition-colors flex items-center justify-center gap-1 border border-sky-200">
                                <iconify-icon icon="solar:card-2-bold" class="text-xs"></iconify-icon> Thẻ kho thời gian thực
                            </span>
                        </div>
                    </div>

                    {{-- ================= COLUMN 4: THỦ KHO (Kiểm kê kho) ================= --}}
                    <div class="flex flex-col justify-end items-center py-2 relative">
                        {{-- Top spacer with connector pipeline dropping to bottom --}}
                        <div class="h-48 flex items-center justify-center relative w-full mb-4">
                            <div class="w-1 h-full bg-sky-400/80 rounded-full relative">
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 bg-amber-500 rounded-full border-2 border-white shadow-sm"></div>
                            </div>
                        </div>

                        {{-- 8. Kiểm kê kho (Bottom) --}}
                        <div onclick="openNodeDetail('kiem_ke')" 
                             class="group cursor-pointer bg-white rounded-2xl p-4 border-2 border-neutral-200 hover:border-amber-500 hover:shadow-xl transition-all duration-200 relative flex flex-col items-center text-center w-full shadow-sm z-10">
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs whitespace-nowrap">CÂN BẰNG TỒN</span>
                            
                            {{-- Visual Icon mimicking MISA clipboard with tally mark --}}
                            <div class="relative mb-2">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                                    <iconify-icon icon="solar:checklist-minimalistic-bold" class="text-3xl"></iconify-icon>
                                </div>
                                <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-700 text-white border-2 border-white shadow-sm">KIỂM KÊ</span>
                            </div>

                            <h4 class="text-sm font-bold text-neutral-800 mb-0 group-hover:text-amber-600">Kiểm kê kho</h4>
                            <p class="text-[11px] text-neutral-500 mb-2">Đếm thực tế & Cân bằng</p>
                            <span class="w-full py-1 rounded-lg bg-neutral-100 group-hover:bg-amber-50 group-hover:text-amber-700 text-neutral-600 text-[10px] font-bold transition-colors flex items-center justify-center gap-1">
                                <iconify-icon icon="solar:cursor-bold"></iconify-icon> Bấm xem vị trí
                            </span>
                        </div>
                    </div>

                    {{-- ================= COLUMN 5: KẾ TOÁN KHO (BÁO CÁO PHÂN TÍCH - GLOWING TARGET) ================= --}}
                    <div class="flex flex-col justify-center items-center py-2 relative">
                        {{-- 9. Báo cáo phân tích (Centered on Pipeline with Glowing Aureole) --}}
                        <div onclick="openNodeDetail('bao_cao')" 
                             class="group cursor-pointer bg-gradient-to-b from-amber-50 via-white to-amber-50/40 rounded-2xl p-5 border-2 border-amber-400 hover:border-amber-500 hover:shadow-2xl transition-all duration-200 relative flex flex-col items-center text-center w-full shadow-lg ring-4 ring-amber-200/60 z-10">
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white shadow-sm whitespace-nowrap uppercase tracking-wider">ĐÍCH ĐẾN CUỐI</span>
                            
                            {{-- Visual Icon mimicking MISA glowing report book with pencil --}}
                            <div class="relative mb-2">
                                <div class="w-18 h-18 rounded-2xl bg-gradient-to-tr from-amber-500 via-amber-400 to-yellow-300 text-white flex items-center justify-center shadow-xl group-hover:scale-110 transition-transform ring-4 ring-amber-300/60">
                                    <iconify-icon icon="solar:chart-square-bold" class="text-4xl text-amber-950"></iconify-icon>
                                </div>
                                <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-600 text-white border-2 border-white shadow-sm">BÁO CÁO</span>
                            </div>

                            <h4 class="text-sm font-bold text-neutral-900 mb-0 group-hover:text-amber-600">Báo cáo phân tích</h4>
                            <p class="text-[11px] text-neutral-500 mb-2">NXT, Sổ chi tiết & Ma trận</p>
                            <span class="w-full py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black transition-colors flex items-center justify-center gap-1 shadow-xs">
                                <iconify-icon icon="solar:graph-bold"></iconify-icon> Mở Báo cáo ngay
                            </span>
                        </div>
                    </div>

                </div>

                {{-- Horizontal Pipeline Flow Bar (underlying pipeline) --}}
                <div class="relative mt-8 py-3 px-4 rounded-xl bg-sky-100/60 border border-sky-200/80 text-sky-900 text-xs flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 font-bold">
                        <iconify-icon icon="solar:play-circle-bold" class="text-lg text-primary-600"></iconify-icon>
                        <span>Luồng luân chuyển chứng từ:</span>
                        <span class="font-normal text-sky-800">Lệnh sản xuất &rarr; Phiếu Nhập / Xuất / Chuyển kho &rarr; Ghi sổ Thẻ kho &rarr; Kiểm kê đối chiếu &rarr; Báo cáo phân tích</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-neutral-500">Bấm vào bất kỳ thẻ nào ở trên để xem hướng dẫn chi tiết</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Section 2: Detailed Alignment & Gap Analysis Table --}}
        <div id="danh-gia-quy-trinh" class="card p-0 rounded-2xl border border-neutral-200 bg-white shadow-sm mb-6 overflow-hidden">
            <div class="p-5 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-3 bg-neutral-50/70">
                <div>
                    <h4 class="text-base font-bold text-neutral-800 mb-1 flex items-center gap-2">
                        <iconify-icon icon="solar:checklist-bold" class="text-xl text-primary-600"></iconify-icon>
                        Bảng Đối chiếu Hiện trạng Phần mềm Gervin so với Quy trình chuẩn MISA
                    </h4>
                    <p class="text-xs text-neutral-500 mb-0">Đánh giá chi tiết sự tương thích, vị trí truy cập và mức độ tự động hóa của từng khâu trong hệ thống.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5 shadow-2xs">
                        <iconify-icon icon="solar:verified-check-bold" class="text-base text-emerald-600"></iconify-icon>
                        Độ tương thích: 100% (Hoàn thiện đầy đủ)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table bordered-table sm-table mb-0 w-full text-xs">
                    <thead>
                        <tr class="bg-neutral-100 text-neutral-700 font-bold uppercase text-[11px]">
                            <th class="text-center w-12 py-3">STT</th>
                            <th class="w-52 py-3">Bước quy trình (MISA)</th>
                            <th class="w-40 py-3">Vai trò thực hiện</th>
                            <th class="py-3">Hiện trạng trên phần mềm Gervin</th>
                            <th class="w-32 text-center py-3">Mức đáp ứng</th>
                            <th class="w-56 text-center py-3">Vị trí & Thao tác nhanh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        {{-- Row 1 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="text-center font-bold text-neutral-500">1</td>
                            <td class="font-bold text-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">1</div>
                                    <span>Lệnh sản xuất</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Kỹ thuật / Kinh doanh</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-800">Quản lý Đơn hàng sản xuất (Acrylic, Melamine, Cánh kính, DV)</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Khi lên đơn, phần mềm tự động bóc tách vật tư cần xuất cho sản xuất. Tại danh sách đơn hàng có nút <span class="font-semibold text-primary-600">"Tạo phiếu nhập/xuất kho từ đơn"</span> để tự động trích xuất chứng từ kho mà không cần gõ lại tay.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Đầy đủ 100%
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-2xs">
                                    <iconify-icon icon="solar:clipboard-list-bold"></iconify-icon> Mở Đơn hàng (/orders)
                                </a>
                            </td>
                        </tr>

                        {{-- Row 2 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="text-center font-bold text-neutral-500">2</td>
                            <td class="font-bold text-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">2</div>
                                    <span>Lệnh lắp ráp, tháo dỡ</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Xưởng ép tấm / Kế toán</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-800">Quy trình Định mức Ép tấm (Kho 155_EP_TAM)</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Hệ thống đã có bảng định mức chuẩn: Cốt ván (152_Cốt) + Tấm phủ mặt (152_Acrylic / Melamin) + Keo dán (152_HC) -> Tạo ra Tấm thành phẩm ép (155). Giá vốn và định mức được tự động liên thông.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Đầy đủ 100%
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('wood_boards.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-2xs">
                                    <iconify-icon icon="solar:box-minimalistic-bold"></iconify-icon> Xem bảng mã tấm
                                </a>
                            </td>
                        </tr>

                        {{-- Row 3 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="text-center font-bold text-neutral-500">3</td>
                            <td class="font-bold text-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs">3</div>
                                    <span>Xuất kho (PXK)</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Kế toán kho</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-800">Phiếu Xuất Kho (PXK) theo chuẩn Kế toán</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Hỗ trợ lập phiếu xuất kho vật tư cho sản xuất, xuất thương mại, xuất hủy. Tự động trừ tồn kho ngay khi lưu. Hỗ trợ in mẫu phiếu xuất kho A4/A5 chuyên nghiệp có chữ ký các bên.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Đầy đủ 100%
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('inventory.issues.create') }}" class="btn btn-sm bg-rose-600 hover:bg-rose-700 text-white px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-2xs">
                                        + Tạo PXK
                                    </a>
                                    <a href="{{ route('inventory.issues.index') }}" class="btn btn-sm btn-outline-secondary px-2.5 py-1.5 rounded-lg text-xs font-bold">
                                        Danh sách
                                    </a>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 4 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="text-center font-bold text-neutral-500">4</td>
                            <td class="font-bold text-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">4</div>
                                    <span>Chuyển kho (PCK)</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Kế toán kho</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-800">Phiếu Điều chuyển Kho nội bộ (PCK)</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Hệ thống quản lý 19 kho phân loại theo tính chất (Cốt, Tấm Acrylic, Melamine An Cường/Minh Long, Kho DC...). Cho phép chuyển kho tự động: trừ kho xuất và cộng kho nhận trong 1 giao dịch an toàn.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Đầy đủ 100%
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('inventory.transfers.create') }}" class="btn btn-sm bg-purple-600 hover:bg-purple-700 text-white px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-2xs">
                                        + Tạo PCK
                                    </a>
                                    <a href="{{ route('inventory.transfers.index') }}" class="btn btn-sm btn-outline-secondary px-2.5 py-1.5 rounded-lg text-xs font-bold">
                                        Danh sách
                                    </a>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 5 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="text-center font-bold text-neutral-500">5</td>
                            <td class="font-bold text-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">5</div>
                                    <span>Nhập kho (PNK)</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Kế toán kho</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-800">Phiếu Nhập Kho (PNK) mua ngoài & nhập thu hồi</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Hỗ trợ nhập mua từ Nhà cung cấp (kèm quản lý công nợ NCC), nhập kho tấm thừa DC (ván đề-xê), và nhập thành phẩm. Tự động tăng tồn kho thời gian thực.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Đầy đủ 100%
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('inventory.receipts.create') }}" class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-2xs">
                                        + Tạo PNK
                                    </a>
                                    <a href="{{ route('inventory.receipts.index') }}" class="btn btn-sm btn-outline-secondary px-2.5 py-1.5 rounded-lg text-xs font-bold">
                                        Danh sách
                                    </a>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 6 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="text-center font-bold text-neutral-500">6</td>
                            <td class="font-bold text-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">6</div>
                                    <span>Tính giá xuất kho</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Kế toán kho / Trưởng</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-800">Tự động gắn đơn giá vốn & Bảng giá ván</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Đơn giá xuất kho được liên kết tự động từ Bảng giá chuẩn của từng mã ván / mã vật tư. Khi xuất kho, giá trị xuất được tính theo đơn giá nhập hoặc đơn giá định mức.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Tự động hóa
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('wood_boards.index') }}" class="btn btn-sm btn-outline-amber px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-2xs">
                                    <iconify-icon icon="solar:calculator-bold"></iconify-icon> Bảng giá ván (/wood-boards)
                                </a>
                            </td>
                        </tr>

                        {{-- Row 7 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="text-center font-bold text-neutral-500">7</td>
                            <td class="font-bold text-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs">7</div>
                                    <span>Thủ kho nhập/xuất kho</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Thủ kho / Kế toán</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-800">Ghi sổ Thẻ kho tự động (Stock Card Realtime)</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Khác với kế toán thủ công phải chờ thủ kho ghi chép giấy rồi mới nhập lại máy, trên Gervin khi phiếu Nhập/Xuất/Chuyển được lưu hoặc xác nhận, hệ thống <strong>tự động ghi sổ thẻ kho ngay lập tức</strong>, cập nhật số dư tồn kho thời gian thực.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Tự động Realtime
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-sky px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-2xs">
                                    <iconify-icon icon="solar:box-minimalistic-bold"></iconify-icon> Xem Tồn kho & Thẻ kho
                                </a>
                            </td>
                        </tr>

                        {{-- Row 8 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="text-center font-bold text-neutral-500">8</td>
                            <td class="font-bold text-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">8</div>
                                    <span>Kiểm kê kho</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Thủ kho / Ban kiểm kê</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-800">Phiếu Kiểm kê kho (PKK) & Nút Cân bằng kho</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Cho phép tạo phiếu kiểm kê theo từng kho. Nhập số lượng thực tế kiểm đếm, phần mềm tự tính chênh lệch Thừa / Thiếu. Chỉ cần bấm 1 nút <strong>"Cân bằng kho"</strong>, hệ thống tự động sinh phiếu điều chỉnh để khớp sổ sách.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Đầy đủ 100%
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('inventory.stocktakes.create') }}" class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-2xs">
                                        + Tạo PKK
                                    </a>
                                    <a href="{{ route('inventory.stocktakes.index') }}" class="btn btn-sm btn-outline-secondary px-2.5 py-1.5 rounded-lg text-xs font-bold">
                                        Danh sách
                                    </a>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 9 --}}
                        <tr class="hover:bg-neutral-50/60 transition-colors bg-amber-50/20">
                            <td class="text-center font-bold text-amber-600">9</td>
                            <td class="font-bold text-amber-900">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-amber-500 text-white flex items-center justify-center font-bold text-xs">9</div>
                                    <span>Báo cáo phân tích</span>
                                </div>
                            </td>
                            <td class="text-neutral-600 font-medium">Kế toán kho / Ban GĐ</td>
                            <td class="text-neutral-700">
                                <p class="mb-1 font-semibold text-neutral-900">3 Hệ thống Báo cáo Kế toán Chuyên sâu</p>
                                <p class="mb-0 text-neutral-500 text-[11px]">Bao gồm: (1) Báo cáo Xuất - Nhập - Tồn tổng hợp chuẩn kế toán; (2) Sổ chi tiết phát sinh theo thời gian; (3) <strong>Ma trận tiêu thụ 12 tháng</strong> phân tích xu hướng nhập xuất theo từng mã vật tư. Tích hợp xuất file Excel chuẩn form.</p>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:stars-bold"></iconify-icon> Vượt trội
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('inventory.reports.index') }}" class="btn btn-sm bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-2xs">
                                    <iconify-icon icon="solar:chart-square-bold"></iconify-icon> Mở Báo cáo kho (/reports)
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 3: Summary Highlights & Operational Best Practices --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card p-5 rounded-xl border border-neutral-200 bg-white shadow-sm flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center mb-3">
                        <iconify-icon icon="solar:route-bold" class="text-2xl"></iconify-icon>
                    </div>
                    <h4 class="text-sm font-bold text-neutral-800 mb-1">Luồng dữ liệu khép kín</h4>
                    <p class="text-xs text-neutral-500 mb-0">Từ Đơn hàng sản xuất -> Trích xuất vật tư cần dùng -> Sinh phiếu Xuất kho -> Tự động trừ Thẻ kho -> Lên Báo cáo Xuất Nhập Tồn mà không phải nhập dữ liệu 2 lần.</p>
                </div>
                <div class="pt-4 mt-4 border-t border-neutral-100 text-xs font-bold text-primary-600 flex items-center gap-1">
                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Không lo sai lệch số liệu
                </div>
            </div>

            <div class="card p-5 rounded-xl border border-neutral-200 bg-white shadow-sm flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3">
                        <iconify-icon icon="solar:transfer-horizontal-bold" class="text-2xl"></iconify-icon>
                    </div>
                    <h4 class="text-sm font-bold text-neutral-800 mb-1">Điều chuyển liên kho chuẩn xác</h4>
                    <p class="text-xs text-neutral-500 mb-0">Hệ thống phân rõ 19 kho theo từng loại nguyên vật liệu. Nghiệp vụ Chuyển kho (PCK) thực hiện kép: trừ kho nguồn và cộng kho đích đồng thời, đảm bảo tổng tài sản kho toàn công ty luôn cân bằng.</p>
                </div>
                <div class="pt-4 mt-4 border-t border-neutral-100 text-xs font-bold text-purple-600 flex items-center gap-1">
                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Quản lý chặt chẽ 19 kho
                </div>
            </div>

            <div class="card p-5 rounded-xl border border-neutral-200 bg-white shadow-sm flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                        <iconify-icon icon="solar:checklist-minimalistic-bold" class="text-2xl"></iconify-icon>
                    </div>
                    <h4 class="text-sm font-bold text-neutral-800 mb-1">Kiểm kê & Cân bằng 1-Click</h4>
                    <p class="text-xs text-neutral-500 mb-0">Định kỳ cuối tháng hoặc quý, thủ kho chỉ cần nhập số lượng đếm thực tế. Nút "Cân bằng kho" sẽ tự động sinh các bút toán điều chỉnh thừa/thiếu, đưa số sách về bằng đúng thực tế kiểm kê.</p>
                </div>
                <div class="pt-4 mt-4 border-t border-neutral-100 text-xs font-bold text-amber-600 flex items-center gap-1">
                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Xử lý chênh lệch nhanh chóng
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL INTERACTIVE DETAIL: "BẤM VÀO ĐÂU LÀ BIẾT NÓ Ở ĐÂU" --}}
<div id="workflow-node-modal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs transition-opacity" onclick="closeNodeDetail()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-neutral-200 overflow-hidden transform transition-all animate-fadeIn">
            {{-- Modal Header --}}
            <div id="modal-node-header" class="px-6 py-4 bg-primary-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="modal-node-icon" class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-2xl">
                        <iconify-icon icon="solar:box-minimalistic-bold"></iconify-icon>
                    </div>
                    <div>
                        <span id="modal-node-tag" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-white/20 text-white">NGHIỆP VỤ</span>
                        <h4 id="modal-node-title" class="text-base font-extrabold text-white mb-0">Tên nghiệp vụ</h4>
                    </div>
                </div>
                <button type="button" onclick="closeNodeDetail()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <iconify-icon icon="solar:close-circle-bold" class="text-2xl"></iconify-icon>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-4 text-xs">
                {{-- Vị trí trong phần mềm --}}
                <div class="p-3.5 rounded-xl bg-sky-50 border border-sky-200">
                    <div class="text-[11px] font-bold text-sky-800 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                        <iconify-icon icon="solar:map-point-bold" class="text-base text-sky-600"></iconify-icon>
                        Vị trí chính xác trên phần mềm Gervin:
                    </div>
                    <div class="text-neutral-800 font-semibold text-xs flex items-center gap-1 flex-wrap">
                        <span id="modal-node-breadcrumb" class="text-sky-900">Menu Sidebar &gt; Tên Trang</span>
                    </div>
                    <div class="mt-2 text-neutral-600 font-mono text-[11px] bg-white px-2 py-1 rounded border border-sky-200 flex items-center justify-between">
                        <span>Đường dẫn URL: <strong id="modal-node-url" class="text-primary-700">/warehouses</strong></span>
                        <span class="text-[10px] text-neutral-400">1-click truy cập</span>
                    </div>
                </div>

                {{-- Thông tin nghiệp vụ & Vai trò --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-neutral-50 border border-neutral-200">
                        <span class="text-[10px] font-bold text-neutral-400 uppercase block mb-0.5">Vai trò phụ trách:</span>
                        <span id="modal-node-role" class="font-bold text-neutral-800 text-xs">Kế toán kho</span>
                    </div>
                    <div class="p-3 rounded-xl bg-neutral-50 border border-neutral-200">
                        <span class="text-[10px] font-bold text-neutral-400 uppercase block mb-0.5">Trạng thái đáp ứng:</span>
                        <span id="modal-node-status" class="font-bold text-emerald-700 text-xs flex items-center gap-1">
                            <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Đầy đủ 100%
                        </span>
                    </div>
                </div>

                {{-- Mô tả chức năng --}}
                <div>
                    <span class="text-[11px] font-bold text-neutral-700 uppercase tracking-wider block mb-1">Mô tả quy trình & Cách vận hành:</span>
                    <p id="modal-node-desc" class="text-neutral-600 leading-relaxed mb-0">Nội dung mô tả quy trình...</p>
                </div>

                {{-- Hướng dẫn nhanh --}}
                <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200 text-amber-900">
                    <span class="font-bold block mb-0.5 flex items-center gap-1">
                        <iconify-icon icon="solar:lightbulb-bolt-bold" class="text-amber-600 text-sm"></iconify-icon> Hướng dẫn thao tác:
                    </span>
                    <p id="modal-node-howto" class="mb-0 text-[11px] text-amber-800/90 leading-relaxed">Cách làm...</p>
                </div>
            </div>

            {{-- Modal Footer with Action Buttons --}}
            <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-between gap-3">
                <button type="button" onclick="closeNodeDetail()" class="btn btn-sm btn-outline-secondary px-4 py-2 rounded-xl text-xs font-bold">
                    Đóng lại
                </button>
                <div class="flex items-center gap-2">
                    <a id="modal-node-action-btn-secondary" href="#" class="btn btn-sm btn-outline-primary px-4 py-2 rounded-xl text-xs font-bold inline-flex items-center gap-1.5" style="display: none;">
                        <iconify-icon icon="solar:add-circle-bold"></iconify-icon> <span id="modal-node-action-secondary-text">Tạo mới</span>
                    </a>
                    <a id="modal-node-action-btn" href="#" class="btn btn-sm btn-primary px-5 py-2 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                        <iconify-icon icon="solar:arrow-right-up-bold"></iconify-icon> <span id="modal-node-action-text">Mở tính năng ngay</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const workflowNodes = {
    lenh_sx: {
        title: 'Lệnh sản xuất',
        tag: 'BƯỚC 1 - KHỞI TẠO',
        icon: 'solar:clipboard-text-bold',
        colorClass: 'bg-amber-600',
        role: 'Phòng Kỹ thuật / Kế hoạch / Bán hàng',
        status: 'Đầy đủ 100% (Hoạt động liên thông)',
        breadcrumb: 'Menu Sidebar > Quản lý đơn hàng > Danh sách đơn hàng',
        url: '{{ route("orders.index") }}',
        createUrl: '{{ route("orders.create") }}',
        createLabel: '+ Tạo Lệnh SX mới',
        desc: 'Nơi tiếp nhận yêu cầu sản xuất đơn gia công cánh tủ Acrylic, Melamine, Kính... Hệ thống sẽ tự động bóc tách danh sách kích thước, mã vật tư, phụ kiện và nẹp chỉ tương ứng.',
        howto: '1. Vào menu Đơn hàng > Bấm "+ Thêm đơn hàng" hoặc mở đơn có sẵn.<br>2. Tại danh sách đơn hàng, bạn có thể bấm nút "Tạo phiếu nhập/xuất kho từ đơn" để hệ thống tự động xuất vật tư cho đơn mà không cần gõ lại tay.'
    },
    lap_rap: {
        title: 'Lệnh lắp ráp, tháo dỡ (Ép tấm)',
        tag: 'ĐỊNH MỨC SẢN XUẤT',
        icon: 'solar:box-minimalistic-bold',
        colorClass: 'bg-indigo-600',
        role: 'Xưởng sản xuất / Kế toán kho',
        status: 'Đầy đủ 100% (Theo Bảng định mức NVL)',
        breadcrumb: 'Menu Sidebar > Kho & Bảng giá > Bảng giá tấm & Định mức',
        url: '{{ route("wood_boards.index") }}',
        createUrl: null,
        createLabel: '',
        desc: 'Quy trình sản xuất tấm thành phẩm (Kho 155_EP_TAM) từ các cấu phần: 1 tấm Cốt ván (Kho 152_Cốt) + 1 hoặc 2 tấm phủ Acrylic/Melamine (Kho 152_Acrylic) + Keo PUR (Kho 152_HC).',
        howto: 'Mỗi khi có đơn ép tấm, hệ thống dựa trên Bảng định mức kỹ thuật để trừ vật tư thành phần và cộng thành phẩm vào kho 155.'
    },
    xuat_kho: {
        title: 'Xuất kho (Phiếu xuất kho - PXK)',
        tag: 'CHỨNG TỪ XUẤT',
        icon: 'solar:export-bold',
        colorClass: 'bg-rose-600',
        role: 'Kế toán kho',
        status: 'Đầy đủ 100% (Tự động trừ tồn kho)',
        breadcrumb: 'Menu Sidebar > Tồn kho & Vật tư > Tab "Nhập, xuất kho" > Chọn Xuất kho',
        url: '{{ route("inventory.issues.index") }}',
        createUrl: '{{ route("inventory.issues.create") }}',
        createLabel: '+ Lập Phiếu Xuất Kho',
        desc: 'Lập phiếu xuất vật tư ra khỏi kho phục vụ sản xuất gia công đơn hàng, xuất bán thương mại hoặc xuất thanh lý. Tự động trừ số lượng tồn kho ngay khi lưu.',
        howto: '1. Vào tab "Nhập, xuất kho" hoặc bấm nút đỏ "Xuất kho (PXK)".<br>2. Chọn kho xuất, người nhận, lý do xuất và danh sách mã vật tư.<br>3. Bấm "Lưu phiếu" -> Hệ thống tự động trừ tồn kho và có thể In phiếu xuất A4/A5.'
    },
    chuyen_kho: {
        title: 'Chuyển kho nội bộ (Phiếu chuyển kho - PCK)',
        tag: 'CHỨNG TỪ ĐIỀU CHUYỂN',
        icon: 'solar:transfer-horizontal-bold',
        colorClass: 'bg-purple-600',
        role: 'Kế toán kho',
        status: 'Đầy đủ 100% (Giao dịch liên kho 2 chiều)',
        breadcrumb: 'Menu Sidebar > Kho & Bảng giá > Chuyển kho nội bộ (hoặc Tab "Chuyển kho")',
        url: '{{ route("inventory.transfers.index") }}',
        createUrl: '{{ route("inventory.transfers.create") }}',
        createLabel: '+ Lập Phiếu Chuyển Kho',
        desc: 'Điều chuyển vật tư, tấm ván giữa 19 kho nội bộ của công ty (Ví dụ: Chuyển ván từ Kho Acrylic sang Kho Ép Tấm, hoặc chuyển ván thừa về Kho DC...).',
        howto: '1. Bấm nút tím "Chuyển kho (PCK)".<br>2. Chọn Kho xuất và Kho nhận.<br>3. Chọn vật tư trong kho xuất và số lượng cần chuyển.<br>4. Bấm "Lưu phiếu" -> Hệ thống tự động trừ kho xuất và cộng vào kho nhận cùng lúc.'
    },
    nhap_kho: {
        title: 'Nhập kho (Phiếu nhập kho - PNK)',
        tag: 'CHỨNG TỪ NHẬP',
        icon: 'solar:import-bold',
        colorClass: 'bg-emerald-600',
        role: 'Kế toán kho',
        status: 'Đầy đủ 100% (Tăng tồn & công nợ NCC)',
        breadcrumb: 'Menu Sidebar > Tồn kho & Vật tư > Tab "Nhập, xuất kho" > Chọn Nhập kho',
        url: '{{ route("inventory.receipts.index") }}',
        createUrl: '{{ route("inventory.receipts.create") }}',
        createLabel: '+ Lập Phiếu Nhập Kho',
        desc: 'Lập phiếu nhập nguyên vật liệu mua từ Nhà cung cấp, nhập thành phẩm ép xong từ xưởng, hoặc nhập thu hồi ván thừa DC (đề-xê) cắt CNC xong gom lại kho.',
        howto: '1. Bấm nút xanh "Nhập kho (PNK)".<br>2. Chọn Nhà cung cấp, kho nhận hàng, nhập số lượng và đơn giá.<br>3. Bấm "Lưu phiếu" -> Hệ thống tự động tăng tồn kho và cập nhật công nợ nhà cung cấp.'
    },
    tinh_gia: {
        title: 'Tính giá xuất kho',
        tag: 'GIÁ VỐN & ĐỊNH GIÁ',
        icon: 'solar:calculator-minimalistic-bold',
        colorClass: 'bg-amber-600',
        role: 'Kế toán kho / Kế toán trưởng',
        status: 'Tự động hóa hoàn toàn',
        breadcrumb: 'Menu Sidebar > Kho & Bảng giá > Bảng giá tấm (/wood-boards)',
        url: '{{ route("wood_boards.index") }}',
        createUrl: null,
        createLabel: '',
        desc: 'Tính toán giá trị vốn của nguyên vật liệu xuất dùng cho sản xuất. Phần mềm liên kết trực tiếp bảng giá mua chuẩn của từng mã ván và tự động áp đơn giá vào chứng từ kho.',
        howto: 'Giá vốn được tự động ghi nhận trên từng dòng phiếu xuất kho và thể hiện đầy đủ trên Báo cáo Xuất - Nhập - Tồn tổng hợp.'
    },
    thu_kho: {
        title: 'Thủ kho nhập/xuất kho (Ghi sổ Thẻ kho)',
        tag: 'XÁC NHẬN THỰC TẾ & GHI SỔ',
        icon: 'solar:user-check-bold',
        colorClass: 'bg-sky-600',
        role: 'Thủ kho / Kế toán trưởng',
        status: 'Tự động ghi sổ thời gian thực (Realtime)',
        breadcrumb: 'Menu Sidebar > Tồn kho & Vật tư > Thẻ kho từng vật tư',
        url: '{{ route("inventory.index") }}',
        createUrl: null,
        createLabel: '',
        desc: 'Ghi nhận số lượng thực xuất, thực nhập vào Thẻ kho (Stock Card). Trên phần mềm Gervin, thẻ kho được ghi sổ tự động và tức thì mỗi khi phiếu Nhập / Xuất / Chuyển được tạo.',
        howto: 'Tại trang Tồn kho & Hàng hóa, bấm vào icon Thẻ kho cạnh bất kỳ mặt hàng nào để xem toàn bộ lịch sử ngày giờ, số phiếu, người tạo và số dư sau mỗi lần xuất/nhập.'
    },
    kiem_ke: {
        title: 'Kiểm kê kho (Cân bằng kho)',
        tag: 'CÂN BẰNG TỒN THỰC TẾ',
        icon: 'solar:checklist-minimalistic-bold',
        colorClass: 'bg-amber-500',
        role: 'Thủ kho / Ban kiểm kê',
        status: 'Đầy đủ 100% (Có nút Cân bằng kho tự động)',
        breadcrumb: 'Menu Sidebar > Tồn kho & Vật tư > Tab "Kiểm kê kho"',
        url: '{{ route("inventory.stocktakes.index") }}',
        createUrl: '{{ route("inventory.stocktakes.create") }}',
        createLabel: '+ Lập Phiếu Kiểm Kê',
        desc: 'Kiểm đếm định kỳ số lượng hàng hóa thực tế tại kho so với số liệu trên sổ sách phần mềm. Tự động tính chênh lệch Thừa / Thiếu và có nút "Cân bằng kho" bù trừ tự động.',
        howto: '1. Vào Tab "Kiểm kê kho" > Bấm "+ Tạo phiếu kiểm kê".<br>2. Chọn kho cần kiểm kê, hệ thống tự nạp số tồn trên sổ.<br>3. Điền số lượng thực tế đếm được.<br>4. Bấm "Cân bằng kho" -> Phần mềm tự động điều chỉnh tồn kho về đúng số thực tế.'
    },
    bao_cao: {
        title: 'Báo cáo phân tích kho',
        tag: 'ĐÍCH ĐẾN - PHÂN TÍCH QUẢN TRỊ',
        icon: 'solar:chart-square-bold',
        colorClass: 'bg-amber-600',
        role: 'Kế toán kho / Ban Giám đốc',
        status: 'Vượt trội (Bao gồm cả Ma trận 12 tháng)',
        breadcrumb: 'Menu Sidebar > Tồn kho & Vật tư > Tab "Báo cáo & Ma trận tồn kho"',
        url: '{{ route("inventory.reports.index") }}',
        createUrl: null,
        createLabel: '',
        desc: 'Cung cấp 3 báo cáo trọng yếu: (1) Báo cáo Xuất - Nhập - Tồn tổng hợp chuẩn kế toán; (2) Sổ chi tiết phát sinh theo thời gian; (3) Ma trận tiêu thụ 12 tháng kế toán theo từng mã màu / nhóm kho. Có nút Xuất file Excel chuẩn.',
        howto: '1. Bấm tab "Báo cáo & Ma trận tồn kho".<br>2. Chọn khoảng thời gian (Hôm nay, Tháng này, Năm nay...) hoặc chọn Kho.<br>3. Xem số liệu Tồn đầu, Nhận trong kỳ, Tiêu thụ trong kỳ và Tồn cuối kỳ.<br>4. Bấm nút xanh "Xuất Excel" để tải file báo cáo.'
    }
};

function openNodeDetail(nodeKey) {
    const node = workflowNodes[nodeKey];
    if (!node) return;

    const modal = document.getElementById('workflow-node-modal');
    const header = document.getElementById('modal-node-header');
    const icon = document.getElementById('modal-node-icon');
    const tag = document.getElementById('modal-node-tag');
    const title = document.getElementById('modal-node-title');
    const breadcrumb = document.getElementById('modal-node-breadcrumb');
    const urlText = document.getElementById('modal-node-url');
    const role = document.getElementById('modal-node-role');
    const status = document.getElementById('modal-node-status');
    const desc = document.getElementById('modal-node-desc');
    const howto = document.getElementById('modal-node-howto');
    const actionBtn = document.getElementById('modal-node-action-btn');
    const actionText = document.getElementById('modal-node-action-text');
    const secondaryBtn = document.getElementById('modal-node-action-btn-secondary');
    const secondaryText = document.getElementById('modal-node-action-secondary-text');

    // Header styling
    header.className = `px-6 py-4 ${node.colorClass} text-white flex items-center justify-between`;
    icon.innerHTML = `<iconify-icon icon="${node.icon}"></iconify-icon>`;
    tag.innerText = node.tag;
    title.innerText = node.title;

    // Content
    breadcrumb.innerHTML = node.breadcrumb;
    urlText.innerText = node.url;
    role.innerText = node.role;
    status.innerHTML = `<iconify-icon icon="solar:check-circle-bold" class="text-base text-emerald-600"></iconify-icon> ${node.status}`;
    desc.innerHTML = node.desc;
    howto.innerHTML = node.howto;

    // Action button
    actionBtn.href = node.url;
    actionText.innerText = `Mở ${node.title} ngay`;

    if (node.createUrl) {
        secondaryBtn.style.display = 'inline-flex';
        secondaryBtn.href = node.createUrl;
        secondaryText.innerText = node.createLabel || '+ Tạo mới';
    } else {
        secondaryBtn.style.display = 'none';
    }

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeNodeDetail() {
    const modal = document.getElementById('workflow-node-modal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNodeDetail();
    }
});
</script>
@endsection

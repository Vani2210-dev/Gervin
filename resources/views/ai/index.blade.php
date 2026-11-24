@extends('layout.layout')

@php
    $title    = 'Trợ lý AI Doanh nghiệp';
    $subTitle = 'Hỗ trợ tra cứu & phân tích số liệu thời gian thực Gervin Wood';
@endphp

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="gv-ai-page-wrapper">
  {{-- Khung bố cục 2 cột: Chat bên trái, Báo cáo nhanh bên phải --}}
  <div class="gv-ai-main-grid">
    
    {{-- Cột Trái: Khung Chat AI --}}
    <div class="gv-ai-chat-card">
      <div id="gvChatMessages" class="gv-chat-messages-area">
        {{-- Welcome message --}}
        <div class="gv-msg-row gv-msg-ai">
          <div class="gv-msg-avatar">
            <i class="fa-solid fa-robot"></i>
          </div>
          <div class="gv-msg-bubble gv-ai-reply">
            <div style="font-weight: 700; margin-bottom: 6px; color: #0f172a; font-size: 14px;">Xin chào! Tôi là Trợ lý AI Quản trị Doanh nghiệp Gervin Wood.</div>
            <p style="margin: 0 0 8px 0; color: #334155; font-size: 13px;">Tôi được kết nối trực tiếp với 7 phân hệ dữ liệu thời gian thực của xưởng và văn phòng:</p>
            <ul style="margin: 0 0 10px 16px; padding: 0; color: #475569; font-size: 13px; line-height: 1.6;">
              <li><strong>Đơn hàng sản xuất:</strong> Tra cứu đơn Acrylic, Min/Late, Glass kính, Flywood; trạng thái duyệt Kế toán / Kỹ thuật, tiến độ sản xuất.</li>
              <li><strong>Khách hàng & Công nợ:</strong> Tra cứu dư nợ hiện tại, hạn mức nợ, chiết khấu mặc định, nhóm thị trường của từng đại lý/xưởng.</li>
              <li><strong>Nhà cung cấp & Vật tư:</strong> Danh sách NCC ván, nẹp, keo; số dư nợ phải trả và lịch sử mua hàng.</li>
              <li><strong>Kho ván gỗ & Nẹp chỉ:</strong> Tồn kho chi tiết theo mã màu, mã vân, độ dày, chủng loại (MDF, HDF, Plywood), cảnh báo tồn kho thấp.</li>
              <li><strong>Tiến độ KCS / QC & Đo đạc:</strong> Quét tem QR từng tấm phôi, kết quả đo kích thước thực tế Dài x Rộng từ thước điện tử, sai số ±1mm.</li>
              <li><strong>Đội xe & Giao nhận vận chuyển:</strong> Danh sách xe tải giao hàng, nhật trình đổ dầu, các kiện hàng đóng gói xuất xưởng.</li>
              <li><strong>Nhân sự & Phân quyền:</strong> Danh bạ nội bộ, phòng ban Kế toán, Kỹ thuật, Quản đốc, KCS, Xưởng sản xuất.</li>
            </ul>
            <p style="margin: 0; font-size: 12px; color: #64748b;">Bạn có thể nhập câu hỏi vào ô bên dưới để bắt đầu tra cứu số liệu (ví dụ: <em>"Kiểm tra công nợ khách hàng"</em>, <em>"Tiến độ đơn 6188"</em>, <em>"Tồn kho ván gỗ"</em>, <em>"Lỗi KCS hôm nay"</em>).</p>
          </div>
        </div>
      </div>

      {{-- Thanh điều khiển dưới: Chọn mô hình & Làm mới --}}
      <div class="gv-ai-control-bar">
        <div class="gv-model-selector-group">
          <span class="gv-model-label">
            <i class="fa-solid fa-microchip" style="color: #0284c7;"></i>
            <span>Mô hình:</span>
          </span>
          <select id="gvAiModel" class="gv-model-select">
            <optgroup label="Miễn phí 100% (Khuyên dùng - Không tốn phí)">
              <option value="openrouter/free" selected>OpenRouter Free (Tự động chuyển mô hình miễn phí tối ưu)</option>
              <option value="nvidia/nemotron-3.5-lightning:free">NVIDIA Nemotron Lightning (Free)</option>
              <option value="google/gemma-4-31b-it:free">Google Gemma 4 31B (Free)</option>
              <option value="liquid/lfm-3-1.7b:free">LiquidAI LFM 1.7B (Free)</option>
            </optgroup>
            <optgroup label="Trả phí chất lượng cao">
              <option value="openai/gpt-4o-mini">OpenAI GPT-4o-mini (Rất rẻ, chính xác)</option>
              <option value="deepseek/deepseek-chat">DeepSeek V3 / Chat (Rất thông minh)</option>
            </optgroup>
          </select>
        </div>

        <div class="gv-control-actions">
          <span class="gv-status-badge">
            <span class="gv-status-dot"></span>
            <span>OpenRouter Sẵn sàng</span>
          </span>
          <button type="button" onclick="clearGvAiChatHistory()" class="gv-btn-clear" title="Làm mới đoạn hội thoại">
            <i class="fa-solid fa-rotate-right"></i>
            <span class="gv-btn-clear-text">Làm mới</span>
          </button>
        </div>
      </div>

      {{-- Khung nhập tin nhắn --}}
      <div class="gv-ai-input-bar">
        <form id="gvAiForm" onsubmit="sendGvAiMessage(event)" class="gv-input-form">
          <input type="text" id="gvAiInput" class="gv-input-field" placeholder="Nhập câu hỏi để AI phân tích số liệu Gervin Wood..." required autocomplete="off">
          <button type="submit" id="gvAiSubmit" class="gv-submit-btn">
            <span>Gửi</span>
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </form>
      </div>
    </div>

    {{-- Cột Phải: Báo cáo nhanh số liệu hệ thống --}}
    <div class="gv-ai-side-panel">
      {{-- Card 1: Top khách nợ --}}
      <div class="gv-side-card">
        <div class="gv-side-card-header">
          <span class="gv-side-card-title">Top khách nợ</span>
          <i class="fa-solid fa-triangle-exclamation" style="color: #f59e0b; font-size: 14px;"></i>
        </div>
        <div class="gv-side-card-body">
          @forelse($context['top_debt_customers'] ?? [] as $td)
            <div class="gv-side-list-item">
              <div class="gv-side-item-main">
                <span class="gv-side-item-name" title="{{ $td['name'] }}">{{ $td['name'] }}</span>
                <span class="gv-side-item-sub">{{ $td['market_group'] ?? 'Chưa phân nhóm' }} ({{ $td['code'] ?? '' }})</span>
              </div>
              <span class="gv-side-item-value red">{{ number_format($td['debt'], 0, ',', '.') }} đ</span>
            </div>
          @empty
            <div class="gv-side-empty">Không có công nợ phát sinh.</div>
          @endforelse
        </div>
      </div>

      {{-- Card 2: Cảnh báo tồn kho ván gỗ --}}
      <div class="gv-side-card">
        <div class="gv-side-card-header">
          <span class="gv-side-card-title">Cảnh báo tồn kho ván</span>
          <i class="fa-solid fa-boxes-stacked" style="color: #ef4444; font-size: 14px;"></i>
        </div>
        <div class="gv-side-card-body">
          @forelse($context['low_stock_wood_boards'] ?? [] as $lw)
            <div class="gv-side-list-item alert">
              <div class="gv-side-item-main">
                <span class="gv-side-item-name" title="{{ $lw['foil_name'] ?: $lw['color_code'] }}">{{ $lw['color_code'] }} - {{ $lw['foil_name'] ?: 'Ván gỗ' }}</span>
                <span class="gv-side-item-sub">Nhóm: {{ $lw['price_group'] ?? 'Mặc định' }}</span>
              </div>
              <span class="gv-side-item-badge">Còn {{ $lw['stock'] }} {{ $lw['unit'] }}</span>
            </div>
          @empty
            <div class="gv-side-empty">Tồn kho các loại ván ổn định.</div>
          @endforelse
        </div>
      </div>

      {{-- Card 3: Thống kê KCS & Đo đạc QR hôm nay --}}
      <div class="gv-side-card">
        <div class="gv-side-card-header">
          <span class="gv-side-card-title">KCS & Đo đạc hôm nay</span>
          <i class="fa-solid fa-ruler-combined" style="color: #0284c7; font-size: 14px;"></i>
        </div>
        <div class="gv-side-card-body">
          <div class="gv-side-list-item">
            <div class="gv-side-item-main">
              <span class="gv-side-item-name">Tổng lượt đo kích thước</span>
              <span class="gv-side-item-sub">Từ thiết bị đo điện tử QR</span>
            </div>
            <span class="gv-side-item-value blue">{{ number_format($context['business_overview']['today_qc_scans'] ?? 0, 0, ',', '.') }}</span>
          </div>
          <div class="gv-side-list-item">
            <div class="gv-side-item-main">
              <span class="gv-side-item-name">Đạt chuẩn KCS (±1mm)</span>
              <span class="gv-side-item-sub">Kích thước chuẩn xác</span>
            </div>
            <span class="gv-side-item-value" style="color: #10b981;">{{ number_format($context['business_overview']['today_qc_passed'] ?? 0, 0, ',', '.') }}</span>
          </div>
          <div class="gv-side-list-item alert">
            <div class="gv-side-item-main">
              <span class="gv-side-item-name">Phát hiện sai lệch/lỗi</span>
              <span class="gv-side-item-sub">Cần cắt sửa lại tấm</span>
            </div>
            <span class="gv-side-item-value red">{{ number_format($context['business_overview']['today_qc_failed'] ?? 0, 0, ',', '.') }}</span>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<style>
  /* Base Wrapper */
  .gv-ai-page-wrapper {
    padding: 12px 4px;
    width: 100%;
    box-sizing: border-box;
  }

  /* Grid 2 Cột trên Desktop */
  .gv-ai-main-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
  }

  /* Chat Main Card */
  .gv-ai-chat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 170px);
    min-height: 600px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    overflow: hidden;
  }

  /* Message Area */
  .gv-chat-messages-area {
    flex: 1 1 auto;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: #f8fafc;
    -webkit-overflow-scrolling: touch;
  }

  /* Message Rows */
  .gv-msg-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    width: 100%;
  }
  .gv-msg-row.gv-msg-user {
    justify-content: flex-end;
  }
  .gv-msg-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0284c7, #0ea5e9);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
  }
  .gv-msg-bubble {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 0 14px 14px 14px;
    padding: 14px 18px;
    max-width: 86%;
    font-size: 13.5px;
    line-height: 1.6;
    color: #1e293b;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    box-sizing: border-box;
    word-break: break-word;
  }
  .gv-msg-user .gv-msg-bubble {
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #ffffff;
    border: none;
    border-radius: 14px 0 14px 14px;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.2);
  }

  /* Markdown & Tables inside AI Reply */
  .gv-ai-reply {
    max-width: 94%;
    overflow-x: auto;
  }
  .gv-ai-reply table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0;
    font-size: 12.5px;
    background: #ffffff;
    display: table;
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
  }
  .gv-ai-reply th, .gv-ai-reply td {
    border: 1px solid #cbd5e1;
    padding: 8px 12px;
    text-align: left;
    white-space: nowrap;
  }
  .gv-ai-reply th {
    background-color: #f1f5f9;
    font-weight: 700;
    color: #0f172a;
  }
  .gv-ai-reply tr:nth-child(even) {
    background-color: #f8fafc;
  }
  .gv-ai-reply p {
    margin: 0 0 8px 0;
  }
  .gv-ai-reply p:last-child {
    margin-bottom: 0;
  }
  .gv-ai-reply ul, .gv-ai-reply ol {
    margin: 6px 0 10px 18px;
    padding: 0;
  }
  .gv-ai-reply li {
    margin-bottom: 4px;
  }

  /* Bottom Control Bar */
  .gv-ai-control-bar {
    padding: 8px 16px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
  }
  .gv-model-selector-group {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .gv-model-label {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }
  .gv-model-select {
    height: 34px;
    padding: 0 10px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #0f172a;
    background: #f8fafc;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
  }
  .gv-model-select:focus {
    border-color: #0284c7;
  }
  .gv-control-actions {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .gv-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    color: #059669;
    white-space: nowrap;
  }
  .gv-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
  }
  .gv-btn-clear {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
  }
  .gv-btn-clear:hover {
    background: #e2e8f0;
    color: #0f172a;
  }

  /* Input Form */
  .gv-ai-input-bar {
    padding: 12px 16px;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
  }
  .gv-input-form {
    display: flex;
    gap: 10px;
    margin: 0;
  }
  .gv-input-field {
    flex: 1 1 auto;
    height: 44px;
    padding: 0 16px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 13.5px;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s;
  }
  .gv-input-field:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
  }
  .gv-submit-btn {
    height: 44px;
    padding: 0 20px;
    background: linear-gradient(135deg, #0284c7, #0369a1);
    border: none;
    border-radius: 10px;
    color: #ffffff;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    transition: all 0.2s;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
  }
  .gv-submit-btn:hover {
    background: linear-gradient(135deg, #0369a1, #075985);
  }
  .gv-submit-btn:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
  }

  /* Cột Phải: Báo cáo nhanh */
  .gv-ai-side-panel {
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: calc(100vh - 170px);
    overflow-y: auto;
  }
  .gv-side-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
  }
  .gv-side-card-header {
    font-weight: 700;
    font-size: 13px;
    color: #0f172a;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .gv-side-card-title {
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-size: 12px;
    color: #475569;
  }
  .gv-side-card-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .gv-side-list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    font-size: 12px;
    gap: 8px;
  }
  .gv-side-list-item.alert {
    background: #fff1f2;
    border-color: #ffe4e6;
  }
  .gv-side-item-main {
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
  .gv-side-item-name {
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .gv-side-item-sub {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 2px;
  }
  .gv-side-item-value {
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
  }
  .gv-side-item-value.red {
    color: #dc2626;
  }
  .gv-side-item-value.blue {
    color: #0284c7;
  }
  .gv-side-item-badge {
    padding: 2px 8px;
    background: #ffe4e6;
    color: #be123c;
    border-radius: 999px;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
  }
  .gv-side-empty {
    font-size: 12px;
    color: #94a3b8;
    text-align: center;
    padding: 12px 0;
  }

  /* Responsive Mobile / Tablet */
  @media (max-width: 1024px) {
    .gv-ai-main-grid {
      grid-template-columns: 1fr;
    }
    .gv-ai-chat-card {
      height: 70vh;
      min-height: 480px;
    }
    .gv-ai-side-panel {
      max-height: none;
      overflow-y: visible;
    }
  }

  @media (max-width: 640px) {
    .gv-ai-control-bar {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }
    .gv-model-selector-group {
      width: 100%;
    }
    .gv-model-select {
      flex: 1 1 auto;
      width: 100%;
    }
    .gv-status-badge {
      display: none;
    }
    .gv-btn-clear-text {
      display: none;
    }
    .gv-btn-clear {
      padding: 6px 10px;
    }
    .gv-ai-input-bar {
      padding: 8px 10px;
    }
    .gv-input-field {
      height: 40px;
      padding: 0 12px;
      font-size: 13px;
    }
    .gv-submit-btn {
      height: 40px;
      padding: 0 14px;
      font-size: 13px;
    }
    .gv-submit-btn span {
      display: none;
    }
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
const STORAGE_KEY_CHAT = 'gervin_ai_chat_history_v1';
const STORAGE_KEY_MODEL = 'gervin_ai_selected_model_v1';

let gvHistory = [];

const welcomeHtml = `
  <div class="gv-msg-row gv-msg-ai">
    <div class="gv-msg-avatar">
      <i class="fa-solid fa-robot"></i>
    </div>
    <div class="gv-msg-bubble gv-ai-reply">
      <div style="font-weight: 700; margin-bottom: 6px; color: #0f172a; font-size: 14px;">Xin chào! Tôi là Trợ lý AI Quản trị Doanh nghiệp Gervin Wood.</div>
      <p style="margin: 0 0 8px 0; color: #334155; font-size: 13px;">Tôi được kết nối trực tiếp với 7 phân hệ dữ liệu thời gian thực của xưởng và văn phòng:</p>
      <ul style="margin: 0 0 10px 16px; padding: 0; color: #475569; font-size: 13px; line-height: 1.6;">
        <li><strong>Đơn hàng sản xuất:</strong> Tra cứu đơn Acrylic, Min/Late, Glass kính, Flywood; trạng thái duyệt Kế toán / Kỹ thuật, tiến độ sản xuất.</li>
        <li><strong>Khách hàng & Công nợ:</strong> Tra cứu dư nợ hiện tại, hạn mức nợ, chiết khấu mặc định, nhóm thị trường của từng đại lý/xưởng.</li>
        <li><strong>Nhà cung cấp & Vật tư:</strong> Danh sách NCC ván, nẹp, keo; số dư nợ phải trả và lịch sử mua hàng.</li>
        <li><strong>Kho ván gỗ & Nẹp chỉ:</strong> Tồn kho chi tiết theo mã màu, mã vân, độ dày, chủng loại (MDF, HDF, Plywood), cảnh báo tồn kho thấp.</li>
        <li><strong>Tiến độ KCS / QC & Đo đạc:</strong> Quét tem QR từng tấm phôi, kết quả đo kích thước thực tế Dài x Rộng từ thước điện tử, sai số ±1mm.</li>
        <li><strong>Đội xe & Giao nhận vận chuyển:</strong> Danh sách xe tải giao hàng, nhật trình đổ dầu, các kiện hàng đóng gói xuất xưởng.</li>
        <li><strong>Nhân sự & Phân quyền:</strong> Danh bạ nội bộ, phòng ban Kế toán, Kỹ thuật, Quản đốc, KCS, Xưởng sản xuất.</li>
      </ul>
      <p style="margin: 0; font-size: 12px; color: #64748b;">Bạn có thể nhập câu hỏi vào ô bên dưới để bắt đầu tra cứu số liệu (ví dụ: <em>"Kiểm tra công nợ khách hàng"</em>, <em>"Tiến độ đơn 6188"</em>, <em>"Tồn kho ván gỗ"</em>, <em>"Lỗi KCS hôm nay"</em>).</p>
    </div>
  </div>
`;

function stripAllEmojis(text) {
  if (!text) return '';
  return String(text).replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F700}-\u{1F77F}\u{1F780}-\u{1F7FF}\u{1F800}-\u{1F8FF}\u{1F900}-\u{1F9FF}\u{1FA00}-\u{1FAFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{2300}-\u{23FF}\u{2B50}\u{200D}\u{FE0E}\u{FE0F}]/gu, '').trim();
}

// Khôi phục hội thoại từ LocalStorage
function initChatFromLocalStorage() {
  const modelSelect = document.getElementById('gvAiModel');
  const savedModel = localStorage.getItem(STORAGE_KEY_MODEL);
  if (savedModel && modelSelect && modelSelect.querySelector(`option[value="${savedModel}"]`)) {
    modelSelect.value = savedModel;
  }
  if (modelSelect) {
    modelSelect.addEventListener('change', function() {
      localStorage.setItem(STORAGE_KEY_MODEL, this.value);
    });
  }

  const chatBox = document.getElementById('gvChatMessages');
  const savedData = localStorage.getItem(STORAGE_KEY_CHAT);
  if (!savedData || !chatBox) return;

  try {
    const list = JSON.parse(savedData);
    if (Array.isArray(list) && list.length > 0) {
      gvHistory = list;
      let html = welcomeHtml;
      list.forEach(item => {
        if (item.role === 'user') {
          html += `
            <div class="gv-msg-row gv-msg-user">
              <div class="gv-msg-bubble">
                ${escapeHtml(item.content)}
              </div>
            </div>
          `;
        } else if (item.role === 'assistant') {
          const rendered = marked.parse(stripAllEmojis(item.content));
          html += `
            <div class="gv-msg-row gv-msg-ai">
              <div class="gv-msg-avatar">
                <i class="fa-solid fa-robot"></i>
              </div>
              <div class="gv-msg-bubble gv-ai-reply">
                ${rendered}
              </div>
            </div>
          `;
        }
      });
      chatBox.innerHTML = html;
      setTimeout(() => { chatBox.scrollTop = chatBox.scrollHeight; }, 50);
    }
  } catch (err) {
    console.warn('Lỗi đọc lịch sử chat từ localStorage:', err);
  }
}

// Xóa đoạn chat khi người dùng bấm nút Làm mới (Reset)
function clearGvAiChatHistory() {
  localStorage.removeItem(STORAGE_KEY_CHAT);
  gvHistory = [];
  const chatBox = document.getElementById('gvChatMessages');
  if (chatBox) {
    chatBox.innerHTML = welcomeHtml;
  }
}

async function sendGvAiMessage(e) {
  e.preventDefault();
  const input = document.getElementById('gvAiInput');
  const submitBtn = document.getElementById('gvAiSubmit');
  const chatBox = document.getElementById('gvChatMessages');
  const model = document.getElementById('gvAiModel').value;
  const msg = stripAllEmojis(input.value.trim());
  if (!msg) return;

  // Render User Message Bubble
  chatBox.insertAdjacentHTML('beforeend', `
    <div class="gv-msg-row gv-msg-user">
      <div class="gv-msg-bubble">
        ${escapeHtml(msg)}
      </div>
    </div>
  `);
  input.value = '';
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  // Loading Indicator
  const loadingId = 'ai-load-' + Date.now();
  chatBox.insertAdjacentHTML('beforeend', `
    <div id="${loadingId}" class="gv-msg-row gv-msg-ai" style="color: #64748b; font-size: 13px;">
      <div class="gv-msg-avatar" style="background: #64748b;">
        <i class="fa-solid fa-robot"></i>
      </div>
      <div style="padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0 12px 12px 12px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-spinner fa-spin" style="color: #0284c7;"></i>
        <span>AI đang phân tích số liệu Gervin Wood...</span>
      </div>
    </div>
  `);
  chatBox.scrollTop = chatBox.scrollHeight;

  try {
    const res = await fetch("{{ route('ai.chat') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ message: msg, model: model, history: gvHistory })
    });
    
    let data;
    const rawText = await res.text();
    document.getElementById(loadingId)?.remove();

    try {
      data = JSON.parse(rawText);
    } catch (e) {
      chatBox.insertAdjacentHTML('beforeend', `
        <div class="gv-msg-row gv-msg-ai">
          <div class="gv-msg-bubble" style="color: #dc2626; background: #fef2f2; border-color: #fecaca;">
            Lỗi máy chủ (${res.status}): ${rawText.includes('Maximum execution time') ? 'Thời gian phản hồi AI quá lâu (Timeout). Vui lòng thử lại hoặc chọn mô hình khác!' : 'Máy chủ phản hồi không đúng định dạng JSON.'}
          </div>
        </div>
      `);
      return;
    }

    if (data.success) {
      const cleanReply = stripAllEmojis(data.reply);
      const rendered = marked.parse(cleanReply);
      chatBox.insertAdjacentHTML('beforeend', `
        <div class="gv-msg-row gv-msg-ai">
          <div class="gv-msg-avatar">
            <i class="fa-solid fa-robot"></i>
          </div>
          <div class="gv-msg-bubble gv-ai-reply">
            ${rendered}
          </div>
        </div>
      `);
      gvHistory.push({ role: 'user', content: msg });
      gvHistory.push({ role: 'assistant', content: cleanReply });
      
      // Lưu vào LocalStorage
      try {
        localStorage.setItem(STORAGE_KEY_CHAT, JSON.stringify(gvHistory));
      } catch (e) {
        console.warn('Lỗi ghi localStorage:', e);
      }
    } else {
      chatBox.insertAdjacentHTML('beforeend', `
        <div class="gv-msg-row gv-msg-ai">
          <div class="gv-msg-bubble" style="color: #dc2626; background: #fef2f2; border-color: #fecaca;">
            Lỗi: ${escapeHtml(data.error || 'Lỗi không xác định từ máy chủ')}
          </div>
        </div>
      `);
    }
  } catch (err) {
    document.getElementById(loadingId)?.remove();
    chatBox.insertAdjacentHTML('beforeend', `
      <div class="gv-msg-row gv-msg-ai">
        <div class="gv-msg-bubble" style="color: #dc2626; background: #fef2f2; border-color: #fecaca;">
          Lỗi kết nối: ${escapeHtml(err.message)}
        </div>
      </div>
    `);
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<span>Gửi</span><i class="fa-solid fa-paper-plane"></i>';
    chatBox.scrollTop = chatBox.scrollHeight;
  }
}

function escapeHtml(str) {
  return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

document.addEventListener('DOMContentLoaded', initChatFromLocalStorage);
</script>
@endsection

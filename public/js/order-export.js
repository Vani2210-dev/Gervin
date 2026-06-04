function romanize(num) {
    if (isNaN(num)) return '';
    const digits = String(+num).split('');
    const key = ["","C","CC","CCC","CD","D","DC","DCC","DCCC","CM",
               "","X","XX","XXX","XL","L","LX","LXX","LXXX","XC",
               "","I","II","III","IV","V","VI","VII","VIII","IX"];
    let roman = '';
    let i = 3;
    while (i--) {
        roman = (key[+digits.pop() + (i * 10)] || "") + roman;
    }
    return Array(+digits.join("") + 1).join("M") + roman;
}

// Helper to estimate height of merged cell with wrapped text
function estimateRowHeight(text, mergedWidth) {
    if (!text) return 18;
    const charsPerLine = Math.max(10, Math.floor(mergedWidth * 0.75));
    const paragraphs = text.split(/\r?\n/);
    let totalLines = 0;
    paragraphs.forEach(p => {
        totalLines += Math.max(1, Math.ceil(p.length / charsPerLine));
    });
    return Math.max(18, totalLines * 16);
}

async function exportToExcel() {
    const btn = document.querySelector('button[onclick="exportToExcel()"]');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<iconify-icon icon="lucide:loader" class="animate-spin text-base"></iconify-icon> Đang xuất...';

    try {
        const orderData = window.orderExportData;
        if (!orderData) {
            throw new Error("Dữ liệu đơn hàng không tìm thấy.");
        }
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Báo giá', {
            views: [{ showGridLines: true }]
        });

        // Thin border styling
        const thinBorder = {
            top: { style: 'thin', color: { argb: 'FFD1D5DB' } },
            left: { style: 'thin', color: { argb: 'FFD1D5DB' } },
            bottom: { style: 'thin', color: { argb: 'FFD1D5DB' } },
            right: { style: 'thin', color: { argb: 'FFD1D5DB' } }
        };

        // Orange fill for header row
        const headerFill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFF79646' }
        };

        // Helper to set cell value, font, alignment
        const setCell = (row, col, value, bold = false, align = 'left', size = 11, italic = false) => {
            const cell = worksheet.getCell(row, col);
            cell.value = value;
            cell.font = { name: 'Times New Roman', size: size, bold: bold, italic: italic, color: { argb: 'FF000000' } };
            cell.alignment = { horizontal: align, vertical: 'middle', wrapText: true };
            return cell;
        };

        if (orderData.type !== 'glass') {
            // Setup general layout (Logo, Company info)
            worksheet.getRow(1).height = 20;
            worksheet.getRow(2).height = 20;
            worksheet.getRow(3).height = 20;
            worksheet.getRow(4).height = 20;

            worksheet.mergeCells('C1:J1');
            worksheet.mergeCells('C2:J2');
            worksheet.mergeCells('C3:J3');
            worksheet.mergeCells('C4:J4');

            setCell(1, 3, 'CÔNG TY TNHH GỖ GERVIN', true, 'left', 14);
            setCell(2, 3, 'Địa chỉ: Xóm 3, Hưng Thịnh, Hưng Nguyên, Nghệ An', false, 'left', 10);
            setCell(3, 3, 'Điện thoại: 0967.181.786 - Email: contact@gervinwood.com', false, 'left', 10);
            setCell(4, 3, 'WWW.GERVINWOOD.COM', true, 'left', 10);

            // Embed logo in A1:B4
            const logoBase64 = await getLogoBase64('/logo.png');
            if (logoBase64) {
                const logoId = workbook.addImage({
                    base64: logoBase64,
                    extension: 'png',
                });
                worksheet.addImage(logoId, {
                    tl: { col: 0.1, row: 0.1 },
                    br: { col: 2.0, row: 3.9 },
                    editAs: 'oneCell'
                });
            }
        }

        // Parse Dates
        let dateStr = '';
        if (orderData.order_date) {
            const d = new Date(orderData.order_date.replace(/-/g, '/'));
            if (!isNaN(d.getTime())) {
                dateStr = `Ngày ${d.getDate()} tháng ${d.getMonth() + 1} năm ${d.getFullYear()}`;
            }
        }
        if (!dateStr && orderData.created_at) {
            const parts = orderData.created_at.split(' ');
            if (parts[0]) {
                const dateParts = parts[0].split('/');
                if (dateParts.length === 3) {
                    dateStr = `Ngày ${parseInt(dateParts[0])} tháng ${parseInt(dateParts[1])} năm ${dateParts[2]}`;
                }
            }
        }
        if (!dateStr) {
            const today = new Date();
            dateStr = `Ngày ${today.getDate()} tháng ${today.getMonth() + 1} năm ${today.getFullYear()}`;
        }

        let chotDonStr = '—';
        if (orderData.order_date) {
            const d = new Date(orderData.order_date.replace(/-/g, '/'));
            if (!isNaN(d.getTime())) {
                chotDonStr = d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            }
        }

        let deadlineStr = '—';
        if (orderData.deadline) {
            const d = new Date(orderData.deadline.replace(/-/g, '/'));
            if (!isNaN(d.getTime())) {
                deadlineStr = d.toLocaleDateString('vi-VN') + ' 12:00';
            }
        }

        let currentRow = 12;

        if (orderData.type === 'acrylic') {
            // Column dimensions
            const colWidths = [
                { col: 'A', width: 5.375 },
                { col: 'B', width: 18.125 },
                { col: 'C', width: 38.75 },
                { col: 'D', width: 7.25 },
                { col: 'E', width: 5.75 },
                { col: 'F', width: 5.875 },
                { col: 'G', width: 10.5 },
                { col: 'H', width: 12.375 },
                { col: 'I', width: 7.5 },
                { col: 'J', width: 8.5 },
                { col: 'K', width: 10.75 },
                { col: 'L', width: 12.5 },
                { col: 'M', width: 20.75 },
                { col: 'N', width: 9.875 },
                { col: 'O', width: 11.625 }
            ];
            colWidths.forEach(w => { worksheet.getColumn(w.col).width = w.width; });

            // Metadata rows (using C:K merge instead of A:O to prevent swallowing columns L & M)
            worksheet.getRow(5).height = 25;
            worksheet.mergeCells('C5:K5');
            setCell(5, 3, 'BÁO GIÁ KIÊM ĐƠN ĐẶT HÀNG', true, 'center', 16);

            worksheet.getRow(6).height = 18;
            worksheet.mergeCells('C6:K6');
            setCell(6, 3, dateStr, false, 'center', 11, true);
            setCell(6, 12, 'Ngày giờ chốt đơn:', true, 'right', 10);
            worksheet.getCell('M6').value = chotDonStr;
            worksheet.getCell('M6').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(7).height = 18;
            worksheet.mergeCells('C7:K7');
            setCell(7, 3, `Số phiếu: ${orderData.order_code}`, true, 'center', 11);
            setCell(7, 12, 'Số ngày phải giao từ lúc chốt đơn:', true, 'right', 10);
            worksheet.getCell('M7').value = parseInt(orderData.delivery_days);
            worksheet.getCell('M7').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(8).height = 18;
            worksheet.mergeCells('C8:J8');
            setCell(8, 3, `Khách hàng: ${orderData.customer_name || ''}`, true, 'left', 11);
            setCell(8, 12, 'Ngày giờ phải giao hàng:', true, 'right', 10);
            worksheet.getCell('M8').value = deadlineStr;
            worksheet.getCell('M8').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(9).height = 18;
            worksheet.mergeCells('C9:J9');
            setCell(9, 3, `Địa chỉ: ${orderData.address || ''}`, false, 'left', 10);

            worksheet.getRow(10).height = 18;
            worksheet.mergeCells('C10:O10');
            setCell(10, 3, `Chính sách KH: `, false, 'left', 10);

            worksheet.getRow(11).height = 20;
            worksheet.mergeCells('C11:O11');
            setCell(11, 3, 'Công ty TNHH Gỗ GERVIN xin cảm ơn quý khách hàng đã tin dùng sản phẩm của chúng tôi và xin được báo giá như sau:', false, 'left', 10, true);

            // Double Row Headers (Row 12 & 13)
            worksheet.getRow(12).height = 24;
            worksheet.getRow(13).height = 24;

            const headersDef = [
                { range: 'A12:A13', val: 'Stt', col: 1 },
                { range: 'B12:B13', val: 'Mã hàng', col: 2 },
                { range: 'C12:C13', val: 'Tên hàng hóa, dịch vụ', col: 3 },
                { range: 'D12:E12', val: ' Kích thước (mm) ', col: 4 },
                { range: 'F12:F13', val: 'Số lượng', col: 6 },
                { range: 'G12:G13', val: 'Cạnh vát', col: 7 },
                { range: 'H12:H13', val: 'Chiều vân', col: 8 },
                { range: 'I12:I13', val: 'Cánh (m2)', col: 9 },
                { range: 'J12:J13', val: 'Thanh phào (m)', col: 10 },
                { range: 'K12:K13', val: 'Đơn giá', col: 11 },
                { range: 'L12:L13', val: 'Thành tiền', col: 12 },
                { range: 'M12:M13', val: 'GHI CHÚ', col: 13 },
                { range: 'N12:N13', val: 'Kính đố 70 hèm s5r10', col: 14 },
                { range: 'O12:O13', val: 'CNC như hình vẽ', col: 15 }
            ];

            headersDef.forEach(h => {
                if (h.range.includes(':')) worksheet.mergeCells(h.range);
                const cell = worksheet.getCell(h.range.split(':')[0]);
                cell.value = h.val;
            });

            worksheet.getCell('D13').value = 'Cao (chiều vân gỗ) ';
            worksheet.getCell('E13').value = 'Rộng\n\n';
            worksheet.getCell('N13').value = 'Vát ';
            worksheet.getCell('O13').value = 'Vân dọc ';

            // Style headers
            for (let r = 12; r <= 13; r++) {
                for (let c = 1; c <= 15; c++) {
                    const cell = worksheet.getCell(r, c);
                    cell.font = { name: 'Times New Roman', size: 11, bold: true };
                    cell.fill = headerFill;
                    cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                    cell.border = thinBorder;
                }
            }

            currentRow = 14;

            // Loop supplies
            orderData.supplies.forEach((supply, supplyIdx) => {
                const supplyRow = worksheet.getRow(currentRow);
                supplyRow.height = 22;
                supplyRow.getCell(1).value = romanize(supplyIdx + 1);
                supplyRow.getCell(3).value = supply.supply_name;
                supplyRow.getCell(6).value = 0;
                supplyRow.getCell(9).value = 0;
                supplyRow.getCell(10).value = 0;
                supplyRow.getCell(12).value = 0;

                for (let c = 1; c <= 15; c++) {
                    const cell = supplyRow.getCell(c);
                    cell.font = { name: 'Times New Roman', size: 11, bold: true };
                    cell.border = thinBorder;
                }
                currentRow++;

                // Loop items
                supply.items.forEach((item, itemIdx) => {
                    const itemRow = worksheet.getRow(currentRow);
                    itemRow.height = 20;

                    itemRow.getCell(1).value = itemIdx + 1;
                    itemRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(2).value = item.product_code || '';
                    itemRow.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(3).value = item.product_name || '';
                    itemRow.getCell(3).alignment = { horizontal: 'left', vertical: 'middle' };

                    itemRow.getCell(4).value = parseFloat(item.height) || 0;
                    itemRow.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(5).value = parseFloat(item.width) || 0;
                    itemRow.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(6).value = parseInt(item.quantity) || 1;
                    itemRow.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(7).value = item.edge_bevel || 'Vát 0';
                    itemRow.getCell(7).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(8).value = item.grain_direction !== null ? String(item.grain_direction) : '—';
                    itemRow.getCell(8).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(9).value = parseFloat(item.wing_area) || 0;
                    itemRow.getCell(9).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(10).value = parseFloat(item.molding_length) || 0;
                    itemRow.getCell(10).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(11).value = parseFloat(item.unit_price) || 0;
                    itemRow.getCell(11).numFmt = '#,##0';
                    itemRow.getCell(11).alignment = { horizontal: 'right', vertical: 'middle' };

                    itemRow.getCell(12).value = parseFloat(item.total_price) || 0;
                    itemRow.getCell(12).numFmt = '#,##0';
                    itemRow.getCell(12).alignment = { horizontal: 'right', vertical: 'middle' };

                    itemRow.getCell(13).value = item.notes || '';
                    itemRow.getCell(13).alignment = { horizontal: 'left', vertical: 'middle' };

                    itemRow.getCell(14).value = item.bevel || '0';
                    itemRow.getCell(14).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(15).value = item.vertical_grain_cnc || '0';
                    itemRow.getCell(15).alignment = { horizontal: 'center', vertical: 'middle' };

                    for (let c = 1; c <= 15; c++) {
                        const cell = itemRow.getCell(c);
                        cell.font = { name: 'Times New Roman', size: 11 };
                        cell.border = thinBorder;
                    }
                    currentRow++;
                });
            });

            // Grand total row
            const totalRow = worksheet.getRow(currentRow);
            totalRow.height = 22;
            totalRow.getCell(11).value = "TỔNG TIỀN HÀNG:";
            totalRow.getCell(11).font = { name: 'Times New Roman', size: 11, bold: true };
            totalRow.getCell(11).alignment = { horizontal: 'right', vertical: 'middle' };

            totalRow.getCell(12).value = parseFloat(orderData.total_amount);
            totalRow.getCell(12).font = { name: 'Times New Roman', size: 11, bold: true, color: { argb: 'FFDC2626' } };
            totalRow.getCell(12).numFmt = '#,##0';
            totalRow.getCell(12).alignment = { horizontal: 'right', vertical: 'middle' };

            for (let c = 1; c <= 15; c++) {
                totalRow.getCell(c).border = {
                    top: { style: 'thin', color: { argb: 'FF9CA3AF' } },
                    bottom: { style: 'double', color: { argb: 'FF1F2937' } }
                };
            }
        } else if (orderData.type === 'glass') {
            // Column dimensions
            const colWidths = [
                { col: 'A', width: 6.5 },
                { col: 'B', width: 46.66 },
                { col: 'C', width: 10.83 },
                { col: 'D', width: 18.33 },
                { col: 'E', width: 10.66 },
                { col: 'F', width: 12.33 },
                { col: 'G', width: 13.0 },
                { col: 'H', width: 13.0 },
                { col: 'I', width: 8.83 },
                { col: 'J', width: 10.16 },
                { col: 'K', width: 11.33 },
                { col: 'L', width: 21.0 },
                { col: 'M', width: 24.66 },
                { col: 'N', width: 23.33 },
                { col: 'O', width: 35 }
            ];
            colWidths.forEach(w => { worksheet.getColumn(w.col).width = w.width; });

            // Write O1 note
            setCell(1, 15, 'KT m2/cánh nhỏ hơn 0.35m2 thì tính = 0.35m2', false, 'right', 10, true);

            // Metadata rows (using C:L merge instead of A:N to prevent swallowing columns M & N)
            worksheet.getRow(2).height = 25;
            worksheet.mergeCells('C2:L2');
            setCell(2, 3, 'BÁO GIÁ CÁNH KÍNH', true, 'center', 16);

            worksheet.getRow(3).height = 18;
            worksheet.mergeCells('C3:L3');
            setCell(3, 3, dateStr, false, 'center', 11, true);
            setCell(3, 13, 'Ngày giờ chốt đơn:', true, 'right', 10);
            worksheet.getCell('N3').value = chotDonStr;
            worksheet.getCell('N3').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(4).height = 18;
            worksheet.mergeCells('C4:L4');
            setCell(4, 3, `Số phiếu: ${orderData.order_code}`, true, 'center', 11);
            setCell(4, 13, 'Số ngày phải giao từ lúc chốt đơn:', true, 'right', 10);
            worksheet.getCell('N4').value = parseInt(orderData.delivery_days);
            worksheet.getCell('N4').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(5).height = 18;
            worksheet.mergeCells('B5:J5');
            setCell(5, 2, `Khách hàng: ${orderData.customer_name || ''}`, true, 'left', 11);
            setCell(5, 13, 'Ngày giờ phải giao hàng:', true, 'right', 10);
            worksheet.getCell('N5').value = deadlineStr;
            worksheet.getCell('N5').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(6).height = 18;
            worksheet.mergeCells('B6:I6');
            setCell(6, 2, `Địa chỉ: ${orderData.address || ''}`, false, 'left', 10);

            worksheet.getRow(7).height = 20;
            worksheet.mergeCells('A7:N7');
            setCell(7, 1, 'Lời đầu tiên, xin trân trọng cảm ơn quý khách hàng đã quan tâm đến sản phẩm cánh kính của công ty chúng tôi. GERVIN xin gửi tới Quý Khách hàng bảng báo giá chi tiết như sau:', false, 'left', 10, true);

            // Double Row Headers (Row 8 & 9)
            worksheet.getRow(8).height = 24;
            worksheet.getRow(9).height = 24;

            const headersDef = [
                { range: 'A8:A9', val: 'STT' },
                { range: 'B8:B9', val: 'TÊN SẢN PHẨM' },
                { range: 'C8:C9', val: 'MÃ SP' },
                { range: 'D8:D9', val: 'CHIỀU MỞ CÁNH' },
                { range: 'E8:E9', val: 'MÀU NHÔM' },
                { range: 'F8:F9', val: 'MÀU KÍNH' },
                { range: 'G8:H8', val: 'KÍCH THƯỚC CÁNH' },
                { range: 'I8:I9', val: 'ĐƠN VỊ' },
                { range: 'J8:J9', val: 'SỐ LƯỢNG CÁNH' },
                { range: 'K8:K9', val: 'KHỐI LƯỢNG (m2)' },
                { range: 'L8:L9', val: 'ĐƠN GIÁ' },
                { range: 'M8:M9', val: 'THÀNH TIỀN' },
                { range: 'N8:N9', val: 'Ghi chú' }
            ];

            headersDef.forEach(h => {
                if (h.range.includes(':')) worksheet.mergeCells(h.range);
                const cell = worksheet.getCell(h.range.split(':')[0]);
                cell.value = h.val;
            });

            worksheet.getCell('G9').value = 'Dài (mm)';
            worksheet.getCell('H9').value = 'Rộng (mm)';

            // Style headers
            for (let r = 8; r <= 9; r++) {
                for (let c = 1; c <= 14; c++) {
                    const cell = worksheet.getCell(r, c);
                    cell.font = { name: 'Times New Roman', size: 11, bold: true };
                    cell.fill = headerFill;
                    cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                    cell.border = thinBorder;
                }
            }

            currentRow = 10;
            worksheet.getRow(currentRow).height = 20;
            worksheet.mergeCells(`A${currentRow}:E${currentRow}`);
            setCell(currentRow, 1, 'GIÁ CÁNH KÍNH, KHUNG NHÔM HOÀN THIỆN:', true, 'left', 11);
            for (let c = 1; c <= 14; c++) {
                worksheet.getCell(currentRow, c).border = thinBorder;
            }
            currentRow++;

            // Loop supplies
            orderData.supplies.forEach((supply, supplyIdx) => {
                const supplyRow = worksheet.getRow(currentRow);
                supplyRow.height = 22;
                supplyRow.getCell(1).value = romanize(supplyIdx + 1) + '.';
                supplyRow.getCell(2).value = supply.supply_name;
                supplyRow.getCell(10).value = 0;
                supplyRow.getCell(11).value = 0;
                supplyRow.getCell(13).value = 0;

                for (let c = 1; c <= 14; c++) {
                    const cell = supplyRow.getCell(c);
                    cell.font = { name: 'Times New Roman', size: 11, bold: true };
                    cell.border = thinBorder;
                }
                currentRow++;

                // Loop items
                supply.items.forEach((item, itemIdx) => {
                    const itemRow = worksheet.getRow(currentRow);
                    itemRow.height = 20;

                    itemRow.getCell(1).value = itemIdx + 1;
                    itemRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(2).value = item.product_name || '';
                    itemRow.getCell(2).alignment = { horizontal: 'left', vertical: 'middle' };

                    itemRow.getCell(3).value = item.product_code || '';
                    itemRow.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(4).value = item.wing_opening_direction || '—';
                    itemRow.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(5).value = item.aluminum_color || '—';
                    itemRow.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(6).value = item.glass_color || '—';
                    itemRow.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(7).value = parseFloat(item.height) || 0;
                    itemRow.getCell(7).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(8).value = parseFloat(item.width) || 0;
                    itemRow.getCell(8).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(9).value = item.unit || 'cánh';
                    itemRow.getCell(9).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(10).value = parseInt(item.wing_quantity) || 0;
                    itemRow.getCell(10).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(11).value = parseFloat(item.area_m2) || 0;
                    itemRow.getCell(11).alignment = { horizontal: 'center', vertical: 'middle' };

                    itemRow.getCell(12).value = parseFloat(item.unit_price) || 0;
                    itemRow.getCell(12).numFmt = '#,##0';
                    itemRow.getCell(12).alignment = { horizontal: 'right', vertical: 'middle' };

                    itemRow.getCell(13).value = parseFloat(item.total_price) || 0;
                    itemRow.getCell(13).numFmt = '#,##0';
                    itemRow.getCell(13).alignment = { horizontal: 'right', vertical: 'middle' };

                    itemRow.getCell(14).value = item.notes || '';
                    itemRow.getCell(14).alignment = { horizontal: 'left', vertical: 'middle' };

                    for (let c = 1; c <= 14; c++) {
                        const cell = itemRow.getCell(c);
                        cell.font = { name: 'Times New Roman', size: 11 };
                        cell.border = thinBorder;
                    }
                    currentRow++;
                });
            });

            // Grand total row
            const totalRow = worksheet.getRow(currentRow);
            totalRow.height = 22;
            worksheet.mergeCells(`K${currentRow}:L${currentRow}`);
            totalRow.getCell(11).value = "TỔNG CỘNG:";
            totalRow.getCell(11).font = { name: 'Times New Roman', size: 11, bold: true };
            totalRow.getCell(11).alignment = { horizontal: 'right', vertical: 'middle' };

            totalRow.getCell(13).value = parseFloat(orderData.total_amount);
            totalRow.getCell(13).font = { name: 'Times New Roman', size: 11, bold: true, color: { argb: 'FFDC2626' } };
            totalRow.getCell(13).numFmt = '#,##0';
            totalRow.getCell(13).alignment = { horizontal: 'right', vertical: 'middle' };

            for (let c = 1; c <= 14; c++) {
                totalRow.getCell(c).border = {
                    top: { style: 'thin', color: { argb: 'FF9CA3AF' } },
                    bottom: { style: 'double', color: { argb: 'FF1F2937' } }
                };
            }
        } else if (orderData.type === 'min_late') {
            // Column dimensions
            const colWidths = [
                { col: 'A', width: 6.375 },
                { col: 'B', width: 11.25 },
                { col: 'C', width: 47.375 },
                { col: 'D', width: 6.75 },
                { col: 'E', width: 13.0 },
                { col: 'F', width: 7.875 },
                { col: 'G', width: 11.25 },
                { col: 'H', width: 7.375 },
                { col: 'I', width: 13.0 },
                { col: 'J', width: 13.0 },
                { col: 'K', width: 7.375 },
                { col: 'L', width: 10.625 },
                { col: 'M', width: 13.0 },
                { col: 'N', width: 13.0 },
                { col: 'O', width: 13.0 },
                { col: 'P', width: 13.0 },
                { col: 'Q', width: 23.75 },
                { col: 'R', width: 8.0 },
                { col: 'S', width: 9.0 },
                { col: 'T', width: 13.0 },
                { col: 'U', width: 13.0 }
            ];
            colWidths.forEach(w => { worksheet.getColumn(w.col).width = w.width; });

            const maxCol = colWidths.length;

            // Metadata rows (using C:O merge instead of A:R to prevent swallowing columns P & Q)
            worksheet.getRow(5).height = 25;
            worksheet.mergeCells('C5:O5');
            setCell(5, 3, 'BÁO GIÁ KIÊM ĐƠN ĐẶT HÀNG', true, 'center', 16);

            worksheet.getRow(6).height = 18;
            worksheet.mergeCells('C6:O6');
            setCell(6, 3, dateStr, false, 'center', 11, true);
            setCell(6, 16, 'Ngày giờ chốt đơn:', true, 'right', 10);
            worksheet.getCell('Q6').value = chotDonStr;
            worksheet.getCell('Q6').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(7).height = 18;
            worksheet.mergeCells('C7:O7');
            setCell(7, 3, `Số phiếu: ${orderData.order_code}`, true, 'center', 11);
            setCell(7, 16, 'Số ngày phải giao từ lúc chốt đơn:', true, 'right', 10);
            worksheet.getCell('Q7').value = parseInt(orderData.delivery_days);
            worksheet.getCell('Q7').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(8).height = 18;
            worksheet.mergeCells('C8:O8');
            setCell(8, 3, `Khách hàng: ${orderData.customer_name || ''}`, true, 'left', 11);
            setCell(8, 16, 'Ngày giờ phải giao hàng:', true, 'right', 10);
            worksheet.getCell('Q8').value = deadlineStr;
            worksheet.getCell('Q8').font = { name: 'Times New Roman', size: 10 };

            worksheet.getRow(9).height = 18;
            worksheet.mergeCells('C9:O9');
            setCell(9, 3, `Địa chỉ: ${orderData.address || ''}`, false, 'left', 10);

            worksheet.getRow(10).height = 20;
            worksheet.mergeCells('C10:R10');
            setCell(10, 3, 'Công ty TNHH Gỗ GERVIN xin cảm ơn quý khách hàng đã tin dùng sản phẩm của chúng tôi và xin được báo giá như sau:', false, 'left', 10, true);

            // Double Row Headers (Row 11 & 12)
            worksheet.getRow(11).height = 24;
            worksheet.getRow(12).height = 24;

            const headersDef = [
                { range: 'A11:A12', val: 'Stt' },
                { range: 'B11:B12', val: 'Mã hàng' },
                { range: 'C11:C12', val: 'Tên hàng hóa, dịch vụ' },
                { range: 'D11:E11', val: ' Kích thước (mm) ' },
                { range: 'F11:F12', val: 'Số lượng' },
                { range: 'G11:G12', val: 'Cạnh vát' },
                { range: 'H11:K11', val: 'Dán cạnh' },
                { range: 'L11:L12', val: 'Số mét dán thẳng' },
                { range: 'M11:M12', val: 'Số mét dán vát' },
                { range: 'N11:N12', val: 'Số mét dán bản rộng 25-35mm' },
                { range: 'O11:O12', val: 'Số mét dán ván chiều rộng 40-59mm' },
                { range: 'P11:P12', val: 'Số mét dán bản rộng 17-39mm' },
                { range: 'Q11:Q12', val: 'Ghi chú' }
            ];

            headersDef.forEach(h => {
                if (h.range.includes(':')) worksheet.mergeCells(h.range);
                const cell = worksheet.getCell(h.range.split(':')[0]);
                cell.value = h.val;
            });

            worksheet.getCell('D12').value = 'Cao (chiều vân gỗ) ';
            worksheet.getCell('E12').value = 'Rộng';
            worksheet.getCell('H12').value = 'Cao';
            worksheet.getCell('I12').value = 'Cao';
            worksheet.getCell('J12').value = 'Rộng';
            worksheet.getCell('K12').value = 'Rộng';
            worksheet.getCell('R12').value = 'Chiều vân';
            worksheet.getCell('S12').value = 'Vát mòi';
            worksheet.getCell('T12').value = 'Tay nắm vát';
            worksheet.getCell('U12').value = 'CNC';

            // Style headers
            for (let r = 11; r <= 12; r++) {
                for (let c = 1; c <= maxCol; c++) {
                    const cell = worksheet.getCell(r, c);
                    cell.font = { name: 'Times New Roman', size: 11, bold: true };
                    cell.fill = headerFill;
                    cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                    cell.border = thinBorder;
                }
            }

            currentRow = 13;

            // Write default VẬT TƯ row
            const vatTuRow = worksheet.getRow(currentRow);
            vatTuRow.height = 22;
            vatTuRow.getCell(1).value = 'A';
            vatTuRow.getCell(3).value = 'VẬT TƯ';
            vatTuRow.getCell(6).value = 0;
            vatTuRow.getCell(12).value = 0;
            vatTuRow.getCell(13).value = 0;
            vatTuRow.getCell(14).value = 0;
            vatTuRow.getCell(15).value = 0;
            vatTuRow.getCell(16).value = 0;
            vatTuRow.getCell(18).value = 'Vát ';

            for (let c = 1; c <= maxCol; c++) {
                const cell = vatTuRow.getCell(c);
                cell.font = { name: 'Times New Roman', size: 11, bold: true };
                cell.border = thinBorder;
            }
            currentRow++;

            // Loop supplies
            orderData.supplies.forEach((supply, supplyIdx) => {
                const supplyRow = worksheet.getRow(currentRow);
                supplyRow.height = 22;
                supplyRow.getCell(1).value = romanize(supplyIdx + 1);
                supplyRow.getCell(3).value = supply.supply_name;

                for (let c = 1; c <= maxCol; c++) {
                    const cell = supplyRow.getCell(c);
                    cell.font = { name: 'Times New Roman', size: 11, bold: true };
                    cell.border = thinBorder;
                }
                currentRow++;

                // Loop items
                supply.items.forEach((item, itemIdx) => {
                    const itemRow = worksheet.getRow(currentRow);
                    itemRow.height = 20;

                    itemRow.getCell(1).value = itemIdx + 1;
                    itemRow.getCell(2).value = item.product_code || '';
                    itemRow.getCell(3).value = item.name || '';

                    itemRow.getCell(4).value = parseFloat(item.height) || 0;
                    itemRow.getCell(5).value = parseFloat(item.width) || 0;
                    itemRow.getCell(6).value = parseInt(item.quantity) || 1;

                    itemRow.getCell(7).value = item.beveled_handle ? 'Vát ' + item.beveled_handle : 'Vát 0';

                    const gluing = item.edge_gluing || {};
                    itemRow.getCell(8).value = gluing.height_1 || '';
                    itemRow.getCell(9).value = gluing.height_2 || '';
                    itemRow.getCell(10).value = gluing.width_1 || '';
                    itemRow.getCell(11).value = gluing.width_2 || '';

                    itemRow.getCell(12).value = parseFloat(item.straight_paste_length) || 0;
                    itemRow.getCell(13).value = parseFloat(item.beveled_length) || 0;
                    itemRow.getCell(14).value = parseFloat(item.ban_rong_25_35) || 0;
                    itemRow.getCell(15).value = parseFloat(item.ban_rong_40_59) || 0;
                    itemRow.getCell(16).value = parseFloat(item.ban_rong_17_39) || 0;
                    itemRow.getCell(17).value = item.notes || '';
                    itemRow.getCell(18).value = item.direction || 0;
                    itemRow.getCell(19).value = parseFloat(item.vat_moi_length) || 0;
                    itemRow.getCell(20).value = parseFloat(item.beveled_handle) || 0;
                    itemRow.getCell(21).value = parseInt(item.cnc) || 0;

                    // Alignments
                    itemRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(3).alignment = { horizontal: 'left', vertical: 'middle' };
                    itemRow.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(5).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(7).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(8).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(9).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(10).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(11).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(12).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(13).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(14).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(15).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(16).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(17).alignment = { horizontal: 'left', vertical: 'middle' };
                    itemRow.getCell(18).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(19).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(20).alignment = { horizontal: 'center', vertical: 'middle' };
                    itemRow.getCell(21).alignment = { horizontal: 'center', vertical: 'middle' };

                    for (let c = 1; c <= maxCol; c++) {
                        const cell = itemRow.getCell(c);
                        cell.font = { name: 'Times New Roman', size: 11 };
                        cell.border = thinBorder;
                    }
                    currentRow++;
                });
            });

            // Separator
            worksheet.getRow(currentRow).height = 12;
            currentRow++;

            // Payment Details Header
            if (orderData.payment_details && orderData.payment_details.length > 0) {
                const payHeaderRow = worksheet.getRow(currentRow);
                payHeaderRow.height = 24;

                payHeaderRow.getCell(1).value = 'B';
                payHeaderRow.getCell(3).value = 'Tính giá bán sản phẩm, dịch vụ:';
                payHeaderRow.getCell(8).value = 'Đơn vị';
                payHeaderRow.getCell(11).value = 'Số lượng';
                payHeaderRow.getCell(12).value = 'Đơn giá';
                payHeaderRow.getCell(13).value = 'Đơn giá chỉ';
                payHeaderRow.getCell(17).value = 'THÀNH TIỀN';

                // Formatting headers (Using #366092 blue background with white bold text)
                const payHeaderFill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'FF366092' }
                };
                const pCols = [1, 3, 8, 11, 12, 13, 17];
                pCols.forEach(c => {
                    const cell = payHeaderRow.getCell(c);
                    cell.font = { name: 'Times New Roman', size: 11, bold: true, color: { argb: 'FFFFFFFF' } };
                    cell.fill = payHeaderFill;
                    cell.alignment = { horizontal: c === 3 ? 'left' : 'center', vertical: 'middle' };
                });

                // Set borders for entire payHeaderRow
                for (let c = 1; c <= maxCol; c++) {
                    const cell = payHeaderRow.getCell(c);
                    cell.border = thinBorder;
                    if (!pCols.includes(c)) {
                        cell.fill = payHeaderFill;
                    }
                }
                currentRow++;

                // Write Payment Rows
                orderData.payment_details.forEach((detail, detailIdx) => {
                    const row = worksheet.getRow(currentRow);
                    row.height = 20;

                    row.getCell(1).value = detailIdx + 1;
                    row.getCell(3).value = detail.name;
                    row.getCell(8).value = detail.unit || 'tấm';

                    row.getCell(11).value = parseFloat(detail.quantity) || 0;
                    row.getCell(12).value = parseFloat(detail.price) || 0;
                    row.getCell(12).numFmt = '#,##0';
                    row.getCell(13).value = parseFloat(detail.price_only) || 0;
                    row.getCell(13).numFmt = '#,##0';
                    row.getCell(17).value = parseFloat(detail.total) || 0;
                    row.getCell(17).numFmt = '#,##0';
                    row.getCell(17).font = { name: 'Times New Roman', size: 11, bold: true };

                    // Alignments
                    row.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(3).alignment = { horizontal: 'left', vertical: 'middle' };
                    row.getCell(8).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(11).alignment = { horizontal: 'center', vertical: 'middle' };
                    row.getCell(12).alignment = { horizontal: 'right', vertical: 'middle' };
                    row.getCell(13).alignment = { horizontal: 'right', vertical: 'middle' };
                    row.getCell(17).alignment = { horizontal: 'right', vertical: 'middle' };

                    for (let c = 1; c <= maxCol; c++) {
                        const cell = row.getCell(c);
                        if (c !== 17) cell.font = { name: 'Times New Roman', size: 11 };
                        cell.border = thinBorder;
                    }
                    currentRow++;
                });

                // Payment summary: TỔNG TIỀN HÀNG:
                const sumRow = worksheet.getRow(currentRow);
                sumRow.height = 22;
                sumRow.getCell(16).value = "TỔNG TIỀN HÀNG:";
                sumRow.getCell(16).font = { name: 'Times New Roman', size: 11, bold: true };
                sumRow.getCell(16).alignment = { horizontal: 'right', vertical: 'middle' };

                sumRow.getCell(17).value = parseFloat(orderData.total_amount);
                sumRow.getCell(17).font = { name: 'Times New Roman', size: 11, bold: true, color: { argb: 'FFDC2626' } };
                sumRow.getCell(17).numFmt = '#,##0';
                sumRow.getCell(17).alignment = { horizontal: 'right', vertical: 'middle' };

                for (let c = 1; c <= maxCol; c++) {
                    sumRow.getCell(c).border = {
                        top: { style: 'thin', color: { argb: 'FF9CA3AF' } },
                        bottom: { style: 'double', color: { argb: 'FF1F2937' } }
                    };
                }
                currentRow++;

                // spacer row
                worksheet.getRow(currentRow).height = 12;
                currentRow++;

                // Payment summary: Còn lại:
                const remainRow = worksheet.getRow(currentRow);
                remainRow.height = 22;
                remainRow.getCell(16).value = "Còn lại:";
                remainRow.getCell(16).font = { name: 'Times New Roman', size: 11, bold: true };
                remainRow.getCell(16).alignment = { horizontal: 'right', vertical: 'middle' };

                remainRow.getCell(17).value = parseFloat(orderData.total_amount);
                remainRow.getCell(17).font = { name: 'Times New Roman', size: 11, bold: true };
                remainRow.getCell(17).numFmt = '#,##0';
                remainRow.getCell(17).alignment = { horizontal: 'right', vertical: 'middle' };

                for (let c = 1; c <= maxCol; c++) {
                    remainRow.getCell(c).border = {
                        top: { style: 'thin', color: { argb: 'FF9CA3AF' } },
                        bottom: { style: 'double', color: { argb: 'FF1F2937' } }
                    };
                }
            }
        }

        // Auto fit row heights by clearing custom heights, and ensure wrap text is enabled on all cells
        worksheet.eachRow((row) => {
            row.height = null;
            row.eachCell({ includeEmpty: true }, (cell) => {
                if (!cell.alignment) {
                    cell.alignment = {};
                }
                cell.alignment.wrapText = true;
                if (!cell.alignment.vertical) {
                    cell.alignment.vertical = 'middle';
                }
            });
        });

        // Specific manual auto-fit for merged address row because Excel does not natively auto-fit merged cells
        const addressText = 'Địa chỉ: ' + (orderData.address || '');
        let addressRowNumber = 9;
        let addressMergedWidth = 96.5;

        if (orderData.type === 'glass') {
            addressRowNumber = 6;
            addressMergedWidth = 133.6;
        } else if (orderData.type === 'min_late') {
            addressRowNumber = 9;
            addressMergedWidth = 176.3;
        }

        const addressRowHeight = estimateRowHeight(addressText, addressMergedWidth);
        worksheet.getRow(addressRowNumber).height = addressRowHeight;

        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        saveAs(blob, `Bao_Gia_${orderData.order_code}.xlsx`);
    } catch (error) {
        console.error("Export error", error);
        alert("Có lỗi xảy ra khi xuất file Excel: " + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

async function getLogoBase64(url) {
    try {
        const response = await fetch(url);
        const blob = await response.blob();
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            const originalOnLoad = reader.onloadend;
            reader.onloadend = () => resolve(reader.result.split(',')[1]);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    } catch (e) {
        console.error("Failed to load logo", e);
        return null;
    }
}

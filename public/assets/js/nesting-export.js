function parseMullionMillingDetails(itemName) {
    const res = {
        offsetLeft: null, offsetRight: null, offsetTop: null, offsetBottom: null,
        millLeft: null, millRight: null, millTop: null, millBottom: null,
        millWidth: null, millDepth: null
    };
    if (!itemName) return res;

    const nameLower = itemName.toLowerCase();
    // Regex to match e.g. "kính đố 70 hèm s5r10" or "kính đố 70 hèm s5 r10"
    const match = nameLower.match(/kính\s+đố\s+(\d+)\s+hèm\s+s(\d+)r(\d+)/)
               || nameLower.match(/kính\s+đố\s+(\d+)\s+hèm\s+s(\d+)\s+r(\d+)/);

    if (match) {
        const border = parseInt(match[1]); // e.g. 70
        const depth = parseInt(match[2]);  // e.g. 5
        const width = parseInt(match[3]);  // e.g. 10

        res.offsetLeft = border;
        res.offsetRight = border;
        res.offsetTop = border;
        res.offsetBottom = border;

        const millOffset = border - width;
        res.millLeft = millOffset;
        res.millRight = millOffset;
        res.millTop = millOffset;
        res.millBottom = millOffset;

        res.millWidth = width;
        res.millDepth = (depth === 5) ? 6 : depth; // standard depth adjustment for s5 in sample is 6
    }
    return res;
}

/**
 * Build one BAZIS-PM nesting workbook and trigger download.
 * @param {Object} orderData   - Full order export data (window.orderExportData)
 * @param {Array}  suppliesData - Filtered supplies (each with their .items subset)
 * @param {string} filename    - Output .xlsx filename
 */
async function generateNestingWorkbook(orderData, suppliesData, filename) {
    const hasItems = suppliesData.some(s => s.items && s.items.length > 0);
    if (!hasItems) {
        console.warn(`[Nesting] No items for "${filename}", skipping.`);
        return null;
    }

    const wb = new ExcelJS.Workbook();
    const wsName = filename.toLowerCase().includes('phao') ? 'Nesting Phào' : 'Nesting';
    const ws = wb.addWorksheet(wsName);

    const thinBorder = {
        top: { style: 'thin', color: { argb: 'FFD1D5DB' } },
        left: { style: 'thin', color: { argb: 'FFD1D5DB' } },
        bottom: { style: 'thin', color: { argb: 'FFD1D5DB' } },
        right: { style: 'thin', color: { argb: 'FFD1D5DB' } }
    };
    const hdrFill = {
        type: 'pattern', pattern: 'solid',
        fgColor: { argb: 'FFF79646' }
    };

    // Column widths
    [
        ['A', 8], ['B', 30], ['C', 22], ['D', 26], ['E', 35], ['F', 52],
        ['G', 10], ['H', 10], ['I', 8], ['J', 14], ['K', 10],
        ['L', 16], ['M', 16], ['N', 16], ['O', 16],
        ['P', 14], ['Q', 14], ['R', 14], ['S', 14],
        ['T', 22],
        ['U', 20], ['V', 20], ['W', 20], ['X', 20],
        ['Y', 18], ['Z', 18], ['AA', 18], ['AB', 18], ['AC', 14], ['AD', 14],
        ['AE', 18], ['AF', 18], ['AG', 18], ['AH', 18], ['AI', 14], ['AJ', 14]
    ].forEach(([col, w]) => { ws.getColumn(col).width = w; });

    // Row 1 – Vietnamese headers (exact sample column order)
    const viHdr = [
        'Cắt', 'Sản phẩm', 'Đơn hàng', 'STT', 'Tên', 'Vật liệu',
        'Dài', 'Rộng', 'Dày', 'Chiều vân', 'Số lượng',
        'Kí hiệu nẹp L1', 'Kí hiệu nẹp L2', 'Kí hiệu nẹp W1', 'Kí hiệu nẹp W2',
        'Dày nẹp L1', 'Dày nẹp L2', 'Dày nẹp W1', 'Dày nẹp W2',
        'Ghi chú',
        'Bao trong-Dịch trái', 'Bao trong-Dịch phải', 'Bao trong-Dịch trên', 'Bao trong-Dịch dưới',
        'Xoi-Dịch trái-1', 'Xoi-Dịch phải-1', 'Xoi-Dịch trên-1', 'Xoi-Dịch dưới-1',
        'Xoi-Rộng-1', 'Xoi-Sâu-1',
        'Xoi-Dịch trái-2', 'Xoi-Dịch phải-2', 'Xoi-Dịch trên-2', 'Xoi-Dịch dưới-2',
        'Xoi-Rộng-2', 'Xoi-Sâu-2'
    ];
    const r1 = ws.getRow(1);
    r1.height = 20;
    viHdr.forEach((h, i) => {
        const c = r1.getCell(i + 1);
        c.value = h;
        c.font = { name: 'Times New Roman', size: 11, bold: true };
        c.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
        c.fill = hdrFill;
        c.border = thinBorder;
    });

    // Row 2 – English headers
    const enHdr = [
        'Cutting', 'Product Name', 'Order', 'Order Number', 'Detail Name', 'Material',
        'Height', 'Width', 'Thickness', 'Texture Orientation', 'Quantity',
        'Edge Banding L1', 'Edge Banding L2', 'Edge Banding W1', 'Edge Banding W2',
        'L1 Thickness', 'L2 Thickness', 'W1 Thickness', 'W2 Thickness',
        'Note',
        'Internal Cutting Left Offset', 'Internal Cutting Right Offset',
        'Internal Cutting Top Offset', 'Internal Cutting Bottom Offset',
        'Milling Left Offset 1', 'Milling Right Offset 1',
        'Milling Top Offset 1', 'Milling Bottom Offset 1',
        'Milling Width 1', 'Milling Depth 1',
        'Milling Left Offset 2', 'Milling Right Offset 2',
        'Milling Top Offset 2', 'Milling Bottom Offset 2',
        'Milling Width 2', 'Milling Depth 2'
    ];
    const r2 = ws.getRow(2);
    r2.height = 20;
    enHdr.forEach((h, i) => {
        const c = r2.getCell(i + 1);
        c.value = h;
        c.font = { name: 'Times New Roman', size: 11, bold: true };
        c.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
        c.fill = hdrFill;
        c.border = thinBorder;
    });

    // Data rows
    let rowIdx = 3;
    const custLabel = orderData.customer_name || '';
    const orderLabel = orderData.order_code || '';

    suppliesData.forEach(supply => {
        (supply.items || []).forEach(item => {
            const h = parseFloat((parseFloat(item.height) || 0).toFixed(2));
            const w = parseFloat((parseFloat(item.width) || 0).toFixed(2));

            // Determine edge banding: 'Vát XXX' → if XXX ≈ W → W2='V', if ≈ H → L2='V'
            const bevelText = (item.edge_bevel || '').replace(/[^0-9.]/g, '');
            const bevelVal = parseFloat(bevelText) || 0;
            let edgeL1 = 'T', edgeL2 = 'T', edgeW1 = 'T', edgeW2 = 'T';
            if (bevelVal > 0) {
                if (Math.abs(bevelVal - h) < 1) {
                    edgeL2 = 'V';   // bevel on the Height/Dài side
                } else {
                    edgeW2 = 'V';   // bevel on the Width/Rộng side (default)
                }
            }

            // Parse Mullion/Milling Details:
            // Prefer saved DB values; fall back to regex parser for legacy records.
            const hasDbParams = (item.offset_left !== null && item.offset_left !== undefined);
            let milling;
            if (hasDbParams) {
                milling = {
                    offsetLeft:   item.offset_left,
                    offsetRight:  item.offset_right,
                    offsetTop:    item.offset_top,
                    offsetBottom: item.offset_bottom,
                    millLeft:     item.mill_left,
                    millRight:    item.mill_right,
                    millTop:      item.mill_top,
                    millBottom:   item.mill_bottom,
                    millWidth:    item.mill_width,
                    millDepth:    item.mill_depth,
                    millLeft2:    item.mill_left_2,
                    millRight2:   item.mill_right_2,
                    millTop2:     item.mill_top_2,
                    millBottom2:  item.mill_bottom_2,
                    millWidth2:   item.mill_width_2,
                    millDepth2:   item.mill_depth_2,
                };
            } else {
                const parsed = parseMullionMillingDetails(item.notes || item.product_name || '');
                milling = {
                    ...parsed,
                    millLeft2: null, millRight2: null, millTop2: null,
                    millBottom2: null, millWidth2: null, millDepth2: null,
                };
            }

            const qty = parseInt(item.quantity) || 1;
            const codes = item.product_codes || [];

            for (let i = 0; i < qty; i++) {
                const sttCode = codes[i] || item.product_code || '';

                const row = ws.getRow(rowIdx++);
                row.height = 18;

                const itemCust = item.customer_name || supply.customer_name || custLabel;
                const itemOrder = item.order_code || supply.order_code || orderLabel;

                const vals = [
                    'V',                                                      // A Cắt
                    itemCust,                                                 // B Sản phẩm
                    itemOrder,                                                // C Đơn hàng
                    sttCode,                                                  // D STT
                    (item.notes && String(item.notes).trim() !== '') ? item.notes.trim() : '.', // E Tên (Detail Name) - Lấy từ Ghi chú (trống thì điền '.')
                    supply.supply_name || '',                                 // F Vật liệu
                    h,                                                        // G Dài
                    w,                                                        // H Rộng
                    (function() {
                        const thick = parseFloat(item.thickness);
                        if (!isNaN(thick)) {
                            return parseFloat((thick + 2).toFixed(2));
                        }
                        return 21;
                    })(), // I Dày (+2 mm)
                    (function() {
                        const gd = String(item.grain_direction || '').trim();
                        if (gd === '0' || gd === '') return 0;
                        return 2;
                    })(), // J Chiều vân (0 or empty -> 0, other -> 2)
                    1,                                                        // K Số lượng (always 1 per expanded sheet row)
                    edgeL1, edgeL2, edgeW1, edgeW2,                         // L M N O Nẹp
                    0.00001, 0.00001, 0.00001, 0.00001,                     // P Q R S Dày nẹp
                    item.edge_bevel || '',                                    // T Ghi chú
                    milling.offsetLeft, milling.offsetRight, milling.offsetTop, milling.offsetBottom, // U-X Bao trong
                    milling.millLeft, milling.millRight, milling.millTop, milling.millBottom,         // Y-AB Xoi 1
                    milling.millWidth, milling.millDepth,                                             // AC-AD Rộng/Sâu 1
                    milling.millLeft2 ?? null, milling.millRight2 ?? null,
                    milling.millTop2 ?? null, milling.millBottom2 ?? null,
                    milling.millWidth2 ?? null, milling.millDepth2 ?? null   // AE-AJ Xoi 2
                ];

                vals.forEach((v, idx) => {
                    const cell = row.getCell(idx + 1);
                    cell.value = (v === null) ? null : v;
                    cell.font = { name: 'Times New Roman', size: 11 };
                    cell.alignment = { horizontal: 'center', vertical: 'middle' };
                });
                // Left-align name & material
                row.getCell(5).alignment = { horizontal: 'left', vertical: 'middle' };
                row.getCell(6).alignment = { horizontal: 'left', vertical: 'middle' };
            }
        });
    });

    // Sheet 2: Kho DC (sheet DC phía sau, luôn vào chung trong file nesting)
    const wsDc = wb.addWorksheet('Kho DC', {
        views: [{ showGridLines: true }]
    });

        // 8 cột cơ bản (không có cột ID/STT)
        const dcCols = [
            { col: 'A', width: 34 }, // Tên vật tư
            { col: 'B', width: 22 }, // Mã ván / SKU
            { col: 'C', width: 14 }, // Chiều vân
            { col: 'D', width: 14 }, // Cao
            { col: 'E', width: 14 }, // Rộng
            { col: 'F', width: 14 }, // Số lượng
            { col: 'G', width: 14 }, // Vị trí
            { col: 'H', width: 26 }, // Ghi chú
        ];
        dcCols.forEach(c => { wsDc.getColumn(c.col).width = c.width; });

        // Dòng 1: Tiêu đề cột cơ bản nhất, không cần banner gộp ô
        const dcHeaders = [
            'Tên vật tư',
            'Mã ván / SKU',
            'Chiều vân',
            'Cao',
            'Rộng',
            'Số lượng',
            'Vị trí',
            'Ghi chú'
        ];
        const r1Dc = wsDc.getRow(1);
        r1Dc.height = 24;
        dcHeaders.forEach((h, i) => {
            const cell = r1Dc.getCell(i + 1);
            cell.value = h;
            cell.font = { name: 'Times New Roman', size: 11, bold: true };
            cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            cell.fill = hdrFill;
            cell.border = thinBorder;
        });

        const dcMatches = orderData.dc_stock_matches || [];
        let curDcRow = 2;

        if (dcMatches.length > 0) {
            dcMatches.forEach(m => {
                const row = wsDc.getRow(curDcRow);
                row.height = 20;

                // Tên vật tư
                row.getCell(1).value = m.supply_name || '—';
                row.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };

                // Mã ván / SKU
                row.getCell(2).value = m.dc_board_code || m.supply_code || '—';
                row.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };

                // Chiều vân: strictly 0 hoặc 2
                const gdVal = (m.grain_direction === 2 || m.grain_direction === '2' || m.grain_direction === 'Vân dọc' || m.grain_direction === 'Vân ngang') ? 2 : 0;
                row.getCell(3).value = gdVal;
                row.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };

                // Cao
                row.getCell(4).value = parseInt(m.dc_height) || 0;
                row.getCell(4).numFmt = '#,##0';
                row.getCell(4).alignment = { horizontal: 'right', vertical: 'middle' };

                // Rộng
                row.getCell(5).value = parseInt(m.dc_width) || 0;
                row.getCell(5).numFmt = '#,##0';
                row.getCell(5).alignment = { horizontal: 'right', vertical: 'middle' };

                // Số lượng
                row.getCell(6).value = parseInt(m.dc_quantity) || 1;
                row.getCell(6).numFmt = '#,##0';
                row.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };

                // Vị trí
                row.getCell(7).value = m.dc_location || '—';
                row.getCell(7).font = { name: 'Times New Roman', size: 11, bold: true, color: { argb: 'FF0D9488' } };
                row.getCell(7).alignment = { horizontal: 'center', vertical: 'middle' };

                // Ghi chú
                row.getCell(8).value = m.dc_note || '';
                row.getCell(8).alignment = { horizontal: 'left', vertical: 'middle' };

                for (let c = 1; c <= 8; c++) {
                    const cell = row.getCell(c);
                    if (c !== 7) {
                        cell.font = { name: 'Times New Roman', size: 11 };
                    }
                    cell.border = thinBorder;
                }

                curDcRow++;
            });

            // Dòng tổng cộng số tấm
            const sumRow = wsDc.getRow(curDcRow);
            sumRow.height = 22;
            wsDc.mergeCells(`A${curDcRow}:E${curDcRow}`);
            sumRow.getCell(1).value = 'TỔNG CỘNG SỐ TẤM DƯ TRONG KHO:';
            sumRow.getCell(1).font = { name: 'Times New Roman', size: 11, bold: true };
            sumRow.getCell(1).alignment = { horizontal: 'right', vertical: 'middle' };

            const totalQty = dcMatches.reduce((sum, item) => sum + (parseInt(item.dc_quantity) || 0), 0);
            sumRow.getCell(6).value = totalQty;
            sumRow.getCell(6).font = { name: 'Times New Roman', size: 11, bold: true, color: { argb: 'FFDC2626' } };
            sumRow.getCell(6).numFmt = '#,##0';
            sumRow.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };

            for (let c = 1; c <= 8; c++) {
                const cell = sumRow.getCell(c);
                cell.border = {
                    top: { style: 'thin', color: { argb: 'FF9CA3AF' } },
                    bottom: { style: 'double', color: { argb: 'FF1F2937' } }
                };
            }
        } else {
            const emptyRow = wsDc.getRow(curDcRow);
            emptyRow.height = 26;
            wsDc.mergeCells(`A${curDcRow}:H${curDcRow}`);
            const emptyCell = emptyRow.getCell(1);
            emptyCell.value = 'Không tìm thấy tấm dư nào trong kho DC phù hợp với đơn hàng.';
            emptyCell.font = { name: 'Times New Roman', size: 11, italic: true, color: { argb: 'FF6B7280' } };
            emptyCell.alignment = { horizontal: 'center', vertical: 'middle' };
            for (let c = 1; c <= 8; c++) {
                emptyRow.getCell(c).border = thinBorder;
            }
        }

    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    return blob;
}

/**
 * Export two nesting files for an acrylic order:
 *  - Nesting      : items where BOTH height > 70 AND width > 70
 *  - Nesting Phào : items where height <= 70 OR width <= 70
 * 
 * @param {Object} customData - Optional order data object. Defaults to window.orderExportData.
 * @param {boolean} returnBlobs - If true, returns Array of { fileName, blob }. Defaults to false.
 */
async function exportNestingFiles(customData = null, returnBlobs = false) {
    const orderData = customData || window.orderExportData;
    if (!orderData || orderData.type !== 'acrylic') {
        if (!customData) {
            alert('Chức năng xuất nesting chỉ dành cho đơn acrylic.');
        }
        return returnBlobs ? [] : null;
    }

    const nestingSupplies = [];
    const phaoSupplies = [];

    (orderData.supplies || []).forEach(supply => {
        const allItems = supply.items || [];
        const nestItems = allItems.filter(i => (parseFloat(i.height) || 0) > 70 && (parseFloat(i.width) || 0) > 70);
        const phaoItems = allItems.filter(i => (parseFloat(i.height) || 0) <= 70 || (parseFloat(i.width) || 0) <= 70);

        if (nestItems.length) nestingSupplies.push({ ...supply, items: nestItems });
        if (phaoItems.length) phaoSupplies.push({ ...supply, items: phaoItems });
    });

    const code = orderData.order_code || 'DH';
    const nestingFilename = `Nesting-${code}.xlsx`;
    const phaoFilename = `Nesting-Phao-${code}.xlsx`;

    const nestingBlob = await generateNestingWorkbook(orderData, nestingSupplies, nestingFilename);
    const phaoBlob = await generateNestingWorkbook(orderData, phaoSupplies, phaoFilename);

    if (returnBlobs) {
        const files = [];
        if (nestingBlob) files.push({ fileName: nestingFilename, blob: nestingBlob });
        if (phaoBlob) files.push({ fileName: phaoFilename, blob: phaoBlob });
        return files;
    }

    if (nestingBlob) {
        saveAs(nestingBlob, nestingFilename);
    }
    if (phaoBlob) {
        saveAs(phaoBlob, phaoFilename);
    }
}

/**
 * Xuất 2 file Nesting gộp cho toàn bộ Lệnh sản xuất (gồm nhiều đơn hàng Acrylic ghép lại)
 */
async function exportManufactureNestingFiles(moCode, ordersData) {
    if (!ordersData || !ordersData.length) {
        alert('Lệnh sản xuất này không có đơn hàng Acrylic nào để xuất Nesting.');
        return;
    }

    const nestingSupplies = [];
    const phaoSupplies = [];

    ordersData.forEach(orderData => {
        if (orderData.type !== 'acrylic' || !orderData.supplies) return;
        orderData.supplies.forEach(supply => {
            const allItems = supply.items || [];
            // Gắn thông tin đơn hàng và khách hàng vào từng item/supply
            const enrichedItems = allItems.map(item => ({
                ...item,
                order_code: orderData.order_code,
                customer_name: orderData.customer_name
            }));

            const nestItems = enrichedItems.filter(i => (parseFloat(i.height) || 0) > 70 && (parseFloat(i.width) || 0) > 70);
            const phaoItems = enrichedItems.filter(i => (parseFloat(i.height) || 0) <= 70 || (parseFloat(i.width) || 0) <= 70);

            if (nestItems.length) nestingSupplies.push({ ...supply, order_code: orderData.order_code, customer_name: orderData.customer_name, items: nestItems });
            if (phaoItems.length) phaoSupplies.push({ ...supply, order_code: orderData.order_code, customer_name: orderData.customer_name, items: phaoItems });
        });
    });

    if (nestingSupplies.length === 0 && phaoSupplies.length === 0) {
        alert('Không tìm thấy tấm Acrylic nào trong các đơn hàng của lệnh này.');
        return;
    }

    const dummyOrder = Object.assign({}, ordersData[0] || {});
    // Tổng hợp dc_stock_matches từ toàn bộ các đơn hàng trong lệnh sản xuất
    const allDcMatches = [];
    const seenDcKeys = new Set();
    ordersData.forEach(od => {
        (od.dc_stock_matches || []).forEach(m => {
            const key = `${m.supply_name}_${m.dc_board_code}_${m.dc_height}_${m.dc_width}_${m.dc_location}`;
            if (!seenDcKeys.has(key)) {
                seenDcKeys.add(key);
                allDcMatches.push(m);
            }
        });
    });
    dummyOrder.dc_stock_matches = allDcMatches;

    const code = moCode || 'LSX';
    const nestingBlob = await generateNestingWorkbook(dummyOrder, nestingSupplies, `Nesting-${code}.xlsx`);
    const phaoBlob = await generateNestingWorkbook(dummyOrder, phaoSupplies, `Nesting-Phao-${code}.xlsx`);

    if (nestingBlob) {
        saveAs(nestingBlob, `Nesting-${code}.xlsx`);
    }
    if (phaoBlob) {
        saveAs(phaoBlob, `Nesting-Phao-${code}.xlsx`);
    }
}

/**
 * Xuất Nesting khi chọn nhiều đơn:
 *  - Mỗi đơn xuất 1 file Nesting riêng (tấm > 70x70) kèm Sheet Kho DC của đơn đó
 *  - Toàn bộ tấm phào (<= 70) của TẤT CẢ các đơn được GỘP CHUNG VÀO 1 ĐƠN (1 file Nesting Phào duy nhất) kèm Sheet Kho DC tổng hợp
 *
 * @param {Array} ordersData - Danh sách data các đơn hàng
 * @param {boolean} returnBlobs - Nếu true, trả về mảng các { fileName, blob }
 */
async function exportBulkNestingFiles(ordersData, returnBlobs = false) {
    if (!ordersData || !ordersData.length) {
        return returnBlobs ? [] : null;
    }

    const acrylicOrders = ordersData.filter(o => o.type === 'acrylic');
    if (!acrylicOrders.length) {
        if (!returnBlobs) alert('Không có đơn hàng Acrylic nào trong các đơn đã chọn.');
        return returnBlobs ? [] : null;
    }

    const files = [];

    // 1. Xuất file Nesting chính (> 70x70) cho từng đơn hàng
    for (const orderData of acrylicOrders) {
        const nestSupplies = [];
        (orderData.supplies || []).forEach(supply => {
            const nestItems = (supply.items || []).filter(i => (parseFloat(i.height) || 0) > 70 && (parseFloat(i.width) || 0) > 70);
            if (nestItems.length > 0) {
                nestSupplies.push({ ...supply, items: nestItems });
            }
        });

        if (nestSupplies.length > 0) {
            const code = orderData.order_code || 'DH';
            const fileName = `Nesting-${code}.xlsx`;
            const blob = await generateNestingWorkbook(orderData, nestSupplies, fileName);
            if (blob) {
                files.push({ fileName, blob });
            }
        }
    }

    // 2. Gộp TOÀN BỘ tấm phào (<= 70) của TẤT CẢ các đơn vào 1 đơn / 1 file Nesting Phào duy nhất
    const allPhaoSupplies = [];
    const allDcMatches = [];
    const seenDcKeys = new Set();

    acrylicOrders.forEach(orderData => {
        // Thu thập Kho DC
        (orderData.dc_stock_matches || []).forEach(m => {
            const key = `${m.supply_name}_${m.dc_board_code}_${m.dc_height}_${m.dc_width}_${m.dc_location}`;
            if (!seenDcKeys.has(key)) {
                seenDcKeys.add(key);
                allDcMatches.push(m);
            }
        });

        // Thu thập các tấm phào
        (orderData.supplies || []).forEach(supply => {
            const allItems = supply.items || [];
            const phaoItems = allItems.filter(i => (parseFloat(i.height) || 0) <= 70 || (parseFloat(i.width) || 0) <= 70);
            if (phaoItems.length > 0) {
                // Gắn mã đơn và tên khách hàng vào từng tấm phào
                const enrichedPhaoItems = phaoItems.map(item => ({
                    ...item,
                    order_code: orderData.order_code,
                    customer_name: orderData.customer_name
                }));
                allPhaoSupplies.push({
                    ...supply,
                    order_code: orderData.order_code,
                    customer_name: orderData.customer_name,
                    items: enrichedPhaoItems
                });
            }
        });
    });

    if (allPhaoSupplies.length > 0) {
        const dummyOrder = {
            order_code: acrylicOrders.length === 1 ? (acrylicOrders[0].order_code || 'DH') : 'GOP-PHAO',
            customer_name: acrylicOrders.length === 1 ? (acrylicOrders[0].customer_name || '') : 'Nhiều khách hàng',
            dc_stock_matches: allDcMatches
        };

        let phaoFilename = '';
        if (acrylicOrders.length === 1) {
            phaoFilename = `Nesting-Phao-${acrylicOrders[0].order_code}.xlsx`;
        } else if (acrylicOrders.length <= 3) {
            const codesStr = acrylicOrders.map(o => o.order_code).filter(Boolean).join('_');
            phaoFilename = `Nesting-Phao-Gop-${codesStr}.xlsx`;
        } else {
            const firstCode = acrylicOrders[0].order_code || 'DH';
            const lastCode = acrylicOrders[acrylicOrders.length - 1].order_code || 'DH';
            phaoFilename = `Nesting-Phao-Gop-${firstCode}_den_${lastCode}.xlsx`;
        }

        const phaoBlob = await generateNestingWorkbook(dummyOrder, allPhaoSupplies, phaoFilename);
        if (phaoBlob) {
            files.push({ fileName: phaoFilename, blob: phaoBlob });
        }
    }

    if (returnBlobs) {
        return files;
    }

    for (const f of files) {
        saveAs(f.blob, f.fileName);
    }
    return files;
}

window.generateNestingWorkbook = generateNestingWorkbook;
window.exportNestingFiles = exportNestingFiles;
window.exportBulkNestingFiles = exportBulkNestingFiles;
window.exportManufactureNestingFiles = exportManufactureNestingFiles;

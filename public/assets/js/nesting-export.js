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
                const parsed = parseMullionMillingDetails(item.product_name || '');
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

                const vals = [
                    'V',                                                      // A Cắt
                    custLabel,                                                // B Sản phẩm
                    orderLabel,                                               // C Đơn hàng
                    sttCode,                                                  // D STT
                    item.product_name || '',                                  // E Tên
                    supply.supply_name || '',                                 // F Vật liệu
                    h,                                                        // G Dài
                    w,                                                        // H Rộng
                    (!isNaN(parseFloat(item.thickness)) ? parseFloat(parseFloat(item.thickness).toFixed(2)) : 19), // I Dày
                    (item.grain_direction !== null && item.grain_direction !== undefined
                        ? parseInt(item.grain_direction) : 0),               // J Chiều vân
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

    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    return blob;
}

/**
 * Export two nesting files for an acrylic order:
 *  - Nesting      : items where BOTH height > 200 AND width > 200
 *  - Nesting Phào : items where height <= 200 OR width <= 200
 */
async function exportNestingFiles() {
    const orderData = window.orderExportData;
    if (!orderData || orderData.type !== 'acrylic') {
        alert('Chức năng xuất nesting chỉ dành cho đơn acrylic.');
        return;
    }

    const nestingSupplies = [];
    const phaoSupplies = [];

    orderData.supplies.forEach(supply => {
        const allItems = supply.items || [];
        const nestItems = allItems.filter(i => (parseFloat(i.height) || 0) > 70 && (parseFloat(i.width) || 0) > 70);
        const phaoItems = allItems.filter(i => (parseFloat(i.height) || 0) <= 70 || (parseFloat(i.width) || 0) <= 70);

        if (nestItems.length) nestingSupplies.push({ ...supply, items: nestItems });
        if (phaoItems.length) phaoSupplies.push({ ...supply, items: phaoItems });
    });

    const code = orderData.order_code || 'DH';
    const nestingBlob = await generateNestingWorkbook(orderData, nestingSupplies, `Nesting-${code}.xlsx`);
    const phaoBlob = await generateNestingWorkbook(orderData, phaoSupplies, `Nesting-Phao-${code}.xlsx`);

    if (nestingBlob) {
        saveAs(nestingBlob, `Nesting-${code}.xlsx`);
    }
    if (phaoBlob) {
        saveAs(phaoBlob, `Nesting-Phao-${code}.xlsx`);
    }
}

window.exportNestingFiles = exportNestingFiles;

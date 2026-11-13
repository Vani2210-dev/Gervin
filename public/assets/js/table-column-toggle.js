/**
 * Table Column Visibility & Reorder (Drag & Drop)
 * Tùy chỉnh ẩn / hiện cột và kéo thả đổi thứ tự vị trí cột của bảng.
 * Lưu cấu hình (ẩn/hiện + thứ tự cột) vào localStorage của từng người dùng.
 * Sử dụng Portal / Fixed positioning cho dropdown và HTML5 Drag & Drop mượt mà.
 */
(function (window, document) {
    'use strict';

    const STORAGE_PREFIX_HIDDEN = 'gervin_table_cols_hidden_';
    const STORAGE_PREFIX_ORDER  = 'gervin_table_cols_order_';

    // Helper: chuẩn hoá và lấy tên cột hiển thị từ thẻ th
    function getCleanColumnName(th) {
        if (!th) return '';
        if (th.getAttribute('data-column-name')) {
            return th.getAttribute('data-column-name').trim();
        }

        // Tạo bản sao để lọc bỏ icon, checkbox, badge, font nhỏ
        const clone = th.cloneNode(true);
        const toRemove = clone.querySelectorAll(
            'input, button, select, iconify-icon, svg, ' +
            '[style*="font-size: 9px"], [style*="font-size: 10px"], ' +
            '.badge, .text-neutral-400, .text-secondary-light'
        );
        toRemove.forEach(el => el.remove());

        let name = clone.textContent.replace(/\s+/g, ' ').trim();
        if (!name) {
            name = th.textContent.replace(/\s+/g, ' ').trim();
        }
        return name;
    }

    // Helper: kiểm tra cột có được phép ẩn/hiện và sắp xếp hay không (cột cố định)
    function isColumnFixed(th, name) {
        if (!th) return true;
        if (th.hasAttribute('data-column-fixed') || th.hasAttribute('data-no-toggle')) {
            return true;
        }
        // Checkbox chọn nhiều
        if (th.querySelector('input[type="checkbox"]')) {
            return true;
        }
        // Cột STT, Thao tác, Hành động
        const lower = (name || '').toLowerCase();
        if (/^(stt|#|hành động|thao tác|action|actions)$/i.test(lower)) {
            return true;
        }
        return false;
    }

    // Helper: xây dựng ma trận vị trí cột cho thead (hỗ trợ cả header đơn dòng và đa dòng)
    function buildTheadMatrix(table) {
        const thead = table.querySelector('thead');
        if (!thead) return [];
        const trList = thead.querySelectorAll('tr');
        if (!trList.length) return [];

        const matrix = [];
        trList.forEach((tr, rIdx) => {
            let cIdx = 0;
            Array.from(tr.children).forEach(cell => {
                if (cell.tagName.toLowerCase() !== 'th') return;
                while (matrix[rIdx] && matrix[rIdx][cIdx]) {
                    cIdx++;
                }
                const rowspan = parseInt(cell.getAttribute('rowspan') || '1', 10);
                const colspan = parseInt(cell.getAttribute('colspan') || '1', 10);
                for (let r = 0; r < rowspan; r++) {
                    for (let c = 0; c < colspan; c++) {
                        const row = matrix[rIdx + r] || (matrix[rIdx + r] = []);
                        row[cIdx + c] = cell;
                    }
                }
                cIdx += colspan;
            });
        });
        return matrix;
    }

    // Gán thuộc tính data-col-key cho tất cả các ô th, td trong bảng để phục vụ reorder
    function tagTableCells(table, columns) {
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        const tfoot = table.querySelector('tfoot');

        // Gán thead
        if (thead) {
            const headerRow = thead.querySelector('tr:first-child');
            if (headerRow) {
                Array.from(headerRow.children).forEach((th, idx) => {
                    if (columns[idx]) {
                        th.setAttribute('data-col-key', columns[idx].key);
                    }
                });
            }
        }

        // Gán tbody
        if (tbody) {
            const trList = tbody.querySelectorAll('tr');
            trList.forEach(tr => {
                if (tr.children.length === columns.length) {
                    Array.from(tr.children).forEach((td, idx) => {
                        if (columns[idx]) {
                            td.setAttribute('data-col-key', columns[idx].key);
                        }
                    });
                }
            });
        }

        // Gán tfoot
        if (tfoot) {
            const trList = tfoot.querySelectorAll('tr');
            trList.forEach(tr => {
                if (tr.children.length === columns.length) {
                    Array.from(tr.children).forEach((td, idx) => {
                        if (columns[idx]) {
                            td.setAttribute('data-col-key', columns[idx].key);
                        }
                    });
                }
            });
        }
    }

    // Quét các cột của bảng
    function scanTableColumns(table) {
        const thead = table.querySelector('thead');
        if (!thead) return [];

        const trList = thead.querySelectorAll('tr');
        if (!trList.length) return [];

        const columns = [];
        const seenKeys = {};

        if (trList.length === 1) {
            // Header đơn dòng thông thường (98% các bảng)
            const thList = Array.from(trList[0].querySelectorAll('th'));
            thList.forEach((th, idx) => {
                const colIdx = idx + 1; // 1-based cho CSS :nth-child
                const name = getCleanColumnName(th);
                const fixed = isColumnFixed(th, name);

                let key = th.getAttribute('data-column-key') || name;
                if (!key) key = 'col_' + colIdx;
                if (seenKeys[key]) {
                    seenKeys[key]++;
                    key = `${key}_${seenKeys[key]}`;
                } else {
                    seenKeys[key] = 1;
                }

                columns.push({
                    colIdx: colIdx,
                    originalIndex: idx,
                    key: key,
                    name: name || `Cột ${colIdx}`,
                    fixed: fixed,
                    th: th
                });
            });
        } else {
            // Header đa dòng có rowspan / colspan (ví dụ: wood_boards, cnc_templates)
            const matrix = buildTheadMatrix(table);
            const totalCols = matrix[0] ? matrix[0].length : 0;
            const bottomRowIdx = matrix.length - 1;

            for (let cIdx = 0; cIdx < totalCols; cIdx++) {
                const colIdx = cIdx + 1;
                const cell = matrix[bottomRowIdx] ? matrix[bottomRowIdx][cIdx] : null;
                const name = getCleanColumnName(cell);
                const fixed = isColumnFixed(cell, name);

                let key = cell ? (cell.getAttribute('data-column-key') || name) : ('col_' + colIdx);
                if (!key) key = 'col_' + colIdx;
                if (seenKeys[key]) {
                    seenKeys[key]++;
                    key = `${key}_${seenKeys[key]}`;
                } else {
                    seenKeys[key] = 1;
                }

                columns.push({
                    colIdx: colIdx,
                    originalIndex: cIdx,
                    key: key,
                    name: name || `Cột ${colIdx}`,
                    fixed: fixed,
                    th: cell
                });
            }
        }

        tagTableCells(table, columns);
        return columns;
    }

    // Đọc danh sách key các cột đang bị ẩn từ localStorage
    function getHiddenKeys(tableId) {
        try {
            const raw = localStorage.getItem(STORAGE_PREFIX_HIDDEN + tableId);
            if (!raw) return [];
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    // Ghi danh sách key các cột đang bị ẩn vào localStorage
    function saveHiddenKeys(tableId, hiddenKeys) {
        try {
            if (!hiddenKeys || !hiddenKeys.length) {
                localStorage.removeItem(STORAGE_PREFIX_HIDDEN + tableId);
            } else {
                localStorage.setItem(STORAGE_PREFIX_HIDDEN + tableId, JSON.stringify(hiddenKeys));
            }
        } catch (e) {
            console.error('Không thể lưu cấu hình cột vào localStorage:', e);
        }
    }

    // Đọc thứ tự cột từ localStorage
    function getSavedOrder(tableId) {
        try {
            const raw = localStorage.getItem(STORAGE_PREFIX_ORDER + tableId);
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : null;
        } catch (e) {
            return null;
        }
    }

    // Ghi thứ tự cột vào localStorage
    function saveOrder(tableId, orderedKeys) {
        try {
            if (!orderedKeys || !orderedKeys.length) {
                localStorage.removeItem(STORAGE_PREFIX_ORDER + tableId);
            } else {
                localStorage.setItem(STORAGE_PREFIX_ORDER + tableId, JSON.stringify(orderedKeys));
            }
        } catch (e) {
            console.error('Không thể lưu thứ tự cột vào localStorage:', e);
        }
    }

    // Áp dụng thứ tự cột vào cấu trúc DOM của Table
    function applyTableDOMOrder(table, fullOrderKeys) {
        if (!table || !fullOrderKeys || !fullOrderKeys.length) return;

        // 1. Sắp xếp thead
        const theadTrList = table.querySelectorAll('thead tr');
        theadTrList.forEach(tr => {
            if (tr.children.length <= 1) return;
            fullOrderKeys.forEach(k => {
                const cell = tr.querySelector(`:scope > [data-col-key="${k}"]`);
                if (cell) tr.appendChild(cell);
            });
        });

        // 2. Sắp xếp tbody
        const tbodyTrList = table.querySelectorAll('tbody tr');
        tbodyTrList.forEach(tr => {
            if (tr.children.length <= 1) return; // Bỏ qua dòng rỗng hoặc phân mục có colspan
            if (!tr.querySelector(':scope > [data-col-key]')) return;
            fullOrderKeys.forEach(k => {
                const cell = tr.querySelector(`:scope > [data-col-key="${k}"]`);
                if (cell) tr.appendChild(cell);
            });
        });

        // 3. Sắp xếp tfoot
        const tfootTrList = table.querySelectorAll('tfoot tr');
        tfootTrList.forEach(tr => {
            if (tr.children.length <= 1) return;
            fullOrderKeys.forEach(k => {
                const cell = tr.querySelector(`:scope > [data-col-key="${k}"]`);
                if (cell) tr.appendChild(cell);
            });
        });
    }

    // Cập nhật lại chỉ số vị trí 1-based (colIdx) sau khi DOM được sắp xếp lại
    function syncColumnIndices(table, columns) {
        const thead = table.querySelector('thead');
        if (!thead) return;
        const thList = thead.querySelectorAll('tr:first-child th');
        thList.forEach((th, idx) => {
            const key = th.getAttribute('data-col-key');
            const col = columns.find(c => c.key === key);
            if (col) {
                col.colIdx = idx + 1; // 1-based cho CSS :nth-child
            }
        });
    }

    // Áp dụng CSS hiển thị / ẩn cho bảng
    function applyStyles(tableId, columns, hiddenKeys) {
        const styleId = `table-col-style-${tableId}`;
        let styleEl = document.getElementById(styleId);
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = styleId;
            document.head.appendChild(styleEl);
        }

        const hiddenSet = new Set(hiddenKeys);
        const hiddenCols = columns.filter(c => !c.fixed && hiddenSet.has(c.key));

        if (!hiddenCols.length) {
            styleEl.textContent = '';
            return;
        }

        const selectors = [];
        hiddenCols.forEach(c => {
            const idx = c.colIdx;
            // Áp dụng cho thẻ thead, tbody, tfoot
            selectors.push(
                `#${tableId} thead tr th:nth-child(${idx})`,
                `#${tableId} tbody tr td:not([colspan]):nth-child(${idx})`,
                `#${tableId} tfoot tr th:nth-child(${idx})`,
                `#${tableId} tfoot tr td:not([colspan]):nth-child(${idx})`
            );
        });

        styleEl.textContent = `${selectors.join(',\n')} {\n    display: none !important;\n}`;
    }

    // Định vị dropdown theo toạ độ viewport của nút bấm (Portal / Fixed position)
    function positionDropdown(toggleBtn, dropdown) {
        const rect = toggleBtn.getBoundingClientRect();
        const ddWidth = dropdown.offsetWidth || 260;
        const ddHeight = dropdown.offsetHeight || 320;

        // Canh theo chiều ngang: mép phải của menu trùng mép phải của nút
        let left = rect.right - ddWidth;

        // Tránh tràn viền trái màn hình
        if (left < 10) left = 10;
        // Tránh tràn viền phải màn hình
        if (left + ddWidth > window.innerWidth - 10) {
            left = window.innerWidth - ddWidth - 10;
        }

        // Canh theo chiều dọc: mặc định hiển thị bên dưới nút
        let top = rect.bottom + 6;

        // Nếu tràn viền dưới màn hình và phía trên có đủ chỗ -> lật lên phía trên
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;

        if (spaceBelow < ddHeight + 10 && spaceAbove > ddHeight + 10) {
            top = rect.top - ddHeight - 6;
        }

        dropdown.style.position = 'fixed';
        dropdown.style.top = `${Math.round(top)}px`;
        dropdown.style.left = `${Math.round(left)}px`;
        dropdown.style.zIndex = '99999';
    }

    // Đóng toàn bộ các dropdown đang mở
    function closeAllDropdowns() {
        document.querySelectorAll('.table-col-dropdown:not(.hidden)').forEach(d => {
            d.classList.add('hidden');
            const wrapper = d._wrapper;
            if (wrapper) {
                const btn = wrapper.querySelector('.table-col-toggle-btn');
                if (btn) btn.classList.remove('bg-neutral-100', 'border-primary-400', 'ring-2', 'ring-primary-100');
            }
        });
    }

    // Xây dựng danh sách đầy đủ các key (gồm cột cố định đầu, các cột tuỳ chỉnh theo thứ tự, và cột cố định cuối)
    function getFullOrderKeys(columns, toggleableKeysOrder) {
        const leadingFixed = [];
        const trailingFixed = [];
        const isPastToggleable = false;

        let seenToggleable = false;
        columns.forEach(c => {
            if (c.fixed) {
                if (!seenToggleable) {
                    leadingFixed.push(c.key);
                } else {
                    trailingFixed.push(c.key);
                }
            } else {
                seenToggleable = true;
            }
        });

        // Bổ sung các cột mới nếu có mà chưa nằm trong toggleableKeysOrder
        const finalToggleable = [...toggleableKeysOrder];
        columns.forEach(c => {
            if (!c.fixed && !finalToggleable.includes(c.key)) {
                finalToggleable.push(c.key);
            }
        });

        return [...leadingFixed, ...finalToggleable, ...trailingFixed];
    }

    // Khởi tạo tính năng kéo thả trên danh sách cột trong menu dropdown
    function initListDragAndDrop(listContainer, table, tableId, columns) {
        let draggedItem = null;

        listContainer.addEventListener('dragstart', function (e) {
            const item = e.target.closest('.col-item');
            if (!item) return;
            draggedItem = item;
            item.classList.add('opacity-40', 'bg-primary-50', 'ring-1', 'ring-primary-300');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.getAttribute('data-col-key'));
        });

        listContainer.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const targetItem = e.target.closest('.col-item');
            if (!targetItem || targetItem === draggedItem) return;

            // Xoá class chỉ báo cũ
            listContainer.querySelectorAll('.col-item').forEach(el => {
                el.classList.remove('border-t-2', 'border-b-2', 'border-primary-500');
            });

            // Xác định nửa trên hay nửa dưới
            const rect = targetItem.getBoundingClientRect();
            const midpoint = rect.top + rect.height / 2;
            if (e.clientY < midpoint) {
                targetItem.classList.add('border-t-2', 'border-primary-500');
            } else {
                targetItem.classList.add('border-b-2', 'border-primary-500');
            }
        });

        listContainer.addEventListener('dragleave', function (e) {
            const targetItem = e.target.closest('.col-item');
            if (targetItem) {
                targetItem.classList.remove('border-t-2', 'border-b-2', 'border-primary-500');
            }
        });

        listContainer.addEventListener('drop', function (e) {
            e.preventDefault();
            const targetItem = e.target.closest('.col-item');
            if (!targetItem || !draggedItem || targetItem === draggedItem) return;

            const rect = targetItem.getBoundingClientRect();
            const midpoint = rect.top + rect.height / 2;
            if (e.clientY < midpoint) {
                listContainer.insertBefore(draggedItem, targetItem);
            } else {
                listContainer.insertBefore(draggedItem, targetItem.nextSibling);
            }

            targetItem.classList.remove('border-t-2', 'border-b-2', 'border-primary-500');

            // Thu thập thứ tự mới từ DOM của danh sách
            const newToggleableKeys = Array.from(listContainer.querySelectorAll('.col-item')).map(el => el.getAttribute('data-col-key'));
            saveOrder(tableId, newToggleableKeys);

            // Cập nhật lại Table DOM
            const fullKeys = getFullOrderKeys(columns, newToggleableKeys);
            applyTableDOMOrder(table, fullKeys);
            syncColumnIndices(table, columns);

            // Cập nhật lại CSS hiển thị
            const hiddenKeys = getHiddenKeys(tableId);
            applyStyles(tableId, columns, hiddenKeys);
        });

        listContainer.addEventListener('dragend', function () {
            if (draggedItem) {
                draggedItem.classList.remove('opacity-40', 'bg-primary-50', 'ring-1', 'ring-primary-300');
                draggedItem = null;
            }
            listContainer.querySelectorAll('.col-item').forEach(el => {
                el.classList.remove('border-t-2', 'border-b-2', 'border-primary-500');
            });
        });
    }

    // Khởi tạo tính năng kéo thả trực tiếp trên thẻ <th> của bảng
    function initTableHeaderDragAndDrop(table, tableId, columns) {
        const thead = table.querySelector('thead');
        if (!thead) return;

        let draggedTh = null;

        thead.querySelectorAll('tr:first-child th').forEach(th => {
            const key = th.getAttribute('data-col-key');
            const col = columns.find(c => c.key === key);
            if (!col || col.fixed) return; // Không kéo thả cột cố định (STT, Hành động)

            th.setAttribute('draggable', 'true');
            th.classList.add('cursor-grab');
            th.setAttribute('title', 'Kéo thả để sắp xếp vị trí cột');

            th.addEventListener('dragstart', function (e) {
                draggedTh = this;
                this.classList.add('opacity-40', 'bg-primary-50');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', key);
            });

            th.addEventListener('dragover', function (e) {
                const targetKey = this.getAttribute('data-col-key');
                const targetCol = columns.find(c => c.key === targetKey);
                if (!targetCol || targetCol.fixed || this === draggedTh) return;

                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                thead.querySelectorAll('tr:first-child th').forEach(el => el.classList.remove('border-l-2', 'border-r-2', 'border-primary-500'));

                const rect = this.getBoundingClientRect();
                const midpoint = rect.left + rect.width / 2;
                if (e.clientX < midpoint) {
                    this.classList.add('border-l-2', 'border-primary-500');
                } else {
                    this.classList.add('border-r-2', 'border-primary-500');
                }
            });

            th.addEventListener('dragleave', function () {
                this.classList.remove('border-l-2', 'border-r-2', 'border-primary-500');
            });

            th.addEventListener('drop', function (e) {
                e.preventDefault();
                thead.querySelectorAll('tr:first-child th').forEach(el => el.classList.remove('border-l-2', 'border-r-2', 'border-primary-500'));

                const targetKey = this.getAttribute('data-col-key');
                const targetCol = columns.find(c => c.key === targetKey);
                if (!targetCol || targetCol.fixed || !draggedTh || this === draggedTh) return;

                // Lấy thứ tự hiện tại của các toggleable columns
                const headerRow = thead.querySelector('tr:first-child');
                const currentThs = Array.from(headerRow.children);
                const rect = this.getBoundingClientRect();
                const midpoint = rect.left + rect.width / 2;

                if (e.clientX < midpoint) {
                    headerRow.insertBefore(draggedTh, this);
                } else {
                    headerRow.insertBefore(draggedTh, this.nextSibling);
                }

                // Đọc lại thứ tự các toggleable columns
                const newOrderedThs = Array.from(headerRow.children);
                const newToggleableKeys = newOrderedThs
                    .map(h => h.getAttribute('data-col-key'))
                    .filter(k => {
                        const c = columns.find(x => x.key === k);
                        return c && !c.fixed;
                    });

                saveOrder(tableId, newToggleableKeys);

                // Đồng bộ tbody & tfoot
                const fullKeys = newOrderedThs.map(h => h.getAttribute('data-col-key'));
                applyTableDOMOrder(table, fullKeys);
                syncColumnIndices(table, columns);

                // Cập nhật lại dropdown checklist cho khớp thứ tự mới
                const wrapper = document.querySelector(`.table-column-toggle-wrapper[data-table-id="${tableId}"]`);
                if (wrapper && wrapper._dropdown) {
                    const hiddenKeys = getHiddenKeys(tableId);
                    renderComponent(wrapper, wrapper._dropdown, tableId, columns, hiddenKeys, table);
                }

                // Cập nhật CSS hiển thị
                const hiddenKeys = getHiddenKeys(tableId);
                applyStyles(tableId, columns, hiddenKeys);
            });

            th.addEventListener('dragend', function () {
                if (draggedTh) {
                    draggedTh.classList.remove('opacity-40', 'bg-primary-50');
                    draggedTh = null;
                }
                thead.querySelectorAll('tr:first-child th').forEach(el => el.classList.remove('border-l-2', 'border-r-2', 'border-primary-500'));
            });
        });
    }

    // Cập nhật giao diện component (nút, badge, danh sách checkbox)
    function renderComponent(wrapper, dropdown, tableId, columns, hiddenKeys, table) {
        // Đọc thứ tự đã lưu để hiển thị danh sách theo đúng thứ tự
        const savedOrder = getSavedOrder(tableId);
        let toggleableCols = columns.filter(c => !c.fixed);

        if (savedOrder && savedOrder.length) {
            const colMap = new Map(toggleableCols.map(c => [c.key, c]));
            const ordered = [];
            savedOrder.forEach(k => {
                if (colMap.has(k)) {
                    ordered.push(colMap.get(k));
                    colMap.delete(k);
                }
            });
            // Bổ sung những cột mới chưa có trong savedOrder
            colMap.forEach(c => ordered.push(c));
            toggleableCols = ordered;
        }

        const hiddenSet = new Set(hiddenKeys);

        // Badge cảnh báo có cột đang bị ẩn hoặc đã đổi thứ tự
        const badge = wrapper.querySelector('.col-toggle-badge');
        if (badge) {
            if (hiddenKeys.length > 0 || (savedOrder && savedOrder.length > 0)) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Thông tin số lượng hiển thị
        const countInfo = dropdown.querySelector('.col-count-info');
        if (countInfo) {
            const visibleCount = toggleableCols.length - hiddenKeys.length;
            countInfo.textContent = `Đang hiện ${visibleCount}/${toggleableCols.length} cột`;
        }

        // Render danh sách checkbox
        const listContainer = dropdown.querySelector('.col-list');
        if (!listContainer) return;

        listContainer.innerHTML = '';

        if (!toggleableCols.length) {
            listContainer.innerHTML = '<div class="px-3 py-4 text-center text-xs text-neutral-400">Không có cột nào để tùy chỉnh</div>';
            return;
        }

        toggleableCols.forEach(c => {
            const isVisible = !hiddenSet.has(c.key);
            const itemEl = document.createElement('div');
            itemEl.className = 'col-item flex items-center justify-between px-2.5 py-1.5 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-xs text-neutral-700 dark:text-neutral-300 select-none transition group rounded-lg';
            itemEl.setAttribute('data-col-key', c.key);
            itemEl.setAttribute('data-col-name', c.name.toLowerCase());
            itemEl.setAttribute('draggable', 'true');

            itemEl.innerHTML = `
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="col-drag-handle cursor-grab active:cursor-grabbing text-neutral-400 group-hover:text-neutral-600 hover:text-primary-600 p-0.5 rounded transition shrink-0" title="Kéo thả để sắp xếp vị trí cột">
                        <iconify-icon icon="lucide:grip-vertical" class="text-sm block"></iconify-icon>
                    </span>
                    <input type="checkbox"
                        class="col-checkbox w-4 h-4 rounded text-primary-600 border-neutral-300 dark:border-neutral-600 focus:ring-primary-500 focus:ring-offset-0 cursor-pointer shrink-0"
                        data-col-key="${c.key}"
                        ${isVisible ? 'checked' : ''}>
                    <span class="col-name-label truncate font-medium cursor-pointer flex-1 select-none" title="${c.name}">${c.name}</span>
                </div>
            `;

            // Bắt sự kiện tick / bỏ tick
            const checkbox = itemEl.querySelector('.col-checkbox');
            const nameLabel = itemEl.querySelector('.col-name-label');

            const toggleCheck = () => {
                let currentHidden = getHiddenKeys(tableId);
                const isChecked = checkbox.checked;
                if (isChecked) {
                    currentHidden = currentHidden.filter(k => k !== c.key);
                } else {
                    if (!currentHidden.includes(c.key)) {
                        currentHidden.push(c.key);
                    }
                }
                saveHiddenKeys(tableId, currentHidden);
                applyStyles(tableId, columns, currentHidden);
                renderComponent(wrapper, dropdown, tableId, columns, currentHidden, table);
            };

            checkbox.addEventListener('change', toggleCheck);
            nameLabel.addEventListener('click', () => {
                checkbox.checked = !checkbox.checked;
                toggleCheck();
            });

            listContainer.appendChild(itemEl);
        });

        // Khởi tạo drag & drop cho danh sách
        initListDragAndDrop(listContainer, table, tableId, columns);
    }

    // Khởi tạo toggle và reorder cho một bảng cụ thể
    function initTableColumnToggle(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const wrapper = document.querySelector(`.table-column-toggle-wrapper[data-table-id="${tableId}"]`);
        if (!wrapper) return;

        let dropdown = wrapper._dropdown || wrapper.querySelector('.table-col-dropdown');
        if (!dropdown) return;

        // Chuyển dropdown ra document.body (Portal) để thoát khỏi overflow:hidden của card
        if (dropdown.parentElement !== document.body) {
            wrapper._dropdown = dropdown;
            dropdown._wrapper = wrapper;
            dropdown.setAttribute('data-target-table', tableId);
            document.body.appendChild(dropdown);
        }

        // Quét cấu trúc cột nguyên bản của bảng
        const columns = scanTableColumns(table);

        // Áp dụng thứ tự đã lưu (nếu có)
        const savedOrder = getSavedOrder(tableId);
        if (savedOrder && savedOrder.length) {
            const fullKeys = getFullOrderKeys(columns, savedOrder);
            applyTableDOMOrder(table, fullKeys);
            syncColumnIndices(table, columns);
        }

        // Áp dụng ẩn/hiện cột đã lưu
        const hiddenKeys = getHiddenKeys(tableId);
        applyStyles(tableId, columns, hiddenKeys);

        // Render menu
        renderComponent(wrapper, dropdown, tableId, columns, hiddenKeys, table);

        // Khởi tạo drag & drop trực tiếp trên header <th>
        initTableHeaderDragAndDrop(table, tableId, columns);

        // Tránh gán sự kiện click/search nhiều lần
        if (wrapper.dataset.initialized === 'true') {
            return;
        }

        // Các thành phần tương tác
        const toggleBtn = wrapper.querySelector('.table-col-toggle-btn');
        const searchInput = dropdown.querySelector('.col-search-input');
        const resetBtn = dropdown.querySelector('.btn-reset-default');
        const showAllBtn = dropdown.querySelector('.btn-show-all');

        // Mở / Đóng dropdown
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isCurrentlyHidden = dropdown.classList.contains('hidden');

                // Đóng tất cả dropdown khác
                closeAllDropdowns();

                if (isCurrentlyHidden) {
                    dropdown.classList.remove('hidden');
                    positionDropdown(toggleBtn, dropdown);
                    toggleBtn.classList.add('bg-neutral-100', 'border-primary-400', 'ring-2', 'ring-primary-100');
                    if (searchInput) {
                        setTimeout(() => searchInput.focus(), 60);
                    }
                }
            });
        }

        // Ngăn chặn đóng dropdown khi click thao tác bên trong menu
        dropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        // Tìm kiếm cột
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                const items = dropdown.querySelectorAll('.col-list .col-item');
                items.forEach(item => {
                    const name = item.getAttribute('data-col-name') || '';
                    if (name.includes(query)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        // Nút "Mặc định": Khôi phục cả thứ tự ban đầu và hiển thị tất cả các cột
        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                // 1. Xoá lưu trữ
                saveHiddenKeys(tableId, []);
                saveOrder(tableId, []);

                // 2. Khôi phục thứ tự DOM theo originalIndex
                const originalOrder = [...columns].sort((a, b) => a.originalIndex - b.originalIndex).map(c => c.key);
                applyTableDOMOrder(table, originalOrder);
                syncColumnIndices(table, columns);

                // 3. Khôi phục hiển thị
                applyStyles(tableId, columns, []);

                // 4. Render lại menu
                renderComponent(wrapper, dropdown, tableId, columns, [], table);
            });
        }

        // Nút "Hiện tất cả"
        if (showAllBtn) {
            showAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                saveHiddenKeys(tableId, []);
                applyStyles(tableId, columns, []);
                renderComponent(wrapper, dropdown, tableId, columns, [], table);
            });
        }

        wrapper.dataset.initialized = 'true';
    }

    // Đóng dropdown khi click bên ngoài menu
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.table-col-dropdown') && !e.target.closest('.table-col-toggle-btn')) {
            closeAllDropdowns();
        }
    });

    // Đóng dropdown khi bấm phím ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });

    // Cập nhật vị trí khi resize màn hình
    window.addEventListener('resize', function () {
        document.querySelectorAll('.table-col-dropdown:not(.hidden)').forEach(dropdown => {
            const wrapper = dropdown._wrapper;
            if (wrapper) {
                const btn = wrapper.querySelector('.table-col-toggle-btn');
                if (btn) positionDropdown(btn, dropdown);
            }
        });
    });

    // Đóng dropdown khi cuộn trang ngoài dropdown (tránh menu trôi lơ lửng)
    window.addEventListener('scroll', function (e) {
        if (e.target && (e.target.classList?.contains('col-list') || e.target.closest?.('.table-col-dropdown'))) {
            return; // Đang cuộn danh sách cột bên trong menu thì không đóng
        }
        closeAllDropdowns();
    }, true);

    // Khởi tạo tất cả component trên trang
    function initAll() {
        document.querySelectorAll('.table-column-toggle-wrapper[data-table-id]').forEach(wrapper => {
            const tableId = wrapper.getAttribute('data-table-id');
            if (tableId) {
                initTableColumnToggle(tableId);
            }
        });
    }

    // Auto-init khi DOM sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Export API toàn cục
    window.TableColumnToggle = {
        init: initTableColumnToggle,
        initAll: initAll,
        getHiddenKeys: getHiddenKeys,
        saveHiddenKeys: saveHiddenKeys,
        getSavedOrder: getSavedOrder,
        saveOrder: saveOrder,
        closeAll: closeAllDropdowns
    };

})(window, document);

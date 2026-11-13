/**
 * Table Column Visibility Toggle
 * Tùy chỉnh ẩn / hiện cột của bảng và lưu cấu hình vào localStorage của từng người dùng.
 * Sử dụng Portal / Fixed positioning gắn trực tiếp vào document.body để chống cắt xén bởi overflow:hidden của card/table.
 */
(function (window, document) {
    'use strict';

    const STORAGE_PREFIX = 'gervin_table_cols_hidden_';

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

    // Helper: kiểm tra cột có được phép ẩn/hiện hay không (cố định)
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
                    key: key,
                    name: name || `Cột ${colIdx}`,
                    fixed: fixed,
                    th: cell
                });
            }
        }

        return columns;
    }

    // Đọc danh sách key các cột đang bị ẩn từ localStorage
    function getHiddenKeys(tableId) {
        try {
            const raw = localStorage.getItem(STORAGE_PREFIX + tableId);
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
                localStorage.removeItem(STORAGE_PREFIX + tableId);
            } else {
                localStorage.setItem(STORAGE_PREFIX + tableId, JSON.stringify(hiddenKeys));
            }
        } catch (e) {
            console.error('Không thể lưu cấu hình cột vào localStorage:', e);
        }
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
            // Lưu ý: :not([colspan]) giúp giữ nguyên hàng thông báo rỗng hoặc banner phân mục
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
        const ddWidth = dropdown.offsetWidth || 256;
        const ddHeight = dropdown.offsetHeight || 300;

        // Canh theo chiều ngang: mép phải của menu trùng mép phải của nút
        let left = rect.right - ddWidth;

        // Tránh tràn viền trái màn hình
        if (left < 10) {
            left = 10;
        }
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

    // Cập nhật giao diện component (nút, badge, danh sách checkbox)
    function renderComponent(wrapper, dropdown, tableId, columns, hiddenKeys) {
        const toggleableCols = columns.filter(c => !c.fixed);
        const hiddenSet = new Set(hiddenKeys);

        // Badge cảnh báo có cột đang bị ẩn
        const badge = wrapper.querySelector('.col-toggle-badge');
        if (badge) {
            if (hiddenKeys.length > 0) {
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
            const label = document.createElement('label');
            label.className = 'col-item flex items-center justify-between px-3 py-2 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 cursor-pointer text-xs text-neutral-700 dark:text-neutral-300 select-none transition';
            label.setAttribute('data-col-name', c.name.toLowerCase());

            label.innerHTML = `
                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                    <input type="checkbox"
                        class="col-checkbox w-4 h-4 rounded text-primary-600 border-neutral-300 dark:border-neutral-600 focus:ring-primary-500 focus:ring-offset-0 cursor-pointer"
                        data-col-key="${c.key}"
                        ${isVisible ? 'checked' : ''}>
                    <span class="truncate font-medium">${c.name}</span>
                </div>
            `;

            // Bắt sự kiện tick / bỏ tick
            const checkbox = label.querySelector('.col-checkbox');
            checkbox.addEventListener('change', function () {
                let currentHidden = getHiddenKeys(tableId);
                const isChecked = this.checked;
                if (isChecked) {
                    currentHidden = currentHidden.filter(k => k !== c.key);
                } else {
                    if (!currentHidden.includes(c.key)) {
                        currentHidden.push(c.key);
                    }
                }
                saveHiddenKeys(tableId, currentHidden);
                applyStyles(tableId, columns, currentHidden);
                renderComponent(wrapper, dropdown, tableId, columns, currentHidden);
            });

            listContainer.appendChild(label);
        });
    }

    // Khởi tạo toggle cho một bảng cụ thể
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

        // Tránh gán sự kiện nhiều lần
        if (wrapper.dataset.initialized === 'true') {
            const columns = scanTableColumns(table);
            const hiddenKeys = getHiddenKeys(tableId);
            applyStyles(tableId, columns, hiddenKeys);
            renderComponent(wrapper, dropdown, tableId, columns, hiddenKeys);
            return;
        }

        const columns = scanTableColumns(table);
        const hiddenKeys = getHiddenKeys(tableId);

        // Áp dụng CSS ẩn cột ngay lập tức để tránh giật giao diện
        applyStyles(tableId, columns, hiddenKeys);
        renderComponent(wrapper, dropdown, tableId, columns, hiddenKeys);

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

        // Nút "Mặc định"
        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                saveHiddenKeys(tableId, []);
                applyStyles(tableId, columns, []);
                renderComponent(wrapper, dropdown, tableId, columns, []);
            });
        }

        // Nút "Hiện tất cả"
        if (showAllBtn) {
            showAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                saveHiddenKeys(tableId, []);
                applyStyles(tableId, columns, []);
                renderComponent(wrapper, dropdown, tableId, columns, []);
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
        closeAll: closeAllDropdowns
    };

})(window, document);

/**
 * Antigravity DataTables Modern UI Loading & UX Enhancer
 * Option A: Ultra-Sleek Top Laser Progress Beam + Shimmer Skeleton Grid + Non-Intrusive Floating Micro-Chip
 */
(function (window, $) {
    'use strict';

    if (!$) return;

    // Option A: Non-intrusive bottom-center floating micro-chip
    const LOADER_HTML = `
        <div class="dt-loader-chip" role="status" aria-live="polite">
            <div class="dt-chip-spinner" aria-hidden="true"></div>
            <span class="dt-chip-text">Syncing data...</span>
        </div>
    `;

    /**
     * Generate column-adaptive skeleton row
     */
    function generateSkeletonRow($table, rowIndex) {
        const $ths = $table.find('thead th');
        const colCount = $ths.length || 6;
        let rowHtml = '<tr class="dt-skeleton-row" aria-hidden="true">';

        if ($ths.length) {
            $ths.each(function (i) {
                const title = ($(this).text() || '').trim().toLowerCase();
                let cellContent = '';

                if (title.includes('id') || title === '#') {
                    cellContent = '<span class="dt-skel-badge dt-skel-id"></span>';
                } else if (title.includes('image') || title.includes('photo') || title.includes('logo') || title.includes('thumb') || title.includes('icon')) {
                    cellContent = '<div class="dt-skel-avatar"></div>';
                } else if (title.includes('name') || title.includes('title') || title.includes('customer') || title.includes('product')) {
                    const width1 = ['78%', '85%', '70%', '80%', '75%'][rowIndex % 5];
                    const width2 = ['45%', '55%', '40%', '50%', '42%'][rowIndex % 5];
                    cellContent = `<div class="dt-skel-text-group">
                        <span class="dt-skel-bar dt-skel-title" style="width: ${width1};"></span>
                        <span class="dt-skel-bar dt-skel-subtitle" style="width: ${width2};"></span>
                    </div>`;
                } else if (title.includes('price') || title.includes('amount') || title.includes('total') || title.includes('cost')) {
                    cellContent = '<span class="dt-skel-bar dt-skel-price"></span>';
                } else if (title.includes('status') || title.includes('active') || title.includes('visibility')) {
                    cellContent = '<div class="dt-skel-toggle"></div>';
                } else if (title.includes('stock') || title.includes('qty') || title.includes('quantity')) {
                    cellContent = '<span class="dt-skel-badge dt-skel-stock"></span>';
                } else if (title.includes('category') || title.includes('brand') || title.includes('role') || title.includes('type')) {
                    cellContent = '<span class="dt-skel-badge dt-skel-cat"></span>';
                } else if (title.includes('action')) {
                    cellContent = '<div class="dt-skel-actions"><span class="dt-skel-btn"></span><span class="dt-skel-btn"></span></div>';
                } else if (title.includes('date') || title.includes('created') || title.includes('updated')) {
                    cellContent = '<span class="dt-skel-bar dt-skel-date"></span>';
                } else {
                    const widths = ['65%', '80%', '50%', '70%', '60%'];
                    const w = widths[(i + rowIndex) % widths.length];
                    cellContent = `<span class="dt-skel-bar" style="width: ${w};"></span>`;
                }

                const alignClass = $(this).hasClass('text-end') ? ' text-end' : ($(this).hasClass('text-center') ? ' text-center' : '');
                rowHtml += `<td class="${alignClass}">${cellContent}</td>`;
            });
        } else {
            for (let c = 0; c < colCount; c++) {
                rowHtml += `<td><span class="dt-skel-bar" style="width: 70%;"></span></td>`;
            }
        }

        rowHtml += '</tr>';
        return rowHtml;
    }

    /**
     * Injects realistic skeleton placeholders if table has no real rows
     */
    function injectRealisticSkeleton($table) {
        if (!$table || !$table.length) return;
        let $tbody = $table.find('tbody');
        if (!$tbody.length) {
            $table.append('<tbody></tbody>');
            $tbody = $table.find('tbody');
        }

        // If table already has real rows (not skeleton and not empty), do not overwrite
        const realRows = $tbody.find('tr:not(.dt-skeleton-row):not(:has(.dataTables_empty))');
        if (realRows.length > 0) {
            return;
        }

        let skeletonHtml = '';
        for (let r = 0; r < 5; r++) {
            skeletonHtml += generateSkeletonRow($table, r);
        }
        $tbody.html(skeletonHtml);
    }

    /**
     * Configure global DataTables defaults
     */
    function applyDataTableDefaults() {
        if (!$.fn || !$.fn.dataTable) return;

        // Suppress generic browser alert popups on Ajax error
        $.fn.dataTable.ext.errMode = 'none';

        if (!$.fn.dataTable.__customDefaultsApplied) {
            $.fn.dataTable.__customDefaultsApplied = true;
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    processing: LOADER_HTML,
                    loadingRecords: '&nbsp;'
                }
            });
        }
    }

    applyDataTableDefaults();

    // 1. Table initialization hook
    $(document).on('preInit.dt', function (e, settings) {
        applyDataTableDefaults();
        if (settings && settings.oLanguage) {
            settings.oLanguage.sProcessing = LOADER_HTML;
            settings.oLanguage.sLoadingRecords = '&nbsp;';
        }
        const $table = $(settings.nTable);
        injectRealisticSkeleton($table);
    });

    $(document).on('init.dt', function (e, settings) {
        const $wrapper = $(settings.nTableWrapper || settings.nTable).closest('.dataTables_wrapper, .dt-container, .table-responsive');
        const $proc = $(settings.nTableWrapper || settings.nTable).find('div.dataTables_processing, .dt-processing')
            .add($wrapper.find('div.dataTables_processing, .dt-processing'));
        if ($proc.length) {
            $proc.html(LOADER_HTML);
        }
    });

    // 2. Processing state toggling (Laser Beam + Dimming + Micro-Chip)
    $(document).on('processing.dt', function (e, settings, processing) {
        const $table = $(settings.nTable);
        const $wrapper = $(settings.nTableWrapper || settings.nTable).closest('.dataTables_wrapper, .dt-container, .table-responsive');
        const $proc = $(settings.nTableWrapper || settings.nTable).find('div.dataTables_processing, .dt-processing')
            .add($wrapper.find('div.dataTables_processing, .dt-processing'));

        if (processing) {
            $wrapper.addClass('dt-is-processing');
            $proc.removeClass('dt-hidden');
            $proc.html(LOADER_HTML);
            $proc.attr('style', 'display: flex !important; visibility: visible !important; opacity: 1 !important;');

            // If empty or only empty row, render skeleton
            const $tbody = $table.find('tbody');
            const hasRealRows = $tbody.find('tr:not(.dt-skeleton-row):not(:has(.dataTables_empty))').length > 0;
            if (!hasRealRows) {
                injectRealisticSkeleton($table);
            }
        } else {
            $wrapper.removeClass('dt-is-processing');
            $proc.addClass('dt-hidden');
            $proc.attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important;');
        }
    });

    // 3. Before Ajax request begins
    $(document).on('preXhr.dt', function (e, settings) {
        const $table = $(settings.nTable);
        const $tbody = $table.find('tbody');
        const hasRealRows = $tbody.find('tr:not(.dt-skeleton-row):not(:has(.dataTables_empty))').length > 0;
        if (!hasRealRows) {
            injectRealisticSkeleton($table);
        }
    });

    // 4. When data arrives and table redraws
    $(document).on('xhr.dt draw.dt', function (e, settings) {
        const $table = $(settings.nTable);
        const $tbody = $table.find('tbody');
        const realRows = $tbody.find('tr:not(.dt-skeleton-row):not(:has(.dataTables_empty))');
        if (realRows.length > 0) {
            $table.find('.dt-skeleton-row').remove();
        }

        const $wrapper = $(settings.nTableWrapper || settings.nTable).closest('.dataTables_wrapper, .dt-container, .table-responsive');
        $wrapper.removeClass('dt-is-processing');
        const $proc = $(settings.nTableWrapper || settings.nTable).find('div.dataTables_processing, .dt-processing')
            .add($wrapper.find('div.dataTables_processing, .dt-processing'));
        $proc.addClass('dt-hidden');
        $proc.attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important;');
    });

    // 5. Fail-safe Error Handling: never freeze on infinite loader
    $(document).on('error.dt', function (e, settings) {
        const $table = $(settings.nTable);
        const $wrapper = $(settings.nTableWrapper || settings.nTable).closest('.dataTables_wrapper, .dt-container, .table-responsive');
        $wrapper.removeClass('dt-is-processing');

        const $proc = $(settings.nTableWrapper || settings.nTable).find('div.dataTables_processing, .dt-processing')
            .add($wrapper.find('div.dataTables_processing, .dt-processing'));
        $proc.addClass('dt-hidden');
        $proc.attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important;');

        $table.find('.dt-skeleton-row').remove();

        const colCount = $table.find('thead th').length || 6;
        $table.find('tbody').html(`
            <tr class="dt-error-row">
                <td colspan="${colCount}" class="text-center py-5">
                    <div class="dt-table-error-state">
                        <div class="dt-table-error-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <h6 class="dt-table-error-title">Failed to load records</h6>
                        <p class="dt-table-error-desc">There was an issue communicating with the server. Please try refreshing.</p>
                        <button type="button" class="btn btn-sm btn-outline-primary dt-retry-btn">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reload Table
                        </button>
                    </div>
                </td>
            </tr>
        `);

        $table.find('.dt-retry-btn').on('click', function () {
            try {
                const dt = $table.DataTable();
                if (dt) dt.ajax.reload();
            } catch (err) {
                window.location.reload();
            }
        });
    });

    // Pre-populate skeleton in any table currently on DOM ready
    $(function () {
        applyDataTableDefaults();
        $('table.table').each(function () {
            const $tbl = $(this);
            if (!$tbl.find('tbody tr').length) {
                injectRealisticSkeleton($tbl);
            }
        });
    });

    // Global helper export
    window.adminDataTables = {
        applyDefaults: applyDataTableDefaults,
        injectSkeleton: injectRealisticSkeleton,
        loaderHtml: LOADER_HTML
    };

})(window, window.jQuery);

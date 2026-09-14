/**
 * Antigravity DataTables Modern Loading & UX Enhancer
 * Hybrid Skeleton Shimmer + Frosted Glass Processing System
 */
(function (window, $) {
    'use strict';

    const LOADER_HTML = `
        <div class="dt-loader-card">
            <div class="dt-spinner-ring">
                <div class="dt-spinner-inner"></div>
            </div>
            <div class="dt-loader-text">
                <span class="dt-loader-title">Updating data</span>
                <span class="dt-loader-dots"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    `;

    function injectSkeletonIfEmpty($table) {
        if (!$table || !$table.length) return;
        const $tbody = $table.find('tbody');
        if (!$tbody.length) {
            $table.append('<tbody></tbody>');
        }
        const $actualTbody = $table.find('tbody');
        
        // If it already has real data rows, don't overwrite
        const realRows = $actualTbody.find('tr:not(.dt-skeleton-row)');
        if (realRows.length > 0 && !$actualTbody.find('.dataTables_empty').length) {
            return;
        }

        const thCount = $table.find('thead th').length || 6;
        let skeletonRows = '';
        for (let i = 0; i < 6; i++) {
            skeletonRows += '<tr class="dt-skeleton-row">';
            for (let c = 0; c < thCount; c++) {
                const barType = (c % 6) + 1;
                skeletonRows += `<td><div class="dt-skeleton-bar dt-skeleton-bar-${barType}"></div></td>`;
            }
            skeletonRows += '</tr>';
        }
        $actualTbody.html(skeletonRows);
    }

    function applyDataTableDefaults() {
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
            return;
        }
        const $ = window.jQuery;
        if (!$.fn.dataTable.__customDefaultsApplied) {
            $.fn.dataTable.__customDefaultsApplied = true;
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    processing: LOADER_HTML
                }
            });
        }
    }

    // Attach to document events (delegated so works with any dynamically created table)
    if (window.jQuery) {
        const $ = window.jQuery;

        // Apply defaults whenever DataTables is available
        applyDataTableDefaults();

        // 1. When a table initializes
        $(document).on('preInit.dt', function (e, settings) {
            applyDataTableDefaults();
            const $table = $(settings.nTable);
            injectSkeletonIfEmpty($table);
        });

        // 2. Processing state toggling
        $(document).on('processing.dt', function (e, settings, processing) {
            const $wrapper = $(settings.nTableWrapper || settings.nTable).closest('.dataTables_wrapper, .dt-container, .table-responsive');
            const $proc = $(settings.nTableWrapper || settings.nTable).find('.dataTables_processing, .dt-processing')
                .add($wrapper.find('.dataTables_processing, .dt-processing'));

            if (processing) {
                $wrapper.addClass('dt-is-processing');
                if ($proc.length && !$proc.find('.dt-loader-card').length) {
                    $proc.html(LOADER_HTML);
                }
            } else {
                $wrapper.removeClass('dt-is-processing');
            }
        });

        // 3. Before Ajax request begins
        $(document).on('preXhr.dt', function (e, settings) {
            const $table = $(settings.nTable);
            const $tbody = $table.find('tbody');
            if ($tbody.children('tr').length === 0 || $tbody.find('.dataTables_empty').length > 0) {
                injectSkeletonIfEmpty($table);
            }
        });

        // 4. When data arrives and table redraws
        $(document).on('xhr.dt draw.dt', function (e, settings) {
            const $table = $(settings.nTable);
            $table.find('.dt-skeleton-row').remove();
            const $wrapper = $(settings.nTableWrapper || settings.nTable).closest('.dataTables_wrapper, .dt-container, .table-responsive');
            $wrapper.removeClass('dt-is-processing');
        });

        // On DOM ready, auto-populate skeleton in any table intended for DataTables that is currently empty
        $(function () {
            applyDataTableDefaults();
            $('table.table').each(function () {
                const $tbl = $(this);
                if (!$tbl.find('tbody tr').length) {
                    injectSkeletonIfEmpty($tbl);
                }
            });
        });
    }

    // Export helper to global scope
    window.adminDataTables = {
        applyDefaults: applyDataTableDefaults,
        injectSkeleton: injectSkeletonIfEmpty,
        loaderHtml: LOADER_HTML
    };

})(window, window.jQuery);

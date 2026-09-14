(function () {
    'use strict';

    var ENHANCE_MATCH = '.admin-select, .form-select, .form-control';
    var LENGTH_CONTAINER = '.dt-length, .dataTables_length';

    function isLengthMenu(el) {
        return !!el.closest(LENGTH_CONTAINER) || el.classList.contains('dt-input');
    }

    function shouldEnhance(el) {
        if (!el || el.tagName !== 'SELECT') return false;
        if (el.tomselect || el.dataset.tsInit === '1') return false;
        if (el.matches('[data-no-enhance]')) return false;
        return el.matches(ENHANCE_MATCH) || isLengthMenu(el);
    }

    function resolvePlaceholder(el) {
        if (el.dataset.placeholder) {
            return el.dataset.placeholder;
        }
        var opt = el.querySelector('option[value=""][disabled]') || el.querySelector('option[value=""]');
        return opt ? opt.textContent.trim() : null;
    }

    function shouldSearch(el) {
        var attr = el.getAttribute('data-search');
        if (attr === 'true') return true;
        if (attr === 'false') return false;
        return el.querySelectorAll('option').length > 8;
    }

    function enhance(el) {
        if (!shouldEnhance(el)) {
            return;
        }
        var lengthMenu = isLengthMenu(el);
        var settings = {
            create: false,
            allowEmptyOption: false,
            sortField: [{ field: '$order' }],
            searchField: ['text', 'value'],
            maxOptions: null,
            dropdownParent: 'body',
        };

        if (lengthMenu) {
            settings.controlInput = null;
            settings.placeholder = null;
            settings.dropdownClass = 'ts-dropdown ts-length-dropdown';
            settings.onChange = function (val) {
                if (window.jQuery) {
                    window.jQuery(el).val(val).trigger('change');
                } else {
                    el.value = val;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };
        } else {
            settings.placeholder = resolvePlaceholder(el);
            settings.allowEmptyOption = true;
            if (!shouldSearch(el)) {
                settings.controlInput = null;
            }
        }

        try {
            var ts = new TomSelect(el, settings);
            el.dataset.tsInit = '1';
            if (lengthMenu && ts && ts.wrapper) {
                ts.wrapper.classList.add('ts-length');
                // Ensure clicking anywhere on the custom length control opens the dropdown
                ts.control.addEventListener('click', function () {
                    if (!ts.isOpen) {
                        ts.open();
                    }
                });
            }
        } catch (e) {
            if (window.console) {
                console.warn('admin-select: enhancement skipped', e);
            }
        }
    }

    function enhanceWithin(root) {
        var scope = root && root.querySelectorAll ? root : document;
        if (scope.matches && scope.matches('select')) {
            enhance(scope);
        }
        scope.querySelectorAll('select').forEach(enhance);
    }

    function initAll(root) {
        if (typeof TomSelect === 'undefined') {
            return;
        }
        enhanceWithin(root || document);
    }
    function observe() {
        if (typeof MutationObserver === 'undefined' || !document.body) {
            return;
        }
        var observer = new MutationObserver(function (mutations) {
            if (typeof TomSelect === 'undefined') return;
            mutations.forEach(function (m) {
                m.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return; // elements only
                    enhanceWithin(node);
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
    window.initAdminSelects = initAll;

    function boot() {
        initAll(document);
        observe();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();

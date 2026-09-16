(function () {
    'use strict';

    var submitTimers = new WeakMap();
    var typingDelay = 450;
    var autoFilterSelector = [
        'input[name="search"]',
        'input[name="date_from"]',
        'input[name="date_to"]',
        'input[name="fromdate"]',
        'input[name="todate"]',
        'input[name="from"]',
        'input[name="to"]',
        'select[name="agent_id"]',
        'select[name="manager"]',
        'select[name="manager_id"]',
        'select[name="client_id"]',
        'select[name="type"]',
        'select[name="store"]',
        'select[name="warehouse_id"]'
    ].join(',');

    function normalizeMethod(form) {
        return (form.getAttribute('method') || 'GET').toUpperCase();
    }

    function isSafeAutoFilterForm(form) {
        if (!form || form.dataset.noAutoFilter === 'true') {
            return false;
        }

        if (form.dataset.autoFilter === 'true') {
            return true;
        }

        if (normalizeMethod(form) !== 'GET') {
            return false;
        }

        return !!form.querySelector(autoFilterSelector);
    }

    function initAutoFilterForms() {
        document.querySelectorAll('form').forEach(function (form) {
            if (!isSafeAutoFilterForm(form)) {
                return;
            }

            form.dataset.autoFilter = 'true';
            form.classList.add('dashboard-filter-form');

            form.querySelectorAll('input[type="text"].date-picker, input[name="fromdate"], input[name="todate"], input[name="date_from"], input[name="date_to"]').forEach(function (field) {
                field.classList.add('js-open-picker');
            });
        });
    }

    function createIcon(iconName) {
        var icon = document.createElement('em');
        icon.className = 'icon ni ni-' + iconName;

        return icon;
    }

    function actionConfig(link) {
        var text = (link.textContent || '').trim().toLowerCase();
        var href = (link.getAttribute('href') || '').toLowerCase();

        if (!text || link.querySelector('img, em, svg') || link.classList.contains('btn')) {
            return null;
        }

        if (/(редакт|o'zgart|ўзгарт|tahrir|edit)/i.test(text)) {
            return { icon: 'edit', style: 'btn-outline-primary', confirm: false };
        }

        if (/(удал|delete|o'chir|ўчир|ochir)/i.test(text) || /delete|status/.test(href)) {
            return { icon: 'trash', style: 'btn-outline-danger', confirm: true, message: 'confirm_delete' };
        }

        if (/(excel|xls|юклаб|yuklab|скач|download)/i.test(text) || /excel|download/.test(href)) {
            return { icon: 'download', style: 'btn-outline-success', confirm: false };
        }

        if (/(pdf|print|печать|chop|чоп|ko'rish|кўриш|посмотреть|view)/i.test(text) || /print|pdf|check/.test(href)) {
            return { icon: 'eye', style: 'btn-outline-primary', confirm: false };
        }

        return null;
    }

    function initDashboardEnhancements() {
        document.querySelectorAll('.nk-content table.table').forEach(function (table) {
            if (table.closest('.no-ui-enhance, .print-page, [data-no-ui-enhance="true"]')) {
                return;
            }

            table.classList.add('dashboard-data-table');
            var card = table.closest('.card');

            if (card) {
                card.classList.add('dashboard-table-card');
            }
        });

        document.querySelectorAll('.nk-content .table td a[href]').forEach(function (link) {
            var config = actionConfig(link);

            if (!config) {
                return;
            }

            var label = (link.textContent || '').trim();
            link.textContent = '';
            link.classList.add('btn', 'btn-icon', 'btn-sm', config.style);
            link.setAttribute('title', link.getAttribute('title') || label);
            link.setAttribute('aria-label', link.getAttribute('aria-label') || label);
            link.appendChild(createIcon(config.icon));
            var cell = link.closest('td');
            if (cell) {
                cell.classList.add('table-actions');
            }

            if (config.confirm && !link.hasAttribute('data-confirm')) {
                link.setAttribute('data-confirm', document.documentElement.lang === 'uz'
                    ? "Rostdan ham shu amalni bajarmoqchimisiz?"
                    : 'Вы действительно хотите выполнить это действие?');
            }
        });

        document.querySelectorAll('.nk-content form').forEach(function (form) {
            if (form.closest('.no-ui-enhance, [data-no-ui-enhance="true"]')) {
                return;
            }

            form.classList.add('dashboard-form');
        });
    }

    function submitForm(form, delay) {
        if (!form || form.dataset.submitting === '1') {
            return;
        }

        clearTimeout(submitTimers.get(form));
        submitTimers.set(form, setTimeout(function () {
            form.dataset.submitting = '1';
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }, delay));
    }

    function submitFormDirect(form, delay) {
        if (!form || form.dataset.submitting === '1') {
            return;
        }

        clearTimeout(submitTimers.get(form));
        submitTimers.set(form, setTimeout(function () {
            form.dataset.submitting = '1';
            HTMLFormElement.prototype.submit.call(form);
        }, delay));
    }

    document.addEventListener('input', function (event) {
        var field = event.target;
        var form = field.closest('form[data-auto-filter="true"]');

        if (!form || field.matches('select, [type="date"], [type="hidden"]')) {
            return;
        }

        submitForm(form, typingDelay);
    });

    document.addEventListener('DOMContentLoaded', function () {
        initAutoFilterForms();
        initDashboardEnhancements();
    });

    document.addEventListener('change', function (event) {
        var field = event.target;
        var form = field.closest('form[data-auto-filter="true"]');

        if (!form) {
            return;
        }

        submitForm(form, 120);
    });

    if (window.jQuery) {
        window.jQuery(document).on('change select2:select select2:clear', 'form[data-auto-filter="true"] select', function () {
            submitFormDirect(this.closest('form[data-auto-filter="true"]'), 220);
        });
    }

    document.addEventListener('click', function (event) {
        var field = event.target.closest('input[type="date"].js-open-picker');

        if (!field || typeof field.showPicker !== 'function') {
            return;
        }

        try {
            field.showPicker();
        } catch (error) {
            field.focus();
        }
    });

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-confirm]');

        if (!trigger) {
            return;
        }

        if (!window.confirm(trigger.dataset.confirm)) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
        }
    });
})();

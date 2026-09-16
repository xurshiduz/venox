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

    document.addEventListener('DOMContentLoaded', initAutoFilterForms);

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

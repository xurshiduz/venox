(function () {
    'use strict';

    var submitTimers = new WeakMap();
    var typingDelay = 450;

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

    document.addEventListener('input', function (event) {
        var field = event.target;
        var form = field.closest('form[data-auto-filter="true"]');

        if (!form || field.matches('select, [type="date"], [type="hidden"]')) {
            return;
        }

        submitForm(form, typingDelay);
    });

    document.addEventListener('change', function (event) {
        var field = event.target;
        var form = field.closest('form[data-auto-filter="true"]');

        if (!form) {
            return;
        }

        submitForm(form, 120);
    });

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
})();
